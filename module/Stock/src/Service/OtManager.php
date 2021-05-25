<?php
namespace Stock\Service;

use Stock\Entity\Vtp;
use Stock\Entity\Ot;
use Stock\Entity\Ptu;
use Stock\Entity\OtGood;
use Admin\Entity\Log;
use Stock\Entity\Movement;
use Stock\Entity\Comiss;

/**
 * This service is responsible for adding/editing ptu.
 */
class OtManager
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
     * Constructs the service.
     */
    public function __construct($entityManager, $logManager) 
    {
        $this->entityManager = $entityManager;
        $this->logManager = $logManager;
    }
    
    /**
     * Обновить движения документа
     * 
     * @param Ot $ot
     */
    public function updateOtMovement($ot)
    {
        
        $this->entityManager->getRepository(Movement::class)
                ->removeDocMovements($ot->getLogKey());
        
        $this->entityManager->getRepository(Comiss::class)
                ->removeDocComiss($ot->getLogKey());

        $otGoods = $this->entityManager->getRepository(OtGood::class)
                ->findByOt($ot->getId());
        
        foreach ($otGoods as $otGood){
            $data = [
                'doc_key' => $ot->getLogKey(),
                'doc_row_key' => $otGood->getDocRowKey(),
                'doc_row_no' => $otGood->getRowNo(),
                'date_oper' => $ot->getDocDate(),
                'status' => $ot->getStatus(),
                'quantity' => $otGood->getQuantity(),
                'amount' => $otGood->getAmount(),
                'good_id' => $otGood->getGood()->getId(),
                'office_id' => $ot->getOffice()->getId(),
                'company_id' => $ot->getCompany()->getId(),
            ];

            $this->entityManager->getRepository(Movement::class)
                    ->insertMovement($data);
            
            if ($ot->getStatus() == Ot::STATUS_COMMISSION){
                $data['user_id'] = $ot->getComiss()->getId();
                $this->entityManager->getRepository(Comiss::class)
                        ->insertComiss($data);
            }
        }
        
        return;
    }    
    
    
    /**
     * Перепроведение ОТ
     * @param Ot $ot
     */
    public function repostOt($ot)
    {
        $this->updateOtMovement($ot);
        
        return;
    }

    /**
     * Перепроведение всех ОП
     */
    public function repostAllOt()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        
        $otQuery = $this->entityManager->getRepository(Ot::class)
                ->queryAllOt();
        $iterable = $otQuery->iterate();
        
        foreach ($iterable as $row){
            foreach($row as $ot){ 
                $this->repostOt($ot);
                $this->entityManager->detach($ot);
                unset($ot);
            }    
        }    
        
        return;
    }


    /**
     * Adds a new Ot.
     * @param array $data
     * @return integer
     */
    public function addOt($data)
    {
        $ot = new Ot();        
        $ot->setAplId($data['apl_id']);
        $ot->setDocDate($data['doc_date']);
        $ot->setComment($data['comment']);
        $ot->setStatusEx($data['status_ex']);
        $ot->setStatus($data['status']);
        $ot->setStatusDoc(Ot::STATUS_DOC_NOT_RECD);
        $ot->setOffice($data['office']);
        $ot->setCompany($data['company']);
        $ot->setAmount(0);
        $ot->setDateCreated(date('Y-m-d H:i:s'));
        if (!empty($data['comiss'])){
            $ot->setComiss($data['comiss']);
        }
        if (!empty($data['doc_no'])){
            $ot->setDocNo($data['doc_no']);
        }
        
        $this->entityManager->persist($ot);        
        $this->entityManager->flush();
        
        return $ot;        
    }
    
    /**
     * Update ot.
     * @param Ot $ot
     * @param array $data
     * @return integer
     */
    public function updateOt($ot, $data)            
    {
        $ot->setAplId($data['apl_id']);
        $ot->setDocDate($data['doc_date']);
        $ot->setComment($data['comment']);
        $ot->setStatusEx($data['status_ex']);
        $ot->setStatus($data['status']);
        $ot->setOffice($data['office']);
        $ot->setCompany($data['company']);
        $ot->setComiss(null);
        if (!empty($data['comiss'])){
            $ot->setComiss($data['comiss']);
        }
        if (!empty($data['doc_no'])){
            $ot->setDocNo($data['doc_no']);
        }
        
        $this->entityManager->persist($ot);
        $this->entityManager->flush($ot);
        
        return;
    }
    
    /**
     * Adds a new ot-good.
     * @param integer $otId
     * @param array $data
     * @param integer $rowNo
     * 
     * @return integer
     */
    public function addOtGood($otId, $data, $rowNo)
    {
//        var_dump($data); exit;
        $otGood = [
            'ot_id' => $otId,
            'status' => (isset($data['status'])) ? $data['status']:OtGood::STATUS_ACTIVE,
            'status_doc' => (isset($data['statusDoc'])) ? $data['statusDoc']:OtGood::STATUS_DOC_RECD,
            'quantity' => $data['quantity'],
            'amount' => $data['amount'],
            'good_id' => $data['good_id'],
            'comment' => (isset($data['comment'])) ? $data['comment']:'',
//            'info' => $data['info'],
            'row_no' => $rowNo,
        ];
        //var_dump($otGood); exit;
        
        $connection = $this->entityManager->getConnection(); 
        $connection->insert('ot_good', $otGood);
        return;
    }
    
    /**
     * Update ot_good.
     * @param OtGood $otGood
     * @param array $data
     * @return integer
     */
    public function updateOtGood($otGood, $data)            
    {
        
        $connection = $this->entityManager->getConnection(); 
        $connection->update('ot_good', $data, ['id' => $otGood->getId()]);
        return;
    }
    
    /**
     * Обновить сумму ОТ
     * @param Ot $ot
     */
    public function updateOtAmount($ot)
    {
        $preLog = $this->entityManager->getRepository(Log::class)
                ->findOneByLogKey($ot->getLogKey());
        if (!$preLog){
            $this->logManager->infoOt($ot, Log::STATUS_INFO);            
        }
        
        $otAmountTotal = $this->entityManager->getRepository(Ot::class)
                ->otAmountTotal($ot);
        $ot->setAmount($otAmountTotal);
        $this->entityManager->persist($ot);
        $this->entityManager->flush($ot);
        
        $this->repostOt($ot);
        $this->logManager->infoOt($ot, Log::STATUS_UPDATE);
        return;
    }
    
    /**
     * Удаление строк ОТ
     * @param Ot $ot
     */
    public function removeOtGood($ot)
    {
        $otGoods = $this->entityManager->getRepository(OtGood::class)
                ->findByOt($ot->getId());
        foreach ($otGoods as $otGood){
            $this->entityManager->getConnection()
                    ->delete('ot_good', ['ot_id' => $ot->getId()]);
        }
        
        return;
    }
    
    /**
     * Обновление строк ОТ
     * 
     * @param Ot $ot
     * @param array $data
     */
    public function updateOtGoods($ot, $data)
    {
        $this->removeOtGood($ot);
        
        $rowNo = 1;
        if ($data){
            foreach ($data as $row){
                $this->addOtGood($ot->getId(), $row, $rowNo);
                $rowNo++;
            }
        }    
        
        $this->updateOtAmount($ot);
        return;
    }   
    
    
    /**
     * Удаление ОТ
     * 
     * @param Ot $ot
     */
    public function removeOt($ot)
    {
        $this->logManager->infoOt($ot, Log::STATUS_DELETE);
        $this->entityManager->getRepository(Movement::class)
                ->removeDocMovements($ot->getLogKey());
        $this->entityManager->getRepository(Comiss::class)
                ->removeDocComiss($ot->getLogKey());
        $this->removeOtGood($ot);
        
        $this->entityManager->getConnection()->delete('ot', ['id' => $ot->getId()]);
        
        return;
    }
}

