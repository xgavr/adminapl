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
use Application\Entity\Order;
use Company\Entity\Contract;
use Company\Entity\Office;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;

/**
 * Возврат товаров от покупателя
 * @ORM\Entity(repositoryClass="\Stock\Repository\VtRepository")
 * @ORM\Table(name="vt")
 * @author Daddy
 */
class Vt {
        
     // Vt status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    const STATUS_COMMISSION   = 3; // commission.
    const STATUS_DEFECT       = 4; // брак.
    const STATUS_WAIT         = 5; // ожидает поставки.
   
     // Vt status doc constants.
    const STATUS_DOC_RECD       = 1; // Получено.
    const STATUS_DOC_NOT_RECD  = 2; // Не получено.

     // Vt status doc constants.
    const STATUS_EX_NEW  = 1; // Не отправлено.
    const STATUS_EX_RECD  = 2; // Получено из АПЛ.
    const STATUS_EX_APL  = 3; // Отправлено в АПЛ.

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
     * @ORM\ManyToOne(targetEntity="Application\Entity\Order", inversedBy="vt") 
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $order;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="vt") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;
    
    /**
     * @ORM\OneToMany(targetEntity="Stock\Entity\VtGood", mappedBy="vt") 
     * @ORM\JoinColumn(name="id", referencedColumnName="vt_id")
     */
    private $vtGoods;    
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
       $this->vtGoods = new ArrayCollection();
    }
    
    
    public function getId() 
    {
        return $this->id;
    }

    public function getLogKey() 
    {
        return 'vt:'.$this->id;
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
            self::STATUS_COMMISSION => 'На комиссию',
            self::STATUS_DEFECT => 'Брак',
            self::STATUS_WAIT => 'Ожидание доставки',
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
    public static function getComissStatusList() 
    {
        return [
            self::STATUS_ACTIVE => 0,
            self::STATUS_RETIRED => 0,
            self::STATUS_COMMISSION => 1,
            self::STATUS_DEFECT => 1,
            self::STATUS_WAIT => 1,
        ];
    }    
    
    
    /**
     * Returns apl status as string.
     * @return string
     */
    public function getComissStatusAsString()
    {
        $list = self::getComissStatusList();
        if (isset($list[$this->status]))
            return $list[$this->status];
        
        return 0;
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
            self::STATUS_DEFECT => 1,
            self::STATUS_WAIT => 1,
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
     * Sets  order.
     * @param Order $order     
     */
    public function setOrder($order) 
    {
        $this->order = $order;
        $order->addVt($this);
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
     * Sets  office.
     * @param Office $office     
     */
    public function setOffice($office) 
    {
        $this->office = $office;
//        $office->addVt($this);
    }    

    /**
     * Returns the order.
     * @return Order     
     */
    public function getOrder() 
    {
        return $this->order;
    }
    
    public function addVtGood($vtGood)
    {
        $this->vtGoods[] = $vtGood;
    }    
    
    public function getVtGoods()
    {
        return $this->vtGoods;
    }    
    
    /**
     * Массив для формы
     * @return array 
     */
    public function goodsToArray()
    {
        $result = [];
        foreach ($this->vtGoods as $item){
            $result[] = $item->toArray();
        }    
        
        return $result;
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
            'contact' => $this->getOrder()->getContact()->getId(),
            'docDate' => (string) $this->getDocDate(),
            'docNo' => $this->getDocNo(),
            'info' => $this->getInfo(),
            //'legal' => $this->getOrder()->getLegal()->getId(),
            'office' => $this->getOffice()->getId(),
            'status' => $this->getStatus(),
            'statusDoc' => $this->getStatusDoc(),
            'statusEx' => $this->getStatusEx(),
            'goods' => [],
        ];
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
            'docDate' => $this->getDocDateAtomFormat(),
            'docNo' => $this->getDocNo(),
            'info' => $this->getInfo(),
            'company' => $this->getCompany()->toArray(),
            'office' => $this->getOffice()->toArray(),
            'status' => $this->getStatus(),
            'statusDoc' => $this->getStatusDoc(),
            'statusEx' => $this->getStatusEx(),
            'id' => $this->getId(),
            'order' => $this->getOrder()->toArray(),
            'goods' => $this->goodsToArray(),
        ];
    }                
}
