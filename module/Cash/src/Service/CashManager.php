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
use Cash\Form\CashInForm;
use Cash\Form\CashOutForm;
use Application\Entity\Phone;
use User\Filter\PhoneFilter;
use Application\Entity\Contact;

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
     * Текущий пользователь
     * @return User
     */    
    public function currentUser()
    {
        return $this->logManager->currentUser();
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
        $cash->setRefillStatus($data['refillStatus']);
        $cash->setSupplierStatus($data['supplierStatus']);
        
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
        $cash->setRefillStatus($data['refillStatus']);
        $cash->setSupplierStatus($data['supplierStatus']);
        
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
            $office = ($cashDoc->getCash()) ? $cashDoc->getCash()->getOffice():$cashDoc->getUser()->getOffice();
            $contract = $this->findDefaultContract($office, 
                    $cashDoc->getLegal(), $cashDoc->getDateOper(), $cashDoc->getId(),
                    $cashDoc->getContractKind(), Contract::PAY_CASH);

            $data = [
                'doc_key' => $cashDoc->getLogKey(),
                'date_oper' => $cashDoc->getDateOper(),
                'status' => ($cashDoc->getStatus() == CashDoc::STATUS_ACTIVE) ? Mutual::STATUS_ACTIVE: Mutual::STATUS_RETIRED,
                'revise' => Mutual::REVISE_NOT,
                'amount' => $cashDoc->getMutualAmount(),
                'legal_id' => $cashDoc->getLegal()->getId(),
                'contract_id' => $contract->getId(),
                'office_id' => $office->getId(),
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
            $office = ($cashDoc->getCash()) ? $cashDoc->getCash()->getOffice():$cashDoc->getUser()->getOffice();            
            $data = [
                'doc_key' => $cashDoc->getLogKey(),
                'date_oper' => $cashDoc->getDateOper(),
                'status' => ($cashDoc->getStatus() == CashDoc::STATUS_ACTIVE) ? Retail::STATUS_ACTIVE: Retail::STATUS_RETIRED,
                'revise' => Retail::REVISE_NOT,
                'amount' => $cashDoc->getMutualAmount(),
                'contact_id' => $cashDoc->getContact()->getId(),
                'office_id' => $office->getId(),
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
        $cashTransaction->setStatus(($cashDoc->getStatus() == CashDoc::STATUS_ACTIVE) ? CashTransaction::STATUS_ACTIVE:CashTransaction::STATUS_RETIRED);
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
        $userTransaction->setStatus(($cashDoc->getStatus() == CashDoc::STATUS_ACTIVE) ? UserTransaction::STATUS_ACTIVE:UserTransaction::STATUS_RETIRED);
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
        
        if ($cashDoc->getCash()){
            $this->addTransaction($cashDoc, $cashDoc->getKindAmount(), $cashDoc->getCash());
        }    
        if ($cashDoc->getUser()){
            $this->addUserTransaction($cashDoc, $cashDoc->getKindAmount(), $cashDoc->getUser());
        }    
        
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
        if (!empty($data['cashRefill'])){
            $data['cashRefill'] = $this->entityManager->getRepository(Cash::class)
                    ->find($data['cashRefill']);
        }
        if (!empty($data['company'])){
            $data['company'] = $this->entityManager->getRepository(Legal::class)
                    ->find($data['company']);
        }
        if (!empty($data['user'])){
            $data['user'] = $this->entityManager->getRepository(User::class)
                    ->find($data['user']);
        }
        if (!empty($data['userRefill'])){
            $data['userRefill'] = $this->entityManager->getRepository(User::class)
                    ->find($data['userRefill']);
        }
        if (!empty($data['legal'])){
            $data['legal'] = $this->entityManager->getRepository(Legal::class)
                    ->find($data['legal']);
        }
        if (!empty($data['cost'])){
            $data['cost'] = $this->entityManager->getRepository(Cost::class)
                    ->find($data['cost']);
        }
        if (!empty($data['phone'])){
            $phoneFilter = new PhoneFilter();
            $phone = $this->entityManager->getRepository(Phone::class)
                    ->findOneByName($phoneFilter->filter($data['phone']));
            if ($phone){        
                $data['contact'] = $phone->getContact();
            }    
        }
        if (!empty($data['order'])){
            $data['order'] = $this->entityManager->getRepository(Order::class)
                    ->find($data['order']);
            $data['contact'] = $data['order']->getContact();
            if ($data['order']->getLegal()){
                $data['legal'] = $data['order']->getLegal();                
            }
        }
        if (!empty($data['contact'])){
            $data['contact'] = $this->entityManager->getRepository(Contact::class)
                    ->find($data['contact']);
        }
        if (!empty($data['vt'])){
            $data['vt'] = $this->entityManager->getRepository(Vt::class)
                    ->find($data['vt']);
        }
        if (!empty($data['supplier'])){
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
        $cashDoc->setCash(empty($data['cash']) ? null:$data['cash']);
        $cashDoc->setCashRefill(empty($data['cashRefill']) ? null:$data['cashRefill']);
        $cashDoc->setCheckStatus($data['checkStatus'] ?: CashDoc::CHECK_RETIRED);
        $cashDoc->setComment(empty($data['comment']) ? null:$data['comment']);
        $cashDoc->setCompany($data['company']);
        $cashDoc->setContact(empty($data['contact']) ? null:$data['contact']);
        $cashDoc->setCost(empty($data['cost']) ? null:$data['cost']);
        $cashDoc->setDateCreated(date('Y-m-d H:i:s'));
        $cashDoc->setDateOper($data['dateOper']);
        $cashDoc->setInfo(empty($data['info']) ? null:$data['info']);
        $cashDoc->setKind($data['kind']);
        $cashDoc->setLegal(empty($data['legal']) ? null:$data['legal']);
        $cashDoc->setOrder(empty($data['order']) ? null:$data['order']);
        $cashDoc->setStatus($data['status']);
        $cashDoc->setUser(empty($data['user']) ? null:$data['user']);
        $cashDoc->setUserRefill(empty($data['userRefill']) ? null:$data['userRefill']);
        $cashDoc->setVt(empty($data['vt']) ? null:$data['vt']);
        
        $cashDoc->setUserCreator($this->logManager->currentUser());
        
        $this->entityManager->persist($cashDoc);
        $this->entityManager->flush($cashDoc);
        
        $this->updateCashTransaction($cashDoc);
        $this->logManager->infoCash($cashDoc, Log::STATUS_NEW);
        
        return $cashDoc;
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
        $cashDoc->setCash(empty($data['cash']) ? null:$data['cash']);
        $cashDoc->setCashRefill(empty($data['cashRefill']) ? null:$data['cashRefill']);
        $cashDoc->setCheckStatus($data['checkStatus'] ?: CashDoc::CHECK_RETIRED);
        $cashDoc->setComment(empty($data['comment']) ? null:$data['comment']);
        $cashDoc->setCompany($data['company']);
        $cashDoc->setContact(empty($data['contact']) ? null:$data['contact']);
        $cashDoc->setCost(empty($data['cost']) ? null:$data['cost']);
        $cashDoc->setDateOper($data['dateOper']);
        $cashDoc->setInfo(empty($data['info']) ? null:$data['info']);
        $cashDoc->setKind($data['kind']);
        $cashDoc->setLegal(empty($data['legal']) ? null:$data['legal']);
        $cashDoc->setOrder(empty($data['order']) ? null:$data['order']);
        $cashDoc->setStatus($data['status']);
        $cashDoc->setUser(empty($data['user']) ? null:$data['user']);
        $cashDoc->setUserRefill(empty($data['userRefill']) ? null:$data['userRefill']);
        $cashDoc->setVt(empty($data['vt']) ? null:$data['vt']);
        
        $this->entityManager->persist($cashDoc);
        $this->entityManager->flush($cashDoc);
        
        $this->updateCashTransaction($cashDoc);
        $this->logManager->infoCash($cashDoc, Log::STATUS_UPDATE);
        
        return $cashDoc;
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
        
    /**
     * Оепрации доступные кассе
     * @param Cash $cash
     * @return array 
     */
    public function outKinds($cash)
    {
        $kinds = CashDoc::getKindOutList();
        if ($cash->getRefillStatus() == Cash::REFILL_RETIRED){
            unset($kinds[CashDoc::KIND_OUT_REFILL]);
            unset($kinds[CashDoc::KIND_OUT_COST]);
            unset($kinds[CashDoc::KIND_OUT_COURIER]);
            unset($kinds[CashDoc::KIND_OUT_SALARY]);
            unset($kinds[CashDoc::KIND_OUT_USER]);
        }
        if ($cash->getSupplierStatus() == Cash::SUPPLIER_RETIRED){
            unset($kinds[CashDoc::KIND_OUT_SUPPLIER]);
        }        
        
        return $kinds;
    }
    
    /**
     * Операции доступные кассе
     * @param Cash $cash
     * @return array 
     */
    public function inKinds($cash)
    {
        $kinds = CashDoc::getKindInList();
        if ($cash->getRefillStatus() == Cash::REFILL_RETIRED){
            unset($kinds[CashDoc::KIND_IN_REFILL]);
            unset($kinds[CashDoc::KIND_IN_RETURN_USER]);
        }        
        if ($cash->getSupplierStatus() == Cash::SUPPLIER_RETIRED){
            unset($kinds[CashDoc::KIND_IN_RETURN_SUPPLIER]);
        }        
        return $kinds;
    }
    /**
     * Подготовка данных для формы
     * @param CashForm $form
     * @param CashDoc $cashDoc
     */
    public function cashFormOptions($form, $cashDoc = null)
    {
        $user = $this->logManager->currentUser();
        if ($form->has('cash')){
            if ($cashDoc){
                $cash = $cashDoc->getCash();
            } else {
                var_dump($user->getOffice()->getId());
                $cash = $this->entityManager->getRepository(Cash::class)
                        ->defaultCash($user->getOffice());
                $form->get('cash')->setValue($cash->getId());
            }           

            $kinds = $this->inKinds($cash);
            if ($form instanceof CashOutForm){
                $kinds = $this->outKinds($cash);
            }    
            $form->get('kind')->setValueOptions($kinds);
            
            $officeId = $cash->getOffice()->getId();            
        }    
        if ($form->has('user')){
            if ($cashDoc){
                $user = $cashDoc->getUser();
            } else {
                $form->get('user')->setValue($user->getId());
            }           
            $officeId = $user->getOffice()->getId();            
        }
        
        if ($form->has('cost')){
            $costs = $this->entityManager->getRepository(Cost::class)
                    ->findBy(['status' => Supplier::STATUS_ACTIVE], ['name' => 'ASC']);
            $costList = ['--не выбран--'];
            foreach ($costs as $cost) {
                $costList[$cost->getId()] = $cost->getName();
            }
            $form->get('cost')->setValueOptions($costList);
        }    

        $suppliers = $this->entityManager->getRepository(Supplier::class)
                ->findBy(['status' => Supplier::STATUS_ACTIVE], ['name' => 'ASC']);
        $supplierList = ['--не выбран--'];
        foreach ($suppliers as $supplier) {
            $supplierList[$supplier->getId()] = $supplier->getName();
        }
        $form->get('supplier')->setValueOptions($supplierList);
        
        $users = $this->entityManager->getRepository(User::class)
                ->findBy(['status' => User::STATUS_ACTIVE], ['fullName' => 'ASC']);
        $userList = ['--не выбран--'];
        foreach ($users as $user) {
            $userList[$user->getId()] = $user->getFullName();
        }
        if ($form->has('user')){
            $form->get('user')->setValueOptions($userList);
        }    
        $form->get('userRefill')->setValueOptions($userList);

        $cashes = $this->entityManager->getRepository(Cash::class)
                ->findBy(['status' => Cash::STATUS_ACTIVE, 'office' => $officeId], ['name' => 'ASC']);
        foreach ($cashes as $cash) {
            $cashList[$cash->getId()] = $cash->getName();
        }
        if ($form->has('cash')){
            $form->get('cash')->setValueOptions($cashList);
        }    
        $refillCashes = $this->entityManager->getRepository(Cash::class)
                ->findBy(['status' => Cash::STATUS_ACTIVE, 'office' => $officeId, 'refillStatus' => Cash::REFILL_ACTIVE], ['name' => 'ASC']);
        foreach ($refillCashes as $cash) {
            $refillCashList[$cash->getId()] = $cash->getName();
        }
        $form->get('cashRefill')->setValueOptions($refillCashList);
        
        $legals = $this->entityManager->getRepository(Legal::class)
                ->formOfficeLegals(['officeId' => $officeId]);        
        foreach ($legals as $legal){
            $companyList[$legal->getId()] = $legal->getName();
        }            
        $form->get('company')->setValueOptions($companyList);
    }    
    
}
