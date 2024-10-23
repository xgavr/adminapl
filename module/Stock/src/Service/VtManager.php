<?php
namespace Stock\Service;

use Stock\Entity\Vt;
use Application\Entity\Order;
use Stock\Entity\VtGood;
use Admin\Entity\Log;
use Stock\Entity\Movement;
use Stock\Entity\Mutual;
use Stock\Entity\Retail;
use Company\Entity\Office;
use Stock\Entity\Comiss;
use Stock\Entity\Register;
use Stock\Entity\ComissBalance;
use Stock\Entity\ComitentBalance;
use Stock\Entity\Comitent;
use Company\Entity\Contract;
use Laminas\Json\Encoder;
use Cash\Entity\CashDoc;
use Application\Entity\Oem;

/**
 * This service is responsible for adding/editing ptu.
 */
class VtManager
{
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;  
    
    /**
     * Log manager
     * @var \Admin\Service\LogManager
     */
    private $logManager;
        
    /**
     * Order manager
     * @var \Application\Service\OrderManager
     */
    private $orderManager;
        
    /**
     * Admin manager
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;

    /**
     * Zp manager
     * @var \Zp\Service\ZpCalculator
     */
    private $zpManager;

    /**
     * Cash manager
     * @var \Cash\Service\CashManager
     */
    private $cashManager;
    
    /**
     * Fold manager
     * @var \GoodMap\Service\FoldManager
     */
    private $foldManager;
    
    /**
     * Дата запрета
     * @var string
     */
    private $allowDate;
    
    /**
     * Constructs the service.
     */
    public function __construct($entityManager, $logManager, $orderManager, $adminManager,
            $zpManager, $cashManager, $foldManager) 
    {
        $this->entityManager = $entityManager;
        $this->logManager = $logManager;
        $this->orderManager = $orderManager;
        $this->adminManager = $adminManager;
        $this->zpManager = $zpManager;
        $this->cashManager = $cashManager;
        $this->foldManager = $foldManager;

        $setting = $this->adminManager->getSettings();
        $this->allowDate = $setting['allow_date'];        
    }
    
    /**
     * Получить дату запрета
     * @return date
     */
    public function getAllowDate()
    {
        return $this->allowDate; 
    }
                
    /**
     * Обновить взаиморасчеты документа
     * 
     * @param Vt $vt
     * @param float $docStamp
     */
    public function updateVtMutuals($vt, $docStamp)
    {
        
        $this->entityManager->getRepository(Mutual::class)
                ->removeDocMutuals($vt->getLogKey());

        if ($vt->getStatus() == Vt::STATUS_COMMISSION){
            return;
        }

        if ($vt->getOrder()->getContract()){
            if ($vt->getOrder()->getContract()->getKind() == Contract::KIND_COMITENT){
                return;
            }
        }
        
        $contractId = null;
        if ($vt->getOrder()->getLegal()){
            $orderRetail = $this->entityManager->getRepository(Mutual::class)
                    ->findOneBy(['docKey' => $vt->getOrder()->getLogKey()]);
            if ($orderRetail){
                if ($orderRetail->getContract()){
                    $contractId = $orderRetail->getContract()->getId();
                }    
            }    
        }
        $data = [
            'doc_key' => $vt->getLogKey(),
            'doc_type' => Movement::DOC_VT,
            'doc_id' => $vt->getId(),
            'date_oper' => $vt->getDocDate(),
            'status' => Mutual::getStatusFromVt($vt),
            'revise' => Mutual::REVISE_NOT,
            'amount' => -$vt->getAmount(),
            'legal_id' => $vt->getOrder()->getLegal()->getId(),
            'contract_id' => $contractId,
            'office_id' => $vt->getOffice()->getId(),
            'company_id' => $vt->getOrder()->getCompany()->getId(),
            'doc_stamp' => $docStamp,
        ];

        $this->entityManager->getRepository(Mutual::class)
                ->insertMutual($data);
        
        return;
    }    
    
    /**
     * Обновить взаиморасчеты возврата розничного заказа
     * 
     * @param Vt $vt
     * @param float $docStamp
     */
    public function updateVtRetails($vt, $docStamp)
    {
        $this->entityManager->getRepository(Retail::class)
                ->removeOrderRetails($vt->getLogKey());  
        
        if ($vt->getStatus() == Vt::STATUS_COMMISSION){
            return;
        }
        
        if ($vt->getOrder()->getContract()){
            if ($vt->getOrder()->getContract()->getKind() == Contract::KIND_COMITENT){
                return;
            }
        }
        
        $legalId = $contractId = null;
        if ($vt->getOrder()->getLegal()){
            $legalId = $vt->getOrder()->getLegal()->getId();
            $orderRetail = $this->entityManager->getRepository(Retail::class)
                    ->findOneBy(['docKey' => $vt->getOrder()->getLogKey()]);
            if ($orderRetail){
                if ($orderRetail->getContract()){
                    $contractId = $orderRetail->getContract()->getId();
                }    
            }    
        }
        $data = [
            'doc_key' => $vt->getLogKey(),
            'doc_type' => Movement::DOC_VT,
            'doc_id' => $vt->getId(),
            'date_oper' => $vt->getDocDate(),
            'status' => Retail::getStatusFromVt($vt),
            'revise' => Retail::REVISE_NOT,
            'amount' => -$vt->getAmount(),
            'contact_id' => $vt->getOrder()->getContact()->getId(),
            'office_id' => $vt->getOffice()->getId(),
            'company_id' => $vt->getOrder()->getCompany()->getId(),
            'legal_id' => $legalId,
            'contract_id' => $contractId,
            'doc_stamp' => $docStamp,
            'user_id' => $vt->getOrder()->getUserId(),
        ];

        $this->entityManager->getRepository(Retail::class)
                ->insertRetail($data);
        
        return;
    }    
    
    
    /**
     * Обновить движения документа
     * 
     * @param Vt $vt
     * @param float $docStamp
     */
    public function updateVtMovement($vt, $docStamp)
    {        
        $this->entityManager->getRepository(Movement::class)
                ->removeDocMovements($vt->getLogKey());
        $this->entityManager->getRepository(Comiss::class)
                ->removeDocComiss($vt->getLogKey());        
        $this->entityManager->getRepository(Comitent::class)
                ->removeDocComitent($vt->getLogKey());        
        
        $vtGoods = $this->entityManager->getRepository(VtGood::class)
                ->findByVt($vt->getId());
        
        $vtTake = $vt->getStatusAccount();
        if ($vt->getStatusAccount() == Vt::STATUS_TAKE_NO){
            $vtTake = Vt::STATUS_ACCOUNT_NO;
        }

        foreach ($vtGoods as $vtGood){
            if ($vt->getStatus() != Vt::STATUS_RETIRED){
                
                $params = ['docKey' => $vt->getOrder()->getLogKey(), 'good' => $vtGood->getGood()->getId()];
                if ($vtGood->getBaseKey()){
                    $params['baseKey'] = $vtGood->getBaseKey();
                }
                
                $movements = $this->entityManager->getRepository(Movement::class)
                        ->findBy($params, ['quantity' => 'ASC']);
                
                $posting = $vtGood->getQuantity();
                
                $take = VtGood::TAKE_NO;
                foreach ($movements as $movement){

                    $quantity = min($posting, -$movement->getQuantity());
                    $amount = $quantity*$vtGood->getAmount()/$vtGood->getQuantity();
                    $basePrice = abs($movement->getBaseAmount()/$movement->getQuantity());
                    $baseAmount = $basePrice*$quantity;
                    
                    if ($vt->getOrder()->isComitentContract()){ //если передача на комиссию
                        $amount = $baseAmount;
                    }           
                    if ($vt->getStatus() == Vt::STATUS_COMMISSION){
                        $baseAmount = $amount;
                    }

                    $data = [
                        'doc_key' => $vt->getLogKey(),
                        'doc_type' => Movement::DOC_VT,
                        'doc_id' => $vt->getId(),
                        'base_key' => ($vt->getStatus() == Vt::STATUS_COMMISSION) ? $vt->getLogKey():$movement->getBaseKey(),
                        'base_type' => ($vt->getStatus() == Vt::STATUS_COMMISSION) ? Movement::DOC_VT:$movement->getBaseType(),
                        'base_id' => ($vt->getStatus() == Vt::STATUS_COMMISSION) ? $vt->getId():$movement->getBaseId(),
                        'doc_row_key' => $vtGood->getDocRowKey(),
                        'doc_row_no' => $vtGood->getRowNo(),
                        'date_oper' => date('Y-m-d 21:00:00', strtotime($vt->getDocDate())),
                        'status' => Movement::getStatusFromVt($vt),
                        'quantity' => $quantity,
                        'amount' => $amount,
                        'base_amount' => $baseAmount,
                        'good_id' => $vtGood->getGood()->getId(),
                        'office_id' => $vt->getOffice()->getId(),
                        'company_id' => $vt->getOrder()->getCompany()->getId(),
                        'doc_stamp' => $docStamp - 60*60*12, //-12 часов
                        'user_id' => $vt->getOrder()->getUserId(),
                        'oe' => $movement->getOe(),
                    ];

                    $this->entityManager->getRepository(Movement::class)
                            ->insertMovement($data);
                    
                    $this->entityManager->getRepository(Comitent::class)
                            ->insertVtComitent($vt, $data);
                    
                    if (!empty($movement->getOe())){
                        $this->entityManager->getRepository(Oem::class)
                                ->updateRating($vt->getGood(), $movement->getOe());
                    }    

                    //проверка компании заказа и компании офиса, переместить, если не совпадает
                    if (!$vt->getOrder()->getCompany()->companyInOffice($vt->getOffice())){
                        
                        $officeCompany = $this->entityManager->getRepository(Office::class)
                                ->findDefaultCompany($vt->getOffice(), $vt->getDocDate());
                        $data['company_id'] = $officeCompany->getId();
    
                        $this->entityManager->getRepository(Movement::class)
                            ->insertMovement($data);

                        $data['quantity'] = -$quantity;
                        $data['amount'] = -$amount;
                        $data['company_id'] = $vt->getOrder()->getCompany()->getId();
                        $data['doc_stamp'] = $docStamp;
    
                        $this->entityManager->getRepository(Movement::class)
                            ->insertMovement($data);

                    }

                    if ($vt->getStatus() == Vt::STATUS_COMMISSION){
                        unset($data['base_key']);
                        unset($data['base_type']);
                        unset($data['base_id']);
                        unset($data['base_amount']);
                        $data['contact_id'] = $vt->getOrder()->getContact()->getId();
                        $this->entityManager->getRepository(Comiss::class)
                                ->insertComiss($data);
                    } else {
                        $baseMovement = $this->entityManager->getRepository(Movement::class)
                                ->findOneBy(['docKey' => $movement->getBaseKey(), 'good' => $movement->getGood()->getId()]);
                        $comiss = $this->entityManager->getRepository(Comiss::class)
                                ->findOneByDocKey($movement->getBaseKey());
                        if ($baseMovement && $comiss){
                            if ($baseMovement->getStatus() == Movement::STATUS_COMMISSION){
                                // вернуть на комиссию
                                unset($data['base_key']);
                                unset($data['base_type']);
                                unset($data['base_id']);
                                unset($data['base_amount']);
                                $data['contact_id'] = $comiss->getContact()->getId();
                                $data['amount'] = -$basePrice*$movement->getQuantity();
                                $this->entityManager->getRepository(Comiss::class)
                                        ->insertComiss($data);                            

                                $legalId = $contractId = null;
                                
                                $data = [
                                    'doc_key' => $vt->getLogKey(),
                                    'doc_type' => Movement::DOC_VT,
                                    'doc_id' => $vt->getId(),
                                    'date_oper' => $vt->getDocDate(),
                                    'status' => Retail::getStatusFromVt($vt),
                                    'revise' => Retail::REVISE_NOT,
                                    'amount' => -$basePrice*$movement->getQuantity(),
                                    'contact_id' => $comiss->getContact()->getId(),
                                    'office_id' => $vt->getOffice()->getId(),
                                    'company_id' => $vt->getOrder()->getCompany()->getId(),
                                    'legal_id' => $legalId,
                                    'contract_id' => $contractId,
                                    'doc_stamp' => $docStamp,
                                    'user_id' => $vt->getOrder()->getUserId(),
                                ];
                                $this->entityManager->getRepository(Retail::class)
                                        ->insertRetail($data);                                
                            }
                        }    
                    }
                    
                    
                    $posting -= $quantity;
                    if ($posting <= 0){
                        break;
                    }
                }    
                if ($posting == 0){
                    $take = VtGood::TAKE_OK;
                } else {
                    $vtTake = Vt::STATUS_TAKE_NO;
                }
                $this->entityManager->getConnection()
                        ->update('vt_good', ['take' => $take], ['id' => $vtGood->getId()]);
            }    
            //обновить количество продаж товара
            $rCount = $this->entityManager->getRepository(Movement::class)
                    ->goodMovementRetail($vtGood->getGood()->getId());

            $this->entityManager->getConnection()
                    ->update('goods', ['retail_count' => -$rCount], ['id' => $vtGood->getGood()->getId()]);
            $this->entityManager->getRepository(Movement::class)
                    ->updateGoodBalance($vtGood->getGood()->getId());
            $this->entityManager->getRepository(ComitentBalance::class)
                    ->updateComitentBalance($vtGood->getGood()->getId()); 

            $this->entityManager->getRepository(ComissBalance::class)
                    ->updateComissBalance($vtGood->getGood()->getId());
        }

        $this->entityManager->getConnection()
                ->update('vt', ['status_account' => $vtTake], ['id' => $vt->getId()]);        
        
        return;
    }    
    
    
    /**
     * Перепроведение возврата
     * @param Vt $vt
     */
    public function repostVt($vt)
    {
        if ($vt->getDocDate() >= $this->getAllowDate()){
            $docStamp = $this->entityManager->getRepository(Register::class)
                    ->vtRegister($vt);
        } else {
            $register = $this->entityManager->getRepository(Register::class)
                    ->findOneBy(['docKey' => $vt->getLogKey()]);
            $docStamp = $register->getDocStamp();
        }    
        
        $this->updateVtRetails($vt, $docStamp);
        if ($vt->getOrder()->getLegal()){
            $this->updateVtMutuals($vt, $docStamp);
        } else {
            $this->entityManager->getRepository(Mutual::class)
                    ->removeDocMutuals($vt->getLogKey());            
        }    
        $this->updateVtMovement($vt, $docStamp);
        
        if ($vt->getDocDate() >= $this->getAllowDate()){
            $this->zpManager->addVtCalculator($vt);
        }    
        
        $this->cashManager->addUserVtTransaction($vt, $docStamp);
        $this->foldManager->vtFold($vt, $docStamp);
        return;
    }

    /**
     * Перепроведение всех возвратов
     */
    public function repostAllVt()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        
        $vtQuery = $this->entityManager->getRepository(Vt::class)
                ->queryAllVt();
        $iterable = $vtQuery->iterate();
        
        foreach ($iterable as $row){
            foreach($row as $vt){ 
                $this->repostVt($vt);
                $this->entityManager->detach($vt);
            }    
        }    
        
        return;
    }


    /**
     * Adds a new vt.
     * @param Office $office
     * @param Order $order
     * @param array $data
     * @return integer
     */
    public function addVt($office, $order, $data)
    {
        if ($data['doc_date'] > $this->allowDate){
            $vt = new Vt();     
            $vt->setOffice($office);
            $vt->setOrder($order);
            $vt->setAplId($data['apl_id']);
            //$vt->setDocNo($data['doc_no']);
            $vt->setDocDate($data['doc_date']);
            $vt->setComment($data['comment']);
            $vt->setStatusEx($data['status_ex']);
            $vt->setStatus($data['status']);
            $vt->setStatusDoc(Vt::STATUS_DOC_NOT_RECD);
            $vt->setAmount(0);
            $vt->setDateCreated(date('Y-m-d H:i:s'));
            $vt->setStatusAccount(Vt::STATUS_ACCOUNT_NO);

            $this->entityManager->persist($vt);        
            $this->entityManager->flush();

            return $vt;
        }
        return;
    }
    
    /**
     * Update vt.
     * @param Vt $vt
     * @param array $data
     * @return integer
     */
    public function updateVt($vt, $data)            
    {
        if ($data['doc_date'] > $this->allowDate){
            $vt->setAplId($data['apl_id']);
            //$vt->setDocNo($data['doc_no']);
            $vt->setDocDate($data['doc_date']);
            $vt->setComment($data['comment']);
            $vt->setStatusEx($data['status_ex']);
            $vt->setStatus($data['status']);
            $vt->setStatusAccount(Vt::STATUS_ACCOUNT_NO);

            $this->entityManager->persist($vt);
            $this->entityManager->flush($vt);
        }    
        return;
    }
    
    /**
     * Adds a new vt-good.
     * @param integer $vtId
     * @param array $data
     * @param integer $rowNo
     * 
     * @return integer
     */
    public function addVtGood($vtId, $data, $rowNo)
    {
//        var_dump($data); exit;
        $vtGood = [
            'vt_id' => $vtId,
            'status' => (isset($data['status'])) ? $data['status']:VtGood::STATUS_ACTIVE,
            'status_doc' => (isset($data['statusDoc'])) ? $data['statusDoc']:VtGood::STATUS_DOC_RECD,
            'quantity' => $data['quantity'],
            'amount' => $data['amount'],
            'good_id' => $data['good_id'],
            'comment' => (isset($data['comment'])) ? $data['comment']:'',
            'base_key' => (isset($data['baseKey'])) ? $data['baseKey']:null,
//            'info' => $data['info'],
            'row_no' => $rowNo,
            'take' => VtGood::TAKE_NO,
        ];
        
        $connection = $this->entityManager->getConnection(); 
        $connection->insert('vt_good', $vtGood);
        return;
    }
    
    /**
     * Update vt_good.
     * @param VtGood $vtGood
     * @param array $data
     * @return integer
     */
    public function updateVtGood($vtGood, $data)            
    {
        
        $connection = $this->entityManager->getConnection(); 
        $connection->update('vt_good', $data, ['id' => $vtGood->getId()]);
        return;
    }
    
    /**
     * Обновить сумму возврата
     * @param Vt $vt
     */
    public function updateVtAmount($vt)
    {
        $preLog = $this->entityManager->getRepository(Log::class)
                ->findOneByLogKey($vt->getLogKey());
        if (!$preLog){
            $this->logManager->infoVt($vt, Log::STATUS_INFO);            
        }
        
        $vtAmountTotal = $this->entityManager->getRepository(Vt::class)
                ->vtAmountTotal($vt);
//        $this->entityManager->getConnection()->update('ptu', ['amount' => $ptuAmountTotal], ['id' => $ptu->getId()]);
        $vt->setAmount($vtAmountTotal);
        $this->entityManager->persist($vt);
        $this->entityManager->flush($vt);
        
        $this->entityManager->refresh($vt);
        $this->repostVt($vt);
        $this->logManager->infoVt($vt, Log::STATUS_UPDATE);
        return;
    }
    
    /**
     * Удаление строк возврата
     * @param Vt $vt
     */
    public function removeVtGood($vt)
    {
        $this->entityManager->getConnection()
                ->delete('vt_good', ['vt_id' => $vt->getId()]);
        return;
    }
    
    /**
     * Обновление строк возврата
     * 
     * @param Vt $vt
     * @param array $data
     */
    public function updateVtGoods($vt, $data)
    {
        $this->removeVtGood($vt);
        
        $rowNo = 1;
        foreach ($data as $row){
            if (isset($row['baseKey'])){
                if ($row['baseKey'] == VtGood::BASE_KEY_AUTO){
                    unset($row['baseKey']);
                }
            }
            $this->addVtGood($vt->getId(), $row, $rowNo);
            $rowNo++;
        }
        
        $this->updateVtAmount($vt);
        return;
    }   
    
    
    /**
     * Ужаление возврата
     * 
     * @param Vt $vt
     */
    public function removeVt($vt)
    {
        if ($vt->getDocDate() > $this->allowDate){
            $this->logManager->infoVt($vt, Log::STATUS_DELETE);
            $this->entityManager->getRepository(Mutual::class)
                    ->removeDocMutuals($vt->getLogKey());
            $this->entityManager->getRepository(Retail::class)
                    ->removeOrderRetails($vt->getLogKey());        
            $this->entityManager->getRepository(Movement::class)
                    ->removeDocMovements($vt->getLogKey());
            $this->entityManager->getRepository(Comiss::class)
                    ->removeDocComiss($vt->getLogKey());        
            $this->removeVtGood($vt);

            $this->entityManager->getConnection()->delete('vt', ['id' => $vt->getId()]);
        }    
        
        return;
    }
    
    /**
     * Заменить товар
     * @param Goods $oldGood
     * @param Goods $newGood
     */
    public function changeGood($oldGood, $newGood)
    {
        $rows = $this->entityManager->getRepository(VtGood::class)
                ->findBy(['good' => $oldGood->getId()]);
        foreach ($rows as $row){
            $row->setGood($newGood);
            $this->entityManager->persist($row);
            $this->entityManager->flush();
            $this->repostVt($row->getVt());
        }
        
        return;
    }      
    
    /**
     * Исправить оплаты и отгрузки на сотрудников
     * @return null
     */
    public function fixUserRetail()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(900);
        
        $registers = $this->entityManager->getRepository(CashDoc::class)
                ->findForUserRetailFix();
        foreach ($registers as $register){
            switch ($register->getDocType()){
//                case Movement::DOC_CASH:
//                    $this->cashManager->removeRetails($register->getCashDoc());
//                    $this->cashManager->addRetails($register->getCashDoc(), $register->getDocStamp());
//                    break;
//                case Movement::DOC_ORDER:
//                    $this->entityManager->getRepository(Retail::class)
//                            ->removeOrderRetails($register->getOrder()->getLogKey());                
//                    $this->updateOrderRetails($register->getOrder(), $register->getDocStamp());
//                    $this->cashManager->addUserOrderTransaction($register->getOrder(), $register->getDocStamp());
//                    break;
                case Movement::DOC_VT:
                    $this->entityManager->getRepository(Retail::class)
                            ->removeOrderRetails($register->getVt()->getLogKey());                
                    $this->updateVtRetails($register->getVt(), $register->getDocStamp());
                    $this->cashManager->addUserVtTransaction($register->getVt(), $register->getDocStamp());
                    break;
            }
        }
        
        return;
    }    
    
    /**
     * Исправить отгрузки 
     * @return null
     */
    public function fixVtRetail()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(900);
        
        $ids = $this->entityManager->getRepository(Retail::class)
                ->findVtToFixRetail();
        foreach ($ids as $row){
            var_dump($row);
            $vt = $this->entityManager->getRepository(Vt::class)
                    ->find($row['vtId']);
            if ($vt){
                $this->updateVtRetails($vt, $row['docStamp']);
                if ($vt->getOrder()->getLegal()){
                    $this->updateVtMutuals($vt, $row['docStamp']);
                } else {
                    $this->entityManager->getRepository(Mutual::class)
                            ->removeDocMutuals($vt->getLogKey());            
                }    
            }
//            exit;
        }
        
        return;
    }
            
}

