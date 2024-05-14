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
use Application\Entity\Goods;
use Stock\Entity\Pt;
use Stock\Entity\Vt;
use Stock\Entity\St;
use Stock\Entity\Ptu;
use Stock\Entity\Ot;
use Application\Entity\Order;
use User\Entity\User;


/**
 * Description of Movement
 * @ORM\Entity(repositoryClass="\Stock\Repository\MovementRepository")
 * @ORM\Table(name="movement")
 * @author Daddy
 */
class Movement {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    const STATUS_COMMISSION    = 3; // commission.
    const STATUS_COMITENT    = 4; // comitent.
    
    const DOC_PTU = 1;
    const DOC_OT = 2;
    const DOC_PT = 3;
    const DOC_VT = 4;
    const DOC_ORDER = 5;
    const DOC_ST = 6;
    const DOC_VTP = 7;
    const DOC_REVISE = 8;
    const DOC_CASH = 9;
    const DOC_MSR = 10; //MarketSaleReport
    const DOC_ZP = 11; // zp calculator
    const DOC_BANK = 12; // выписка
    const DOC_ZPRV = 13; // корректировка зп
    const DOC_ORDER_USER = 14; //заказ в подотчет
    const DOC_VT_USER = 15; //возврат в подотчет
    const DOC_CASH_USER = 16; //оплата/возврат заказа в подотчет
    const DOC_ORDER_COMISS = 17; //заказ списания комиссионного товар
        
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="doc_key")   
     */
    protected $docKey;
    
    /**
     * @ORM\Column(name="doc_type")   
     */
    protected $docType;
    
    /**
     * @ORM\Column(name="doc_id")   
     */
    protected $docId;
    
    /**
     * @ORM\Column(name="doc_stamp")   
     */
    protected $docStamp;
    
    /**
     * @ORM\Column(name="base_key")   
     */
    protected $baseKey;

    /**
     * @ORM\Column(name="base_type")   
     */
    protected $baseType;
    
    /**
     * @ORM\Column(name="base_id")   
     */
    protected $baseId;
    
    /**
     * @ORM\Column(name="doc_row_key")   
     */
    protected $docRowKey;
    
    /**
     * @ORM\Column(name="doc_row_no")   
     */
    protected $docRowNo;

    /** 
     * @ORM\Column(name="date_oper")  
     */
    protected $dateOper;
    
    /**
     * @ORM\Column(name="status")   
     */
    protected $status;

    /** 
     * @ORM\Column(name="quantity")  
     */
    protected $quantity;

    /** 
     * @ORM\Column(name="amount")  
     */
    protected $amount;

    /** 
     * @ORM\Column(name="base_amount")  
     */
    protected $baseAmount;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="movements") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    private $good;
            
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="movements") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="movements") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Stock\Entity\Ptu", inversedBy="movements") 
     * @ORM\JoinColumn(name="base_id", referencedColumnName="id")
     */
    private $ptu;    

    /**
     * @ORM\ManyToOne(targetEntity="Stock\Entity\Ot", inversedBy="movements") 
     * @ORM\JoinColumn(name="base_id", referencedColumnName="id")
     */
    private $ot;    

    /**
     * @ORM\ManyToOne(targetEntity="Stock\Entity\Vt", inversedBy="movements") 
     * @ORM\JoinColumn(name="base_id", referencedColumnName="id")
     */
    private $vt;    

    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="movements") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;    

    public function __construct() {
    }
   
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getDocKey() 
    {
        return $this->docKey;
    }

    public function setDocKey($docKey) 
    {
        $this->docKey = $docKey;
    }     

    public function getDocType() 
    {
        return $this->docType;
    }

    public function setDocType($docType) 
    {
        $this->docType = $docType;
    }     

    public function getDocId() 
    {
        return $this->docId;
    }

    public function setDocId($docId) 
    {
        $this->docId = $docId;
    }     

    public function getDocStamp() 
    {
        return $this->docStamp;
    }

    public function setDocStamp($docStamp) 
    {
        $this->docStamp = $docStamp;
    }     

    public function getBaseKey() 
    {
        return $this->baseKey;
    }

    public function setBaseKey($baseKey) 
    {
        $this->baseKey = $baseKey;
    }     
    
    public function getBaseType() 
    {
        return $this->baseType;
    }

    public function setBaseType($baseType) 
    {
        $this->baseType = $baseType;
    }     

    public function getBaseId() 
    {
        return $this->baseId;
    }

    public function setBaseId($baseId) 
    {
        $this->baseId = $baseId;
    }     

    public function getDocRowKey() 
    {
        return $this->docRowKey;
    }

    public function setDocRowKey($docRowKey) 
    {
        $this->docRowKey = $docRowKey;
    }     

    public function getDocRowNo() 
    {
        return $this->docRowNo;
    }

    public function setDocRowNo($docRowNo) 
    {
        $this->docRowNo = $docRowNo;
    }     

    /**
     * Returns the date of operation.
     * @return string     
     */
    public function getDateOper() 
    {
        return $this->dateOper;
    }
    
    /**
     * Sets the date when this oper was created.
     * @param string $dateOper     
     */
    public function setDateOper($dateOper) 
    {
        $this->dateOper = $dateOper;
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
     * Sets  baseAmount.
     * @param float $baseAmount     
     */
    public function setBaseAmount($baseAmount) 
    {
        $this->baseAmount = $baseAmount;
    }    
    
    /**
     * Returns the baseAmount of doc.
     * @return float     
     */
    public function getBaseAmount() 
    {
        return $this->baseAmount;
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
     * Returns possible doc as array.
     * @return array
     */
    public static function getDocList() 
    {
        return [
            self::DOC_ORDER => 'Заказы',
            self::DOC_VTP => 'Возвраты поставщикам',
            self::DOC_ST => 'Списания',
            self::DOC_VT => 'Возвраты покупателей',
            self::DOC_OT => 'Оприходования',
            self::DOC_PT => 'Перемещения',
            self::DOC_PTU => 'Поступления',
            self::DOC_REVISE => 'Корректировки',
            self::DOC_MSR => 'Отчет комитента',
            self::DOC_CASH => 'Оплата',
            self::DOC_ZP => 'Расчет ЗП',
            self::DOC_BANK => 'Выписка',
            self::DOC_ZPRV => 'Корректировка ЗП',
        ];
    }        

    /**
     * Returns possible doc as array.
     * @return array
     */
    public static function getReviseDocList() 
    {
        return [
            self::DOC_ORDER => 'Продажа',
            self::DOC_VTP => 'Возврат',
            self::DOC_ST => 'Списание',
            self::DOC_VT => 'Возврат',
            self::DOC_OT => 'Оприходование',
            self::DOC_PT => 'Перемещение',
            self::DOC_PTU => 'Поступление',
            self::DOC_REVISE => 'Корректировка',
            self::DOC_MSR => 'Отчет комитента',
            self::DOC_CASH => 'Оплата',
            self::DOC_ZP => 'Расчет ЗП',
            self::DOC_BANK => 'Выписка',
            self::DOC_ZPRV => 'Корректировка ЗП',
        ];
    }        

    /**
     * Returns possible doc as array.
     * @return array
     */
    public static function getKeyDocList() 
    {
        return [
            self::DOC_ORDER => 'ord',
            self::DOC_VTP => 'vtp',
            self::DOC_ST => 'st',
            self::DOC_VT => 'vt',
            self::DOC_OT => 'ot',
            self::DOC_PT => 'pt',
            self::DOC_PTU => 'ptu',
            self::DOC_REVISE => 'rvs',
            self::DOC_MSR => 'msr',
            self::DOC_ZP => 'zpdc',
            self::DOC_BANK => 'bank',
            self::DOC_ZPRV => 'zprv',
            self::DOC_CASH => 'cash',
        ];
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
     * Returns possible order status.
     * @param Order $order
     * @return integer
     */
    public static function getStatusFromOrder($order) 
    {
        switch ($order->getStatus()){
            case Order::STATUS_SHIPPED: 
                if ($order->isComitentContract()){
                    return self::STATUS_COMITENT;
                }
                return self::STATUS_ACTIVE;
            default: return self::STATUS_RETIRED;    
        }
    }    

    /**
     * Returns possible pt status.
     * @param Pt $pt
     * @return integer
     */
    public static function getStatusFromPt($pt) 
    {
        switch ($pt->getStatus()){
            case Pt::STATUS_RETIRED: return self::STATUS_RETIRED;
            default: return self::STATUS_ACTIVE;    
        }
    }    

    /**
     * Returns possible vt status.
     * @param Vt $vt
     * @return integer
     */
    public static function getStatusFromVt($vt) 
    {
        switch ($vt->getStatus()){
            case Vt::STATUS_RETIRED: return self::STATUS_RETIRED;
            case Vt::STATUS_COMMISSION: return self::STATUS_COMMISSION;    
            case Vt::STATUS_DEFECT: return self::STATUS_COMMISSION;    
            case Vt::STATUS_WAIT: return self::STATUS_COMMISSION;    
            default: 
                if ($vt->getOrder()->isComitentContract()){
                    return self::STATUS_COMITENT;
                }
                return self::STATUS_ACTIVE;    
        }
    }    

    /**
     * Returns possible ot status.
     * @param Ot $ot
     * @return integer
     */
    public static function getStatusFromOt($ot) 
    {
        switch ($ot->getStatus()){
            case Ot::STATUS_RETIRED: return self::STATUS_RETIRED;
            case Ot::STATUS_COMMISSION: return self::STATUS_COMMISSION;    
            default: return self::STATUS_ACTIVE;    
        }
    }    

    /**
     * Returns possible st status.
     * @param St $st
     * @return integer
     */
    public static function getStatusFromSt($st) 
    {
        switch ($st->getStatus()){
            case St::STATUS_RETIRED: return self::STATUS_RETIRED;
            default: return self::STATUS_ACTIVE;    
        }
    }    

    /**
     * Returns possible vtp status.
     * @param Vtp $vtp
     * @return integer
     */
    public static function getStatusFromVtp($vtp) 
    {
        switch ($vtp->getStatus()){
            case Vtp::STATUS_RETIRED: return self::STATUS_RETIRED;
            default: return self::STATUS_ACTIVE;    
        }
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
     * Returns the office.
     * @return Office     
     */
    public function getOffice() 
    {
        return $this->office;
    }

    /**
     * Returns the good.
     * @return Goods     
     */
    public function getGood() 
    {
        return $this->good;
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
     * @return Ptu
     */    
    public function getPtu() 
    {
        return $this->ptu;
    }

    /**
     * @param Ptu $ptu
     */    
    public function setPtu($ptu) 
    {
        $this->ptu = $ptu;
    }                         

    /*
     * @return Ot
     */    
    public function getOt() 
    {
        return $this->ot;
    }

    /**
     * @param Ot $ot
     */    
    public function setOt($ot) 
    {
        $this->ot = $ot;
    }                         

    /*
     * @return Vt
     */    
    public function getVt() 
    {
        return $this->vt;
    }

    /**
     * @param Vt $vt
     */    
    public function setVt($vt) 
    {
        $this->vt = $vt;
    }   
    
    /**
     * 
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * 
     * @param User $user
     * @return $this
     */
    public function setUser($user) {
        $this->user = $user;
        return $this;
    }

}
