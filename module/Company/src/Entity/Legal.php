<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Company\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Laminas\Filter\Digits;
use Company\Entity\BankAccount;
use Company\Entity\Contract;
use Company\Entity\EdoOperator;
use Company\Entity\Office;
use Company\Entity\LegalLocation;

/**
 * Description of Legal
 * @ORM\Entity(repositoryClass="\Company\Repository\LegalRepository")
 * @ORM\Table(name="legal")
 * @author Daddy
 */
class Legal {
        
     // Legal status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
   
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="inn")   
     */
    protected $inn;

    /**
     * @ORM\Column(name="kpp")   
     */
    protected $kpp;

    /**
     * @ORM\Column(name="ogrn")   
     */
    protected $ogrn;

    /**
     * @ORM\Column(name="okpo")   
     */
    protected $okpo;

    /**
     * @ORM\Column(name="oktmo")   
     */
    protected $oktmo;

    /**
     * @ORM\Column(name="okato")   
     */
    protected $okato;

    /**
     * @ORM\Column(name="head")   
     */
    protected $head;

    /**
     * @ORM\Column(name="chief_account")   
     */
    protected $chiefAccount;

    /**
     * @ORM\Column(name="name")   
     */
    protected $name;

    /**
     * @ORM\Column(name="info")   
     */
    protected $info;

    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;

    /**
     * @ORM\Column(name="address")   
     */
    protected $address;

    /**
     * @ORM\Column(name="edo_address")   
     */
    protected $edoAddress;

    /**
     * Уникальный код клиента в СПБ
     * @ORM\Column(name="sbp_legal_id")   
     */
    protected $sbpLegalId;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;
    
    /** 
     * @ORM\Column(name="date_start")  
     */
    protected $dateStart;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\EdoOperator", inversedBy="legals") 
     * @ORM\JoinColumn(name="edo_operator_id", referencedColumnName="id")
     */
    private $edoOperator;

    /**
    * @ORM\OneToMany(targetEntity="Company\Entity\BankAccount", mappedBy="legal")
    * @ORM\JoinColumn(name="id", referencedColumnName="legal_id")
     * @ORM\OrderBy({"status" = "ASC", "id" = "DESC"})
     */
    private $bankAccounts;
    
    /**
    * @ORM\OneToMany(targetEntity="Company\Entity\Contract", mappedBy="legal")
    * @ORM\JoinColumn(name="id", referencedColumnName="legal_id")
     * @ORM\OrderBy({"status" = "ASC", "dateStart" = "DESC", "id" = "DESC"})
     */
    private $contracts;
    
    /**
    * @ORM\OneToMany(targetEntity="Stock\Entity\Mutual", mappedBy="legal")
    * @ORM\JoinColumn(name="id", referencedColumnName="legal_id")
    */
    private $mutuals;
    
    /**
    * @ORM\OneToMany(targetEntity="Stock\Entity\Mutual", mappedBy="company")
    * @ORM\JoinColumn(name="id", referencedColumnName="company_id")
    */
    private $companyMutuals;
    
    /**
     * @ORM\ManyToMany(targetEntity="\Application\Entity\Contact", mappedBy="legals")
     */
    private $contacts;
    
   /**
    * @ORM\OneToMany(targetEntity="Stock\Entity\Ptu", mappedBy="legal")
    * @ORM\JoinColumn(name="id", referencedColumnName="legal_id")
   */
   private $ptu;            

    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Order", mappedBy="legal")
    * @ORM\JoinColumn(name="id", referencedColumnName="legal_id")
     */
    private $orders;
    
    /**
    * @ORM\OneToMany(targetEntity="Zp\Entity\Personal", mappedBy="company")
    * @ORM\JoinColumn(name="id", referencedColumnName="company_id")
     */
    private $personal;
    
    /**
    * @ORM\OneToMany(targetEntity="Company\Entity\LegalLocation", mappedBy="legal")
    * @ORM\JoinColumn(name="id", referencedColumnName="legal_id")
     * @ORM\OrderBy({"status" = "ASC", "id" = "DESC"})
     */
    private $locations;

    /**
    * @ORM\OneToMany(targetEntity="Zp\Entity\PersonalAccrual", mappedBy="company")
    * @ORM\JoinColumn(name="id", referencedColumnName="company_id")
     */
    private $personalAccruals;
    
    /**
    * @ORM\OneToMany(targetEntity="Zp\Entity\PersonalMutual", mappedBy="company")
    * @ORM\JoinColumn(name="id", referencedColumnName="company_id")
     */
    private $personalMutuals;
    
    /**
    * @ORM\OneToMany(targetEntity="Zp\Entity\PersonalRevise", mappedBy="company")
    * @ORM\JoinColumn(name="id", referencedColumnName="company_id")
     */
    private $personalRevises;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->bankAccounts = new ArrayCollection();
        $this->contracts = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->personal = new ArrayCollection();
        $this->personalMutuals = new ArrayCollection();
        $this->personalAccruals = new ArrayCollection();
        $this->personalRevises = new ArrayCollection();
        $this->mutuals = new ArrayCollection();
        $this->companyMutuals = new ArrayCollection();
    }
    
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getName() 
    {
        return $this->name;
    }

    public function setName($name) 
    {
        $this->name = $name;
    }     

    public function getInn() 
    {
        return $this->inn;
    }

    public function setInn($inn) 
    {
        $filter = new Digits();
        $this->inn = $filter->filter($inn);
    }     

    public function getKpp() 
    {
        return $this->kpp;
    }

    public function setKpp($kpp) 
    {
        $filter = new Digits();
        $this->kpp = $filter->filter($kpp);
    }     

    public function getInnKpp($separator = '/') 
    {
        return trim($this->inn.$separator.$this->kpp, ' /');
    }


    public function getOgrn() 
    {
        return $this->ogrn;
    }

    public function getOgrnPresent() 
    {
        if ($this->ogrn){
            return 'ОГРН: '.$this->ogrn;
        }
        
        return;
    }

    public function setOgrn($ogrn) 
    {
        $this->ogrn = $ogrn;
    }     
    
    public function getOkpo() 
    {
        return $this->okpo;
    }

    public function getOkpoPresent() 
    {
        if ($this->okpo){
            return 'ОКПО: '.$this->okpo;
        }
        
        return;
    }

    public function setOkpo($okpo) 
    {
        $this->okpo = $okpo;
    }     
    
    public function getOkato() {
        return $this->okato;
    }

    public function getOkatoPresent() 
    {
        if ($this->okato){
            return 'ОКАТО: '.$this->okato;
        }
        
        return;
    }

    public function setOkato($okato) {
        $this->okato = $okato;
        return $this;
    }

    public function getOktmo() {
        return $this->oktmo;
    }

    public function getOktmoPresent() 
    {
        if ($this->oktmo){
            return 'ОКTMO: '.$this->oktmo;
        }
        
        return;
    }

    public function setOktmo($oktmo) {
        $this->oktmo = $oktmo;
        return $this;
    }
    
    public function getHead() 
    {
        return $this->head;
    }

    public function getHeadFio() 
    {
        if ($this->head){
            list($lastName, $firstName, $secondName) = explode(' ', $this->head);
            $result[] = $lastName;
            $result[] = ($firstName) ? ucfirst(mb_substr(trim($firstName, '.'), 0, 1)).'.': null;
            $result[] = ($secondName) ? ucfirst(mb_substr(trim($secondName, '.'), 0, 1)).'.': null;
            return implode(' ', array_filter($result));
        }
        
        return;
    }

    public function getHeadLastName() 
    {
        if ($this->head){
            list($lastName, $firstName, $secondName) = explode(' ', $this->head);
            return $lastName;
        }
        
        return;
    }

    public function getHeadFirstName() 
    {
        if ($this->head){
            list($lastName, $firstName, $secondName) = explode(' ', $this->head);
            return $firstName;
        }
        
        return;
    }

    public function getHeadSecondName() 
    {
        if ($this->head){
            list($lastName, $firstName, $secondName) = explode(' ', $this->head);
            return $secondName;
        }
        
        return;
    }

    public function setHead($head) 
    {
        $this->head = $head;
    }     
    
    public function getChiefAccount() 
    {
        return $this->chiefAccount;
    }

    public function getChiefAccountPresent() 
    {
        if ($this->chiefAccount){
            return 'Главный бухгалтер: '.$this->chiefAccount;
        }
        
        return;
    }

    public function setChiefAccount($chiefAccount) 
    {
        $this->chiefAccount = $chiefAccount;
    }     
    
    public function getInfo() 
    {
        return $this->info;
    }

    public function setInfo($info) 
    {
        $this->info = $info;
    }     

    /**
     * Адрес организации
     * @param date $onDate
     * @return string
     */
    public function getAddress($onDate = null) 
    {
        return $this->address;
    }

    public function setAddress($address) 
    {
        $this->address = $address;
    }     

    public function getEdoAddress() 
    {
        return $this->edoAddress;
    }

    public function setEdoAddress($edoAddress) 
    {
        $this->edoAddress = $edoAddress;
    }     

    public function getSbpLegalId() {
        return $this->sbpLegalId;
    }

    public function setSbpLegalId($sbpLegalId): void {
        $this->sbpLegalId = $sbpLegalId;
    }
    
    /**
     * Returns status.
     * @return int     
     */
    public function getStatus() 
    {
        return $this->status;
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusList() 
    {
        return [
            self::STATUS_ACTIVE => 'Действующее',
            self::STATUS_RETIRED => 'Не используется'
        ];
    }    
    
    /**
     * Returns user status as string.
     * @return string
     */
    public function getStatusAsString()
    {
        $list = self::getStatusList();
        if (isset($list[$this->status]))
            return $list[$this->status];
        
        return 'Unknown';
    }    
    
    public function getStatusActive()
    {
        return self::STATUS_ACTIVE;
    }        
    
    public function getStatusRetired()
    {
        return self::STATUS_RETIRED;
    }        
    
    /**
     * Sets status.
     * @param int $status     
     */
    public function setStatus($status) 
    {
        $this->status = $status;
    }   
    
    /**
     * Returns the date of user creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * Sets the date when this user was created.
     * @param string $dateCreated     
     */
    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
    }    
        
    /**
     * Returns the date of start.
     * @return string     
     */
    public function getDateStart() 
    {
        return $this->dateStart;
    }
    
    /**
     * Sets the date when start.
     * @param string $dateStart     
     */
    public function setDateStart($dateStart) 
    {
        $this->dateStart = $dateStart;
    }    
        
    /**
     * @return array
     */
    public function getBankAccounts()
    {
        return $this->bankAccounts;
    }
    
    /**
     * Банковские счета с сортировкой
     * @return ArrayCollection
     */
    public function getOrderBankAccounts() 
    {
        $iterator = $this->bankAccounts->getIterator();
        $iterator->uasort(function ($a, $b) {
            return ($a->getId() < $b->getId()) ? 1 : -1;
        });        
        return new ArrayCollection(iterator_to_array($iterator));
    }    
    
    /**
     * Банковский счет
     * @return BankAccount
     */
    public function getLastActiveBankAccount()
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', BankAccount::STATUS_ACTIVE))
                ->orderBy(['id' => Criteria::DESC])
                ->setMaxResults(1)
                ;
        $data = $this->bankAccounts->matching($criteria);
        foreach ($data as $row){
            return $row;
        }
        
        return;
    }
        
    /**
     * Представдение расчетного счета
     * @param array $options
     * @retrun string
     */
    public function getBankAccountPresent($options = null)
    {
        $bankAccount = $this->getLastActiveBankAccount();
        $result = '';
        if ($bankAccount){
            $result .= 'р/с '.$bankAccount->getRs();
            $result .= ', ';
            $result .= 'в банке '.$bankAccount->getNameWithCity();
            $result .= ', ';
            $result .= 'БИК '.$bankAccount->getBik();
            $result .= ', ';
            $result .= 'к/с '.$bankAccount->getKs();
        }    
        return trim($result);        
    }
    
    /**
     * Assigns.
     */
    public function addBankAccount($bankAccount)
    {
        $this->bankAccounts[] = $bankAccount;
    }    
    
    /**
     * @return array
     */
    public function getContracts()
    {
        return $this->contracts;
    }
        
    /**
     * Договора с сортировкой
     * @return ArrayCollection
     */
    public function getOrderContracts() 
    {
        $iterator = $this->contracts->getIterator();
        $iterator->uasort(function ($a, $b) {
            return ($a->getDateStart() < $b->getDateStart()) ? 1 : -1;
        });        
        return new ArrayCollection(iterator_to_array($iterator));
    }    
    
    /**
     * Текущий договор
     * @return Contract
     */
    public function getLastContract()
    {
        $contracts = $this->getOrderContracts();
        if ($contracts->count()){
            return $contracts[0];
        }        
        return;
    }
    
    /**
     * Assigns.
     */
    public function addContract($contract)
    {
        $this->contracts[] = $contract;
    }    
    
    /*
     * Возвращает первый связанный contact с клиентом.
     * @return Contact
     */
    
    public function getClientContact() 
    {
        foreach ($this->contacts as $contact){
            if ($contact->getClient()){
                return $contact;
            }
        }
        return;
    }

    /*
     * Возвращает связанный contact.
     * @return array
     */
    
    public function getContacts() 
    {
        return $this->contacts;
    }

    /*
     * Возвращает связанный supplier.
     * @return array
     */
    
    public function getSupplier() 
    {
        foreach ($this->contacts as $contact){
            if ($contact->getSupplier()){
                return $contact->getSupplier();
            }
        }
        return;
    }


    /**
     * Задает связанный contact.
     * @param \Application\Entity\Contact $contact
     */    
    public function addContact($contact) 
    {
        $this->contacts[] = $contact;
//        $contact->removeLegalAssociation($legal);
        $contact->addLegal($this);
    }
    
    /**
     * Проверяет принадлежность к офису
     */
    public function isOfficeLegal()
    {
        foreach ($this->contacts as $contact){
            if ($contact->getOffice()){
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Получить офисы
     */
    public function getOffices()
    {
        $result = [];
        foreach ($this->contacts as $contact){
            if ($contact->getOffice()){
                $result[$contact->getOffice()->getId()] = $contact->getOffice();
            }
        }
        
        return $result;
    }
    
    /**
     * Компания есть в этом офисе
     * @param Office $office
     * @return bool
     */
    public function companyInOffice($office)
    {
        return array_key_exists($office->getId(), $this->getOffices());         
    }

    /**
     * Представдение компании
     * @param Office $office
     * @param array $options
     * @retrun string
     */
    public function getCompanyPresent($office, $options = null)
    {
        $result = '';
        $result .= $this->name;
        $result .= ', ';
        $result .= $this->inn.'/'.$this->kpp;
        $result .= ', ';
        $result .= $this->address;
        $result .= ', ';
        $result .= $office->getLegalContact()->getPhonesAsString();
        
        return trim($result);        
    }
    
    /**
     * Представдение организации с р/с
     * @param Office $office
     * @param array $options
     * @retrun string
     */
    public function getCompanyBankAccountPresent($office, $options = null)
    {
        $result = '';
        $result .= $this->getCompanyPresent($office, $options);
        $bankPresent = $this->getBankAccountPresent($options);
        if ($bankPresent){
            $result .= ', '.$bankPresent;
        }
        
        return trim($result);        
    }
    
    /**
     * Представдение организации
     * @param array $options
     * @retrun string
     */
    public function getLegalPresent($options = null)
    {
        $locationStatus = LegalLocation::STATUS_ACTIVE;
        $onDate = date('Y-m-d');
        if (is_array($options)){
            if (!empty($options['locationStatus'])){
                $locationStatus = $options['locationStatus'];
            }
            if (!empty($options['onDate'])){
                $onDate = $options['onDate'];
            }
        }

        $result = '';
        $result .= $this->name;
        $kpp = $this->getCurrentKpp($locationStatus, $onDate);
        if ($this->inn){
            $result .= ', ';
            $result .= $this->inn.'/'.$kpp;
        }
        
        $address = $this->getCurrentLocation($locationStatus, $onDate);
        if ($address){
            $result .= ', ';
            $result .= $address;
        }
        if ($this->contacts[0]->getPhonesAsString()){
            $result .= ', ';
            $result .= $this->contacts[0]->getPhonesAsString();
        }    
        
        return trim($result);        
    }    
    
    /**
     * Представдение организации с р/с
     * @param array $options
     * @retrun string
     */
    public function getLegalBankAccountPresent($options = null)
    {
        $result = '';
        $result .= $this->getLegalPresent($options);
        $bankPresent = $this->getBankAccountPresent($options);
        if ($bankPresent){
            $result .= ', '.$bankPresent;
        }
        
        return trim($result);        
    }

    /**
     * Для обновления в Апл
     * @return array
     */
    public function getAplTransfer()
    {
        $result = [
            'inn' => $this->getInn(),
            'kpp' => $this->getKpp(),
            'firmName' => $this->getName(),
            'ogrn' => $this->getOgrn(),
            'okpo' => $this->getOkpo(),
            'firmAddress' => $this->getAddress(),
        ];
        
        $bankAccount = $this->getLastActiveBankAccount();
        if ($bankAccount){
            $result['bik'] = $bankAccount->getBik();
            $result['bank'] = $bankAccount->getNameWithCity();
            $result['firmAccount'] = $bankAccount->getRs();
            $result['firmAccount1'] = $bankAccount->getKs();
        }
        
        
        return $result;
    }
    
    /*
     * @return EdoOperator
     */    
    public function getEdoOperator() 
    {
        return $this->edoOperator;
    }

    /**
     * @param EdoOperator $edoOperator
     */    
    public function setEdoOpertator($edoOperator) 
    {
        $this->edoOperator = $edoOperator;
        if ($edoOperator){
            $edoOperator->addLegal($this);
        }    
    }     
    
    /*
     * Возвращает связанный ptu.
     * @return Ptu
     */    
    public function getPtu() 
    {
        return $this->ptu;
    }    

    /*
     * Возвращает связанный orders.
     * @return array
     */    
    public function getOrders() 
    {
        return $this->orders;
    }    
    
    /**
     * 
     * @return array
     */
    public function getPersonal() {
        return $this->personal;
    }

    /*
     * Возвращает связанный location на дату.
     * @param integer $locationStatus
     * @param date $onDate
     * @return string
     */    
    public function getCurrentLocation($locationStatus = LegalLocation::STATUS_ACTIVE, $onDate = null) 
    {
        if (!$onDate){
            $onDate = date('Y-m-d');
        }

        if (!$this->locations->count()){
            return $this->address;
        }

        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', (string) $locationStatus))
                ->andWhere(Criteria::expr()->lte('dateStart', $onDate))
                ->orderBy(['dateStart' => Criteria::DESC])
                ->setMaxResults(1)
                ;
        
        $data = $this->locations->matching($criteria);
//        var_dump(count($data)); exit;
        foreach ($data as $row){
            return $row->getAddress();
        }

        return $this->address;
    }    

    /*
     * Возвращает связанный kpp на дату.
     * @param integer $locationStatus
     * @param date $onDate
     * @return string
     */    
    public function getCurrentKpp($locationStatus = LegalLocation::STATUS_ACTIVE, $onDate = null) 
    {
        if (!$onDate){
            $onDate = date('Y-m-d');
        }

        if (!$this->locations->count()){
            return $this->kpp;
        }

        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', (string) $locationStatus))
                ->andWhere(Criteria::expr()->lte('dateStart', $onDate))
                ->orderBy(['dateStart' => Criteria::DESC])
                ->setMaxResults(1)
                ;
        
        $data = $this->locations->matching($criteria);
//        var_dump(count($data)); exit;
        foreach ($data as $row){
            if (!empty($row->getKpp())){
                return $row->getKpp();
            }    
        }

        return $this->kpp;
    }    

    /*
     * Возвращает связанный location.
     * @return array
     */    
    public function getLocations() 
    {
        return $this->locations;
    }    
    
    /**
     * Assigns.
     * @param LegalLocation $location
     */
    public function addLocation($location)
    {
        $this->locations[] = $location;
    }

    public function getPersonalMutuals() {
        return $this->personalMutuals;
    }
    
    public function getPersonalAccruals() {
        return $this->personalAccruals;
    }
    
    public function getPersonalRevises() {
        return $this->personalRevises;
    }
    
    public function getMutuals() {
        return $this->mutuals;
    }    

    public function getCompanyMutuals() {
        return $this->companyMutuals;
    }
    
    /**
     * Массив для формы
     * @return array 
     */
    public function toArray()
    {
        $result = [
            'inn' => $this->getInn(),
            'kpp' => $this->getKpp(),
            'ogrn' => $this->getOgrn(),
            'okpo' => $this->getOkpo(),
            'okato' => $this->getOkato(),
            'oktmo' => $this->getOktmo(),
            'name' => $this->getName(),
            'id' => $this->getId(),
        ];
        
        return $result;
    }        
}
