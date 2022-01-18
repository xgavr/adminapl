<?php
namespace Stock\Service;

use Admin\Entity\Log;
use Stock\Entity\Mutual;
use Stock\Entity\Retail;
use Stock\Entity\Revise;
use Company\Entity\Legal;
use Application\Entity\Phone;
use Application\Entity\Contact;
use Company\Entity\Contract;
use Application\Entity\Supplier;
use Company\Entity\Office;
use User\Filter\PhoneFilter;

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
    
    public function currentUser()
    {
        return $this->logManager->currentUser();
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
        
        if ($revise->getKind() == Revise::KIND_REVISE_SUPPLIER){
            $data = [
                'doc_key' => $revise->getLogKey(),
                'date_oper' => $revise->getDocDate(),
                'status' => ($revise->getStatus() == Revise::STATUS_ACTIVE) ? Mutual::STATUS_ACTIVE: Mutual::STATUS_RETIRED,
                'revise' => Mutual::REVISE_NOT,
                'amount' => $revise->getAmount(),
                'legal_id' => $revise->getLegal()->getId(),
                'contract_id' => $revise->getContract()->getId(),
                'office_id' => $revise->getOffice()->getId(),
                'company_id' => $revise->getContract()->getCompany()->getId(),
            ];

            $this->entityManager->getRepository(Mutual::class)
                    ->insertMutual($data);
        }    
        
        return;
    }    
        
    /**
     * Обновить взаиморасчеты розницы
     * 
     * @param Revise $revise
     */
    public function updateReviseRetails($revise)
    {
        $this->entityManager->getRepository(Retail::class)
                ->removeOrderRetails($revise->getLogKey());                
        
        if ($revise->getKind() == Revise::KIND_REVISE_CLIENT){
            $data = [
                'doc_key' => $revise->getLogKey(),
                'date_oper' => $revise->getDocDate(),
                'status' => ($revise->getStatus() == Revise::STATUS_ACTIVE) ? Retail::STATUS_ACTIVE: Retail::STATUS_RETIRED,
                'revise' => Retail::REVISE_NOT,
                'amount' => $revise->getAmount(),
                'contact_id' => $revise->getContact()->getId(),
                'office_id' => $revise->getOffice()->getId(),
                'company_id' => $revise->getCompany()->getId(),
            ];

            $this->entityManager->getRepository(Retail::class)
                    ->insertRetail($data);
        }    
        
        return;
    }    
    
    /**
     * Перепроведение Revise
     * @param Revise $revise
     */
    public function repostRevise($revise)
    {
        $this->updateReviseMutuals($revise);
        $this->updateReviseRetails($revise);
        $this->logManager->infoRevise($revise, Log::STATUS_UPDATE);
        
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
     * Подготовить данные
     * @param array $data
     */
    protected function prepareData($data)
    {
        if (!empty($data['office'])){
            $data['office'] = $this->entityManager->getRepository(Office::class)
                    ->find($data['office']);
        }
        if (!empty($data['company'])){
            $data['company'] = $this->entityManager->getRepository(Legal::class)
                    ->find($data['company']);
        }
        if (!empty($data['legal'])){
            $data['legal'] = $this->entityManager->getRepository(Legal::class)
                    ->find($data['legal']);
        }
        if (!empty($data['contact'])){
            $data['contact'] = $this->entityManager->getRepository(Contact::class)
                    ->find($data['contact']);
        }
        if (!empty($data['phone'])){
            $phoneFilter = new PhoneFilter();
            $phone = $this->entityManager->getRepository(Phone::class)
                    ->findOneByName($phoneFilter->filter($data['phone']));
            if ($phone){        
                $data['contact'] = $phone->getContact();
            }    
        }
        if (!empty($data['contract'])){
            $data['contract'] = $this->entityManager->getRepository(Contract::class)
                    ->find($data['contract']);
        }
        if (!empty($data['supplier'])){
            $supplier = $this->entityManager->getRepository(Supplier::class)
                    ->find($data['supplier']);
            if ($supplier && empty($data['legal'])){
                $data['legal'] = $this->entityManager->getRepository(Legal::class)
                        ->formContactLegal($supplier->getLegalContact());
            }    
        }
        
        return $data;
    }

    /**
     * Adds a new revise.
     * @param array $data
     * @return integer
     */
    public function addRevise($data)
    {
        $data = $this->prepareData($data);
        
        $revise = new Revise();        
        $revise->setAplId(empty($data['aplId']) ? null:$data['aplId']);
        $revise->setDocNo(empty($data['docNo']) ? null:$data['docNo']);
        $revise->setDocDate($data['docDate']);
        $revise->setComment($data['comment']);
        $revise->setStatusEx(empty($data['statusEx']) ? Revise::STATUS_EX_NEW:$data['statusEx']);
        $revise->setStatus($data['status']);
        $revise->setOffice($data['office']);
        $revise->setLegal(empty($data['legal']) ? null:$data['legal']);
        $revise->setContract(empty($data['contract']) ? null:$data['contract']); 
        $revise->setStatusDoc(Revise::STATUS_DOC_NOT_RECD);
        $revise->setAmount($data['amount']);
        $revise->setCompany($data['company']);
        $revise->setContact(empty($data['contact']) ? null:$data['contact']);
        $revise->setInfo(empty($data['info']) ? null:$data['info']);
        $revise->setKind($data['kind']);
        
        $revise->setDateCreated(date('Y-m-d H:i:s'));
        $creator = $this->logManager->currentUser();
        $revise->setUserCreator($creator);
        
        $this->entityManager->persist($revise);
        $this->entityManager->flush($revise);
        
        $this->repostRevise($revise);
        
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
        $preLog = $this->entityManager->getRepository(Log::class)
                ->findOneByLogKey($revise->getLogKey());
        if (!$preLog){
            $this->logManager->infoRevise($revise, Log::STATUS_INFO);            
        }

        $data = $this->prepareData($data);
        
        $revise->setAplId(empty($data['aplId']) ? null:$data['aplId']);
        $revise->setDocNo(empty($data['docNo']) ? null:$data['docNo']);
        $revise->setDocDate($data['docDate']);
        $revise->setComment($data['comment']);
        $revise->setStatusEx(empty($data['statusEx']) ? Revise::STATUS_EX_NEW:$data['statusEx']);
        $revise->setStatus($data['status']);
        $revise->setOffice($data['office']);
        $revise->setLegal(empty($data['legal']) ? null:$data['legal']);
        $revise->setContract(empty($data['contract']) ? null:$data['contract']); 
        $revise->setAmount($data['amount']);
        $revise->setCompany($data['company']);
        $revise->setContact(empty($data['contact']) ? null:$data['contact']);
        $revise->setInfo(empty($data['info']) ? null:$data['info']);
        $revise->setKind($data['kind']);
        
        $this->entityManager->persist($revise);
        $this->entityManager->flush($revise);
        
        $this->repostRevise($revise);
        
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

