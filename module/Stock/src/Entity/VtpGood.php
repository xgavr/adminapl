<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Entity;


use Doctrine\ORM\Mapping as ORM;
use Stock\Entity\Vtp;
use Application\Entity\Goods;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;

/**
 * Description of VtpGood
 * @ORM\Entity(repositoryClass="\Stock\Repository\VtpRepository")
 * @ORM\Table(name="vtp_good")
 * @author Daddy
 */
class VtpGood {
        
    const TAKE_OK  = 1;// учтено 
    const TAKE_NO  = 2;// не учтено 
    
     // Ptu status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    const STATUS_COMMISSION    = 3; // commission.
   
     // Ptu status doc constants.
    const STATUS_DOC_RECD       = 1; // Получено.
    const STATUS_DOC_NOT_RECD  = 2; // Не получено.

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="row_no")   
     */
    protected $rowNo;

    /**
     * @ORM\Column(name="comment")   
     */
    protected $comment;

    /**
     * @ORM\Column(name="info")   
     */
    protected $info;

    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;

    /** 
     * @ORM\Column(name="status_doc")  
     */
    protected $statusDoc;

    /** 
     * @ORM\Column(name="quantity")  
     */
    protected $quantity;

    /** 
     * @ORM\Column(name="amount")  
     */
    protected $amount;

    /**
     * @ORM\Column(name="take")   
     */
    protected $take;

    /**
     * @ORM\ManyToOne(targetEntity="Stock\Entity\Vtp", inversedBy="vtpGoods") 
     * @ORM\JoinColumn(name="vtp_id", referencedColumnName="id")
     */
    private $vtp;
        
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="vtpGoods") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    private $good;
        
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
    
    public function getDocRowKey() 
    {
        return 'vtp_good:'.$this->id;
    }    

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getRowNo() 
    {
        return $this->rowNo;
    }

    public function setRowNo($rowNo) 
    {
        $this->rowNo = $rowNo;
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
     * Sets  quantity.
     * @param float $quantity     
     */
    public function setQuantity($quantity) 
    {
        $this->quantity = $quantity;
    }    
    
    /**
     * Returns the quantity of doc.
     * @return float     
     */
    public function getQuantity() 
    {
        return $this->quantity;
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
     * Returns the amount of good.
     * @return float     
     */
    public function getAmount() 
    {
        return round($this->amount, 2);
    }
    
    /**
     * Returns the amount of good.
     * @return float     
     */
    public function getTotal() 
    {
        return round($this->amount, 2);
    }

    /**
     * Returns take.
     * @return int     
     */
    public function getTake() 
    {
        return $this->take;
    }

    /**
     * Returns possible take as array.
     * @return array
     */
    public static function getTakeList() 
    {
        return [
            self::TAKE_OK => 'Проведено',
            self::TAKE_NO => 'Не проведено',
        ];
    }    
    
    /**
     * Returns take as string.
     * @return string
     */
    public function getTakeAsString()
    {
        $list = self::getTakeList();
        if (isset($list[$this->take]))
            return $list[$this->take];
        
        return 'Unknown';
    }    
        
    /**
     * Sets take.
     * @param int $take     
     */
    public function setTake($take) 
    {
        $this->take = $take;
    }   
    
    /**
     * Returns the price of good.
     * @return float     
     */
    public function getPrice() 
    {
        if ($this->quantity){
            return round($this->amount/$this->quantity, 2);
        }
        
        return 0;
    }
    
    /**
     * Returns the vtp.
     * @return Vtp     
     */
    public function getVtp() 
    {
        return $this->vtp;
    }
    
    /**
     * Sets  good.
     * @param Goods $good     
     */
    public function setGood($good) 
    {
        $this->good = $good;
    }    
    
    /**
     * Returns the good.
     * @return Goods     
     */
    public function getGood() 
    {
        return $this->good;
    }
        
    /**
     * Лог
     * @return array
     */
    public function toLog()
    {
        return [
            'amount' => $this->getAmount(),
            'comment' => $this->getComment(),
            'good' => $this->getGood()->getId(),
            'code' => $this->getGood()->getCode(),
            'info' => $this->getInfo(),
            'quantity' => $this->getQuantity(),
            'rowNo' => $this->getRowNo(),
            'status' => $this->getStatus(),
            'statusDoc' => $this->getStatusDoc(),
        ];
    }    
    
    /**
     * Массив для формы
     * @return array 
     */
    public function toArray()
    {
        $result = [
            'amount' => $this->getAmount(),
            'price' => $this->getPrice(),
            'comment' => $this->getComment(),
            'good' => $this->getGood()->toArray(),
            'info' => $this->getInfo(),
            'quantity' => $this->getQuantity(),
            'rowNo' => $this->getRowNo(),
            'status' => $this->getStatus(),
            'statusDoc' => $this->getStatusDoc(),
        ];
        
        return $result;
    }                    
}
