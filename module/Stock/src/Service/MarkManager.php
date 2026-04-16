<?php
namespace Stock\Service;

use Stock\Entity\Ot;
use Stock\Entity\OtGood;
use Admin\Entity\Log;
use Stock\Entity\Movement;
use Stock\Entity\Comiss;
use Application\Entity\Goods;
use Stock\Entity\Mark;
use Application\Entity\Order;

/**
 * This service is responsible for adding/editing ptu.
 */
class MarkManager
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
     * Constructs the service.
     */
    public function __construct($entityManager, $logManager, $adminManager) 
    {
        $this->entityManager = $entityManager;
        $this->logManager = $logManager;
        $this->adminManager = $adminManager;
        
    }
    

    /**
     * Adds a new Mark.
     * @param array $data
     * @return integer
     */
    public function addMark($data)
    {        
        var_dump($data); exit;
        
        $good = $this->entityManager->getRepository(Goods::class)
                ->findOneBy(['aplId' => $data['parent']]);
        
        $order = $this->entityManager->getRepository(Order::class)
                ->findOneBy(['aplId' => $data['publish']]); 
        
        if ($good && $order){
            
            $mark = $this->entityManager->getRepository(Mark::class)
                    ->findOneBy(['mark' => $data['type']]);
            
            if (empty($mark)){
                $mark = new Mark();
            }
                           
            $mark->setAplId($data['id']);
            $mark->setCreated($data['created']);
            $mark->setGood($good);
            $mark->setMark($data['type']);
            $mark->setMarkGroup($data['sort']);
            $mark->setMarkStatus(Mark::MARK_UNKNOWN);
            $mark->setOrder($order);
            $mark->setStatus(Mark::STATUS_ACTIVE);
            $mark->setUpdated(date('Y-m-d H:i:s'));

            $this->entityManager->persist($mark);        
            $this->entityManager->flush();

            return $mark;        
        }
        
        return;
    }
    
    /**
     * Update ot.
     * @param Ot $ot
     * @param array $data
     * @return integer
     */
    public function updateOt($ot, $data)            
    {
        if ($data['doc_date'] > $this->allowDate){
            $ot->setAplId($data['apl_id']);
            $ot->setDocDate($data['doc_date']);
            $ot->setComment($data['comment']);
            $ot->setStatusEx($data['status_ex']);
            $ot->setStatus($data['status']);
            $ot->setStatusAccount(Ot::STATUS_ACCOUNT_NO);
            $ot->setOffice($data['office']);
            $ot->setCompany($data['company']);
            $ot->setComiss(null);
            if (!empty($data['comiss'])){
                $ot->setComiss($data['comiss']);
            }
            if (!empty($data['doc_no'])){
//                $ot->setDocNo($data['doc_no']);
            }

            $this->entityManager->persist($ot);
            $this->entityManager->flush($ot);
        }    
        
        return;
    }
    
    /**
     * Update ot status.
     * @param Ot $ot
     * @param integer $status
     * @return integer
     */
    public function updateOtStatus($ot, $status)            
    {

        if ($ot->getDocDate() > $this->allowDate || $ot->getStatus() != Ot::STATUS_ACTIVE){
            $ot->setStatus($status);
            $ot->setStatusEx(Ot::STATUS_EX_NEW);
            $ot->setStatusAccount(Ot::STATUS_ACCOUNT_NO);
            
            $this->entityManager->persist($ot);
            $this->entityManager->flush();

            $this->repostOt($ot);
            $this->logManager->infoOt($ot, Log::STATUS_UPDATE);
        }    
        
        return;
    }
    
    /**
     * Удаление ОТ
     * 
     * @param Ot $ot
     */
    public function removeOt($ot)
    {
        if ($ot->getDocDate() > $this->allowDate){
            $this->logManager->infoOt($ot, Log::STATUS_DELETE);
            $this->entityManager->getRepository(Movement::class)
                    ->removeDocMovements($ot->getLogKey());
            $this->entityManager->getRepository(Comiss::class)
                    ->removeDocComiss($ot->getLogKey());
            $this->removeOtGood($ot);

            $this->entityManager->getConnection()->delete('ot', ['id' => $ot->getId()]);
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
        $rows = $this->entityManager->getRepository(OtGood::class)
                ->findBy(['good' => $oldGood->getId()]);
        foreach ($rows as $row){
            $row->setGood($newGood);
            $this->entityManager->persist($row);
            $this->entityManager->flush();
            $this->updateOtMovement($row->getOt());
        }
        
        return;
    }
}

