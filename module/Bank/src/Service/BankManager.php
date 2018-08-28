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
use Bank\Filter\Statement1cToArray;
use Bank\Filter\ConvertStatement1c;

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
        
        foreach (new \DirectoryIterator(self::STAEMENTS_DIR) as $fileInfo) {
            if ($fileInfo->isDot()) continue;
            if ($fileInfo->isFile()){
                if (strtolower($fileInfo->getExtension()) == 'txt'){
                    $convetFilter = new Statement1cToArray();
                    $statement = $convetFilter->filter($fileInfo->getPathname());

                    $bankAccount = $this->entityManager->getRepository(BankAccount::class)
                        ->findOneByRs($statement['РасчСчет']);
            
                    if ($bankAccount){
                        $this->saveBalanceFromStatement1c($bankAccount, $statement);
                        $this->saveStatementFromStatement1c($bankAccount, $statement);
                        
                    }
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
