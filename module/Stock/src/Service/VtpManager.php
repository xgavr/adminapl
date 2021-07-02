<?php
namespace Stock\Service;

use Stock\Entity\Vtp;
use Stock\Entity\Ptu;
use Stock\Entity\Ntd;
use Stock\Entity\Unit;
use Company\Entity\Country;
use Stock\Entity\VtpGood;
use Admin\Entity\Log;
use Stock\Entity\Movement;
use Stock\Entity\Mutual;

/**
 * This service is responsible for adding/editing ptu.
 */
class VtpManager
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
     * Обновить взаиморасчеты документа
     * 
     * @param Vtp $vtp
     */
    public function updateVtpMutuals($vtp)
    {
        
        $this->entityManager->getRepository(Mutual::class)
                ->removeDocMutuals($vtp->getLogKey());
        
        $data = [
            'doc_key' => $vtp->getLogKey(),
            'date_oper' => $vtp->getDocDate(),
            'status' => $vtp->getStatus(),
            'revise' => Mutual::REVISE_NOT,
            'amount' => -$vtp->getAmount(),
            'legal_id' => $vtp->getPtu()->getLegal()->getId(),
            'contract_id' => $vtp->getPtu()->getContract()->getId(),
            'office_id' => $vtp->getPtu()->getOffice()->getId(),
            'company_id' => $vtp->getPtu()->getContract()->getCompany()->getId(),
        ];

        $this->entityManager->getRepository(Mutual::class)
                ->insertMutual($data);
         
        return;
    }    
    
    /**
     * Обновить движения документа
     * 
     * @param Vtp $vtp
     */
    public function updateVtpMovement($vtp)
    {
        
        $this->entityManager->getRepository(Movement::class)
                ->removeDocMovements($vtp->getLogKey());
        
        $vtpGoods = $this->entityManager->getRepository(VtpGood::class)
                ->findByVtp($vtp->getId());
        foreach ($vtpGoods as $vtpGood){
            $data = [
                'doc_key' => $vtp->getLogKey(),
                'doc_row_key' => $vtpGood->getDocRowKey(),
                'doc_row_no' => $vtpGood->getRowNo(),
                'date_oper' => $vtp->getDocDate(),
                'status' => $vtp->getStatus(),
                'quantity' => -$vtpGood->getQuantity(),
                'amount' => -$vtpGood->getAmount(),
                'good_id' => $vtpGood->getGood()->getId(),
                'office_id' => $vtp->getPtu()->getOffice()->getId(),
                'company_id' => $vtp->getPtu()->getContract()->getCompany()->getId(),
            ];

            $this->entityManager->getRepository(Movement::class)
                    ->insertMovement($data);
        }
        
        return;
    }    
    
    
    /**
     * Перепроведение ВТП
     * @param Vtp $vtp
     */
    public function repostVtp($vtp)
    {
        $this->updateVtpMovement($vtp);
        $this->updateVtpMutuals($vtp);
        
        return;
    }

    /**
     * Перепроведение всех ВТП
     * @param Vtp $vtp
     */
    public function repostAllVtp()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        
        $vtpQuery = $this->entityManager->getRepository(Vtp::class)
                ->queryAllVtp();
        $iterable = $vtpQuery->iterate();
        
        foreach ($iterable as $row){
            foreach($row as $vtp){ 
                $this->repostVtp($vtp);
                $this->entityManager->detach($vtp);
                unset($vtp);
            }    
        }    
        
        return;
    }


    /**
     * Adds a new vtp.
     * @param Ptu $ptu
     * @param array $data
     * @return integer
     */
    public function addVtp($ptu, $data)
    {
        $vtp = new Vtp();        
        $vtp->setPtu($ptu);
        $vtp->setAplId($data['apl_id']);
        //$vtp->setDocNo($data['doc_no']);
        $vtp->setDocDate($data['doc_date']);
        $vtp->setComment($data['comment']);
        $vtp->setStatusEx($data['status_ex']);
        $vtp->setStatus($data['status']);
        $vtp->setStatusDoc(Vtp::STATUS_DOC_NOT_RECD);
        $vtp->setAmount(0);
        $vtp->setDateCreated(date('Y-m-d H:i:s'));
        
        $this->entityManager->persist($vtp);        
        $this->entityManager->flush();
        
        return $vtp;        
    }
    
    /**
     * Update vtp.
     * @param Vtp $vtp
     * @param array $data
     * @return integer
     */
    public function updateVtp($vtp, $data)            
    {
//        $connection = $this->entityManager->getConnection(); 
//        $connection->update('ptu', $data, ['id' => $ptu->getId()]);
//        var_dump($data); exit;
        $vtp->setAplId($data['apl_id']);
        $vtp->setDocNo($data['doc_no']);
        $vtp->setDocDate($data['doc_date']);
        $vtp->setComment($data['comment']);
        $vtp->setStatusEx($data['status_ex']);
        $vtp->setStatus($data['status']);
        
        $this->entityManager->persist($vtp);
        $this->entityManager->flush($vtp);
        
        return;
    }
    
    /**
     * Adds a new vtp-good.
     * @param integer $vtpId
     * @param array $data
     * @param integer $rowNo
     * 
     * @return integer
     */
    public function addVtpGood($vtpId, $data, $rowNo)
    {
//        var_dump($data); exit;
        $vtpGood = [
            'vtp_id' => $vtpId,
            'status' => (isset($data['status'])) ? $data['status']:VtpGood::STATUS_ACTIVE,
            'status_doc' => (isset($data['statusDoc'])) ? $data['statusDoc']:VtpGood::STATUS_DOC_RECD,
            'quantity' => $data['quantity'],
            'amount' => $data['amount'],
            'good_id' => $data['good_id'],
            'comment' => (isset($data['comment'])) ? $data['comment']:'',
//            'info' => $data['info'],
            'row_no' => $rowNo,
        ];
        
        $connection = $this->entityManager->getConnection(); 
        $connection->insert('vtp_good', $vtpGood);
        return;
    }
    
    /**
     * Update vtp_good.
     * @param VtpGood $vtpGood
     * @param array $data
     * @return integer
     */
    public function updateVtpGood($vtpGood, $data)            
    {
        
        $connection = $this->entityManager->getConnection(); 
        $connection->update('vtp_good', $data, ['id' => $vtpGood->getId()]);
        return;
    }
    
    /**
     * Обновить сумму ВТП
     * @param Vtp $vtp
     */
    public function updateVtpAmount($vtp)
    {
        $preLog = $this->entityManager->getRepository(Log::class)
                ->findOneByLogKey($vtp->getLogKey());
        if (!$preLog){
            $this->logManager->infoVtp($vtp, Log::STATUS_INFO);            
        }
        
        $vtpAmountTotal = $this->entityManager->getRepository(Vtp::class)
                ->vtpAmountTotal($vtp);
//        $this->entityManager->getConnection()->update('ptu', ['amount' => $ptuAmountTotal], ['id' => $ptu->getId()]);
        $vtp->setAmount($vtpAmountTotal);
        $this->entityManager->persist($vtp);
        $this->entityManager->flush($vtp);
        
        $this->entityManager->refresh($vtp);
        $this->repostVtp($vtp);
        $this->logManager->infoVtp($vtp, Log::STATUS_UPDATE);
        return;
    }
    
    /**
     * Удаление строк ВТП
     * @param Vtp $vtp
     */
    public function removeVtpGood($vtp)
    {
        $this->entityManager->getConnection()
                ->delete('vtp_good', ['vtp_id' => $vtp->getId()]);
        return;
    }
    
    /**
     * Обновление строк ВТП
     * 
     * @param Vtp $vtp
     * @param array $data
     */
    public function updateVtpGoods($vtp, $data)
    {
        $this->removeVtpGood($vtp);
        
        $rowNo = 1;
        foreach ($data as $row){
            $this->addVtpGood($vtp->getId(), $row, $rowNo);
            $rowNo++;
        }
        
        $this->updateVtpAmount($vtp);
        return;
    }   
    
    
    /**
     * Ужаление ВТП
     * 
     * @param Vtp $vtp
     */
    public function removeVtp($vtp)
    {
        $this->logManager->infoVtp($vtp, Log::STATUS_DELETE);
        $this->entityManager->getRepository(Mutual::class)
                ->removeDocMutuals($vtp->getLogKey());
        $this->entityManager->getRepository(Movement::class)
                ->removeDocMovements($vtp->getLogKey());
        $this->removeVtpGood($vtp);
        
        $this->entityManager->getConnection()->delete('vtp', ['id' => $vtp->getId()]);
        
        return;
    }
}

