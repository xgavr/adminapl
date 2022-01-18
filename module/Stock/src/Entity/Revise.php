<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Entity;

use Doctrine\ORM\Mapping as ORM;
use Company\Entity\Legal;
use Company\Entity\Office;
use Application\Entity\Contact;
use User\Entity\User;
use Laminas\Json\Encoder;


/**
 * Description of Comiss
 * @ORM\Entity(repositoryClass="\Stock\Repository\ReviseRepository")
 * @ORM\Table(name="revise")
 * @author Daddy
 */
class Revise {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    const STATUS_DOC_RECD       = 1; // Получено.
    const STATUS_DOC_NOT_RECD  = 2; // Не получено.

     // Ptu status doc constants.
    const STATUS_EX_NEW  = 1; // Не отправлено.
    const STATUS_EX_RECD  = 2; // Получено из АПЛ.
    const STATUS_EX_APL  = 3; // Отправлено в АПЛ.
    
    const KIND_REVISE_SUPPLIER       = 1; // Корректировка поставщика.
    const KIND_REVISE_CLIENT         = 2; // Корректировка клиента.

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
     * @ORM\Column(name="comment")   
     */
    protected $comment;

    /**
     * @ORM\Column(name="info")   
     */
    protected $info;

    /**
     * @ORM\Column(name="apl_id")   
     */
    protected $aplId;

    /** 
     * @ORM\Column(name="kind")  
     */
    protected $kind;

    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;

    /** 
     * @ORM\Column(name="status_doc")  
     */
    protected $statusDoc;

    /** 
     * @ORM\Column(name="status_ex")  
     */
    protected $statusEx;

    /** 
     * @ORM\Column(name="doc_no")  
     */
    protected $docNo;
        
    /** 
     * @ORM\Column(name="doc_date")  
     */
    protected $docDate;
        
    /** 
     * @ORM\Column(name="amount")  
     */
    protected $amount;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="revises") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="revises") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;    

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="revises") 
     * @ORM\JoinColumn(name="legal_id", referencedColumnName="id")
     */
    private $legal;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Contract", inversedBy="revises") 
     * @ORM\JoinColumn(name="contract_id", referencedColumnName="id")
     */
    private $contract;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Contact", inversedBy="revises") 
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    private $contact;
    
    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="revises") 
     * @ORM\JoinColumn(name="user_creator_id", referencedColumnName="id")
     */
    private $userCreator;        
    
    public function __construct() {
    }
   
    public function getId() 
    {
        return $this->id;
    }

    public function getLogKey() 
    {
        return 'rvs:'.$this->id;
    }

    
    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getAplId() 
    {
        return $this->aplId;
    }

    public function setAplId($aplId) 
    {
        $this->aplId = $aplId;
    }     

    public function getComment() 
    {
        return $this->comment;
    }

    public function setComment($comment) 
    {
        $this->comment = $comment;
    }     

    public function getInfo() 
    {
        return $this->info;
    }

    public static function getInfoAsArray()
    {
        try{
            return Decoder::decode($this->info, \Laminas\Json\Json::TYPE_ARRAY);            
        } catch (Exception $ex) {
            return [];
        }
    }

    public static function jsonInfo($info)
    {
        return Encoder::encode($info);
    }
    
    public function setInfo($info) 
    {
        $this->info = $this->jsonInfo($info);
    }     

    
    /**
     * Sets  amount.
     * @param float $amount     
     */
    public function setAmount($amount) 
    {
        $this->amount = $amount;
    }    
    
    /**
     * Returns the amount of doc.
     * @return float     
     */
    public function getAmount() 
    {
        return $this->amount;
    }
    
    /**
     * Returns kind.
     * @return int     
     */
    public function getKind() 
    {
        return $this->kind;
    }

    /**
     * Returns possible kind as array.
     * @return array
     */
    public static function getKindList() 
    {
        return [
            self::KIND_REVISE_SUPPLIER => 'Корректировка поставщика',
            self::KIND_REVISE_CLIENT => 'Корректировка клиента',
        ];
    }    
    
    /**
     * Returns revise kind as string.
     * @return string
     */
    public function getKindsAsString()
    {
        $list = self::getKindList();
        if (isset($list[$this->kind]))
            return $list[$this->kind];
        
        return 'Unknown';
    }    
    
    /**
     * Sets kind.
     * @param int $kind     
     */
    public function setKind($kind) 
    {
        $this->kind = $kind;
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
            self::STATUS_ACTIVE => 'Активный',
            self::STATUS_RETIRED => 'Удален',
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
    
    /**
     * Sets status.
     * @param int $status     
     */
    public function setStatus($status) 
    {
        $this->status = $status;
    }   
    
    /**
     * Returns status.
     * @return int     
     */
    public function getStatusDoc() 
    {
        return $this->statusDoc;
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusDocList() 
    {
        return [
            self::STATUS_DOC_RECD => 'Получено',
            self::STATUS_DOC_NOT_RECD => 'Не получено'
        ];
    }    
    
    /**
     * Returns user status as string.
     * @return string
     */
    public function getStatusDocAsString()
    {
        $list = self::getStatusList();
        if (isset($list[$this->statusDoc]))
            return $list[$this->statusDoc];
        
        return 'Unknown';
    }    
    
    /**
     * Sets status.
     * @param int $statusDoc     
     */
    public function setStatusDoc($statusDoc) 
    {
        $this->statusDoc = $statusDoc;
    }   

    /**
     * Returns status.
     * @return int     
     */
    public function getStatusEx() 
    {
        return $this->statusEx;
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusExList() 
    {
        return [
            self::STATUS_EX_NEW => 'Новый',
            self::STATUS_EX_APL => 'Отправлен в АПЛ',
            self::STATUS_EX_RECD => 'Получен из АПЛ',
        ];
    }    
    
    /**
     * Returns user status as string.
     * @return string
     */
    public function getStatusExAsString()
    {
        $list = self::getStatusList();
        if (isset($list[$this->statusEx]))
            return $list[$this->statusEx];
        
        return 'Unknown';
    }    
    
    /**
     * Sets status.
     * @param int $statusEx     
     */
    public function setStatusEx($statusEx) 
    {
        $this->statusEx = $statusEx;
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
     * Returns the date of doc.
     * @return string     
     */
    public function getDocDate() 
    {
        return $this->docDate;
    }
    
    /**
     * Sets the date when doc.
     * @param date $docDate     
     */
    public function setDocDate($docDate) 
    {
        $this->docDate = $docDate;
    }    
        
    /**
     * Returns the number of doc.
     * @return string     
     */
    public function getDocNo() 
    {
        return $this->docNo;
    }
    
    /**
     * Sets the number when doc.
     * @param string $docNo     
     */
    public function setDocNo($docNo) 
    {
        $this->docNo = $docNo;
    }    
    
    /**
     * 
     * @param Legal $legal
     */
    public function setLegal($legal)
    {
        $this->legal = $legal;
    }
    
    /**
     * Returns the legal.
     * @return Legal     
     */
    public function getLegal() 
    {
        return $this->legal;
    }
    
    public function getDefaultSupplier()
    {
        if ($this->legal){
            $contacts = $this->legal->getContacts();
            foreach ($contacts as $contact){
                if ($contact->getSupplier()){
                    return $contact->getSupplier();
                }
            }
        }
        
        return;
    }    
    
    public function getDefaultSupplierId()
    {
        $supplier = $this->getDefaultSupplier();
        if ($supplier){
            return $supplier->getId();
        }
        return;
    }
    
    /**
     * Returns the supplier.
     * @return Supplier     
     */
    public function getSupplier() 
    {
        $legal = $this->legal;
        if ($legal){
            $contacts = $legal->getContacts();
            foreach ($contacts as $contact){
                $supplier = $contact->getSupplier();
                if ($supplier){
                    return $supplier;
                }
            }
        }    
        return;        
    }
        
    /**
     * 
     * @param Contract $contract
     */
    public function setContract($contract)
    {
        $this->contract = $contract;
    }
    
    /**
     * Returns the contract.
     * @return Contract     
     */
    public function getContract() 
    {
        return $this->contract;
    }
    
    
    /**
     * 
     * @param Office $office
     */
    public function setOffice($office)
    {
        $this->office = $office;
    }

    /**
     * Returns the office.
     * @return Office     
     */
    public function getOffice() 
    {
        return $this->office;
    }
    /*
     * @return Legal
     */    
    public function getCompany() 
    {
        return $this->company;
    }

    /**
     * @param Legal $company
     */    
    public function setCompany($company) 
    {
        $this->company = $company;
    }                         

    /*
     * @return Contact
     */    
    public function getContact() 
    {
        return $this->contact;
    }

    /**
     * @param Contact $contact
     */    
    public function setContact($contact) 
    {
        $this->contact = $contact;
    }    
    
    public function getUserCreator()
    {
        return $this->userCreator;
    }
    
    /**
     * Add userCreator
     * @param User $userCreator
     */
    public function setUserCreator($userCreator)
    {
        $this->userCreator = $userCreator;
    }    
    
    /**
     * Массив для формы
     * @return array 
     */
    public function toArray()
    {
        $result = [
            'amount' => $this->amount,
            'aplId' => $this->aplId,
            'comment' => $this->comment,
            'company' => $this->company->getId(),
            'contact' => ($this->contact) ? $this->contact->getId():NULL,            
            'contract' => ($this->contract) ? $this->contract->getId():NULL,            
            'kind' => $this->kind,
            'phone' => ($this->contact) ? ($this->contact->getPhone()) ? $this->contact->getPhone()->getName():NULL:NULL,            
            'docDate' => date('Y-m-d', strtotime($this->docDate)),
            'info' => $this->info,
            'legal' => ($this->legal) ? $this->legal->getId():null,
            'office' => $this->office->getId(),
            'supplier' => $this->getDefaultSupplierId(),
            'status' => $this->status,
        ];
        
        return $result;
    }    
        
    /**
     * Лог
     * @return array
     */
    public function toLog()
    {
        return [
            'amount' => $this->amount,
            'aplId' => $this->aplId,
            'comment' => $this->comment,
            'company' => $this->company->getId(),
            'contact' => ($this->contact) ? $this->contact->getId():NULL,            
            'contract' => ($this->contract) ? $this->contract->getId():NULL, 
            'kind' => $this->kind,
            'docDate' => $this->docDate,
            'phone' => ($this->contact) ? ($this->contact->getPhone()) ? $this->contact->getPhone()->getName():NULL:NULL,            
            'info' => $this->info,
            'legal' => ($this->legal) ? $this->legal->getId():null,
            'office' => $this->office->getId(),
            'supplier' => $this->getDefaultSupplierId(),
            'status' => $this->status,
        ];
    }        
}
