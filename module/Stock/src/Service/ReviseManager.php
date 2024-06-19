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
use Stock\Entity\Movement;
use Stock\Entity\Register;
use ApiMarketPlace\Entity\MarketSaleReport;
use Laminas\Json\Encoder;
use Application\Entity\Client;

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
    
    public function currentUser()
    {
        return $this->logManager->currentUser();
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
     * @param Revise $revise
     * @param float $docStamp
     */
    public function updateReviseMutuals($revise, $docStamp)
    {
        
        $this->entityManager->getRepository(Mutual::class)
                ->removeDocMutuals($revise->getLogKey());
        
        if ($revise->getLegal()){        
            $data = [
                'doc_key' => $revise->getLogKey(),
                'doc_type' => Movement::DOC_REVISE,
                'doc_id' => $revise->getId(),
                'date_oper' => $revise->getDocDate(),
                'status' => ($revise->getStatus() == Revise::STATUS_ACTIVE) ? Mutual::STATUS_ACTIVE: Mutual::STATUS_RETIRED,
                'revise' => Mutual::REVISE_NOT,
                'amount' => $revise->getAmount(),
                'legal_id' => $revise->getLegal()->getId(),
                'contract_id' => $revise->getContract()->getId(),
                'office_id' => $revise->getOffice()->getId(),
                'company_id' => $revise->getContract()->getCompany()->getId(),
                'doc_stamp' => $docStamp,
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
     * @param float $docStamp
     */
    public function updateReviseRetails($revise, $docStamp)
    {
        $this->entityManager->getRepository(Retail::class)
                ->removeOrderRetails($revise->getLogKey());                
        $legalId = $contractid = null;
        if ($revise->getLegal()){
            $legalId = $revise->getLegal()->getId();
        }
        if ($revise->getContract()){
            $contractid = $revise->getContract()->getId();
        }
        if ($revise->getKind() == Revise::KIND_REVISE_CLIENT){
            $legalId = $contractId = null;
            if ($revise->getLegal()){
                $legalId = $revise->getLegal()->getId();
                if ($revise->getContract()){
                    $contractId = $revise->getContract()->getId();
                }    
            }
            $data = [
                'doc_key' => $revise->getLogKey(),
                'doc_type' => Movement::DOC_REVISE,
                'doc_id' => $revise->getId(),
                'date_oper' => $revise->getDocDate(),
                'status' => ($revise->getStatus() == Revise::STATUS_ACTIVE) ? Retail::STATUS_ACTIVE: Retail::STATUS_RETIRED,
                'revise' => Retail::REVISE_NOT,
                'amount' => $revise->getAmount(),
                'contact_id' => $revise->getContact()->getId(),
                'office_id' => $revise->getOffice()->getId(),
                'company_id' => $revise->getCompany()->getId(),
                'legal_id' => $legalId,
                'contract_id' => $contractId,                
                'doc_stamp' => $docStamp,
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
        if ($revise->getDocDate() > $this->getAllowDate()){
            $docStamp = $this->entityManager->getRepository(Register::class)
                    ->reviseRegister($revise);
        } else {
            $register = $this->entityManager->getRepository(Register::class)
                    ->findOneBy(['docKey' => $revise->getLogKey()]);
            $docStamp = $register->getDocStamp();            
        }    
        
        $this->updateReviseMutuals($revise, $docStamp);
        $this->updateReviseRetails($revise, $docStamp);
        $this->entityManager->getRepository(Revise::class)
                ->updateReportRevise($revise);
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
     * @param array $inData
     * @return integer
     */
    public function addRevise($inData)
    {
        $data = $this->prepareData($inData);
        
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
        $revise->setStatusAccount(Revise::STATUS_ACCOUNT_NO);
        
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
     * @param array $inData
     * @return integer
     */
    public function updateRevise($revise, $inData)            
    {
        $preLog = $this->entityManager->getRepository(Log::class)
                ->findOneByLogKey($revise->getLogKey());
        if (!$preLog){
            $this->logManager->infoRevise($revise, Log::STATUS_INFO);            
        }

        $data = $this->prepareData($inData);
        
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
        $revise->setStatusAccount(Revise::STATUS_ACCOUNT_NO);
        
        $this->entityManager->persist($revise);
        $this->entityManager->flush($revise);
        
        $this->repostRevise($revise);
        
        return;
    }
    
    /**
     * Update revise status.
     * @param Revise $revise
     * @param int $status
     * @return integer
     */
    public function updateReviseStatus($revise, $status)            
    {
        $preLog = $this->entityManager->getRepository(Log::class)
                ->findOneByLogKey($revise->getLogKey());
        if (!$preLog){
            $this->logManager->infoRevise($revise, Log::STATUS_INFO);            
        }

        $revise->setStatusEx(Revise::STATUS_EX_NEW);
        $revise->setStatus($status);
        $revise->setStatusAccount(Revise::STATUS_ACCOUNT_NO);
        
        $this->entityManager->persist($revise);
        $this->entityManager->flush($revise);
        
        $this->repostRevise($revise);
        
        return;
    }
    
    /**
     * Удаление Revise
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
    
    /**
     * Последняя операция клиента
     * @param Client $client
     * @return Retail
     */
    private function lastClientRetail($client)
    {
        foreach ($client->getContacts() as $contact){
            $retail = $this->entityManager->getRepository(Retail::class)
                    ->findOneBy(['contact' => $contact->getId(), 'status' => Retail::STATUS_ACTIVE], 
                            ['docStamp' => 'Desc']);
            if ($retail){
                return $retail;
            }
        }
        
        return;
    }
    
    /**
     *  Списание долга клиентов
     * @param Client $client
     * @return Revise $revise
     */
    public function resetClientBalance($client)
    {
        if ($client->getBalance()){
            $retailBalance = $this->entityManager->getRepository(Client::class)
                    ->getRetailBalance($client);
            $retail = $this->lastClientRetail($client);
            if ($retail && $retailBalance){
                $data = [
                    'docNo' => 'Nавто',
                    'docDate' => date('Y-m-d'),
                    'comment' => 'Обнуление розничного баланса',
                    'status' => Revise::STATUS_ACTIVE,
                    'amount' => -$retailBalance,
                    'contact' => $client->getContact()->getId(),
                    'office' => $retail->getOffice()->getId(),
                    'company' => $retail->getCompany()->getId(),
                    'kind' => Revise::KIND_REVISE_CLIENT,
                ];
                
                try{
                    $revise = $this->addRevise($data);
                    return $revise;
                } catch (\Doctrine\DBAL\Exception\NotNullConstraintViolationException $e){
                    var_dump($client->getId());
                }    
            }    
        }
        
        return;
    }
    
    /**
     * Обнуление старых долгов клиентов
     * @param integer $year
     */
    public function resetClientBalances($year = 2014)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(1800);
        $startTime = time();
        $finishTime = $startTime + 1740;

        $clients = $this->entityManager->getRepository(Client::class)
                ->findClientsForReset($year);
        
        foreach ($clients as $client){
            $this->resetClientBalance($client);
            if (time() >= $finishTime){
                break;
            }
        }
        
        return;
    }
}

