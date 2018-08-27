<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Bank\Service;

use Bank\Entity\Balance;
use Bank\Entity\Statement;
use Company\Entity\BankAccount;

/**
 * Description of BankManager
 *
 * @author Daddy
 */
class BankManager 
{
    const STAEMENTS_DIR       = './data/statements/'; // папка с файлами выписок
    const STAEMENTS_ARCH_DIR       = './data/statements/arch'; // папка с архивом файлами выписок
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * TochkaApi manager
     * @var Bankapi\Service\TochkaApi
     */
    private $tochkaApi;
    
    /**
     * AdminManager manager
     * @var Admin\Service\AdminManager
     */
    private $adminManager;

    /**
     * PostManager manager
     * @var Admin\Service\PostManager
     */
    private $postManager;

    public function __construct($entityManager, $tochkaApi, $adminManager, $postManager)
    {
        $this->entityManager = $entityManager;
        $this->tochkaApi = $tochkaApi;    
        $this->adminManager = $adminManager;
        $this->postManager = $postManager;
        
        if (!is_dir(self::STAEMENTS_DIR)){
            mkdir(self::STAEMENTS_DIR);
        }

        if (!is_dir(self::STAEMENTS_ARCH_DIR)){
            mkdir(self::STAEMENTS_ARCH_DIR);
        }
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
                'password' => $bankSettings['statement_email_password'],
                'leave_message' => true,
            ];

            $mailList = $this->postManager->readImap($box);

            if (count($mailList)){
                foreach ($mailList as $mail){
                    if (isset($mail['attachment'])){
                        foreach($mail['attachment'] as $attachment){
                            if ($attachment['filename'] && file_exists($attachment['temp_file'])){
                                $target = self::STAEMENTS_DIR.'/'.$attachment['filename'];
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
    
    public function readStatementFormat1c($statement_file)
    {
        $text = explode("\n", file_get_contents($statement_file));
        
        $rsult = [];
        foreach ($text as $line){
            $str = explode('=', iconv('Windows-1251', 'UTF-8', $line));
            
            if (trim($str[0]) == 'СекцияДокумент'){
                $doc = [];					
            }
            if (trim($strs[0]) == 'КонецДокумента'){
                $result[] = $doc;					
            }
            if (trim($str[0]) == 'ДатаПоступило')       $doc['payment_charge_date'] = date('Y-m-d', strtotime(trim($str[1])));
            if (trim($str[0]) == 'ДатаСписано')         $doc['payment_charge_date'] = date('Y-m-d', strtotime(trim($str[1])));					

            if (trim($str[0]) == 'Номер')               $doc['payment_number'] = trim($str[1]);					
            if (trim($str[0]) == 'Дата')                $doc['payment_date'] = date('Y-m-d', strtotime(trim($str[1])));					
            if (trim($str[0]) == 'Сумма')               $doc['payment_amount'] = trim($str[1]);	
            if (trim($str[0]) == 'НазначениеПлатежа')   $doc['payment_purpose'] = trim($str[1]);	

            if (trim($str[0]) == 'ПлательщикРасчСчет')  $doc['payerAcc'] = trim($str[1]);					
            if (trim($str[0]) == 'ПлательщикИНН') 		$doc['payerINN'] = trim($str[1]);					
            if (trim($str[0]) == 'ПлательщикКПП') 		$doc['payerKPP'] = trim($str[1]);					
            if (trim($str[0]) == 'Плательщик1') 		$doc['payerName'] = trim($str[1]);					
            if (trim($str[0]) == 'Плательщик') 		$doc['payerName'] = trim($str[1]);					
            if (trim($str[0]) == 'ПлательщикБанк1') 	$doc['payerBankName'] = trim($str[1]);					
            if (trim($str[0]) == 'ПлательщикБИК') 		$doc['payerBankBic'] = trim($str[1]);					
            if (trim($str[0]) == 'ПлательщикКорсчет') 	$doc['payerBankCorrAcc'] = trim($str[1]);					

            if (trim($str[0]) == 'ПолучательРасчСчет') $doc['payeeAcc'] = trim($str[1]);					
            if (trim($str[0]) == 'ПолучательИНН') 		$doc['payeeINN'] = trim($str[1]);					
            if (trim($str[0]) == 'ПолучательКПП') 		$doc['payeeKPP'] = trim($str[1]);					
            if (trim($str[0]) == 'Получатель1') 		$doc['payeeName'] = trim($str[1]);					
            if (trim($str[0]) == 'Получатель') 		$doc['payeeName'] = trim($str[1]);					
            if (trim($str[0]) == 'ПолучательБанк1') 	$doc['payeeBankName'] = trim($str[1]);					
            if (trim($str[0]) == 'ПолучательБИК') 		$doc['payeeBankBic'] = trim($str[1]);					
            if (trim($str[0]) == 'ПолучательКорсчет') 	$doc['payeeBankCorrAcc'] = trim($str[1]);									
        }        
        
        return $result;
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
        $this->entityManager->flush($balance);
        
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
     * @return \Bank\Entity\Statement
     */
    public function addNewOrUpdateStatement($data)
    {
        $statement = $this->entityManager->getRepository(Statement::class)
                ->findOneBy([
                    'account' => $data['account'],
                    'counterpartyInn' => $data['counterparty_inn'],
                    'counterpartyAccountNumber' => $data['counterparty_account_number'],
                    'paymentNumber' => $data['payment_number'],
                    'paymentDate' => date('Y-m-d', strtotime($data['payment_date'])),
                    'chargeDate' => date('Y-m-d', strtotime($data['payment_charge_date'])),
                ]);
        
        if ($statement){
            $data['swap1'] = $statement->getSwap1();
        } else {
            $statement = new Statement();            
        }
        
        $filter = new \Zend\Filter\Word\SeparatorToCamelCase('_');
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
     * Удаление строки выписки
     * @param \Bank\Entity\Statement $statement
     */
    public function removeStatement($statement)
    {
        $this->entityManager->remove($statement);
        $this->entityManager->flush();
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
            $tochkaStatement = $this->tochkaApi->statements($dateStart, $dateEnd);
        } catch (\Exception $e){
            return $e->getMessage();
        }
        
        if (is_array($tochkaStatement)){
            foreach ($tochkaStatement['statements'] as $bik => $accounts){
                foreach ($accounts as $code => $account){
                    $this->addNewOrUpdateBalance(['bik' => $bik, 'account' => $code, 'dateBalance' => $dateStart, 'balance' => $account['balance_opening']]);
                    foreach($account['payments'] as $payment){
                        $payment['bik'] = $bik;
                        $payment['account'] = $code;
                        $this->addNewOrUpdateStatement($payment);
                    }
                }
            }
        }
        
        return true;
    }
}
