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
use Bank\Entity\Statement;
use Company\Entity\BankAccount;
use Stock\Entity\Register;
use Stock\Entity\Movement;
use Bank\Entity\QrCodePayment;
use Laminas\Json\Encoder;
use Bank\Entity\AplPayment;

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
        $cash->setPayment($data['payment']);
        
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
        $cash->setPayment($data['payment']);
        
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
                        'nds' => Contract::NDS_NO,
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
     * @param float $docStamp
     */
    protected function addMutuals($cashDoc, $docStamp)
    {
        if ($cashDoc->isMutual()){
            $office = ($cashDoc->getCash()) ? $cashDoc->getCash()->getOffice():$cashDoc->getUser()->getOffice();
            $contract = $this->findDefaultContract($office, 
                    $cashDoc->getLegal(), $cashDoc->getDateOper(), $cashDoc->getId(),
                    $cashDoc->getContractKind(), $cashDoc->contractPayCash());

            $data = [
                'doc_key' => $cashDoc->getLogKey(),
                'doc_type' => Movement::DOC_CASH,
                'doc_id' => $cashDoc->getId(),
                'date_oper' => $cashDoc->getDateOper(),
                'status' => ($cashDoc->getStatus() == CashDoc::STATUS_ACTIVE) ? Mutual::STATUS_ACTIVE: Mutual::STATUS_RETIRED,
                'revise' => Mutual::REVISE_NOT,
                'amount' => $cashDoc->getMutualAmount(),
                'legal_id' => $cashDoc->getLegal()->getId(),
                'contract_id' => $contract->getId(),
                'office_id' => $office->getId(),
                'company_id' => $cashDoc->getCompany()->getId(),
                'doc_stamp' => $docStamp,
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
     * @param float $docStamp
     */
    public function addRetails($cashDoc, $docStamp)
    {
        if ($cashDoc->isRetail()&& $cashDoc->getContact()){
            $legalId = $contractId = null;
            $office = ($cashDoc->getCash()) ? $cashDoc->getCash()->getOffice():$cashDoc->getUser()->getOffice();            
            if ($cashDoc->getLegal()){
                $contract = $this->findDefaultContract($office, 
                        $cashDoc->getLegal(), $cashDoc->getDateOper(), $cashDoc->getId(),
                        $cashDoc->getContractKind(), $cashDoc->contractPayCash());
                $legalId = $cashDoc->getLegal()->getId();
                $contractId = $contract->getId();
            }
            $data = [
                'doc_key' => $cashDoc->getLogKey(),
                'doc_type' => Movement::DOC_CASH,
                'doc_id' => $cashDoc->getId(),
                'date_oper' => $cashDoc->getDateOper(),
                'status' => ($cashDoc->getStatus() == CashDoc::STATUS_ACTIVE) ? Retail::STATUS_ACTIVE: Retail::STATUS_RETIRED,
                'revise' => Retail::REVISE_NOT,
                'amount' => $cashDoc->getMutualAmount(),
                'contact_id' => $cashDoc->getContact()->getId(),
                'office_id' => $office->getId(),
                'company_id' => $cashDoc->getCompany()->getId(),
                'legal_id' => $legalId,
                'contract_id' => $contractId,
                'doc_stamp' =>$docStamp,
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
     * @param float $docStamp
     */
    protected function addTransaction($cashDoc, $amount, $cash, $docStamp)
    {
        $cashTransaction = new CashTransaction();
        $cashTransaction->setAmount($amount);
        $cashTransaction->setCashDoc($cashDoc);
        $cashTransaction->setDateCreated(date('Y-m-d H:i:s'));
        $cashTransaction->setDateOper($cashDoc->getDateOper());
        $cashTransaction->setStatus(($cashDoc->getStatus() == CashDoc::STATUS_ACTIVE) ? CashTransaction::STATUS_ACTIVE:CashTransaction::STATUS_RETIRED);
        $cashTransaction->setCash($cash);
        $cashTransaction->setDocStamp($docStamp);
        
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
     * @param float $docStamp
     */
    protected function addUserTransaction($cashDoc, $amount, $user, $docStamp)
    {
        $userTransaction = new UserTransaction();
        $userTransaction->setAmount($amount);
        $userTransaction->setCashDoc($cashDoc);
        $userTransaction->setDateCreated(date('Y-m-d H:i:s'));
        $userTransaction->setDateOper($cashDoc->getDateOper());
        $userTransaction->setStatus(($cashDoc->getStatus() == CashDoc::STATUS_ACTIVE) ? UserTransaction::STATUS_ACTIVE:UserTransaction::STATUS_RETIRED);
        $userTransaction->setUser($user);
        $userTransaction->setDocStamp($docStamp);
        
        $this->entityManager->persist($userTransaction);        
    }
    
    /**
     * Добавить запись о кассовой операции
     * @param CashDoc $cashDoc
     */
    public function updateCashTransaction($cashDoc)
    {
        $docStamp = $this->entityManager->getRepository(Register::class)
                ->cashDocRegister($cashDoc);
        
        $this->removeMutuals($cashDoc);
        $this->removeRetails($cashDoc);
        $this->removeTransactions($cashDoc);
        $this->removeUserTransactions($cashDoc);
        
        if ($cashDoc->getCash()){
            $this->addTransaction($cashDoc, $cashDoc->getKindAmount(), $cashDoc->getCash(), $docStamp);
        }    
        if ($cashDoc->getUser()){
            $this->addUserTransaction($cashDoc, $cashDoc->getKindAmount(), $cashDoc->getUser(), $docStamp);
        }    
        
        switch ($cashDoc->getKind()){
            case CashDoc::KIND_IN_REFILL:
                $this->addTransaction($cashDoc, -$cashDoc->getAmount(), $cashDoc->getCashRefill(), $docStamp);
                break;
            case CashDoc::KIND_IN_RETURN_USER:
                $this->addUserTransaction($cashDoc, -$cashDoc->getAmount(), $cashDoc->getUserRefill(), $docStamp);
                break;
            case CashDoc::KIND_OUT_USER:
                $this->addUserTransaction($cashDoc, $cashDoc->getAmount(), $cashDoc->getUserRefill(), $docStamp);
                break;
            case CashDoc::KIND_OUT_REFILL:
                $this->addTransaction($cashDoc, $cashDoc->getAmount(), $cashDoc->getCashRefill(), $docStamp);
                break;
        }
        
        $this->entityManager->flush();
        
        if ($cashDoc->getStatus() == CashDoc::STATUS_ACTIVE){
            $this->addMutuals($cashDoc, $docStamp);
            $this->addRetails($cashDoc, $docStamp);
        }    
            
        return;
    }
        
    /**
     * Подготовить данные
     * @param array $data
     */
    protected function prepareData($data)
    {
        if (!empty($data['cash'])){
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
     * @param array $inData
     * @return CashDoc
     */
    public function addCashDoc($inData)
    {
//        var_dump($inData); exit;
        $data = $this->prepareData($inData);
        
        $cashDoc = new CashDoc();
        $cashDoc->setAmount($data['amount']);
        $cashDoc->setAplId(empty($data['aplId']) ? null:$data['aplId']);
        $cashDoc->setCash(empty($data['cash']) ? null:$data['cash']);
        $cashDoc->setCashRefill(empty($data['cashRefill']) ? null:$data['cashRefill']);
        $cashDoc->setCheckStatus(empty($data['checkStatus']) ? CashDoc::CHECK_RETIRED:$data['checkStatus']);
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
        $cashDoc->setStatusEx(empty($data['statusEx']) ? CashDoc::STATUS_EX_NEW:$data['statusEx']);
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
     * @param array $inData
     * @return CashDoc
     */
    public function updateCashDoc($cashDoc, $inData)
    {
//        var_dump($data); exit;
        $preLog = $this->entityManager->getRepository(Log::class)
                ->findOneByLogKey($cashDoc->getLogKey());
        if (!$preLog){
            $this->logManager->infoCash($cashDoc, Log::STATUS_INFO);            
        }
        $data = $this->prepareData($inData);

        $cashDoc->setAmount($data['amount']);
        $cashDoc->setAplId(empty($data['aplId']) ? null:$data['aplId']);
        $cashDoc->setCash(empty($data['cash']) ? null:$data['cash']);
        $cashDoc->setCashRefill(empty($data['cashRefill']) ? null:$data['cashRefill']);
        $cashDoc->setCheckStatus(empty($data['checkStatus']) ? CashDoc::CHECK_RETIRED : $data['checkStatus']);
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
        $cashDoc->setStatusEx(empty($data['statusEx']) ? CashDoc::STATUS_EX_NEW:$data['statusEx']);
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
        if ($cash){
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
        if ($cash){
            if ($cash->getRefillStatus() == Cash::REFILL_RETIRED){
                unset($kinds[CashDoc::KIND_IN_REFILL]);
                unset($kinds[CashDoc::KIND_IN_RETURN_USER]);
            }        
            if ($cash->getSupplierStatus() == Cash::SUPPLIER_RETIRED){
                unset($kinds[CashDoc::KIND_IN_RETURN_SUPPLIER]);
            }        
        }    
        return $kinds;
    }
    /**
     * Подготовка данных для формы
     * @param CashForm $form
     * @param CashDoc $cashDoc
     * @param integer $cashId
     * @param integer $statementId
     */
    public function cashFormOptions($form, $cashDoc = null, $cashId = null, $statementId = null)
    {
        $user = $this->logManager->currentUser();
        if ($form->has('cash')){
            if ($cashDoc){
                $cash = $cashDoc->getCash();
            } else {
                if ($cashId > 0){
                    $cash = $this->entityManager->getRepository(Cash::class)
                            ->find($cashId);                    
                } else {
                    $cash = $this->entityManager->getRepository(Cash::class)
                            ->defaultCash($user->getOffice());
                }    
                if ($cash){
                    $form->get('cash')->setValue($cash->getId());
                }
                if ($statementId > 0){ //из выписки
                    $statement = $this->entityManager->getRepository(Statement::class)
                            ->find($statementId);
                    if ($statement){
                        $form->get('amount')->setValue(abs($statement->getAmount()));
                        if ($statement->getAmount() < 0){
                            $form->get('kind')->setValue(CashDoc::KIND_OUT_SUPPLIER);
                        }
                    }
                }
            }           

            $kinds = $this->inKinds($cash);
            if ($form instanceof CashOutForm){
                $kinds = $this->outKinds($cash);
            }    
            $form->get('kind')->setValueOptions($kinds);
            
            if ($cash){
                $officeId = $cash->getOffice()->getId();            
            } else {
                $officeId = $this->currentUser()->getOffice()->getId();
            }    
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
                    ->findBy([], ['status' => 'ASC', 'name' => 'ASC']);
            $costList = ['--не выбран--'];
            foreach ($costs as $cost) {
                $costList[$cost->getId()] = $cost->getName();
            }
            $form->get('cost')->setValueOptions($costList);
        }    

        $suppliers = $this->entityManager->getRepository(Supplier::class)
                ->findBy([], ['status' => 'ASC', 'name' => 'ASC']);
        $supplierList = ['--не выбран--'];
        foreach ($suppliers as $supplier) {
            $supplierList[$supplier->getId()] = $supplier->getName();
        }
        $form->get('supplier')->setValueOptions($supplierList);
        
        $users = $this->entityManager->getRepository(User::class)
                ->findBy([], ['status' => 'ASC', 'fullName' => 'ASC']);
        $userList = ['--не выбран--'];
        foreach ($users as $user) {
            $userList[$user->getId()] = $user->getFullName();
        }
        if ($form->has('user')){
            $form->get('user')->setValueOptions($userList);
        }    
        $form->get('userRefill')->setValueOptions($userList);
        if ($officeId){
            $cashes = $this->entityManager->getRepository(Cash::class)
                    ->findBy(['office' => $officeId], ['status' => 'ASC','name' => 'ASC']);
            foreach ($cashes as $cash) {
                $cashList[$cash->getId()] = $cash->getName();
            }
            if ($form->has('cash')){
                $form->get('cash')->setValueOptions($cashList);
            }    
            $refillCashes = $this->entityManager->getRepository(Cash::class)
                    ->findBy(['office' => $officeId, 'refillStatus' => Cash::REFILL_ACTIVE], ['status' => 'ASC','name' => 'ASC']);
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
    
    /**
     * Оплата/возврат от поставщика
     * @param Statement $statement
     * @param array $data
     * 
     * @return CashDoc
     */
    private function supplierCashDocFromStatement($statement, $data)
    {
        $cashDoc = $statement->getCashDoc();

        if ($statement->getAmount() > 0){
            $data['kind'] = CashDoc::KIND_IN_RETURN_SUPPLIER;
        } else {
            $data['kind'] = CashDoc::KIND_OUT_SUPPLIER;
        }

        if ($cashDoc){
            $this->updateCashDoc($cashDoc, $data);
        } else {
            $cashDoc = $this->addCashDoc($data);
        }
                
        return $cashDoc;
    }
    
    /**
     * Оплата/возврат от покупателея
     * @param Statement $statement
     * @param array $data
     * 
     * @return CashDoc
     */
    private function clientCashDocFromStatement($statement, $data)
    {
        $cashDoc = $statement->getCashDoc();
        $legal = null;
        $legalAccount = $this->entityManager->getRepository(BankAccount::class)
                ->findOneBy(['rs' => $statement->getCounterpartyAccountNumber()]);
        if ($legalAccount){
            $legal = $legalAccount->getLegal();                
        }
        if (!$legal){
            $legal = $this->entityManager->getRepository(Legal::class)
                    ->findOneBy(['inn' => $statement->getСounterpartyInn(), 'kpp' => $statement->getСounterpartyKpp()]);
        }
        if (!$legal){
            $legal = $this->entityManager->getRepository(Legal::class)
                    ->findOneBy(['inn' => $statement->getСounterpartyInn()]);
        }
        if ($legal){
            $order = $this->entityManager->getRepository(Order::class)
                    ->findOneBy(['legal' => $legal->getId()], ['id' => 'DESC']);
            if ($order){
                $data['order'] = $order;
                $data['contact'] = $order->getContact();
                if ($statement->getAmount() > 0){
                    $data['kind'] = CashDoc::KIND_IN_PAYMENT_CLIENT;
                } else {
                    $data['kind'] = CashDoc::KIND_OUT_RETURN_CLIENT;
                }

                if ($cashDoc){
                    $this->updateCashDoc($cashDoc, $data);
                } else {
                    $cashDoc = $this->addCashDoc($data);
                }                
            }            
        }

        return $cashDoc;
    }
    
    /**
     * Создать платеж из выписки
     * оплата/возврат поставщику
     * поступление/возврат от покупателя
     * 
     * @param Statement $statement
     * @param Legal $legal Description
     * @return CashDoc
     */
    public function cashDocFromStatement($statement, $legal=null)
    {
        $legalInn = $cash = $company = null;
        $cashDoc = $statement->getCashDoc();
        $data = [
            'amount' => abs($statement->getAmount()),
            'status' => CashDoc::STATUS_ACTIVE,
        ];
        
        $companyAccount = $this->entityManager->getRepository(BankAccount::class)
                ->findOneBy(['rs' => $statement->getAccount()]);
        
        if (!$legal){
            $legalAccount = $this->entityManager->getRepository(BankAccount::class)
                    ->findOneBy(['rs' => $statement->getCounterpartyAccountNumber()]);

            if ($legalAccount){
                $legal = $legalAccount->getLegal();
            }
        }    
        if ($legal){
            $legalInn = $legal->getInn();
        }
        
        if ($companyAccount){
            $cash = $companyAccount->getCash();
            $company = $companyAccount->getLegal();
        }    
        if ($cash && $company && $legal){
            if ($company->getInn() != $legalInn){ 
                $data['cash'] = $cash;
                $data['checkStatus'] = $cash->getCheckStatus();
                $data['company'] = $company;
                $data['dateOper'] = $statement->getChargeDate();
                $data['legal'] = $legal;
    
                if ($legal->getSupplier()){
                    return $this->supplierCashDocFromStatement($statement, $data);
                }
                
                if ($legal->getClientContact()){
                    $data['comment'] = $statement->getPaymentPurpose();
                    return $this->clientCashDocFromStatement($statement, $data);
                }    
            }    
        }
        
        return $cashDoc;
    }
        
    /**
     * Найти документ оплаты от юрлица
     * @param Legal $legal
     * @param float $amount
     * @param date $paymentDate
     * 
     * @return CashDoc 
     */
    private function findCashDocLegal($legal, $amount, $paymentDate)
    {
        $cashDoc = $this->entityManager->getRepository(CashDoc::class)
                ->findCashDocForStatement($legal, $amount, $paymentDate);        
        
        return $cashDoc;
    }
    
    /**
     * Привязать выписку к кассовому документу
     * @param Statement $statement
     * @param bool $flush
     * @return CashDoc
     */
    public function bindCashDocStatement($statement, $flush = true)
    {
        $legalRs = $statement->getCounterpartyAccountNumber();
        $legalInn = $statement->getСounterpartyInn();
        $legalKpp = $statement->getСounterpartyKpp();
        $amount = abs($statement->getAmount());
        
        $legal = $cashDoc = null;
        $legalsToCheck = [];
        
        if ($statement->getCashDoc()){
            $cashDoc = $statement->getCashDoc();
            $legal = $cashDoc->getLegal();
            $legalsToCheck[$legal->getId()] = $legal;
        }
        
        if (!$legal){
            $legals = $this->entityManager->getRepository(Legal::class)
                    ->findBy(['inn' => $legalInn, 'kpp' => $legalKpp]);
            foreach ($legals as $legal){
                $cashDoc = $this->findCashDocLegal($legal, $amount, $statement->getChargeDate());
                $legalsToCheck[$legal->getId()] = $legal;
                if ($cashDoc){
                    break;
                }
            }    
        }

        if (!$legal){
            $legals = $this->entityManager->getRepository(Legal::class)
                    ->findBy(['inn' => $legalInn]);
            foreach ($legals as $legal){
                $cashDoc = $this->findCashDocLegal($legal, $amount, $statement->getChargeDate());
                $legalsToCheck[$legal->getId()] = $legal;
                if ($cashDoc){
                    break;
                }
            }    
        }

        if (!$legal){
            $bankAccounts = $this->entityManager->getRepository(BankAccount::class)
                    ->findBy(['rs' => $legalRs]);

            foreach ($bankAccounts as $bankAccount){
                $legal = $bankAccount->getLegal();
                $legalsToCheck[$legal->getId()] = $legal;
                $cashDoc = $this->findCashDocLegal($legal, $amount, $statement->getChargeDate());
                if ($cashDoc){
                    break;
                }
            }
        }    
        
        
        $statement->setPay(Statement::PAY_CHECK);
        foreach ($legalsToCheck as $legal){            
    
            $statement->setPay(Statement::PAY_NEW);
            if ($cashDoc){
                if ($cashDoc->getAplId()){
                    $statement->getSwap1(Statement::SWAP1_TO_TRANSFER);
                    $statement->setPay(Statement::PAY_CHECK);
                    break;
                }
            } else {
                if ($legal->isOfficeLegal()){
                    $statement->setPay(Statement::PAY_CHECK);  // внутренние транзакции                 
                    break;
                }
                if ($legal->getSupplier() || $legal->getClientContact()){
                    $cashDoc = $this->cashDocFromStatement($statement, $legal);
                    if (!$cashDoc){
                        $statement->setPay(Statement::PAY_WARNING);  //нет документа оплаты, а должен быть                  
                    }  
                    break;
                }
            }
            
        }
        
        $statement->setCashDoc($cashDoc);
        $this->entityManager->persist($statement);
        
        if ($flush){
            $this->entityManager->flush();
        }
        
        return;
    }
    
    /**
     * Привязать все строки выписки к кассовым документам
     */
    public function bindCashDocStatements()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(900);
        $startTime = time();

        $statements = $this->entityManager->getRepository(Statement::class)
                ->findBy(['pay' => Statement::PAY_NEW]);
        
        foreach ($statements as $statement){
            $this->bindCashDocStatement($statement, false);
            if (time() >= $startTime + 800){
                break;
            }
        }
        
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Создать платеж из оплаты по qr коду
     * поступление/возврат от покупателя
     * 
     * @param QrCodePayment $qrCodePayment
     * @return CashDoc
     */
    public function cashDocFromQrCodePayment($qrCodePayment)
    {
        $cash = $company = null;
        $cashDoc = $qrCodePayment->getCashDoc();
        $data = [
            'amount' => abs($qrCodePayment->getAmount()),
            'status' => CashDoc::STATUS_ACTIVE,
        ];
        
        $companyAccount = $qrCodePayment->getBankAccount();
        
        if ($companyAccount){
            $cash = $companyAccount->getCashSbp();
            $company = $companyAccount->getLegal();
        }    
        if ($cash && $company){
            $data['cash'] = $cash;
            $data['checkStatus'] = $cash->getCheckStatus();
            $data['company'] = $company;
            $data['dateOper'] = $qrCodePayment->getDateCreated();
            $order = $qrCodePayment->getOrder();
            if ($order){
                $data['order'] = $order;
                $data['contact'] = $order->getContact();                
                $data['kind'] = $qrCodePayment->getCashDocKind();
                if ($cashDoc){
                    $this->updateCashDoc($cashDoc, $data);
                } else {
                    $cashDoc = $this->addCashDoc($data);
                }                
            }            
        }
        
        $qrCodePayment->setCashDoc($cashDoc);
        $this->entityManager->persist($qrCodePayment);
        $this->entityManager->flush();
        
        return $cashDoc;
    }
    
    /**
     * Создать документы из платежей по куаркоду
     * 
     * @return null
     */
    public function cashDocFromQrCodePayments()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(900);
        $startTime = time();
        
        $payments = $this->entityManager->getRepository(QrCodePayment::class)
                ->findBy(['cashDoc' => null]);
        foreach ($payments as $qrCodePayment){
            $this->cashDocFromQrCodePayment($qrCodePayment);
            
            if (time() > $startTime + 840){
                break;
            }
        }
        
        return;
    }
    
    /**
     * Обновить данные торгового эквайринга
     */    
    public function updateAcquiringPayments()
    {
        $cashDocs = $this->entityManager->getRepository(CashDoc::class)
                ->findForAsquiring();
        if (count($cashDocs)){        
            foreach ($cashDocs as $cashDoc){
                $payment = $this->entityManager->getRepository(AplPayment::class)
                        ->findOneBy(['cashDoc' => $cashDoc->getId()]);
                if ($payment == null && $cashDoc->getContact()){
                    $payment = new AplPayment();
                    $payment->setAplPaymentId($cashDoc->getAplId());
                    $payment->setAplPaymentDate($cashDoc->getDateCreated());
                    $payment->setAplPaymentSum(($cashDoc->getKind() == CashDoc::KIND_OUT_RETURN_CLIENT) ? -$cashDoc->getAmount():$cashDoc->getAmount());
                    if ($cashDoc->getOrder()){
                        $payment->setAplPaymentType('Orders');
                        $payment->setAplPaymentTypeId($cashDoc->getOrder()->getAplId());
                    } else {
                        $payment->setAplPaymentType('Users');
                        $payment->setAplPaymentTypeId($cashDoc->getContact()->getClient()->getAplId());                            
                    }   
                    $payment->setCashDoc($cashDoc);

                    $this->entityManager->persist($payment);
                }    
            }
            $this->entityManager->flush();
        }    
        return;
    }
}
