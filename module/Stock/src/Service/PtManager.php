<?php
namespace Stock\Service;

use Stock\Entity\Pt;
use Stock\Entity\PtGood;
use Admin\Entity\Log;
use Stock\Entity\Movement;
use Stock\Entity\Comiss;

/**
 * This service is responsible for adding/editing pt.
 */
class PtManager
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
     * @param Pt $pt
     */
    public function updatePtMovement($pt)
    {
        
        $this->entityManager->getRepository(Movement::class)
                ->removeDocMovements($pt->getLogKey());
        
        $ptGoods = $this->entityManager->getRepository(PtGood::class)
                ->findByPt($pt->getId());
        
        foreach ($ptGoods as $ptGood){
            $data = [
                'doc_key' => $pt->getLogKey(),
                'doc_row_key' => $ptGood->getDocRowKey(),
                'doc_row_no' => $ptGood->getRowNo(),
                'date_oper' => $pt->getDocDate(),
                'status' => $pt->getStatus(),
                'quantity' => -$ptGood->getQuantity(),
                'amount' => -$ptGood->getAmount(),
                'good_id' => $ptGood->getGood()->getId(),
                'office_id' => $pt->getOffice()->getId(),
                'company_id' => $pt->getCompany()->getId(),
            ];

            $this->entityManager->getRepository(Movement::class)
                    ->insertMovement($data);            
            
            $data2 = [
                'doc_key' => $pt->getLogKey(),
                'doc_row_key' => $ptGood->getDocRowKey(),
                'doc_row_no' => $ptGood->getRowNo(),
                'date_oper' => $pt->getDocDate(),
                'status' => $pt->getStatus(),
                'quantity' => $ptGood->getQuantity(),
                'amount' => $ptGood->getAmount(),
                'good_id' => $ptGood->getGood()->getId(),
                'office_id' => $pt->getOffice2()->getId(),
                'company_id' => $pt->getCompany2()->getId(),
            ];

            $this->entityManager->getRepository(Movement::class)
                    ->insertMovement($data2);            
        }
        
        return;
    }    
    
    
    /**
     * Перепроведение ПТ
     * @param Pt $pt
     */
    public function repostPt($pt)
    {
        $this->updatePtMovement($pt);
        
        return;
    }

    /**
     * Перепроведение всех ПТ
     */
    public function repostAllPt()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        
        $ptQuery = $this->entityManager->getRepository(Pt::class)
                ->queryAllPt();
        $iterable = $ptQuery->iterate();
        
        foreach ($iterable as $row){
            foreach($row as $pt){ 
                $this->repostPt($pt);
                $this->entityManager->detach($pt);
                unset($pt);
            }    
        }    
        
        return;
    }


    /**
     * Adds a new Pt.
     * @param array $data
     * @return integer
     */
    public function addPt($data)
    {
        $pt = new Pt();        
        $pt->setAplId($data['apl_id']);
        $pt->setDocDate($data['doc_date']);
        $pt->setComment($data['comment']);
        $pt->setStatusEx($data['status_ex']);
        $pt->setStatus($data['status']);
        $pt->setStatusDoc(Pt::STATUS_DOC_NOT_RECD);
        $pt->setOffice($data['office']);
        $pt->setCompany($data['company']);
        $pt->setOffice2($data['office2']);
        $pt->setCompany2($data['company2']);
        $pt->setAmount(0);
        $pt->setDateCreated(date('Y-m-d H:i:s'));
        if (!empty($data['doc_no'])){
            $pt->setDocNo($data['doc_no']);
        }
        
        $this->entityManager->persist($pt);        
        $this->entityManager->flush();
        
        return $pt;        
    }
    
    /**
     * Update pt.
     * @param Pt $pt
     * @param array $data
     * @return integer
     */
    public function updatePt($pt, $data)            
    {
        $pt->setAplId($data['apl_id']);
        $pt->setDocDate($data['doc_date']);
        $pt->setComment($data['comment']);
        $pt->setStatusEx($data['status_ex']);
        $pt->setStatus($data['status']);
        $pt->setOffice($data['office']);
        $pt->setCompany($data['company']);
        $pt->setOffice2($data['office2']);
        $pt->setCompany2($data['company2']);
        if (!empty($data['doc_no'])){
            $pt->setDocNo($data['doc_no']);
        }
        
        $this->entityManager->persist($pt);
        $this->entityManager->flush($pt);
        
        return;
    }
    
    /**
     * Adds a new pt-good.
     * @param integer $ptId
     * @param array $data
     * @param integer $rowNo
     * 
     * @return integer
     */
    public function addPtGood($ptId, $data, $rowNo)
    {
//        var_dump($data); exit;
        $ptGood = [
            'pt_id' => $ptId,
            'status' => (isset($data['status'])) ? $data['status']:PtGood::STATUS_ACTIVE,
            'status_doc' => (isset($data['statusDoc'])) ? $data['statusDoc']:PtGood::STATUS_DOC_RECD,
            'quantity' => $data['quantity'],
            'amount' => $data['amount'],
            'good_id' => $data['good_id'],
            'comment' => (isset($data['comment'])) ? $data['comment']:'',
//            'info' => $data['info'],
            'row_no' => $rowNo,
        ];
        //var_dump($ptGood); exit;
        
        $connection = $this->entityManager->getConnection(); 
        $connection->insert('pt_good', $ptGood);
        return;
    }
    
    /**
     * Update pt_good.
     * @param PtGood $ptGood
     * @param array $data
     * @return integer
     */
    public function updatePtGood($ptGood, $data)            
    {
        
        $connection = $this->entityManager->getConnection(); 
        $connection->update('pt_good', $data, ['id' => $ptGood->getId()]);
        return;
    }
    
    /**
     * Обновить сумму ПТ
     * @param Pt $pt
     */
    public function updatePtAmount($pt)
    {
        $preLog = $this->entityManager->getRepository(Log::class)
                ->findOneByLogKey($pt->getLogKey());
        if (!$preLog){
            $this->logManager->infoPt($pt, Log::STATUS_INFO);            
        }
        
        $ptAmountTotal = $this->entityManager->getRepository(Pt::class)
                ->ptAmountTotal($pt);
        $pt->setAmount($ptAmountTotal);
        $this->entityManager->persist($pt);
        $this->entityManager->flush($pt);
        
        $this->repostPt($pt);
        $this->logManager->infoPt($pt, Log::STATUS_UPDATE);
        return;
    }
    
    /**
     * Удаление строк ПТ
     * @param Pt $pt
     */
    public function removePtGood($pt)
    {
        $this->entityManager->getConnection()
                ->delete('pt_good', ['pt_id' => $pt->getId()]);        
        return;
    }
    
    /**
     * Обновление строк ПТ
     * 
     * @param Pt $pt
     * @param array $data
     */
    public function updatePtGoods($pt, $data)
    {
        $this->removePtGood($pt);
        
        $rowNo = 1;
        if ($data){
            foreach ($data as $row){
                $this->addPtGood($pt->getId(), $row, $rowNo);
                $rowNo++;
            }
        }    
        
        $this->updatePtAmount($pt);
        return;
    }   
    
    
    /**
     * Удаление ПТ
     * 
     * @param Pt $pt
     */
    public function removePt($pt)
    {
        $this->logManager->infoPt($pt, Log::STATUS_DELETE);
        $this->entityManager->getRepository(Movement::class)
                ->removeDocMovements($pt->getLogKey());
        $this->removePtGood($pt);
        
        $this->entityManager->getConnection()->delete('pt', ['id' => $pt->getId()]);
        
        return;
    }
}

