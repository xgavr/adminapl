<?php
namespace Company\Service;

use Company\Entity\Legal;
use Company\Entity\BankAccount;
use Company\Entity\Contract;
use Company\Entity\Office;
use Laminas\Json\Json;
use Application\Entity\Contact;
use Stock\Entity\Mutual;
use Bank\Entity\Statement;
use Stock\Entity\Retail;
use Company\Entity\EdoOperator;

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
     * Doctrine entity manager.
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;  
        
    /**
     * Constructs the service.
     */
    public function __construct($entityManager, $adminManager) 
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
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
        $inn = (empty($data['inn'])) ? null:$data['inn']; 
        $kpp = (empty($data['kpp'])) ? null:$data['kpp']; 
        $name = (empty($data['name'])) ? null:$data['name']; 

        $legal->setName($name);            
        $legal->setInn($inn);            
        $legal->setKpp($kpp);            
        $legal->setOgrn((empty($data['ogrn'])) ? null:$data['ogrn']);            
        $legal->setOkpo((empty($data['okpo'])) ? null:$data['okpo']);            
        $legal->setHead((empty($data['head'])) ? null:$data['head']);            
        $legal->setChiefAccount((empty($data['chiefAccount'])) ? null:$data['chiefAccount']);            
        $legal->setInfo((empty($data['info'])) ? null:$data['info']);            
        $legal->setAddress((empty($data['address'])) ? null:$data['address']);            
        $legal->setStatus((empty($data['status'])) ? Legal::STATUS_ACTIVE:$data['status']);            
        $legal->setEdoAddress((empty($data['edoAddress'])) ? null:$data['edoAddress']); 
                
        if (!empty($data['edoOperator'])){
            $edoOperator = $this->entityManager->getRepository(EdoOperator::class)
                    ->find($data['edoOperator']);
            $legal->setEdoOpertator($edoOperator); 
        } else {
            $legal->setEdoOpertator(null);             
        }

        $currentDate = date('Y-m-d H:i:s');
        $legal->setDateStart($currentDate);
        if (isset($data['dateStart'])){
            $legal->setDateStart(date('Y-m-d', strtotime($data['dateStart'])));
        }

        $this->entityManager->persist($legal);        
        $this->entityManager->flush($legal);                
        
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
        $inn = (empty($data['inn'])) ? null:$data['inn']; 
        $kpp = (empty($data['kpp'])) ? null:$data['kpp']; 
        $name = (empty($data['name'])) ? null:$data['name']; 
        
        $legal = $this->entityManager->getRepository(Legal::class)
                ->findOneByInnKpp($inn, $kpp);
        
        if (!$legal){            
            $legal = $this->entityManager->getRepository(Legal::class)
                    ->findOneByName($name);            
        }
        
        if ($legal){
//            $contact->removeLegalAssociation($legal);
            if (!$contact->getLegals()->contains($legal)){
                $contact->addLegal($legal);
                $this->entityManager->persist($contact);
                $this->entityManager->flush();                
            }    
            $this->updateLegal($legal, $data);
        }

        if ($legal == null){
            $legal = new Legal();            
            $legal->setName($name);            
            $legal->setInn($inn);            
            $legal->setKpp($kpp);            
            $legal->setOgrn((empty($data['ogrn'])) ? null:$data['ogrn']);            
            $legal->setOkpo((empty($data['okpo'])) ? null:$data['okpo']);            
            $legal->setHead((empty($data['head'])) ? null:$data['head']);            
            $legal->setChiefAccount((empty($data['chiefAccount'])) ? null:$data['chiefAccount']);            
            $legal->setInfo((empty($data['info'])) ? null:$data['info']);            
            $legal->setAddress((empty($data['address'])) ? null:$data['address']);            
            $legal->setStatus((empty($data['status'])) ? Legal::STATUS_ACTIVE:$data['status']);            
            $legal->setEdoAddress((empty($data['edoAddress'])) ? null:$data['edoAddress']); 
            
            if (!empty($data['edoOperator'])){
                $edoOperator = $this->entityManager->getRepository(EdoOperator::class)
                        ->find($data['edoOperator']);
                $legal->setEdoOpertator($edoOperator); 
            } else {
                $legal->setEdoOpertator(null);                 
            }

            $currentDate = date('Y-m-d H:i:s');
            $legal->setDateCreated($currentDate);
            
            $legal->setDateStart($currentDate);
            if (isset($data['dateStart'])){
                $legal->setDateStart(date('Y-m-d', strtotime($data['dateStart'])));
            }
            
            $legal->addContact($contact);
            $this->entityManager->persist($legal);
            $this->entityManager->flush();                
        }   
        
        return $legal;
    }
    
    /**
     * Удалить ЮЛ контакта
     * @param Legal $legal
     * @param Contact $contact
     */
    public function removeLegalAssociation($legal, $contact = null)
    {
        if ($contact){
            $contact->removeLegalAssociation($legal);
        } else {
            $contacts = $legal->getContacts();
            foreach ($contacts as $contact){
                $contact->removeLegalAssociation($legal);                
            }
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
        $bankAccount->setName((empty($data['name'])) ? 'Банк':$data['name']);            
        $bankAccount->setCity((empty($data['city'])) ? null:$data['city']);            
        $bankAccount->setBik((empty($data['bik'])) ? null:$data['bik']);            
        $bankAccount->setKs((empty($data['ks'])) ? null:$data['ks']);            
        $bankAccount->setRs((empty($data['rs'])) ? null:$data['rs']);            
        $bankAccount->setStatus((empty($data['status'])) ? BankAccount::STATEMENT_ACTIVE:$data['status']);            
        $bankAccount->setAccountType((empty($data['accountType'])) ? BankAccount::ACСOUNT_CHECKING:$data['accountType']);            
        $bankAccount->setApi((empty($data['api'])) ? BankAccount::API_NO:$data['api']);            
        $bankAccount->setStatement((empty($data['statement'])) ? BankAccount::STATEMENT_RETIRED:$data['statement']);            
        $bankAccount->setCash((empty($data['cash'])) ? null:$data['cash']); 
        $bankAccount->setDateStart((empty($data['dateStart'])) ? date('Y-m-d'):$data['dateStart']);                    

        $currentDate = date('Y-m-d H:i:s');
        $bankAccount->setDateCreated($currentDate);
            
        $this->entityManager->persist($bankAccount);
        
        $bankAccount->setLegal($legal);
        
        if ($flushnow){
            $this->entityManager->flush();                
        }
        
        return $bankAccount;
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
        $bankAccount->setName((empty($data['name'])) ? null:$data['name']);            
        $bankAccount->setCity((empty($data['city'])) ? null:$data['city']);            
        $bankAccount->setBik((empty($data['bik'])) ? null:$data['bik']);            
        $bankAccount->setKs((empty($data['ks'])) ? null:$data['ks']);            
        $bankAccount->setRs((empty($data['rs'])) ? null:$data['rs']);            
        $bankAccount->setStatus((empty($data['status'])) ? BankAccount::STATEMENT_ACTIVE:$data['status']);            
        $bankAccount->setAccountType((empty($data['accountType'])) ? BankAccount::ACСOUNT_CHECKING:$data['accountType']);            
        $bankAccount->setApi((empty($data['api'])) ? BankAccount::API_NO:$data['api']);            
        $bankAccount->setStatement((empty($data['statement'])) ? BankAccount::STATEMENT_RETIRED:$data['statement']);            
        $bankAccount->setCash((empty($data['cash'])) ? null:$data['cash']);            
        $bankAccount->setDateStart((empty($data['dateStart'])) ? date('Y-m-d'):$data['dateStart']);                    

        $this->entityManager->persist($bankAccount);

        if ($flushnow){
            $this->entityManager->flush();                
        }
        return $bankAccount;
    }
    
    /**
     * Удалить банковский счет
     * 
     * @param BankAccount $bankAccount
     */
    public function removeBankAccount($bankAccount)
    {
        $statementCount = $this->entityManager->getRepository(Statement::class)
                ->count(['counterpartyAccountNumber' => $bankAccount->getRs()]);
        $statementCount1 = $this->entityManager->getRepository(Statement::class)
                ->count(['account' => $bankAccount->getRs()]);
        
        if ($statementCount > 0 || $statementCount1 > 0){
            $bankAccount->setStatus(BankAccount::STATUS_RETIRED);
            $this->entityManager->persist($bankAccount);
        } else {
            $this->entityManager->remove($bankAccount);            
        }
        
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
   
    /*
     * Получение информации о предприятии по ИНН
     * @var $inn string
     * return json 
     */
    public function innInfo($inn)
    {
        $inn = preg_replace('/[^0-9]/', '', $inn);
        if (strlen($inn) >= 10){
            $setting = $this->adminManager->getSettings();
            $token = $setting['dadata_api_key'];
            $secret = $setting['dadata_standart_key'];
            $dadata = new \Dadata\DadataClient($token, $secret);

            $data = $dadata->findById("party", $inn);
//            var_dump($data); exit;
            return $data;
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
        $contract->setNds(($data['nds']));
        
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
        $this->entityManager->flush($contract);                
        
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
        $contract->setNds($data['nds']);

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
        if (empty($contractCount)){
            $contractCount = $this->entityManager->getRepository(Retail::class)
                    ->count(['contract' => $contract->getId()]);            
        }
        
        return empty($contractCount);
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
        } else {
            $contract->setStatus(Contract::STATUS_RETIRED);
            $this->entityManager->persist($contract);
        }

        $this->entityManager->flush();
        return;
    }
    
    /**
     * Добавить оператора эдо
     * 
     * @param array $data
     * @return EdoOperator 
     */
    public function addEdoOperator($data)
    {                
        $edoOperator = new EdoOperator();            
        $edoOperator->setName($data['name']);            
        $edoOperator->setCode($data['code']);            
        $edoOperator->setInn($data['inn']);            
        $edoOperator->setStatus($data['status']);            
        $edoOperator->setInfo(empty($data['info']) ? null:$data['info']);            
        $edoOperator->setSite(empty($data['site']) ? null:$data['site']);            
                
        $currentDate = date('Y-m-d H:i:s');
        $edoOperator->setDateCreated($currentDate);
            
        $this->entityManager->persist($edoOperator);
        $this->entityManager->flush();                
        
        return $edoOperator;
    }

    /**
     * обновить оператора эдо
     *  
     * @param EdoOperator $edoOperator 
     * @param array $data
     * @return EdoOperator 
     */
    public function updateEdoOperator($edoOperator, $data)
    {                
        $edoOperator->setName($data['name']);            
        $edoOperator->setCode($data['code']);            
        $edoOperator->setInn($data['inn']);            
        $edoOperator->setStatus($data['status']);            
        $edoOperator->setInfo(empty($data['info']) ? null:$data['info']);            
        $edoOperator->setSite(empty($data['site']) ? null:$data['site']);            
                
        $this->entityManager->persist($edoOperator);
        $this->entityManager->flush();                
        
        return $edoOperator;
    }
    
    /**
     * Удаление опреатора эдо
     * @param EdoOperator $edoOperator
     * @return null
     */
    public function removeEdoOperator($edoOperator)
    {
        $legalCount = $this->entityManager->getRepository(Legal::class)
                ->count(['edoOperator' => $edoOperator->getId()]);
        if ($legalCount === 0){
            $this->entityManager->remove($edoOperator);
            $this->entityManager->flush();
        }

        return;
    }    
}

