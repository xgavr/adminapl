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
use Stock\Entity\Ptu;
use Company\Entity\Office;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;

/**
 * Description of Vtp
 * @ORM\Entity(repositoryClass="\Stock\Repository\VtpRepository")
 * @ORM\Table(name="vtp")
 * @author Daddy
 */
class Vtp {
        
     // Vtp status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    //const STATUS_COMMISSION    = 3; // commission.
   
     // Vtp status doc constants.
    const STATUS_DOC_RECD       = 1; // Отправлен.
    const STATUS_DOC_NEW       = 3; // Новый.
    const STATUS_DOC_NOT_RECD  = 2; // Принят поставщиком.

     // Vtp status doc constants.
    const STATUS_EX_NEW  = 1; // Не отправлено.
    const STATUS_EX_RECD  = 2; // Получено из АПЛ.
    const STATUS_EX_APL  = 3; // Отправлено в АПЛ.
    
    const PRINT_FOLDER          = './data/template/vtp'; 
    const TEMPLATE_TORG2        = './data/template/torg-2.xls';
    const TEMPLATE_TORG12        = './data/template/torg12.xls';
    const TEMPLATE_UPD        = './data/template/upd3.xls';

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
     * @ORM\Column(name="cause")   
     */
    protected $cause;

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
     * @ORM\ManyToOne(targetEntity="Stock\Entity\Ptu", inversedBy="vtp") 
     * @ORM\JoinColumn(name="ptu_id", referencedColumnName="id")
     */
    private $ptu;
    
    /**
     * @ORM\OneToMany(targetEntity="Stock\Entity\VtpGood", mappedBy="vtp") 
     * @ORM\JoinColumn(name="id", referencedColumnName="vtp_id")
     */
    private $vtpGoods;    
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
       $this->vtpGoods = new ArrayCollection();
    }
    
    
    public function getId() 
    {
        return $this->id;
    }

    /**
     * Returns the namefile.
     * @param string $docName
     * @return string     
     */
    public function getPrintName($ext, $docName = 'ТОРГ2') 
    {
        return self::PRINT_FOLDER.'/'.$this->getDocPresent($docName).'.'.strtolower($ext);
    }

    /**
     * Returns the present of doc.
     * @param string $docName
     * @return string     
     */
    public function getDocPresent($docName = 'ТОРГ2') 
    {
        $docDate = date('d-m-Y', strtotime($this->docDate));
        $docNo = ($this->aplId) ? $this->aplId:$this->id;
        return "$docName №{$docNo} от {$docDate}";
    }
    
    public function getLogKey() 
    {
        return 'vtp:'.$this->id;
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

    public function getCause() 
    {
        return $this->cause;
    }

    public function setCause($cause) 
    {
        $this->cause = $cause;
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
//        $this->info = $this->setJsonInfo($info);
        $this->info = $info;
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
            //self::STATUS_COMMISSION => 'На комиссии',
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
     * Returns possible apl statuses as array.
     * @return array
     */
    public static function getAplStatusList() 
    {
        return [
            self::STATUS_ACTIVE => 1,
            self::STATUS_RETIRED => 0,
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
     * @retrun integer
     */
    public function getAplStatus()
    {
        if ($this->status == self::STATUS_ACTIVE && $this->statusDoc == self::STATUS_DOC_NOT_RECD){
            return 1;
        }
        return 0;
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
            self::STATUS_DOC_NEW => 'Новый',
            self::STATUS_DOC_RECD => 'Отправлен',
            self::STATUS_DOC_NOT_RECD => 'Принят'
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
        if (!empty($this->aplId)){
            return $this->aplId;
        }
        return $this->id;
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
     * Returns the total of doc.
     * @return float     
     */
    public function getTotal() 
    {
        return $this->amount;
    }

    /**
     * Sets  ptu.
     * @param Ptu $ptu     
     */
    public function setPtu($ptu) 
    {
        $this->ptu = $ptu;
        $ptu->addVtp($this);
    }    

    /**
     * Returns the ptu.
     * @return Ptu     
     */
    public function getPtu() 
    {
        return $this->ptu;
    }
    
    public function addVtpGoods($vtpGood)
    {
        $this->vtpGoods[] = $vtpGood;
    }    

    public function getVtpGoods()
    {
        return $this->vtpGoods;
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
            'cause' => $this->getCause(),
            'contract' => $this->getPtu()->getContract()->getId(),
            'docDate' => (string) $this->getDocDate(),
            'docNo' => $this->getDocNo(),
            'info' => $this->getInfo(),
            'legal' => $this->getPtu()->getLegal()->getId(),
            'office' => $this->getPtu()->getOffice()->getId(),
            'status' => $this->getStatus(),
            'statusDoc' => $this->getStatusDoc(),
            'statusEx' => $this->getStatusEx(),
            'statusAccount' => $this->getStatusAccount(),
            'goods' => [],
        ];
    }
}
