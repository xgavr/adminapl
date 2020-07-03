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

/**
 * Description of Ptu
 * @ORM\Entity(repositoryClass="\Stock\Repository\StockRepository")
 * @ORM\Table(name="ptu_good")
 * @author Daddy
 */
class PtuGood {
        
     // Ptu status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
   
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
     * @ORM\ManyToOne(targetEntity="Stock\Entity\Ptu", inversedBy="ptuGoods") 
     * @ORM\JoinColumn(name="ptu_id", referencedColumnName="id")
     */
    private $ptu;
        
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="ptuGoods") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    private $good;
        
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Country", inversedBy="ptuGoods") 
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    private $country;

    /**
     * @ORM\ManyToOne(targetEntity="Stock\Entity\Unit", inversedBy="ptuGoods") 
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id")
     */
    private $unit;

    /**
     * @ORM\ManyToOne(targetEntity="Stock\Entity\Ntd", inversedBy="ptuGoods") 
     * @ORM\JoinColumn(name="ntd_id", referencedColumnName="id")
     */
    private $ntd;

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
            self::STATUS_RETIRED => 'Удален'
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
    public function getDateDoc() 
    {
        return $this->dateDoc;
    }
    
    /**
     * Sets the date when doc.
     * @param string $dateStart     
     */
    public function setDateDoc($dateDoc) 
    {
        $this->dateDoc = $dateDoc;
    }    
        
    /**
     * Returns the number of doc.
     * @return string     
     */
    public function getNumberDoc() 
    {
        return $this->numberDoc;
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
     * Returns the quantity of doc.
     * @return float     
     */
    public function getQuantity() 
    {
        return $this->quantity;
    }
    
    /**
     * Sets the number when doc.
     * @param string $numberDoc     
     */
    public function setNumberDoc($numberDoc) 
    {
        $this->numberDoc = $numberDoc;
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
     * Returns the contract.
     * @return Contract     
     */
    public function getContract() 
    {
        return $this->contract;
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
     * Returns the country.
     * @return Country     
     */
    public function getCountry() 
    {
        return $this->country;
    }
    
    /**
     * Returns the unit.
     * @return Unit     
     */
    public function getUnit() 
    {
        return $this->unit;
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
     * Returns the ntd.
     * @return Ntd     
     */
    public function getNtd() 
    {
        return $this->ntd;
    }
    
    /**
     * Returns the ptu.
     * @return Ptu     
     */
    public function getPtu() 
    {
        return $this->ptu;
    }
    
}
