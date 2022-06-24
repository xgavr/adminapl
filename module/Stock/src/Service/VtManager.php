<?php
namespace Stock\Service;

use Stock\Entity\Vt;
use Application\Entity\Order;
use Stock\Entity\Ntd;
use Stock\Entity\Unit;
use Company\Entity\Country;
use Stock\Entity\VtGood;
use Admin\Entity\Log;
use Stock\Entity\Movement;
use Stock\Entity\Mutual;
use Stock\Entity\Retail;
use Company\Entity\Office;
use Stock\Entity\Comiss;
use Stock\Entity\Register;

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
     * Дата запрета
     * @var string
     */
    private $allowDate;
    
    /**
     * Constructs the service.
     */
    public function __construct($entityManager, $logManager, $orderManager, $adminManager) 
    {
        $this->entityManager = $entityManager;
        $this->logManager = $logManager;
        $this->orderManager = $orderManager;
        $this->adminManager = $adminManager;

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
     */
    public function updateVtMutuals($vt)
    {
        
        $this->entityManager->getRepository(Mutual::class)
                ->removeDocMutuals($vt->getLogKey());
        
        $contract = $this->orderManager->findDefaultContract($vt->getOrder()->getOffice(), 
                $vt->getOrder()->getLegal(), $vt->getOrder()->getDateOper(), 
                $vt->getOrder()->getAplId());
        
        $data = [
            'doc_key' => $vt->getLogKey(),
            'doc_type' => Movement::DOC_VT,
            'doc_id' => $vt->getId(),
            'date_oper' => $vt->getDocDate(),
            'status' => Mutual::getStatusFromVt($vt),
            'revise' => Mutual::REVISE_NOT,
            'amount' => -$vt->getAmount(),
            'legal_id' => $vt->getOrder()->getLegal()->getId(),
            'contract_id' => $contract->getId(),
            'office_id' => $vt->getOffice()->getId(),
            'company_id' => $vt->getOrder()->getCompany()->getId(),
        ];

        $this->entityManager->getRepository(Mutual::class)
                ->insertMutual($data);
        
        return;
    }    
    
    /**
     * Обновить взаиморасчеты возврата розничного заказа
     * 
     * @param Vt $vt
     */
    public function updateVtRetails($vt)
    {
        $this->entityManager->getRepository(Retail::class)
                ->removeOrderRetails($vt->getLogKey());        
        
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
        ];

        $this->entityManager->getRepository(Retail::class)
                ->insertRetail($data);
        
        return;
    }    
    
    
    /**
     * Обновить движения документа
     * 
     * @param Vt $vt
     */
    public function updateVtMovement($vt)
    {
        
        $this->entityManager->getRepository(Register::class)
                ->vtRegister($vt);
        $this->entityManager->getRepository(Movement::class)
                ->removeDocMovements($vt->getLogKey());
        $this->entityManager->getRepository(Comiss::class)
                ->removeDocComiss($vt->getLogKey());        
        
        $vtGoods = $this->entityManager->getRepository(VtGood::class)
                ->findByVt($vt->getId());
        
        foreach ($vtGoods as $vtGood){
            if ($vt->getStatus() != Vt::STATUS_RETIRED){
                $movements = $this->entityManager->getRepository(Movement::class)
                        ->findBy(['docKey' => $vt->getOrder()->getLogKey(), 'good' => $vtGood->getGood()->getId()]);
                
                $posting = $vtGood->getQuantity();
                
                $take = VtGood::TAKE_NO;
                foreach ($movements as $movement){

                    $quantity = min($vtGood->getQuantity(), -$movement->getQuantity());
                    $amount = $quantity*$vtGood->getAmount()/$vtGood->getQuantity();

                    $data = [
                        'doc_key' => $vt->getLogKey(),
                        'doc_type' => Movement::DOC_VT,
                        'doc_id' => $vt->getId(),
                        'base_type' => ($vt->getStatus() == Vt::STATUS_COMMISSION) ? Movement::DOC_VT:$movement->getBaseType(),
                        'base_id' => ($vt->getStatus() == Vt::STATUS_COMMISSION) ? $vt->getId():$movement->getBaseId(),
                        'doc_row_key' => $vtGood->getDocRowKey(),
                        'doc_row_no' => $vtGood->getRowNo(),
                        'date_oper' => $vt->getDocDate(),
                        'status' => Movement::getStatusFromVt($vt),
                        'quantity' => $quantity,
                        'amount' => $amount,
                        'good_id' => $vtGood->getGood()->getId(),
                        'office_id' => $vt->getOffice()->getId(),
                        'company_id' => $vt->getOrder()->getCompany()->getId(),
                    ];

                    $this->entityManager->getRepository(Movement::class)
                            ->insertMovement($data);

                    if ($vt->getStatus() == Vt::STATUS_COMMISSION){
                        unset($data['base_type']);
                        unset($data['base_id']);
                        $data['contact_id'] = $vt->getOrder()->getContact()->getId();
                        $this->entityManager->getRepository(Comiss::class)
                                ->insertComiss($data);
                    } else {
                        if ($movement->getStatus() == Movement::STATUS_COMMISSION){
                            // вернуть на комиссию
                            unset($data['base_type']);
                            unset($data['base_id']);
                            $data['contact_id'] = $movement->getContact()->getId();
                            $this->entityManager->getRepository(Comiss::class)
                                    ->insertComiss($data);                            
                            
                            $data = [
                                'doc_key' => $vt->getLogKey(),
                                'doc_type' => Movement::DOC_ORDER,
                                'doc_id' => $vt->getId(),
                                'date_oper' => $vt->getDateOper(),
                                'status' => Retail::getStatusFromVt($vt),
                                'revise' => Retail::REVISE_NOT,
                                'amount' => $amount,
                                'contact_id' => $movement->getContact()->getId(),
                                'office_id' => $vt->getOffice()->getId(),
                                'company_id' => $vt->getOrder()->getCompany()->getId(),
                            ];

                            $this->entityManager->getRepository(Retail::class)
                                    ->insertRetail($data);                                
                        }
                    }
                    
                    $posting -= $quantity;
                    if ($posting <= 0){
                        break;
                    }
                }    
                if ($posting == 0){
                    $take = VtGood::TAKE_OK;
                }
                $this->entityManager->getConnection()
                        ->update('vt_good', ['take' => $take], ['id' => $vtGood->getId()]);
            }    
            //обновить количество продаж товара
            $rCount = $this->entityManager->getRepository(Movement::class)
                    ->goodMovementRetail($vtGood->getGood()->getId());

            $this->entityManager->getConnection()
                    ->update('goods', ['retail_count' => -$rCount], ['id' => $vtGood->getGood()->getId()]);
        }
        
        return;
    }    
    
    
    /**
     * Перепроведение возврата
     * @param Vt $vt
     */
    public function repostVt($vt)
    {
        $this->updateVtMovement($vt);
        $this->updateVtRetails($vt);
        if ($vt->getOrder()->getLegal()){
            $this->updateVtMutuals($vt);
        } else {
            $this->entityManager->getRepository(Mutual::class)
                    ->removeDocMutuals($vt->getLogKey());            
        }    
        
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
}

