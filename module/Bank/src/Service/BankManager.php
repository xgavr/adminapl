<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 */
namespace Bank\Service;

use Bank\Entity\Balance;
use Bank\Entity\Statement;
use Company\Entity\BankAccount;
use Bank\Filter\Statement1cToArray;
use Bank\Filter\ConvertStatement1c;
use Application\Filter\CsvDetectDelimiterFilter;
use Application\Filter\RawToStr;
use Application\Filter\Basename;
use Bank\Entity\Acquiring;
use Bank\Entity\AplPayment;
use Application\Filter\ToFloat;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Description of BankManager
 *
 * @author Daddy
 */
class BankManager 
{
    const STATEMENTS_DIR       = './data/statements/'; // папка с файлами выписок
    const STATEMENTS_ARCH_DIR       = './data/statements/arch'; // папка с архивом файлами выписок
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Tochka Statetment manager
     * @var \Bankapi\Service\Tochka\Statement
     */
    private $tochkaStatement;
    
    /**
     * AdminManager manager
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;

    /**
     * PostManager manager
     * @var \Admin\Service\PostManager
     */
    private $postManager;

    /**
     * CostManager manager
     * @var \Company\Service\CostManager
     */
    private $costManager;

    /**
     * GigaManager manager
     * @var \Ai\Service\GigaManager
     */
    private $gigaManager;

    public function __construct($entityManager, $tochkaStatement, $adminManager, 
            $postManager, $costManager, $gigaManager)
    {
        $this->entityManager = $entityManager;
        $this->tochkaStatement = $tochkaStatement;    
        $this->adminManager = $adminManager;
        $this->postManager = $postManager;
        $this->costManager = $costManager;
        $this->gigaManager = $gigaManager;
        
        if (!is_dir(self::STATEMENTS_DIR)){
            mkdir(self::STATEMENTS_DIR);
        }

        if (!is_dir(self::STATEMENTS_ARCH_DIR)){
            mkdir(self::STATEMENTS_ARCH_DIR);
        }
    }

    /**
     * Добавление новой или обновлние записи остатков на счете
     * @param array $data
     * @return \Bank\Entity\Balance 
     */
    public function addNewOrUpdateBalance($data)
    {
        $balance = $this->entityManager->getRepository(Balance::class)
                ->findOneBy(['bik' => $data['bik'], 'account' => $data['account'], 'dateBalance' => $data['dateBalance']]);
        
        if ($balance){
            $balance->setBalance($data['balance']);
        } else {
                
            $balance = new Balance();
            $balance->setBik($data['bik']);
            $balance->setAccount($data['account']);
            $balance->setDateBalance($data['dateBalance']);
            $balance->setBalance($data['balance']);
        }    
        
        $this->entityManager->persist($balance);
        $this->entityManager->flush();
        
        return $balance;
    }
    
    /**
     * Удаление записи остатка
     * @param \Bank\Entity\Balance $balance
     */
    public function removeBalance($balance) 
    {
        $this->entityManager->remove($balance);
        $this->entityManager->flush();
    }
    
    /**
     * Добавление или обновление строки выписки
     * @param array $data
     * @return Statement
     */
    public function addNewOrUpdateStatement($data)
    {
        $statement = $this->entityManager->getRepository(Statement::class)
                ->findOneBy([
                    'account' => $data['account'],
                    'counterpartyInn' => $data['counterparty_inn'],
                    'counterpartyAccountNumber' => $data['counterparty_account_number'],
                    'paymentNumber' => $data['payment_number'],
                    'bankSystemId' => $data['payment_bank_system_id'],
                    'paymentDate' => date('Y-m-d', strtotime($data['payment_date'])),
                    'chargeDate' => date('Y-m-d', strtotime($data['payment_charge_date'])),
                ]);
        
        if ($statement){
            $data['swap1'] = $statement->getSwap1();
        } else {
            $statement = new Statement(); 
            $statement->setPay(Statement::PAY_NEW);
            $statement->setCashDoc(null);
        }
        
        $filter = new \Laminas\Filter\Word\SeparatorToCamelCase('_');
        $methods = get_class_methods($statement);
        foreach ($data as $key => $value){
            $func = 'set'.ucfirst($filter->filter($key));
            if (in_array($func, $methods)){
                $statement->$func($value);
            }
        }
        
        $this->entityManager->persist($statement);
        $this->entityManager->flush($statement);
    }
    
    /**
     * Обновление метки обмена
     * 
     * @param Statement $statement
     * @param integer $swap
     */
    public function updateStatementSwap($statement, $swap)
    {
        $statement->setSwap1($swap);
        $this->entityManager->persist($statement);
        $this->entityManager->flush();
        return;
    }
    
    /**
     * Получить комиссию из назначения
     * @param Statement $statement
     */
    public function acquiringCommissionFromPurpose($statement)
    {
        $messages[] = [
            'role' => 'system',
            'content' => 'Какова сумма комиссии? Ответь числом',
        ];
        $messages[] = [
            'role' => 'user',
            'content' => $statement->getPaymentPurpose(),
        ];
        
        $result = $this->gigaManager->completions($messages);
        var_dump($result);
        if (!empty($result['choices'])){
            foreach ($result['choices'] as $choice){
                if (!empty($choice['message']['content'])){
                    return (float) $choice['message']['content'];
                }    
            }
        }
        
        return 0;
    }
    
    /**
     * Обновление вида операции
     * 
     * @param Statement $statement
     * @param integer $kind
     */
    public function updateStatementKind($statement, $kind)
    {
        $statement->setKind($kind);
        
        if ($kind == Statement::KIND_IN_CART && empty($statement->getAmountService())){
            $statement->setAmountService($this->acquiringCommissionFromPurpose($statement));
        }
        
        $this->entityManager->flush();
        
        $this->costManager->repostStatement($statement);
        
        return;
    }
    
    /**
     * Удаление строки выписки
     * @param \Bank\Entity\Statement $statement
     */
    public function removeStatement($statement)
    {
        $this->entityManager->remove($statement);
        $this->entityManager->flush();
    }

    /**
     * Загруза выписки эквайринга
     * 
     * @param string $filename
     * @return null
     */
    public function uploadStatementCsv($filename)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        $i = 0;
        
        if (file_exists($filename)){
            
            if (!filesize($filename)){
                return;
            }

            $basenameFilter = new Basename();

            $lines = fopen($filename, 'r');

            if($lines) {

                $detector = new CsvDetectDelimiterFilter();
                $delimiter = $detector->filter($filename);
                $filter = new RawToStr();
                $floatFilter = new ToFloat();
                
                while (($line = fgetcsv($lines, 4096, $delimiter)) !== false) {
                    
                    $row = explode(';', $filter->filter($line));

                    if ($floatFilter->filter($row[10])){
                        
                        $acq = $this->entityManager->getRepository(Acquiring::class)
                                ->findOneBy(['rrn' => $row[14], 'output' => $floatFilter->filter($row[10])]);

                        if ($acq == null){
                            $acq = new Acquiring();
                            $acq->setInn($row[0]);
                            $acq->setPoint($row[3]);
                            $acq->setCart($row[5]);
                            $acq->setAcode($row[6]);
                            $acq->setCartType($row[7]);
                            $acq->setAmount($floatFilter->filter($row[8]));
                            $acq->setComiss($floatFilter->filter($row[9]));
                            $acq->setOutput($floatFilter->filter($row[10]));
                            $acq->setOperType($row[11]);
                            $acq->setOperDate($row[12]);
                            $acq->setTransDate($row[13]);
                            $acq->setRrn($row[14]);
                            $acq->setIdent($row[15]);
    
                            $this->entityManager->persist($acq);
                        }    

                    }    
                }
                    
                $this->entityManager->flush();                    

                fclose($lines);                
            }    
        }
        
        return;
    }
    
    /**
     * Загруза выписки эквайринга xlsx
     * 
     * @param string $filename
     * @return null
     */
    public function uploadStatementXlsx($filename)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        $i = 0;
        
        if (file_exists($filename)){
            
            if (!filesize($filename)){
                return;
            }

            $floatFilter = new ToFloat();

            $reader = IOFactory::createReaderForFile($filename);
            $filterSubset = new \Application\Filter\ExcelColumn();
            $reader->setReadFilter($filterSubset);
            $spreadsheet = $reader->load($filename);
            $sheets = $spreadsheet->getAllSheets();
            
            foreach ($sheets as $sheet) { // PHPExcel_Worksheet

                $excel_sheet_content = $sheet->toArray();

                if (count($excel_sheet_content)){
                    foreach ($excel_sheet_content as $row){ 
                        
//                        var_dump($row); exit;
                        
                        $point = !empty($row[0]) ? $row[0]:null;
                        $dateOper = !empty($row[3]) ? $row[3]:null;
                        $operType = !empty($row[4]) ? $row[4]:null;
                        $amount = $floatFilter->filter(!empty($row[5]) ? $row[5]:null);
                        $cart = !empty($row[6]) ? $row[6]:null;
                        $rrn = !empty($row[8]) ? $row[8]:null;
                        $acq = null;
                        
                        if (empty($rrn) && !empty($cart) && $amount < 0){//возврат
                            $foundAcq = $this->entityManager->getRepository(Acquiring::class)
                                    ->findOneBy(['cart' => $cart], ['transDate' => 'desc']);
                            if ($foundAcq){
                                $rrn = $foundAcq->getRrn();
                            }        
                        }
                        
                        if (empty($rrn) && !empty($cart) && $amount > 0){//оплата без rrn почемуто
                            $acq = $this->entityManager->getRepository(Acquiring::class)
                                    ->findOneBy(['cart' => $cart, 'output' => $amount]);
                            $rrn = 'не указан';
                        }
                        
                        if ($point && $rrn && $amount){
                            $acq = $this->entityManager->getRepository(Acquiring::class)
                                    ->findOneBy(['rrn' => $rrn, 'output' => $amount]);
                        }    
                        
                        if ($acq == null && $rrn && !empty($cart) && !empty($amount)){
                            $acq = new Acquiring();
                            $acq->setCart($cart);
                            $acq->setAmount($amount);
                            $acq->setOutput($amount);
                            $acq->setComiss(0);
                            $acq->setOperType($operType);
                            $acq->setOperDate($dateOper);
                            $acq->setTransDate($dateOper);
                            $acq->setRrn($rrn);
                            $acq->setPoint($point);

                            $this->entityManager->persist($acq);
                            $this->entityManager->flush();                    
                        }    
                    }                    
                }
            }            
        }
        
        return;
    }
    
    /**
     * Очистка от записей с отказами
     * 
     * @return null
     */
    public function compressAcquiring()
    {
        $data = $this->entityManager->getRepository(Acquiring::class)
                ->compressAcquiring();

        foreach ($data as $row){
            $forDelete = $this->entityManager->getRepository(Acquiring::class)
                    ->findBy(['rrn' => $row['rrn']]);
            foreach ($forDelete as $acquiring){
                $this->updateAcquiringStatus($acquiring, Acquiring::STATUS_MATCH);
            }
        }
        
        return;
    }
    
    /**
     * Очистка от записей с отказами
     * 
     * @return null
     */
    public function compressAplPayment()
    {
        $data = $this->entityManager->getRepository(AplPayment::class)
                ->compressAplPayment();

        foreach ($data as $row){
            $forDelete = $this->entityManager->getRepository(AplPayment::class)
                    ->findBy(['aplPaymentType' => $row['aplPaymentType'], 'aplPaymentTypeId' => $row['aplPaymentTypeId']]);
            
            foreach ($forDelete as $aplPayment){
                $this->updateAplPaymentStatus($aplPayment, AplPayment::STATUS_MATCH);
            }
        }        
        return;
    }
    
    /**
     * Поиск пересечений эквайринга
     * 
     */
    public function findAcquiringIntersect()
    {
        $acquirings = $this->entityManager->getRepository(Acquiring::class)
                ->findBy(['status' => Acquiring::STATUS_NO_MATCH]);
        
        foreach($acquirings as $acquiring){
            $aplPayment = $this->entityManager->getRepository(Acquiring::class)
                    ->findAcquiringIntersect($acquiring);
            
            if ($aplPayment){
                $acquiring->setStatus(Acquiring::STATUS_MATCH);
                $acquiring->addAplPayment($aplPayment);
                $this->entityManager->persist($acquiring);
                
                $aplPayment->setStatus(AplPayment::STATUS_MATCH);
                $this->entityManager->persist($aplPayment);                
                
                $this->entityManager->flush();
            }
        }
    }
    
    /**
     * Поиск пересечений эквайринга
     * 
     */
    public function findAcquiringIntersectSum()
    {
        $acquirings = $this->entityManager->getRepository(Acquiring::class)
                ->findBy(['status' => Acquiring::STATUS_NO_MATCH]);
        
        foreach($acquirings as $acquiring){
            $aplPaymentTypeIds = $this->entityManager->getRepository(Acquiring::class)
                    ->findAcquiringIntersectSum($acquiring);
            
            if (count($aplPaymentTypeIds)){
                foreach ($aplPaymentTypeIds as $row){
                    
                    $aplPayments = $this->entityManager->getRepository(AplPayment::class)
                            ->findBy(['aplPaymentType' => $row['aplPaymentType'], 'aplPaymentTypeId' => $row['aplPaymentTypeId']]);
                    
                    foreach ($aplPayments as $aplPayment){                    
                        $acquiring->setStatus(Acquiring::STATUS_MATCH);
                        $acquiring->addAplPayment($aplPayment);
                        $this->entityManager->persist($acquiring);

                        $aplPayment->setStatus(AplPayment::STATUS_MATCH);
                        $this->entityManager->persist($aplPayment);                
                    }    
                    $this->entityManager->flush();
                }    
            }
        }
    }
    
    /**
     * Обновление статуса эквайринга
     * @param \Bank\Entity\Acquiring $acquiring
     * @param integer $status
     */
    public function updateAcquiringStatus($acquiring, $status)
    {
        $acquiring->setStatus($status);
        $this->entityManager->persist($acquiring);
        $this->entityManager->flush();
        
        return;
    }

    /**
     * Обновление статуса эквайринга
     * @param \Bank\Entity\AplPayment $aplPayment
     * @param integer $status
     */
    public function updateAplPaymentStatus($aplPayment, $status)
    {
        $aplPayment->setStatus($status);
        $this->entityManager->persist($aplPayment);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Удаление оплаты по карте
     * 
     * @param \Bank\Entity\AplPayment $aplPayment
     * @return null
     */
    public function removeAplPayment($aplPayment)
    {
        $this->entityManager->remove($aplPayment);
        $this->entityManager->flush();
        
        return;
                
    }


    /**
     * Получение выписок по почте
     */    
    public function getStatementsByEmail()
    {
        $bankSettings = $this->adminManager->getBankTransferSettings();
        
        if ($bankSettings['statement_email'] && $bankSettings['statement_email_password']){
            $box = [
                'host' => 'imap.yandex.ru',
                'server' => '{imap.yandex.ru:993/imap/ssl}',
                'user' => $bankSettings['statement_email'],
                'password' => $bankSettings['statement_app_password'],
                'leave_message' => false,
            ];

            $mailList = $this->postManager->readImap($box);

            if (count($mailList)){
                /* @var $mailList array */
                foreach ($mailList as $mail){
                    if (isset($mail['attachment'])){
                        foreach($mail['attachment'] as $attachment){
                            if ($attachment['filename'] && file_exists($attachment['temp_file'])){
                                $target = self::STATEMENTS_DIR.'/'.rand().'_'.$attachment['filename'];
                                if (copy($attachment['temp_file'], $target)){
                                    unlink($attachment['temp_file']);
                                }
                            }
                        }
                    }
                }
            }
        }     
    }
    
    /**
     * Записать отсатки на счетах из выписки 1с
     * 
     * @param Bank\Entity\BankAccount
     * @param array $statement
     */
    public function saveBalanceFromStatement1c($bankAccount, $statement)
    {
        if (is_array($statement)){            
            foreach ($statement['accounts'] as $account){
                $data['bik'] = $bankAccount->getBik();
                $data['account'] = $account['РасчСчет'];
                $data['dateBalance'] = date('Y-m-d', strtotime($account['ДатаНачала']));
                $data['balance'] = $account['НачальныйОстаток'];
                    
                $this->addNewOrUpdateBalance($data);
            }            
        }
        
        return;
    }
    
    /**
     * Запись документов из выписки 1с
     * 
     * @param Bank\Entity\BankAccount $bankAccount
     * @param array $statement
     */
    public function saveStatementFromStatement1c($bankAccount, $statement)
    {
        if (is_array($statement)){
            $converFilter = new ConvertStatement1c($bankAccount);
            foreach ($statement['docs'] as $doc){
                $data = $converFilter->filter($doc);
                $this->addNewOrUpdateStatement($data);
            }            
        }
        
        return;        
    }
    
    public function checkStatementFolder()
    {            
        setlocale(LC_ALL,'ru_RU.UTF-8');
        
        foreach (new \DirectoryIterator(self::STATEMENTS_DIR) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            if ($fileInfo->isFile()){
                if (strtolower($fileInfo->getExtension()) == 'txt'){
//                    $convetFilter = new Statement1cToArray();
//                    $statement = $convetFilter->filter($fileInfo->getPathname());
//
//                    $bankAccount = $this->entityManager->getRepository(BankAccount::class)
//                        ->findOneBy(['rs' => $statement['РасчСчет']]);
//            
//                    if ($bankAccount){
//                        $this->saveBalanceFromStatement1c($bankAccount, $statement);
//                        $this->saveStatementFromStatement1c($bankAccount, $statement);
//                        
//                    }
                }
                if (strtolower($fileInfo->getExtension()) == 'csv'){

                    $this->uploadStatementCsv($fileInfo->getPathname());
                    $this->compressAcquiring();
                    $this->compressAplPayment();
                    $this->findAcquiringIntersect();
                    $this->findAcquiringIntersectSum();
                    if (is_dir(self::STATEMENTS_ARCH_DIR)){
                        if (copy($fileInfo->getPathname(), self::STATEMENTS_ARCH_DIR.'/'.$fileInfo->getFilename())){
                            unlink($fileInfo->getPathname());
                        }
                    }
                }
                if (strtolower($fileInfo->getExtension()) == 'xlsx'){

                    $this->uploadStatementXlsx($fileInfo->getPathname());
                    $this->compressAcquiring();
                    $this->compressAplPayment();
                    $this->findAcquiringIntersect();
                    $this->findAcquiringIntersectSum();
                    if (is_dir(self::STATEMENTS_ARCH_DIR)){
                        if (copy($fileInfo->getPathname(), self::STATEMENTS_ARCH_DIR.'/'.$fileInfo->getFilename())){
                            unlink($fileInfo->getPathname());
                        }
                    }
//                    break;
                }                
            }            
        }

        return;
    }    
    
    
    /**
     * Получение выписки из банка Точка
     * и запись ее в базу
     * @param date $dateStart дата начала периода
     * @param date $dateEnd дата конца периода
     * @param array $options другие опции
     */
    public function tochkaStatement($dateStart, $dateEnd, $options = null)
    {
        try{
            $tochkaStatement = $this->tochkaStatement->statements($dateStart, $dateEnd);
        } catch (\Exception $e){
            return $e->getMessage();
        }
        
        if (is_array($tochkaStatement)){
            foreach ($tochkaStatement['statements'] as $bik => $accounts){
                foreach ($accounts as $code => $account){
                    if (isset($account['balance_opening'])){
                        $this->addNewOrUpdateBalance(['bik' => $bik, 'account' => $code, 'dateBalance' => $dateStart, 'balance' => $account['balance_opening']]);
                    }    
                    if (isset($account['payments'])){
                        foreach($account['payments'] as $payment){
                            $payment['bik'] = $bik;
                            $payment['account'] = $code;
                            $this->addNewOrUpdateStatement($payment);
                        }
                    }    
                }
            }
        }
        
        return true;
    }
    
    /**
     * Получение выписки из банка Точка v2
     * и запись ее в базу
     * @param date $dateStart дата начала периода
     * @param date $dateEnd дата конца периода
     * @param array $options другие опции
     */
    public function tochkaStatementV2($dateStart = null, $dateEnd = null, $options = null)
    {
        try{
            $tochkaStatement = $this->tochkaStatement->statementsV2($dateStart, $dateEnd);
        } catch (\Exception $e){
            return $e->getMessage();
        }
        if (!empty($tochkaStatement['statements'])){            
            foreach ($tochkaStatement['statements'] as $statement){
                if (isset($statement->accountId)){
                    list($code, $bik) = explode('/', $statement->accountId);
                }    
                if (isset($statement->startDateBalance) && $code && $bik){
                    $this->addNewOrUpdateBalance(['bik' => $bik, 'account' => $code, 'dateBalance' => $statement->startDateTime, 'balance' => $statement->startDateBalance]);
                }    
                if (isset($statement->Transaction) && $code && $bik){
                    foreach($statement->Transaction as $transaction){
                        $payment = [
                            'bik' => $bik,
                            'account' => $code,
                            'counterparty_inn' => (isset($transaction->CreditorParty)) ? $transaction->CreditorParty->inn:$transaction->DebtorParty->inn,
                            'counterparty_name' => (isset($transaction->CreditorParty)) ? $transaction->CreditorParty->name:$transaction->DebtorParty->name,
                            'counterparty_account_number' => (isset($transaction->CreditorAccount)) ? $transaction->CreditorAccount->identification:$transaction->DebtorAccount->identification,
                            'counterparty_bank_bic' => (isset($transaction->CreditorAgent)) ? $transaction->CreditorAgent->identification:$transaction->DebtorAgent->identification,
                            'counterparty_bank_name' => (isset($transaction->CreditorAgent)) ? $transaction->CreditorAgent->name:$transaction->DebtorAgent->name,
                            'payment_number' => $transaction->documentNumber,
                            'payment_bank_system_id' => $transaction->transactionId,
                            'x_payment_id' => $transaction->transactionId,
                            'payment_date' => date('Y-m-d', strtotime($transaction->documentProcessDate)),
                            'payment_charge_date' => date('Y-m-d', strtotime($transaction->documentProcessDate)),
                            'operation_type' => 0,
                            'payment_amount' => (isset($transaction->CreditorParty)) ? -$transaction->Amount->amount:$transaction->Amount->amount,
                            'payment_purpose' => $transaction->description,
                        ];
                        
                        if (isset($transaction->CreditorParty)){
                            if (isset($transaction->CreditorParty->kpp)){
                                $payment['counterparty_kpp'] = $transaction->CreditorParty->kpp;
                            }
                        }

                        if (isset($transaction->DebtorParty)){
                            if (isset($transaction->DebtorParty->kpp)){
                                $payment['counterparty_kpp'] = $transaction->DebtorParty->kpp;
                            }
                        }
                        
                        $this->addNewOrUpdateStatement($payment);
                    }
                }
            }
        }
        
        return true;
    }

    public function accountListV2()
    {
        $accountList = $this->tochkaStatement->accountListV2();
        
        return $accountList;
    }
}
