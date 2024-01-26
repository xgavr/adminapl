<?php
namespace Zp\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zp\Entity\Position;
use Zp\Entity\Accrual;
use User\Entity\User;
use Company\Entity\Legal;

/**
 * This class represents a position accrual.
 * @ORM\Entity(repositoryClass="\Zp\Repository\ZpRepository")
 * @ORM\Table(name="doc_calculator")
 */
class DocCalculator
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
     * @ORM\Column(name="date_oper")  
     */
    protected $dateOper;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;

    /** 
     * @ORM\Column(name="base")  
     */
    protected $base;
    
    /** 
     * @ORM\Column(name="rate")  
     */
    protected $rate;

    /** 
     * @ORM\Column(name="num")  
     */
    protected $num;
    
    /** 
     * @ORM\Column(name="amount")  
     */
    protected $amount;
    
    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="Zp\Entity\Position", inversedBy="docCalculators") 
     * @ORM\JoinColumn(name="position_id", referencedColumnName="id")
     */
    private $position;

    /**
     * @ORM\ManyToOne(targetEntity="Zp\Entity\Accrual", inversedBy="docCalculators") 
     * @ORM\JoinColumn(name="accrual_id", referencedColumnName="id")
     */
    private $accrual;

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="docCalculators") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="docCalculators") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
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

    public function getBase() {
        return $this->base;
    }

    public function setBase($base) {
        $this->base = $base;
        return $this;
    }

    public function getRate() {
        return $this->rate;
    }

    public function setRate($rate) {
        $this->rate = $rate;
        return $this;
    }

    public function getNum() {
        return $this->num;
    }

    public function setNum($num) {
        $this->num = $num;
        return $this;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
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
     * @return Position
     */
    public function getPosition() {
        return $this->position;
    }

    /**
     * 
     * @param Position $position
     * @return $this
     */
    public function setPosition($position) {
        $this->position = $position;
        return $this;
    }

    /**
     * 
     * @return Accrual
     */
    public function getAccrual() {
        return $this->accrual;
    }

    /**
     * 
     * @param Accrual $accrual
     * @return $this
     */
    public function setAccrual($accrual) {
        $this->accrual = $accrual;
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



