<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;
use Company\Entity\Office;
use Company\Entity\Legal;
use Company\Entity\Cost;

/**
 * Description of St
 * @ORM\Entity(repositoryClass="\Stock\Repository\StRepository")
 * @ORM\Table(name="st")
 * @author Daddy
 */
class St {
        
     // St status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
   
     // Ptu status doc constants.
    const STATUS_DOC_RECD       = 1; // Получено.
    const STATUS_DOC_NOT_RECD  = 2; // Не получено.

     // Ptu status doc constants.
    const STATUS_EX_NEW  = 1; // Не отправлено.
    const STATUS_EX_RECD  = 2; // Получено из АПЛ.
    const STATUS_EX_APL  = 3; // Отправлено в АПЛ.

      // St write constants.
    const WRITE_COST       = 1; // Списать в зп.
    const WRITE_PAY        = 2; // Списать на расходы.
    
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
     * @ORM\Column(name="write_off")  
     */
    protected $writeOff;

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
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="st") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="st") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;    
    
    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="st") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;        

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Cost", inversedBy="st") 
     * @ORM\JoinColumn(name="cost_id", referencedColumnName="id")
     */
    private $cost;        

    /**
     * @ORM\OneToMany(targetEntity="Stock\Entity\StGood", mappedBy="st") 
     * @ORM\JoinColumn(name="id", referencedColumnName="st_id")
     */
    private $stGoods;    
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
       $this->otGoods = new ArrayCollection();
    }
    
    
    public function getId() 
    {
        return $this->id;
    }

    public function getLogKey() 
    {
        return 'st:'.$this->id;
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

    public static function setJsonInfo($info)
    {
        return Encoder::encode($info);
    }
    
    public function setInfo($info) 
    {
        $this->info = $this->setJsonInfo($info);
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
     * Returns writeOff.
     * @return int     
     */
    public function getWriteOff() 
    {
        return $this->writeOff;
    }

    /**
     * Returns possible writeOff as array.
     * @return array
     */
    public static function getWriteOffList() 
    {
        return [
            self::WRITE_COST => 'Списать на расходы',
            self::WRITE_PAY => 'Списать в зп'
        ];
    }    
    
    /**
     * Returns writeOff as string.
     * @return string
     */
    public function getWriteOffAsString()
    {
        $list = self::getWriteOffList();
        if (isset($list[$this->writeOff]))
            return $list[$this->writeOff];
        
        return 'Unknown';
    }    
    
    /**
     * Sets writeOff.
     * @param int $writeOff     
     */
    public function setWriteOff($writeOff) 
    {
        $this->writeOff = $writeOff;
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
     * Sets office.
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
     * Sets company.
     * @param Legal $company    
     */
    public function setCompany($company) 
    {
        $this->company = $company;
    }    

    /**
     * Returns the company.
     * @return Legal     
     */
    public function getCompany() 
    {
        return $this->company;
    }

    /**
     * Sets comiss.
     * @param User $user    
     */
    public function setUser($user) 
    {
        $this->user = $user;
    }    

    /**
     * Returns the user.
     * @return User     
     */
    public function getUser() 
    {
        return $this->user;
    }

    /**
     * Sets cost.
     * @param Cost $cost    
     */
    public function setCost($cost) 
    {
        $this->cost = $cost;
    }    

    /**
     * Returns the cost.
     * @return Cost     
     */
    public function getCost() 
    {
        return $this->cost;
    }

    public function addStGoods($stGood)
    {
        $this->stGoods[] = $stGood;
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
            'docDate' => (string) $this->getDocDate(),
            'docNo' => $this->getDocNo(),
            'info' => $this->getInfo(),
            'legal' => $this->getCompany()->getId(),
            'office' => $this->getOffice()->getId(),
            'status' => $this->getStatus(),
            'statusDoc' => $this->getStatusDoc(),
            'statusEx' => $this->getStatusEx(),
            'goods' => [],
        ];
    }
}
