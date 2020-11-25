<?php
namespace Company\Service;

use Company\Entity\Legal;
use Company\Entity\BankAccount;
use Company\Entity\Contract;
use Company\Entity\Office;
use Laminas\Json\Json;
use Application\Entity\Contact;
use Stock\Entity\Mutual;

/**
 * This service legal.
 */
class LegalManager
{
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;  
        
    /**
     * Constructs the service.
     */
    public function __construct($entityManager) 
    {
        $this->entityManager = $entityManager;
    }
 
    /**
     * Изменить юрлицо
     * 
     * @param Legal $legal
     * @param array $data
     * 
     * @return Legal
     */
    public function updateLegal($legal, $data)
    {                

        $legal->setName($data['name']);            
        $legal->setInn($data['inn']);            
        $legal->setKpp($data['kpp']);            
        $legal->setOgrn($data['ogrn']);            
        $legal->setOkpo($data['okpo']);            
        $legal->setHead($data['head']);            
        $legal->setChiefAccount($data['chiefAccount']);            
        $legal->setInfo($data['info']);            
        $legal->setAddress($data['address']);            
        $legal->setStatus($data['status']);  
        
        $currentDate = date('Y-m-d H:i:s');
        $legal->setDateStart($currentDate);
        if (isset($data['dateStart'])){
            $legal->setDateStart($data['dateStart']);
        }

        $this->entityManager->persist($legal);        
        $this->entityManager->flush();                
        
        return $legal;
    }

    /**
     * Добавить/изменить юрлицо
     * 
     * @param Contact $contact
     * @param array $data
     * @param bool $flushnow
     * @return Legal
     */
    public function addLegal($contact, $data)
    {       
        
        $legal = $this->entityManager->getRepository(Legal::class)
                ->findOneByInnKpp($data['inn'], $data['kpp']);
        
        if (!$legal){            
            $legal = $this->entityManager->getRepository(Legal::class)
                    ->findOneByName($data['name']);            
        }
        
        if ($legal){
            $contact->removeLegalAssociation($legal);
            $contact->addLegal($legal);
            $this->updateLegal($legal, $data);
        }

        if ($legal == null){
            $legal = new Legal();            
            $legal->setName($data['name']);            
            $legal->setInn($data['inn']);            
            $legal->setKpp($data['kpp']);            
            $legal->setOgrn($data['ogrn']);            
            $legal->setOkpo($data['okpo']);            
            $legal->setHead($data['head']);            
            $legal->setChiefAccount($data['chiefAccount']);            
            $legal->setInfo($data['info']);            
            $legal->setAddress($data['address']);            
            $legal->setStatus($data['status']);            

            $currentDate = date('Y-m-d H:i:s');
            $legal->setDateCreated($currentDate);
            
            $legal->setDateStart($currentDate);
            if (isset($data['dateStart'])){
                $legal->setDateStart($data['dateStart']);
            }
            
            $legal->addContact($contact);
            $this->entityManager->persist($legal);
            $this->entityManager->flush();                
        }   
        
        return $legal;
    }
    
    public function removeLegalAssociation($legal)
    {
        $contacts = $legal->getContacts();
        foreach ($contacts as $contact){
            $contact->removeLegalAssociation($legal);
        }        
        
        $this->entityManager->flush();
    }
    
    /**
     * Возможность удаления юр лица
     * @param Legal $legal
     * @return bool
     */
    public function allowRemoveLegal($legal)
    {
        $legalCount = $this->entityManager->getRepository(Mutual::class)
                ->count(['legal' => $legal->getId()]);
        $companyCount = $this->entityManager->getRepository(Mutual::class)
                ->count(['company' => $legal->getId()]);
        
        return $legalCount == 0 && $companyCount == 0;
    }
    
    /**
     * Удаление юр лица
     * 
     * @param Legal $legal
     */
    public function removeLegal($legal)
    {
        if ($this->allowRemoveLegal($legal)){
            
            $contacts = $legal->getContacts();
            foreach ($contacts as $contact){
                $contact->removeLegalAssociation($legal);
            }

            $contracts = $legal->getContracts();
            foreach ($contracts as $contract){
                $this->entityManager->remove($contract);
            }

            $bankAccounts = $legal->getBankAccounts();
            foreach ($bankAccounts as $bankAccount){
                $this->entityManager->remove($bankAccount);
            }

            $this->entityManager->remove($legal);

            $this->entityManager->flush();
        }
        
        return;
    }    
       
    /**
     * Добавить банковский счет
     * 
     * @param \Company\Entity\Legal $legal
     * @param array $data
     * @param bool $flushnow
     */
    public function addBankAccount($legal, $data, $flushnow = false)
    {                
        $bankAccount = new BankAccount();            
        $bankAccount->setName($data['name']);            
        $bankAccount->setCity($data['city']);            
        $bankAccount->setBik($data['bik']);            
        $bankAccount->setKs($data['ks']);            
        $bankAccount->setRs($data['rs']);            
        $bankAccount->setStatus($data['status']);            
        $bankAccount->setApi($data['api']);            
        $bankAccount->setStatement($data['statement']);            

        $currentDate = date('Y-m-d H:i:s');
        $bankAccount->setDateCreated($currentDate);
            
        $this->entityManager->persist($bankAccount);
        
        $bankAccount->setLegal($legal);
        
        if ($flushnow){
            $this->entityManager->flush();                
        }
    }
   
    /**
     * Обновить банковский счет
     * 
     * @param \Company\Entity\BankAccount $bankAccount
     * @param array $data
     * @param bool $flushnow
     */
    public function updateBankAccount($bankAccount, $data, $flushnow = false)
    {                
        $bankAccount->setName($data['name']);            
        $bankAccount->setCity($data['city']);            
        $bankAccount->setBik($data['bik']);            
        $bankAccount->setKs($data['ks']);            
        $bankAccount->setRs($data['rs']);            
        $bankAccount->setStatus($data['status']);            
        $bankAccount->setApi($data['api']);            
        $bankAccount->setStatement($data['statement']);            

        $this->entityManager->persist($bankAccount);

        if ($flushnow){
            $this->entityManager->flush();                
        }
    }
    
    /**
     * Удалить банковский счет
     * 
     * @param \Company\Entity\BankAccount $bankAccount
     */
    public function removeBankAccount($bankAccount)
    {
        $this->entityManager->remove($bankAccount);

        $this->entityManager->flush();
        
    }
    
    /*
     * Получение информации из Справочник БИК РФ http://www.bik-info.ru/
     * @var $bik string
     * return json 
     */
    public function bikInfo($bik)
    {
        $bikInfoUrl =  'http://www.bik-info.ru/api.html?type=json&bik='.$bik; 

        $data = file_get_contents($bikInfoUrl);
        
        if (is_string($data)){
            return (array) Json::decode($data);            
        }
        
        return;
    }
   
    /**
     * Добавить договор
     * 
     * @param Legal $legal
     * @param array $data
     * @param bool $flushnow
     */
    public function addContract($legal, $data, $flushnow = false)
    {                
//        var_dump($data); exit;
        $contract = new Contract();            
        $contract->setName($data['name']);            
        $contract->setAct($data['act']);            
        $contract->setDateStart($data['dateStart']);            
        $contract->setStatus($data['status']);
        $contract->setKind($data['kind']);
        $contract->setPay($data['pay']);
        
        $office = $this->entityManager->getRepository(Office::class)
                ->findOneById($data['office']);
        $contract->setOffice($office);
        if (isset($data['company'])){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->findOneById($data['company']);
        } else {
            $company = $this->entityManager->getRepository(Office::class)
                    ->findDefaultCompany($office);
        }    
        $contract->setCompany($company);
        
        $currentDate = date('Y-m-d H:i:s');
        $contract->setDateCreated($currentDate);
        $contract->setLegal($legal);
            
        $this->entityManager->persist($contract);
        $this->entityManager->flush();                
        
        return $contract;
    }
   
    /**
     * Обновить договор
     * 
     * @param Contract $contract
     * @param array $data
     * @param bool $flushnow
     */
    public function updateContract($contract, $data, $flushnow = false)
    {                
        $contract->setName($data['name']);            
        $contract->setAct($data['act']);            
        $contract->setDateStart($data['dateStart']);            
        $contract->setStatus($data['status']); 
        $contract->setKind($data['kind']);
        $contract->setPay($data['pay']);

        $office = $this->entityManager->getRepository(Office::class)
                ->findOneById($data['office']);
        $contract->setOffice($office);

        $company = $this->entityManager->getRepository(Legal::class)
                ->findOneById($data['company']);
        $contract->setCompany($company);

        $this->entityManager->persist($contract);

        if ($flushnow){
            $this->entityManager->flush();                
        }
    }
    
    
    /**
     * Возможность удаления договора
     * @param Contract $contract
     * @return bool
     */
    public function allowRemoveContract($contract)
    {
        $contractCount = $this->entityManager->getRepository(Mutual::class)
                ->count(['contract' => $contract->getId()]);
        
        return $contractCount == 0;
    }
    
    /**
     * Удаление договора
     * @param Contract $contract
     * @return type
     */
    public function removeContract($contract)
    {
        if ($this->allowRemoveContract($contract)){
            $this->entityManager->remove($contract);

            $this->entityManager->flush();
        }
        return;
    }
    
}

