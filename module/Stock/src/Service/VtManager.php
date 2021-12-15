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
     * @param Vt $vt
     */
    public function updateVtMutuals($vt)
    {
        
        $this->entityManager->getRepository(Mutual::class)
                ->removeDocMutuals($vt->getLogKey());
        
        $data = [
            'doc_key' => $vt->getLogKey(),
            'date_oper' => $vt->getDocDate(),
            'status' => $vt->getStatus(),
            'revise' => Mutual::REVISE_NOT,
            'amount' => $vt->getAmount(),
            'legal_id' => $vt->getPtu()->getLegal()->getId(),
            'contract_id' => $vt->getOrder()->getContract()->getId(),
            'office_id' => $vt->getOrder()->getOffice()->getId(),
            'company_id' => $vt->getOrder()->getContract()->getCompany()->getId(),
        ];

        $this->entityManager->getRepository(Mutual::class)
                ->insertMutual($data);
         
        return;
    }    
    
    /**
     * Обновить движения документа
     * 
     * @param Vt $vt
     */
    public function updateVtMovement($vt)
    {
        
        $this->entityManager->getRepository(Movement::class)
                ->removeDocMovements($vt->getLogKey());
        
        $vtGoods = $this->entityManager->getRepository(VtGood::class)
                ->findByVt($vt->getId());
        foreach ($vtGoods as $vtGood){
            $data = [
                'doc_key' => $vt->getLogKey(),
                'doc_row_key' => $vtGood->getDocRowKey(),
                'doc_row_no' => $vtGood->getRowNo(),
                'date_oper' => $vt->getDocDate(),
                'status' => $vt->getStatus(),
                'quantity' => $vtGood->getQuantity(),
                'amount' => $vtGood->getAmount(),
                'good_id' => $vtGood->getGood()->getId(),
                'office_id' => $vt->getPtu()->getOffice()->getId(),
                'company_id' => $vt->getPtu()->getContract()->getCompany()->getId(),
            ];

            $this->entityManager->getRepository(Movement::class)
                    ->insertMovement($data);
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
        $this->updateVtMutuals($vt);
        
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
     * @param Order $order
     * @param array $data
     * @return integer
     */
    public function addVt($order, $data)
    {
        $vt = new Vt();        
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
        
        $this->entityManager->persist($vt);        
        $this->entityManager->flush();
        
        return $vt;        
    }
    
    /**
     * Update vt.
     * @param Vt $vt
     * @param array $data
     * @return integer
     */
    public function updateVt($vt, $data)            
    {
        $vt->setAplId($data['apl_id']);
        $vt->setDocNo($data['doc_no']);
        $vt->setDocDate($data['doc_date']);
        $vt->setComment($data['comment']);
        $vt->setStatusEx($data['status_ex']);
        $vt->setStatus($data['status']);
        
        $this->entityManager->persist($vt);
        $this->entityManager->flush($vt);
        
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
        $this->logManager->infoVt($vt, Log::STATUS_DELETE);
        $this->entityManager->getRepository(Mutual::class)
                ->removeDocMutuals($vt->getLogKey());
        $this->entityManager->getRepository(Movement::class)
                ->removeDocMovements($vt->getLogKey());
        $this->removeVtGood($vt);
        
        $this->entityManager->getConnection()->delete('vt', ['id' => $vt->getId()]);
        
        return;
    }
}

