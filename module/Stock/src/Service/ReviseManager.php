<?php
namespace Stock\Service;

use Admin\Entity\Log;
use Stock\Entity\Movement;
use Stock\Entity\Mutual;
use Stock\Entity\Revise;

/**
 * This service is responsible for adding/editing revise.
 */
class ReviseManager
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
     * @param Revise $revise
     */
    public function updateReviseMutuals($revise)
    {
        
        $this->entityManager->getRepository(Mutual::class)
                ->removeDocMutuals($revise->getLogKey());
        
        $data = [
            'doc_key' => $revise->getLogKey(),
            'date_oper' => $revise->getDocDate(),
            'status' => $revise->getStatus(),
            'revise' => Revise::REVISE_NOT,
            'amount' => $revise->getAmount(),
            'legal_id' => $revise->getLegal()->getId(),
            'contract_id' => $revise->getContract()->getId(),
            'office_id' => $revise->getOffice()->getId(),
            'company_id' => $revise->getContract()->getCompany()->getId(),
        ];

        $this->entityManager->getRepository(Mutual::class)
                ->insertMutual($data);
        
        return;
    }    
        
    /**
     * Перепроведение Revise
     * @param Revise $revise
     */
    public function repostRevise($revise)
    {
        $this->updateReviseMutuals($revise);
        
        return;
    }

    /**
     * Перепроведение всех Revise
     * @param Revise $revise
     */
    public function repostAllRevise()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        
        $reviseQuery = $this->entityManager->getRepository(Revise::class)
                ->queryAllRevise();
        $iterable = $reviseQuery->iterate();
        
        foreach ($iterable as $row){
            foreach($row as $revise){ 
                $this->repostRevise($revise);
                $this->entityManager->detach($revise);
            }    
        }    
        
        return;
    }


    /**
     * Adds a new revise.
     * @param array $data
     * @return integer
     */
    public function addRevise($data)
    {
        $revise = new Revise();        
        $revise->setAplId($data['apl_id']);
        $revise->setDocNo($data['doc_no']);
        $revise->setDocDate($data['doc_date']);
        $revise->setComment($data['comment']);
        $revise->setStatusEx($data['status_ex']);
        $revise->setStatus($data['status']);
        $revise->setOffice($data['office']);
        $revise->setLegal($data['legal']);
        $revise->setContract($data['contract']); 
        $revise->setStatusDoc(Revise::STATUS_DOC_NOT_RECD);
        $revise->setAmount($data['amount']);
        $revise->setDateCreated(date('Y-m-d H:i:s'));
        
        $this->entityManager->persist($revise);
        $this->entityManager->flush($revise);
        
        return $revise;        
    }
    
    /**
     * Update revise.
     * @param Revise $revise
     * @param array $data
     * @return integer
     */
    public function updateRevise($revise, $data)            
    {
        $revise->setAplId($data['apl_id']);
        $revise->setDocNo($data['doc_no']);
        $revise->setDocDate($data['doc_date']);
        $revise->setComment($data['comment']);
        $revise->setStatusEx($data['status_ex']);
        $revise->setStatus($data['status']);
        $revise->setOffice($data['office']);
        $revise->setLegal($data['legal']);
        $revise->setContract($data['contract']);
        $revise->setAmount($data['amount']);
        
        $this->entityManager->persist($revise);
        $this->entityManager->flush($revise);
        
        return;
    }
    
    /**
     * Ужаление Revise
     * 
     * @param Revise $revise
     */
    public function removeRevise($revise)
    {
        $this->logManager->infoRevise($revise, Log::STATUS_DELETE);
        $this->entityManager->getRepository(Mutual::class)
                ->removeDocMutuals($revise->getLogKey());
        
        $this->entityManager->getConnection()->delete('revise', ['id' => $revise->getId()]);
        
        return;
    }
}

