<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Bank\Service;

use Bank\Entity\Balance;
use Bank\Entity\Statement;
/**
 * Description of BankManager
 *
 * @author Daddy
 */
class BankManager 
{
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
    
    public function __construct($entityManager, $tochkaApi)
    {
        $this->entityManager = $entityManager;
        $this->tochkaApi = $tochkaApi;
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
                    'bik' => $data['bik'],
                    'account' => $data['account'],
                    'chargeDate' => date('Y-m-d', strtotime($data['payment_charge_date'])),
                    'xPaymentId' => $data['x_payment_id'],
                ]);
        
        if (!$statement){
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
