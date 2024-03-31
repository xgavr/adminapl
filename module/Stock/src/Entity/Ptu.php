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
use Company\Entity\Legal;
use Company\Entity\Contract;
use Company\Entity\Office;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;
use Application\Entity\Idoc;
use Application\Entity\Supplier;

/**
 * Description of Ptu
 * @ORM\Entity(repositoryClass="\Stock\Repository\PtuRepository")
 * @ORM\Table(name="ptu")
 * @author Daddy
 */
class Ptu {
        
     // Ptu status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    const STATUS_COMMISSION    = 3; // commission.
   
     // Ptu status doc constants.
    const STATUS_DOC_RECD       = 1; // Получено.
    const STATUS_DOC_NOT_RECD  = 2; // Не получено.

     // Ptu status doc constants.
    const STATUS_EX_NEW  = 1; // Не отправлено.
    const STATUS_EX_RECD  = 2; // Получено из АПЛ.
    const STATUS_EX_APL  = 3; // Отправлено в АПЛ.
    const STATUS_EX_UPL  = 4; // Загружается.

    const STATUS_ACCOUNT_OK  = 1;// обновлено 
    const STATUS_ACCOUNT_NO  = 2;// не обновлено
    const STATUS_TAKE_NO  = 3;// не проведено
    
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
     * @ORM\Column(name="status_account")  
     */
    protected $statusAccount;

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
     * @ORM\ManyToOne(targetEntity="Application\Entity\Supplier", inversedBy="ptu") 
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    private $supplier;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="ptu") 
     * @ORM\JoinColumn(name="legal_id", referencedColumnName="id")
     */
    private $legal;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Contract", inversedBy="ptu") 
     * @ORM\JoinColumn(name="contract_id", referencedColumnName="id")
     */
    private $contract;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="ptu") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;
    
    /**
     * @ORM\OneToMany(targetEntity="Stock\Entity\PtuGood", mappedBy="ptu") 
     * @ORM\JoinColumn(name="id", referencedColumnName="ptu_id")
     */
    private $ptuGoods;
    
    /**
     * @ORM\OneToMany(targetEntity="Stock\Entity\PtuCost", mappedBy="ptu") 
     * @ORM\JoinColumn(name="id", referencedColumnName="ptu_id")
     */
    private $ptuCosts;
    
    /**
     * @ORM\OneToMany(targetEntity="Stock\Entity\Vtp", mappedBy="ptu") 
     * @ORM\JoinColumn(name="id", referencedColumnName="ptu_id")
     */
    private $vtp;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Idoc", inversedBy="ptu") 
     * @ORM\JoinColumn(name="idoc_id", referencedColumnName="id")
     */
    private $idoc;
    
    /**
     * @ORM\OneToMany(targetEntity="Company\Entity\CostMutual", mappedBy="ptu") 
     * @ORM\JoinColumn(name="id", referencedColumnName="doc_id")
     */
    private $costMutuals;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
       $this->vtp = new ArrayCollection();
       $this->ptuGoods = new ArrayCollection();
       $this->ptuCosts = new ArrayCollection();
       $this->costMutuals = new ArrayCollection();
    }
    
    
    public function getId() 
    {
        return $this->id;
    }

    public function getLogKey() 
    {
        return 'ptu:'.$this->id;
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

    public function getInfoAsArray()
    {
        try{
            if ($this->info){
                return Decoder::decode($this->info, \Laminas\Json\Json::TYPE_ARRAY);            
            }    
        } catch (\Laminas\Json\Exception\RuntimeException $ex) {
        }
        return [];
    }

    public static function setJsonInfo($info)
    {
        return Encoder::encode($info);
    }
    
    public function setInfo($info) 
    {
        $this->info = $this->setJsonInfo($info);
    }     

    /**
     * Получить зависимые данные
     * @return array
     */
    public function dependInfo()
    {
        $result = [
            'vtp' => [],
            'goods' => [],
        ];
        
        foreach ($this->vtp as $vtp){
            $result['vtp'][] = $vtp->toLog();
        }
        
        foreach ($this->ptuGoods as $ptuGood){
            $result['goods'][] = $ptuGood->toLog();
        }
        $info = $this->getInfoAsArray();
        if (!is_array($info)){
            $info = [];
        }
        $info['depend'] = $result;
        
        return $info;
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
            self::STATUS_COMMISSION => 'На комиссии',
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
     * Returns possible apl statuses as array.
     * @return array
     */
    public static function getAplStatusList() 
    {
        return [
            self::STATUS_ACTIVE => 1,
            self::STATUS_RETIRED => 0,
            self::STATUS_COMMISSION => 1,
        ];
    }    
    
    /**
     * Returns apl status as string.
     * @return string
     */
    public function getAplStatusAsString()
    {
        $list = self::getAplStatusList();
        if (isset($list[$this->status]))
            return $list[$this->status];
        
        return 0;
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
            self::STATUS_EX_UPL => 'Загружается',
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
     * Returns statusAccount.
     * @return int     
     */
    public function getStatusAccount() 
    {
        return $this->statusAccount;
    }

    /**
     * Returns possible statusAccount as array.
     * @return array
     */
    public static function getStatusAccountList() 
    {
        return [
            self::STATUS_ACCOUNT_OK => 'Обновлено',
            self::STATUS_ACCOUNT_NO=> 'Не обновлено',
            self::STATUS_TAKE_NO=> 'Не проведено',
        ];
    }    
    
    /**
     * Returns statusAccount as string.
     * @return string
     */
    public function getStatusAccountAsString()
    {
        $list = self::getStatusAccountList();
        if (isset($list[$this->statusAccount]))
            return $list[$this->statusAccount];
        
        return 'Unknown';
    }    
        
    /**
     * Sets statusAccount.
     * @param int $statusAccount     
     */
    public function setStatusAccount($statusAccount) 
    {
        $this->statusAccount = $statusAccount;
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
    
    public function getDocDateAtomFormat() {
        $datetime = new \DateTime($this->docDate);
        return $datetime->format(\DateTime::ATOM);
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
     * Returns the present of doc.
     * @param string $docName
     * @return string     
     */
    public function getDocPresent($docName = 'УПД') 
    {
        $docDate = date('d-m-Y', strtotime($this->docDate));
        return "$docName №{$this->docNo} от {$docDate} г.";
    }
    
    /**
     * Returns the present of doc.
     * @param string $docName
     * @return string     
     */
    public function getDocIdPresent($docName = 'УПД') 
    {
        $docDate = date('d-m-Y', strtotime($this->docDate));
        return "$docName №{$this->docNo}/{$this->id} от {$docDate} г.";
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
        return round($this->amount, 2);
    }
    
    /**
     * Returns the supplier.
     * @return Idoc     
     */
    public function getSupplier() 
    {
        return $this->supplier;
    }

    /**
     * Add supplier
     * @param Supplier $supplier
     */
    public function setSupplier($supplier)
    {
        $this->supplier = $supplier;
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
    
    /**
     * Returns the supplier.
     * @return Supplier     
     */
    public function getContactSupplier() 
    {
        $legal = $this->legal;
        $contacts = $legal->getContacts();
        foreach ($contacts as $contact){
            $supplier = $contact->getSupplier();
            if ($supplier){
                return $supplier;
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
    
    /**
     * @return array 
     */
    public function getPtuGoods()
    {
        return $this->ptuGoods;
    }

    public function addPtuGoods($ptuGood)
    {
        $this->ptuGoods[] = $ptuGood;
    }
    
    /**
     * @return array 
     */
    public function getPtuCosts()
    {
        return $this->ptuCosts;
    }

    public function addPtuCosts($ptuCost)
    {
        $this->ptuCosts[] = $ptuCost;
    }
    
    /**
     * 
     * @return array
     */
    public function getVtp()
    {
        return $this->vtp;
    }
    
    public function addVtp($vtp)
    {
        $this->vtp[] = $vtp;
    }
    
    /**
     * Returns the idoc.
     * @return Idoc     
     */
    public function getIdoc() 
    {
        return $this->idoc;
    }

    /**
     * Add Idoc
     * @param Idoc $idoc
     */
    public function setIdoc($idoc)
    {
        $this->idoc = $idoc;
    }
    
    public function getCostMutuals() {
        return $this->costMutuals;
    }
    
    /**
     * Лог
     * @return array
     */
    public function toLog()
    {
        return [
            'amount' => $this->getAmount(),
            'aplId' => $this->getAplId(),
            'comment' => $this->getComment(),
            'contract' => $this->getContract()->getId(),
            'docDate' => (string) $this->getDocDate(),
            'docNo' => $this->getDocNo(),
            'info' => $this->getInfo(),
            'legal' => $this->getLegal()->getId(),
            'office' => $this->getOffice()->getId(),
            'status' => $this->getStatus(),
            'statusDoc' => $this->getStatusDoc(),
            'statusEx' => $this->getStatusEx(),
            'goods' => [],
            'costs' => [],
        ];
    }
    
    /**
     * Массив для формы
     * @return array 
     */
    public function goodsToArray()
    {
        $result = [];
        foreach ($this->ptuGoods as $item){
            $result[] = $item->toArray();
        }    
        
        return $result;
    }    

    /**
     * Массив для формы
     * @return array 
     */
    public function costsToArray()
    {
        $result = [];
        foreach ($this->ptuCosts as $item){
            $result[] = $item->toArray();
        }    
        
        return $result;
    }    
    
    /**
     * Массив для формы
     * @return array
     */
    public function toArray()
    {
        return [
            'amount' => $this->getAmount(),
            'aplId' => $this->getAplId(),
            'comment' => $this->getComment(),
            'contract' => $this->getContract()->toArray(),
            'docDate' => $this->getDocDateAtomFormat(),
            'docNo' => $this->getDocNo(),
            'info' => $this->getInfo(),
            'supplier' => $this->getSupplier()->toArray(),
            'legal' => $this->getLegal()->toArray(),
            'office' => $this->getOffice()->toArray(),
            'status' => $this->getStatus(),
            'statusDoc' => $this->getStatusDoc(),
            'statusEx' => $this->getStatusEx(),
            'id' => $this->getId(),
            'goods' => $this->goodsToArray(),
            'costs' => $this->costsToArray(),
        ];
    }
}
