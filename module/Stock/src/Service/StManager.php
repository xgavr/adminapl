<?php
namespace Stock\Service;

use Stock\Entity\St;
use Stock\Entity\StGood;
use Admin\Entity\Log;
use Stock\Entity\Movement;
use Stock\Entity\Comiss;
use Stock\Entity\Retail;
use Stock\Entity\Register;

/**
 * This service is responsible for adding/editing ptu.
 */
class StManager
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
    public function __construct($entityManager, $logManager, $adminManager) 
    {
        $this->entityManager = $entityManager;
        $this->logManager = $logManager;
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
     * Обновить движения документа
     * 
     * @param St $st
     */
    public function updateStMovement($st)
    {
        
        $this->entityManager->getRepository(Register::class)
                ->stRegister($st);
        $this->entityManager->getRepository(Movement::class)
                ->removeDocMovements($st->getLogKey());
        $this->entityManager->getRepository(Comiss::class)
                ->removeDocComiss($st->getLogKey());
        $this->entityManager->getRepository(Retail::class)
                ->removeOrderRetails($st->getLogKey());
        
        if ($st->getStatus() == St::STATUS_ACTIVE){
            $stGoods = $this->entityManager->getRepository(StGood::class)
                    ->findBySt($st->getId());
        
            foreach ($stGoods as $stGood){
                $bases = $this->entityManager->getRepository(Movement::class)
                        ->findBases($stGood->getGood()->getId(), $st->getDocDate(), $st->getOffice()->getId());
                
                $write = $stGood->getQuantity();
                
                $take = StGood::TAKE_NO;
                
                foreach ($bases as $base){
                    $movement = $this->entityManager->getRepository(Movement::class)
                            ->findOneByDocKey($base['docKey']);
                    
                    $quantity = min($base['rest'], $write);
                    $amount = $quantity*$stGood->getAmount()/$stGood->getQuantity();

                    $data = [
                        'doc_key' => $st->getLogKey(),
                        'doc_type' => Movement::DOC_ST,
                        'doc_id' => $st->getId(),
                        'base_type' => $movement->getDocType(),
                        'base_id' => $movement->getDocId(),
                        'doc_row_key' => $stGood->getDocRowKey(),
                        'doc_row_no' => $stGood->getRowNo(),
                        'date_oper' => $st->getDocDate(),
                        'status' => Movement::getStatusFromSt($st),
                        'quantity' => -$quantity,
                        'amount' => -$amount,
                        'good_id' => $stGood->getGood()->getId(),
                        'office_id' => $st->getOffice()->getId(),
                        'company_id' => $st->getCompany()->getId(),
                    ];

                    $this->entityManager->getRepository(Movement::class)
                            ->insertMovement($data); 

                    if ($movement->getStatus() == Movement::STATUS_COMMISSION){
                        $comiss = $this->entityManager->getRepository(Comiss::class)
                                ->findOneByDocKey($base['docKey']);
                        $data = [
                            'doc_key' => $st->getLogKey(),
                            'doc_type' => Movement::DOC_ST,
                            'doc_id' => $vt->getId(),
                            'doc_row_key' => $stGood->getRowKey(),
                            'doc_row_no' => $stGood->getRowNo(),
                            'date_oper' => $st->getDateOper(),
                            'status' => Movement::getStatusFromSt($st),
                            'quantity' => -$quantity,
                            'amount' => -$amount,
                            'good_id' => $stGood->getGood()->getId(),
                            'office_id' => $st->getOffice()->getId(),
                            'company_id' => $st->getCompany()->getId(),
                            'contact_id' => $comiss->getContact()->getId(),
                        ];
                        $this->entityManager->getRepository(Comiss::class)
                                ->insertComiss($data);

                        if ($st->getWriteOff() != St::WRITE_COMMISSION){
                            $data = [
                                'doc_key' => $st->getLogKey(),
                                'doc_type' => Movement::DOC_ST,
                                'doc_id' => $st->getId(),
                                'date_oper' => $st->getDateOper(),
                                'status' => Retail::getStatusFromSt($st),
                                'revise' => Retail::REVISE_NOT,
                                'amount' => -$amount,
                                'contact_id' => $comiss->getContact()->getId(),
                                'office_id' => $st->getOffice()->getId(),
                                'company_id' => $st->getCompany()->getId(),
                            ];

                            $this->entityManager->getRepository(Retail::class)
                                    ->insertRetail($data);   
                        }
                    }                    
                    
                    $write -= $quantity;
                    if ($write <= 0){
                        break;
                    }
                }    
                
                if ($write == 0){
                    $take = StGood::TAKE_OK;
                }
                
                $this->entityManager->getConnection()
                        ->update('st_good', ['take' => $take], ['id' => $stGood->getId()]);
            }    
        }
        
        return;
    }    
    
    
    /**
     * Перепроведение СТ
     * @param St $st
     */
    public function repostSt($st)
    {
        $this->updateStMovement($st);
        
        return;
    }

    /**
     * Перепроведение всех СТ
     */
    public function repostAllSt()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        
        $stQuery = $this->entityManager->getRepository(St::class)
                ->queryAllSt();
        $iterable = $stQuery->iterate();
        
        foreach ($iterable as $row){
            foreach($row as $st){ 
                $this->repostSt($st);
                $this->entityManager->detach($st);
                unset($st);
            }    
        }    
        
        return;
    }


    /**
     * Adds a new st.
     * @param array $data
     * @return integer
     */
    public function addSt($data)
    {
        if ($data['doc_date'] > $this->allowDate){
            $st = new St();        
            $st->setAplId($data['apl_id']);
            $st->setDocDate($data['doc_date']);
            $st->setComment($data['comment']);
            $st->setStatusEx($data['status_ex']);
            $st->setStatus($data['status']);
            $st->setStatusDoc(St::STATUS_DOC_NOT_RECD);
            $st->setWriteOff($data['writeOff']);
            $st->setOffice($data['office']);
            $st->setCompany($data['company']);
            $st->setAmount(0);
            $st->setDateCreated(date('Y-m-d H:i:s'));
            $st->setCost(null);
            $st->setUser(null);
            $st->setStatusAccount(St::STATUS_ACCOUNT_NO);
            if (!empty($data['cost'])){
                $st->setCost($data['cost']);
            }
            if (!empty($data['user'])){
                $st->setUser($data['user']);
            }

            $this->entityManager->persist($st);        
            $this->entityManager->flush();

            return $st;        
        }    
        
        return;
    }
    
    /**
     * Update st.
     * @param St $st
     * @param array $data
     * @return integer
     */
    public function updateSt($st, $data)            
    {
        if ($data['doc_date'] > $this->allowDate){
            $st->setAplId($data['apl_id']);
            $st->setDocDate($data['doc_date']);
            $st->setComment($data['comment']);
            $st->setStatus($data['status']);
            $st->setStatusEx(St::STATUS_EX_NEW);
            $st->setWriteOff($data['writeOff']);
            $st->setOffice($data['office']);
            $st->setCompany($data['company']);
            $st->setCost(null);
            $st->setUser(null);
            $st->setStatusAccount(St::STATUS_ACCOUNT_NO);
            if (!empty($data['cost'])){
                $st->setCost($data['cost']);
            }
            if (!empty($data['user'])){
                $st->setUser($data['user']);
            }

            $this->entityManager->persist($st);
            $this->entityManager->flush($st);
        }    
        
        return;
    }
    
    /**
     * Adds a new st-good.
     * @param integer $stId
     * @param array $data
     * @param integer $rowNo
     * 
     * @return integer
     */
    public function addStGood($stId, $data, $rowNo)
    {
//        var_dump($data); exit;
        $stGood = [
            'st_id' => $stId,
            'status' => (isset($data['status'])) ? $data['status']:StGood::STATUS_ACTIVE,
            'status_doc' => (isset($data['statusDoc'])) ? $data['statusDoc']:StGood::STATUS_DOC_RECD,
            'quantity' => $data['quantity'],
            'amount' => $data['amount'],
            'good_id' => $data['good_id'],
            'comment' => (isset($data['comment'])) ? $data['comment']:'',
//            'info' => $data['info'],
            'row_no' => $rowNo,
            'take' => StGood::TAKE_NO,
        ];
        //var_dump($stGood); exit;
        
        $connection = $this->entityManager->getConnection(); 
        $connection->insert('st_good', $stGood);
        return;
    }
    
    /**
     * Update st_good.
     * @param StGood $stGood
     * @param array $data
     * @return integer
     */
    public function updateStGood($stGood, $data)            
    {
        
        $connection = $this->entityManager->getConnection(); 
        $connection->update('st_good', $data, ['id' => $stGood->getId()]);
        return;
    }
    
    /**
     * Обновить сумму СТ
     * @param St $st
     */
    public function updateStAmount($st)
    {
        $preLog = $this->entityManager->getRepository(Log::class)
                ->findOneByLogKey($st->getLogKey());
        if (!$preLog){
            $this->logManager->infoSt($st, Log::STATUS_INFO);            
        }
        
        $stAmountTotal = $this->entityManager->getRepository(St::class)
                ->stAmountTotal($st);
        $st->setAmount($stAmountTotal);
        $this->entityManager->persist($st);
        $this->entityManager->flush($st);
        
        $this->entityManager->refresh($st);
        $this->repostSt($st);
        $this->logManager->infoSt($st, Log::STATUS_UPDATE);
        return;
    }
    
    /**
     * Удаление строк СТ
     * @param St $st
     */
    public function removeStGood($st)
    {
        $this->entityManager->getConnection()
                ->delete('st_good', ['st_id' => $st->getId()]);
        return;
    }
    
    /**
     * Обновление строк СТ
     * 
     * @param St $st
     * @param array $data
     */
    public function updateStGoods($st, $data)
    {
        $this->removeStGood($st);
        
        $rowNo = 1;
        if ($data){
            foreach ($data as $row){
                $this->addStGood($st->getId(), $row, $rowNo);
                $rowNo++;
            }
        }    
        
        $this->updateStAmount($st);
        return;
    }   
    
    
    /**
     * Удаление СТ
     * 
     * @param St $st
     */
    public function removeSt($st)
    {
        if ($st->getDocDate() > $this->allowDate){
            $this->logManager->infoSt($st, Log::STATUS_DELETE);
            $this->entityManager->getRepository(Movement::class)
                    ->removeDocMovements($st->getLogKey());
            $this->entityManager->getRepository(Comiss::class)
                    ->removeDocComiss($st->getLogKey());
            $this->entityManager->getRepository(Retail::class)
                    ->removeOrderRetails($st->getLogKey());
            $this->removeStGood($st);

            $this->entityManager->getConnection()->delete('st', ['id' => $st->getId()]);
        }    
        return;
    }
}

