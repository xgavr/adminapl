<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cash\Service;

use Cash\Entity\Cash;
use Company\Entity\Office;
use Cash\Entity\CashDoc;
use Cash\Entity\CashTransaction;
use User\Entity\User;
use Stock\Entity\Mutual;
use Company\Entity\Contract;
use Stock\Entity\Retail;
use Admin\Entity\Log;
use Company\Entity\Legal;
use Company\Entity\Cost;
use Application\Entity\Order;
use Stock\Entity\Vt;
use Application\Entity\Supplier;
use Cash\Entity\UserTransaction;
use Laminas\Validator\Date;

/**
 * Description of CashManager
 * 
 * @author Daddy
 */
class CashManager {
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    /**
     * Log manager.
     * @var \Admin\Service\LogManager
     */
    private $logManager;

    /**
     * Legal manager.
     * @var \Company\Service\LegalManager
     */
    private $legalManager;

    public function __construct($entityManager, $logManager, $legalManager)
    {
        $this->entityManager = $entityManager;
        $this->logManager = $logManager;
        $this->legalManager = $legalManager;
    }
    
    /**
     * Новая касса
     * 
     * @param Office $office
     * @param array $data
     * @return Cash
     */
    public function addCash($office, $data)
    {
        $cash = new Cash();
        $cash->setAplId($data['aplId']);
        $cash->setCheckStatus($data['checkStatus']);
        $cash->setComission($data['commission']);
        $cash->setDateCreated(date('Y-m-d H:i:s'));
        $cash->setName($data['name']);
        $cash->setRestStatus($data['restStatus']);
        $cash->setStatus($data['status']);
        $cash->setTillStatus($data['tillStatus']);
        $cash->setOrderStatus($data['orderStatus']);
        
        $cash->setOffice($office);
        $this->entityManager->persist($cash);
        $this->entityManager->flush();
        
        return $cash;
    }
    
    /**
     * Обновить кассу
     * 
     * @param Cash $cash
     * @param array $data
     * @return Cash
     */
    public function updateCash($cash, $data)
    {
        $cash->setAplId($data['aplId']);
        $cash->setCheckStatus($data['checkStatus']);
        $cash->setComission($data['commission']);
        $cash->setName($data['name']);
        $cash->setRestStatus($data['restStatus']);
        $cash->setStatus($data['status']);
        $cash->setTillStatus($data['tillStatus']);
        $cash->setOrderStatus($data['orderStatus']);
        
        $this->entityManager->persist($cash);
        $this->entityManager->flush();
        
        return $cash;
    }    
    
    /**
     * Удалить кассу
     * @param Cash $cash
     * @return null
     */
    public function removeCash($cash)
    {
        $cashDocCount = $this->entityManager->getRepository(CashDoc::class)
                ->count(['cash' => $cash->getId()]);
        if (!$cashDocCount){
            $this->entityManager->remove($cash);
            $this->entityManager->flush();
        }
        return;
    }
    
    /**
     * Получить контракт по умолчанию
     * 
     * @param Office $office
     * @param Legal $legal
     * @param date $dateStart
     * @param string $act
     * @param integer $pay
     * 
     * @return Contract
     */
    protected function findDefaultContract($office, $legal, $dateDoc, $act, $kind = Contract::KIND_CUSTOMER, $pay = Contract::PAY_CASH)
    {
        $dateValidator = new Date();
        $dateValidator->setFormat('Y-m-d');
        if (!$dateValidator->isValid($dateDoc)){
            $dateDoc = date('Y-m-d');
        }
        
        $contract = $this->entityManager->getRepository(Office::class)
                ->findDefaultContract($office, $legal, $dateDoc, $pay);
        
        if (!$contract){
            $contract = $this->legalManager->addContract($legal, 
                    [
                        'office' => $office->getId(),
                        'name' => ($pay == Contract::PAY_CASH) ? 'Поставка Н':'Поставка БН',
                        'act' => trim($act),
                        'dateStart' => '2012-05-15',
                        'status' => Contract::STATUS_ACTIVE,
                        'kind' => $kind,
                        'pay' => $pay,
                    ]);
        }
        
        return $contract;
    }
    
    /**
     * Удаление взаиморасчетов
     * @param CashDoc $cashDoc
     */
    protected function removeMutuals($cashDoc)
    {
        $this->entityManager->getRepository(Mutual::class)
                ->removeDocMutuals($cashDoc->getLogKey());                
    }
    
    /**
     * Добавить взаиморасчеты
     * @param CashDoc $cashDoc
     */
    protected function addMutuals($cashDoc)
    {
        if ($cashDoc->isMutual()){
            $contract = $this->findDefaultContract($cashDoc->getCash()->getOffice(), 
                    $cashDoc->getLegal(), $cashDoc->getDateOper(), $cashDoc->getId(),
                    $cashDoc->getContractKind(), Contract::PAY_CASH);

            $data = [
                'doc_key' => $cashDoc->getLogKey(),
                'date_oper' => $cashDoc->getDateOper(),
                'status' => $cashDoc->getStatus(),
                'revise' => Mutual::REVISE_NOT,
                'amount' => $cashDoc->getMutualAmount(),
                'legal_id' => $cashDoc->getLegal()->getId(),
                'contract_id' => $contract->getId(),
                'office_id' => $cashDoc->getCash()->getOffice()->getId(),
                'company_id' => $cashDoc->getCompany()->getId(),
            ];

            $this->entityManager->getRepository(Mutual::class)
                    ->insertMutual($data);
        }
        
        return;
    }

    /**
     * Удаление взаиморасчетов розницы
     * @param CashDoc $cashDoc
     */
    protected function removeRetails($cashDoc)
    {
        $this->entityManager->getRepository(Retail::class)
                ->removeOrderRetails($cashDoc->getLogKey());                
    }
    
    /**
     * Обновить взаиморасчеты розницы
     * 
     * @param CashDoc $cashDoc
     */
    public function addRetails($cashDoc)
    {
        if ($cashDoc->isRetail()){
            $data = [
                'doc_key' => $cashDoc->getLogKey(),
                'date_oper' => $cashDoc->getDateOper(),
                'status' => $cashDoc->getStatus(),
                'revise' => Retail::REVISE_NOT,
                'amount' => $cashDoc->getMutualAmount(),
                'contact_id' => $cashDoc->getContact()->getId(),
                'office_id' => $cashDoc->getCash()->getId(),
                'company_id' => $cashDoc->getCompany()->getId(),
            ];

            $this->entityManager->getRepository(Retail::class)
                    ->insertRetail($data);
        }    
        
        return;
    }    
    
    /**
     * Удалить кассовые записи
     * @param CashDoc $cashDoc
     */
    protected function removeTransactions($cashDoc)
    {
        $cashTransactions = $cashDoc->getCashTransactions();
        foreach ($cashTransactions as $cashTransaction){
            $this->entityManager->remove($cashTransaction);
        }
    }

    /**
     * Создать кассовую запись
     * @param CashDoc $cashDoc
     * @param float $amount
     * @param Cash $cash
     */
    protected function addTransaction($cashDoc, $amount, $cash)
    {
        $cashTransaction = new CashTransaction();
        $cashTransaction->setAmount($amount);
        $cashTransaction->setCashDoc($cashDoc);
        $cashTransaction->setDateCreated(date('Y-m-d H:i:s'));
        $cashTransaction->setDateOper($cashDoc->getDateOper());
        $cashTransaction->setStatus(($cashDoc->getStatus() === CashDoc::STATUS_ACTIVE) ? CashTransaction::STATUS_ACTIVE:CashTransaction::STATUS_RETIRED);
        $cashTransaction->setCash($cash);
        
        $this->entityManager->persist($cashTransaction);        
    }
    
    /**
     * Удалить записи подотчета
     * @param CashDoc $cashDoc
     */
    protected function removeUserTransactions($cashDoc)
    {
        $userTransactions = $cashDoc->getUserTransactions();
        foreach ($userTransactions as $userTransaction){
            $this->entityManager->remove($userTransaction);
        }
    }

    /**
     * Создать запись подотчет
     * @param CashDoc $cashDoc
     * @param float $amount
     * @param User $user
     */
    protected function addUserTransaction($cashDoc, $amount, $user)
    {
        $userTransaction = new UserTransaction();
        $userTransaction->setAmount($amount);
        $userTransaction->setCashDoc($cashDoc);
        $userTransaction->setDateCreated(date('Y-m-d H:i:s'));
        $userTransaction->setDateOper($cashDoc->getDateOper());
        $userTransaction->setStatus(($cashDoc->getStatus() === CashDoc::STATUS_ACTIVE) ? UserTransaction::STATUS_ACTIVE:UserTransaction::STATUS_RETIRED);
        $userTransaction->setUser($user);
        
        $this->entityManager->persist($userTransaction);        
    }
    
    /**
     * Добавить запись о кассовой операции
     * @param CashDoc $cashDoc
     */
    public function updateCashTransaction($cashDoc)
    {
        $this->removeMutuals($cashDoc);
        $this->removeRetails($cashDoc);
        $this->removeTransactions($cashDoc);
        $this->removeUserTransactions($cashDoc);
        
        $this->addTransaction($cashDoc, $cashDoc->getKindAmount(), $cashDoc->getCash());
        
        switch ($cashDoc->getKind()){
            case CashDoc::KIND_IN_REFILL:
                $this->addTransaction($cashDoc, -$cashDoc->getAmount(), $cashDoc->getCashRefill());
                break;
            case CashDoc::KIND_IN_RETURN_USER:
                $this->addUserTransaction($cashDoc, -$cashDoc->getAmount(), $cashDoc->getUserRefill());
                break;
            case CashDoc::KIND_OUT_USER:
                $this->addUserTransaction($cashDoc, $cashDoc->getAmount(), $cashDoc->getUserRefill());
                break;
            case CashDoc::KIND_OUT_REFILL:
                $this->addTransaction($cashDoc, $cashDoc->getAmount(), $cashDoc->getCashRefill());
                break;
        }
        
        $this->entityManager->flush();
        
        $this->addMutuals($cashDoc);
        $this->addRetails($cashDoc);
        
        return;
    }
    
    /**
     * Подготовить данные
     * @param array $data
     */
    protected function prepareData($data)
    {
        if (is_numeric($data['cash'])){
            $data['cash'] = $this->entityManager->getRepository(Cash::class)
                    ->find($data['cash']);
        }
        if (is_numeric($data['cashRefill'])){
            $data['cashRefill'] = $this->entityManager->getRepository(Cash::class)
                    ->find($data['cashRefill']);
        }
        if (is_numeric($data['company'])){
            $data['company'] = $this->entityManager->getRepository(Legal::class)
                    ->find($data['company']);
        }
        if (is_numeric($data['user'])){
            $data['user'] = $this->entityManager->getRepository(User::class)
                    ->find($data['user']);
        }
        if (is_numeric($data['userRefill'])){
            $data['userRefill'] = $this->entityManager->getRepository(User::class)
                    ->find($data['userRefill']);
        }
        if (is_numeric($data['legal'])){
            $data['legal'] = $this->entityManager->getRepository(Legal::class)
                    ->find($data['legal']);
        }
        if (is_numeric($data['cost'])){
            $data['cost'] = $this->entityManager->getRepository(Cost::class)
                    ->find($data['cost']);
        }
        if (is_numeric($data['order'])){
            $data['order'] = $this->entityManager->getRepository(Order::class)
                    ->find($data['order']);
            $data['contact'] = $data['order']->getContact();
        }
        if (is_numeric($data['vt'])){
            $data['vt'] = $this->entityManager->getRepository(Vt::class)
                    ->find($data['vt']);
        }
        if (is_numeric($data['supplier'])){
            $supplier = $this->entityManager->getRepository(Supplier::class)
                    ->find($data['supplier']);
            if ($supplier){
                $data['legal'] = $this->entityManager->getRepository(Legal::class)
                        ->formContactLegal($supplier->getLegalContact());
            }    
        }
        
        return $data;
    }
    
    /**
     * Новый кассовый документ
     * 
     * @param array $data
     * @return CashDoc
     */
    public function addCashDoc($data)
    {
//        var_dump($data); exit;
        $data = $this->prepareData($data);
        
        $cashDoc = new CashDoc();
        $cashDoc->setAmount($data['amount']);
        $cashDoc->setAplId($data['aplId'] ?: 0);
        $cashDoc->setCash($data['cash'] ?: null);
        $cashDoc->setCashRefill($data['cashRefill'] ?: null);
        $cashDoc->setCheckStatus($data['checkStatus'] ?: CashDoc::CHECK_RETIRED);
        $cashDoc->setComment($data['comment'] ?: null);
        $cashDoc->setCompany($data['company']);
        $cashDoc->setContact($data['contact'] ?: null);
        $cashDoc->setCost($data['cost'] ?: null);
        $cashDoc->setDateCreated(date('Y-m-d H:i:s'));
        $cashDoc->setDateOper($data['dateOper']);
        $cashDoc->setInfo($data['info'] ?: null);
        $cashDoc->setKind($data['kind']);
        $cashDoc->setLegal($data['legal'] ?: null);
        $cashDoc->setOrder($data['order'] ?: null);
        $cashDoc->setStatus($data['status']);
        $cashDoc->setUser($data['user'] ?: null);
        $cashDoc->setUserRefill($data['userRefill'] ?: null);
        $cashDoc->setVt($data['vt'] ?: null);
        
        $this->entityManager->persist($cashDoc);
        $this->entityManager->flush($cashDoc);
        
        $this->updateCashTransaction($cashDoc);
        $this->logManager->infoCash($cashDoc, Log::STATUS_NEW);
        
        return $cash;
    }
        
    /**
     * Обновить кассовый документ
     * 
     * @param CashDoc $cashDoc
     * @param array $data
     * @return CashDoc
     */
    public function updateCashDoc($cashDoc, $data)
    {
//        var_dump($data); exit;
        $preLog = $this->entityManager->getRepository(Log::class)
                ->findOneByLogKey($cashDoc->getLogKey());
        if (!$preLog){
            $this->logManager->infoCash($cashDoc, Log::STATUS_INFO);            
        }
        $data = $this->prepareData($data);

        $cashDoc->setAmount($data['amount']);
        $cashDoc->setAplId($data['aplId'] ?: 0);
        $cashDoc->setCash($data['cash'] ?: null);
        $cashDoc->setCashRefill($data['cashRefill'] ?: null);
        $cashDoc->setCheckStatus($data['checkStatus'] ?: CashDoc::CHECK_RETIRED);
        $cashDoc->setComment($data['comment'] ?: null);
        $cashDoc->setCompany($data['company']);
        $cashDoc->setContact($data['contact'] ?: null);
        $cashDoc->setCost($data['cost'] ?: null);
        $cashDoc->setDateOper($data['dateOper']);
        $cashDoc->setInfo($data['info'] ?: null);
        $cashDoc->setKind($data['kind']);
        $cashDoc->setLegal($data['legal'] ?: null);
        $cashDoc->setOrder($data['order'] ?: null);
        $cashDoc->setStatus($data['status']);
        $cashDoc->setUser($data['user'] ?: null);
        $cashDoc->setUserRefill($data['userRefill'] ?: null);
        $cashDoc->setVt($data['vt'] ?: null);
        
        $this->entityManager->persist($cashDoc);
        $this->entityManager->flush($cashDoc);
        
        $this->updateCashTransaction($cashDoc);
        $this->logManager->infoCash($cashDoc, Log::STATUS_UPDATE);
        
        return $cash;
    }
        
    
    /**
     * Удалить кассовый документ
     * @param CashDoc $cashDoc
     * @return null
     */
    public function removeCashDoc($cashDoc)
    {
        $this->removeMutuals($cashDoc);
        $this->removeRetails($cashDoc);
        $this->removeTransactions($cashDoc);
        
        $this->entityManager->remove($cashDoc);
        $this->entityManager->flush();
        return;
    }
        
}
