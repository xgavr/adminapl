<?php
namespace Zp\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use User\Entity\User;
use Company\Entity\Legal;
use Application\Entity\Shipping;
use Application\Entity\Order;
use Company\Entity\Office;

/**
 * This class represents a position accrual.
 * @ORM\Entity(repositoryClass="\Zp\Repository\ZpRepository")
 * @ORM\Table(name="order_calculator")
 */
class OrderCalculator
{
    const STATUS_ACTIVE       = 1; //.
    const STATUS_RETIRED      = 2; // .
    
    /**
     * @ORM\Id
     * @ORM\Column(name="id")
     * @ORM\GeneratedValue
     */
    protected $id;

    /** 
     * @ORM\Column(name="doc_type")  
     */
    protected $docType;

    /** 
     * @ORM\Column(name="doc_id")  
     */
    protected $docId;

    /** 
     * @ORM\Column(name="date_oper")  
     */
    protected $dateOper;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;
    
    /** 
     * @ORM\Column(name="amount")  
     */
    protected $amount;
    
    /** 
     * @ORM\Column(name="pay_amount")  
     */
    protected $payAmount;
    
    /** 
     * @ORM\Column(name="delivery_amount")  
     */
    protected $deliveryAmount;
    
    /** 
     * @ORM\Column(name="base_amount")  
     */
    protected $baseAmount;
    
    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Order", inversedBy="orderCalculators") 
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="orderCalculators") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="orderCalculators") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;

    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="orderCalculators") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="orderCalculators") 
     * @ORM\JoinColumn(name="courier_id", referencedColumnName="id")
     */
    private $courier;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Shipping", inversedBy="orderCalculators") 
     * @ORM\JoinColumn(name="shipping_id", referencedColumnName="id")
     */
    private $shipping;

    /**
     * Constructor.
     */
    public function __construct() 
    {
    }
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getDocType() {
        return $this->docType;
    }

    public function setDocType($docType) {
        $this->docType = $docType;
        return $this;
    }

    public function getDocId() {
        return $this->docId;
    }

    public function setDocId($docId) {
        $this->docId = $docId;
        return $this;
    }

    
    public function getDateOper() {
        return $this->dateOper;
    }

    public function setDateOper($dateOper) {
        $this->dateOper = $dateOper;
        return $this;
    }
    
    public function getDateCreated() {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated) {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
        return $this;
    }

    public function getPayAmount() {
        return $this->payAmount;
    }

    public function setPayAmount($payAmount) {
        $this->payAmount = $payAmount;
        return $this;
    }

    public function getDeliveryAmount() {
        return $this->deliveryAmount;
    }

    public function setDeliveryAmount($deliveryAmount) {
        $this->deliveryAmount = $deliveryAmount;
        return $this;
    }

    public function getBaseAmount() {
        return $this->baseAmount;
    }

    public function setBaseAmount($baseAmount) {
        $this->baseAmount = $baseAmount;
        return $this;
    }

    public function getStatus() {
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
    
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    /**
     * 
     * @return Order
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     * 
     * @param Order $order
     * @return $this
     */
    public function setOrder($order) {
        $this->order = $order;
        return $this;
    }

    /**
     * 
     * @return Legal
     */
    public function getCompany() {
        return $this->company;
    }

    /**
     * 
     * @param Legal $company
     * @return $this
     */
    public function setCompany($company) {
        $this->company = $company;
        return $this;
    }

    /**
     * 
     * @return Office
     */
    public function getOffice() {
        return $this->office;
    }

    /**
     * 
     * @param Office $office
     * @return $this
     */
    public function setOffice($office) {
        $this->office = $office;
        return $this;
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

    /**
     * 
     * @return User
     */
    public function getCourier() {
        return $this->courier;
    }

    /**
     * 
     * @param User $courier
     * @return $this
     */
    public function setCourier($courier) {
        $this->courier = $courier;
        return $this;
    }

    /**
     * 
     * @return Shipping
     */
    public function getShipping() {
        return $this->shipping;
    }

    /**
     * 
     * @param Shipping $shipping
     * @return $this
     */
    public function setShipping($shipping) {
        $this->shipping = $shipping;
        return $this;
    }
    
}



