<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Description of Ptu
 * @ORM\Entity(repositoryClass="\Company\Repository\StockRepository")
 * @ORM\Table(name="legal")
 * @author Daddy
 */
class Ptu {
        
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
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;    

    /**
     * @ORM\Column(name="info")   
     */
    protected $info;

    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;

    /** 
     * @ORM\Column(name="number_doc")  
     */
    protected $numberDoc;
        
    /** 
     * @ORM\Column(name="date_doc")  
     */
    protected $dateDoc;
        
    /**
    * @ORM\OneToMany(targetEntity="Company\Entity\BankAccount", mappedBy="legal")
    * @ORM\JoinColumn(name="id", referencedColumnName="legal_id")
     */
    private $legal;
    
    /**
    * @ORM\OneToMany(targetEntity="Company\Entity\Contract", mappedBy="legal")
    * @ORM\JoinColumn(name="id", referencedColumnName="legal_id")
     */
    private $contract;
    

    /**
     * Constructor.
     */
    public function __construct() 
    {
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

    public function getOgrn() 
    {
        return $this->ogrn;
    }

    public function setOgrn($ogrn) 
    {
        $this->ogrn = $ogrn;
    }     
    
    public function getOkpo() 
    {
        return $this->okpo;
    }

    public function setOkpo($okpo) 
    {
        $this->okpo = $okpo;
    }     
    
    public function getHead() 
    {
        return $this->head;
    }

    public function setHead($head) 
    {
        $this->head = $head;
    }     
    
    public function getChiefAccount() 
    {
        return $this->chiefAccount;
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

    public function getAddress() 
    {
        return $this->address;
    }

    public function setAddress($address) 
    {
        $this->address = $address;
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
     * Assigns.
     */
    public function addContract($contract)
    {
        $this->contracts[] = $contract;
    }    
    
    /*
     * Возвращает связанный contact.
     * @return \Application\Entity\Contact
     */
    
    public function getContacts() 
    {
        return $this->contacts;
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
}
