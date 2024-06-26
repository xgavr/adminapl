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
use Company\Entity\LegalLocation;
use Application\Entity\Order;
use Bank\Entity\Payment;
use Bank\Entity\QrCode;
use Bank\Entity\QrCodePayment;
use Stock\Entity\Ptu;
use Stock\Entity\Revise;
use ApiMarketPlace\Entity\Marketplace;
use Stock\Entity\Comitent;
use Stock\Entity\ComitentBalance;
use ApiMarketPlace\Entity\MarketSaleReport;

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
//        var_dump($data);
        $inn = (empty($data['inn'])) ? null:$data['inn']; 
        $kpp = (empty($data['kpp'])) ? null:$data['kpp']; 
        $name = (empty($data['name'])) ? null:$data['name']; 

        $legal->setName($name);            
        $legal->setInn($inn);            
        $legal->setKpp($kpp);            
        $legal->setOgrn((empty($data['ogrn'])) ? null:$data['ogrn']);            
        $legal->setOkpo((empty($data['okpo'])) ? null:$data['okpo']);            
        $legal->setOkato((empty($data['okato'])) ? null:$data['okato']);            
        $legal->setOktmo((empty($data['oktmo'])) ? null:$data['oktmo']);            
        $legal->setHead((empty($data['head'])) ? null:$data['head']);            
        $legal->setAddress((empty($data['address'])) ? null:$data['address']); 
        $legal->setInfo((empty($data['info'])) ? null:$data['info']);            
        $legal->setEdoAddress((empty($data['edoAddress'])) ? null:$data['edoAddress']); 
        $legal->setSbpLegalId((empty($data['sbpLegalId'])) ? null:$data['sbpLegalId']); 
        
        if (isset($data['chiefAccount'])){
            $legal->setChiefAccount($data['chiefAccount']);            
        }    
        if (!empty($data['status'])){
            $legal->setStatus($data['status']);
        }    
        if (isset($data['edoAddress'])){
            $legal->setEdoAddress($data['edoAddress']); 
        }    
                
        if (isset($data['edoOperator'])){
            if (is_numeric($data['edoOperator'])){
                $edoOperator = $this->entityManager->getRepository(EdoOperator::class)
                        ->find($data['edoOperator']);
                $legal->setEdoOpertator($edoOperator); 
            } else {
                $legal->setEdoOpertator(null);             
            }
        }    

        if (isset($data['dateStart'])){
            $legal->setDateStart(date('Y-m-d', strtotime($data['dateStart'])));
        }

        $this->entityManager->persist($legal);        
        $this->entityManager->flush($legal);
        $this->entityManager->refresh($legal);
        
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
            $legal->setOkato((empty($data['okato'])) ? null:$data['okato']);            
            $legal->setOktmo((empty($data['oktmo'])) ? null:$data['oktmo']);            
            $legal->setHead((empty($data['head'])) ? null:$data['head']);            
            $legal->setChiefAccount((empty($data['chiefAccount'])) ? null:$data['chiefAccount']);            
            $legal->setInfo((empty($data['info'])) ? null:$data['info']);            
            $legal->setAddress((empty($data['address'])) ? null:$data['address']);            
            $legal->setStatus((empty($data['status'])) ? Legal::STATUS_ACTIVE:$data['status']);            
            $legal->setEdoAddress((empty($data['edoAddress'])) ? null:$data['edoAddress']); 
            $legal->setSbpLegalId((empty($data['spbLegalId'])) ? null:$data['spbLegalId']); 
            
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
        if (!empty($data['rs'])){
            
            $foundAccount = $this->entityManager->getRepository(BankAccount::class)
                    ->findOneBy(['legal' => $legal->getId(), 'rs' => $data['rs']]);
            
            if ($foundAccount){
                return $this->updateBankAccount($foundAccount, $data, $flushnow);
            }

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
            $bankAccount->setCashSbp((empty($data['cashSbp'])) ? null:$data['cashSbp']); 
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
        
        return;
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
        if (!empty($data['rs'])){
            $bankAccount->setName((empty($data['name'])) ? null:$data['name']);            
            $bankAccount->setCity((empty($data['city'])) ? null:$data['city']);            
            $bankAccount->setBik((empty($data['bik'])) ? null:$data['bik']);            
            $bankAccount->setKs((empty($data['ks'])) ? null:$data['ks']);            
            $bankAccount->setRs((empty($data['rs'])) ? null:$data['rs']);
            
            if (!empty($data['status'])){
                $bankAccount->setStatus($data['status']);            
            }    
            if (!empty($data['accountType'])){
                $bankAccount->setAccountType($data['accountType']);            
            }    
            if (!empty($data['api'])){
                $bankAccount->setApi($data['api']);
            }            
            if (!empty($data['statement'])){
                $bankAccount->setStatement($data['statement']);
            }
            $bankAccount->setCash(null);
            if (!empty($data['cash'])){
                $bankAccount->setCash($data['cash']);
            }
            $bankAccount->setCashSbp(null);
            if (!empty($data['cashSbp'])){
                $bankAccount->setCashSbp($data['cashSbp']);
            }
            if (!empty($data['dateStart'])){
                $bankAccount->setDateStart($data['dateStart']);                    
            }    

            $this->entityManager->persist($bankAccount);

            if ($flushnow){
                $this->entityManager->flush();                
            }
            return $bankAccount;
        }
        
        return;
    }
    
    /**
     * Можно ли удалить счет
     * @param BankAccount $bankAccount
     * @return bool
     */
    public function allowRemoveBankAccount($bankAccount)
    {
        $orderCount = $this->entityManager->getRepository(Order::class)
                ->count(['bankAccount' => $bankAccount->getId()]);
        if (!empty($orderCount)){
            return false;
        }
        $paymentCount = $this->entityManager->getRepository(Payment::class)
                ->count(['bankAccount' => $bankAccount->getId()]);
        if (!empty($paymentCount)){
//            var_dump(2);
            return false;
        }
        $qrcodeCount = $this->entityManager->getRepository(QrCode::class)
                ->count(['bankAccount' => $bankAccount->getId()]);
        if (!empty($qrcodeCount)){
//            var_dump(3);
            return false;
        }
        $qrcodePaymentCount = $this->entityManager->getRepository(QrCodePayment::class)
                ->count(['bankAccount' => $bankAccount->getId()]);
        if (!empty($qrcodePaymentCount)){
//            var_dump(4);
            return false;
        }
        
        $rsCount = $this->entityManager->getRepository(BankAccount::class)
                ->count(['rs' => $bankAccount->getRs(), 'legal' => $bankAccount->getLegal()->getId()]);
        
        $statementCount = $this->entityManager->getRepository(Statement::class)
                ->count(['counterpartyAccountNumber' => $bankAccount->getRs()]);
        $statementCount1 = $this->entityManager->getRepository(Statement::class)
                ->count(['account' => $bankAccount->getRs()]);
        if ($rsCount == 1 && ($statementCount > 0 || $statementCount1 > 0)){
//            var_dump(5);
            return false;
        }    
        
        return true;
    }
    
    /**
     * Удалить банковский счет
     * 
     * @param BankAccount $bankAccount
     */
    public function removeBankAccount($bankAccount)
    {        
        if ($this->allowRemoveBankAccount($bankAccount)){
            $this->entityManager->remove($bankAccount);            
        } else {
            $bankAccount->setStatus(BankAccount::STATUS_RETIRED);
            $this->entityManager->persist($bankAccount);
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
   
    /**
     * Получение информации о банке по БИК
     * @param string $bik
     * @return array 
     */
    public function bankInfo($bik)
    {
        $bikId = preg_replace('/[^0-9]/', '', $bik);
        if (!empty($bikId)){
            $setting = $this->adminManager->getSettings();
            $token = $setting['dadata_api_key'];
            $secret = $setting['dadata_standart_key'];
            $dadata = new \Dadata\DadataClient($token, $secret);

            $data = $dadata->findById("bank", $bikId, 1);
//            var_dump($data); exit;
            if (is_array($data)){
                return $data;
            }    
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
        $contract->setBalance(0);
        
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
        if (empty($contractCount)){
            $contractCount = $this->entityManager->getRepository(Ptu::class)
                    ->count(['contract' => $contract->getId()]);            
        }
        if (empty($contractCount)){
            $contractCount = $this->entityManager->getRepository(Revise::class)
                    ->count(['contract' => $contract->getId()]);            
        }
        if (empty($contractCount)){
            $contractCount = $this->entityManager->getRepository(Comitent::class)
                    ->count(['contract' => $contract->getId()]);            
        }
        if (empty($contractCount)){
            $contractCount = $this->entityManager->getRepository(ComitentBalance::class)
                    ->count(['contract' => $contract->getId()]);            
        }
        if (empty($contractCount)){
            $contractCount = $this->entityManager->getRepository(MarketSaleReport::class)
                    ->count(['contract' => $contract->getId()]);            
        }
        if (empty($contractCount)){
            $contractCount = $this->entityManager->getRepository(Marketplace::class)
                    ->count(['contract' => $contract->getId()]);            
        }
        if (empty($contractCount)){
            $contractCount = $this->entityManager->getRepository(Order::class)
                    ->count(['contract' => $contract->getId()]);            
        }
        
        return empty($contractCount);
    }
    
    /**
     * Замена договора
     * 
     * @param Contrcat $contract
     * @param Contract $oldContract
     */
    private function changeContract($contract, $oldContract)
    {
        $this->entityManager->getConnection()
                ->update('mutual', ['contract_id' => $contract->getId()], ['contract_id' => $oldContract->getId()]);
        $this->entityManager->getConnection()
                ->update('retail', ['contract_id' => $contract->getId()], ['contract_id' => $oldContract->getId()]);
        $this->entityManager->getConnection()
                ->update('ptu', ['contract_id' => $contract->getId()], ['contract_id' => $oldContract->getId()]);
        $this->entityManager->getConnection()
                ->update('revise', ['contract_id' => $contract->getId()], ['contract_id' => $oldContract->getId()]);
        $this->entityManager->getConnection()
                ->update('comitent', ['contract_id' => $contract->getId()], ['contract_id' => $oldContract->getId()]);
        $this->entityManager->getConnection()
                ->update('comitent_balance', ['contract_id' => $contract->getId()], ['contract_id' => $oldContract->getId()]);
        $this->entityManager->getConnection()
                ->update('market_sale_report', ['contract_id' => $contract->getId()], ['contract_id' => $oldContract->getId()]);
        $this->entityManager->getConnection()
                ->update('marketplace', ['contract_id' => $contract->getId()], ['contract_id' => $oldContract->getId()]);
        $this->entityManager->getConnection()
                ->update('orders', ['contract_id' => $contract->getId()], ['contract_id' => $oldContract->getId()]);
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
     * Обновить баланс договора
     * @param Contract $contract
     */
    public function updateContractBalance($contract)
    {
        $data = ['contract_id' => $contract->getId()];
        $this->entityManager->getRepository(Mutual::class)
                ->updateContractBalance($data);
        return;
    }
    
    /**
     * Слияние договоров одного тпа в один выбранный
     * 
     * @param Contract $selectedContract
     */
    public function contractUnion($selectedContract)
    {
       $contracts = $this->entityManager->getRepository(Contract::class)
               ->findBy([
                   'legal' => $selectedContract->getLegal()->getId(),
                   'office' => $selectedContract->getOffice()->getId(),
                   'company' => $selectedContract->getCompany()->getId(),
                   'kind' => $selectedContract->getKind(),
                   'pay' => $selectedContract->getPay(),
                   'status' => $selectedContract->getStatus(),
                ]);
       
       foreach ($contracts as $oldContract){
           if ($selectedContract->getId() != $oldContract->getId()){
                $this->changeContract($selectedContract, $oldContract);
                $this->removeContract($oldContract);
           }
       }
       
       $this->updateContractBalance($selectedContract);
       
       return;
    }
    
    /**
     * Обновить балансы всех договоров
     * @return null
     */
    public function contractsBalance()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(900);
        
        $contracts = $this->entityManager->getRepository(Contract::class)
                ->findAll();
        foreach ($contracts as $contract){
            $this->updateContractBalance($contract);
        }
        
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
    
    /**
     * Добавить адрес
     * 
     * @param Legal $legal
     * @param array $data
     * @retun LegalLocation 
     */
    public function addLegalLocation($legal, $data)
    {                
        $location = new LegalLocation();            
        $location->setAddress($data['address']);            
        $location->setDateStart($data['dateStart']);            
        $location->setStatus($data['status']);            
        $location->setKpp($data['kpp']);            
                
        $currentDate = date('Y-m-d H:i:s');
        $location->setDateCreated($currentDate);
        
        $location->setLegal($legal);
            
        $this->entityManager->persist($location);
        $this->entityManager->flush();                
        
        return $location;
    }

    /**
     * Обновить адрес
     *  
     * @param LegalLocation $location 
     * @param array $data
     * @return LegalLocation 
     */
    public function updateLegalLocation($location, $data)
    {                
        $location->setAddress($data['address']);            
        $location->setDateStart($data['dateStart']);            
        $location->setStatus($data['status']);            
        $location->setKpp($data['kpp']);            
                
        $this->entityManager->persist($location);
        $this->entityManager->flush();                
        
        return $location;
    }
    
    /**
     * Удаление адреса
     * @param LegalLocation $location
     * @return null
     */
    public function removeLegalLocation($location)
    {
        $this->entityManager->remove($location);
        $this->entityManager->flush();

        return;
    }        
}

