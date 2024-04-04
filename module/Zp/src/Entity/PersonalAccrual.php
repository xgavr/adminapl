<?php
namespace Zp\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use User\Entity\User;
use Zp\Entity\Accrual;
use Zp\Entity\Personal;
use Company\Entity\Legal;

/**
 * This class represents a position accrual.
 * @ORM\Entity(repositoryClass="\Zp\Repository\ZpRepository")
 * @ORM\Table(name="personal_accrual")
 */
class PersonalAccrual
{
    const STATUS_ACTIVE       = 1; //.
    const STATUS_RETIRED      = 2; // .
    
    const TAXED_NDFL_YES = 1; // облагается НДФЛ
    const TAXED_NDFL_NO = 2; // не облагается НДФЛ
    
    /**
     * @ORM\Id
     * @ORM\Column(name="id")
     * @ORM\GeneratedValue
     */
    protected $id;

    /** 
     * @ORM\Column(name="rate")  
     */
    protected $rate;
    
    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;

    /** 
     * @ORM\Column(name="date_oper")  
     */
    protected $dateOper;

    /** 
     * @ORM\Column(name="row_no")  
     */
    protected $rowNo;

    /** 
     * @ORM\Column(name="taxed_ndfl")  
     */
    protected $taxedNdfl;

    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="personalAccruals") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Zp\Entity\Accrual", inversedBy="personalAccruals") 
     * @ORM\JoinColumn(name="accrual_id", referencedColumnName="id")
     */
    private $accrual;

    /**
     * @ORM\ManyToOne(targetEntity="Zp\Entity\Personal", inversedBy="personalAccruals") 
     * @ORM\JoinColumn(name="personal_id", referencedColumnName="id")
     */
    private $personal;

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="personalAccruals") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;

    /**
     * Constructor.
     */
    public function __construct() 
    {
    }
    public function getId() {
        return $this->id;
    }

    public function getRate() {
        return $this->rate;
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
            self::STATUS_ACTIVE => 'Начать',
            self::STATUS_RETIRED => 'Прекратить',
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
    
    public function getTaxedNdfl() {
        return $this->taxedNdfl;
    }
    
    /**
     * Returns possible taxed as array.
     * @return array
     */
    public static function getTaxedNdflList() 
    {
        return [
            self::TAXED_NDFL_YES => 'Да',
            self::TAXED_NDFL_NO => 'Нет',
        ];
    }    

    /**
     * Returns taxed as string.
     * @return string
     */
    public function getTaxedNdflAsString()
    {
        $list = self::getTaxedNdflList();
        if (isset($list[$this->taxedNdfl]))
            return $list[$this->taxedNdfl];
        
        return 'Unknown';
    }    

    public function setTaxedNdfl($taxedNdfl) {
        $this->taxedNdfl = $taxedNdfl;
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
     * @return Accrual
     */
    public function getAccrual() {
        return $this->accrual;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setRate($rate) {
        $this->rate = $rate;
        return $this;
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
     * @param Accrual $accrual
     * @return $this
     */
    public function setAccrual($accrual) {
        $this->accrual = $accrual;
        return $this;
    }

    public function getDateOper() {
        return $this->dateOper;
    }

    /**
     * 
     * @return Personal
     */
    public function getPersonal() {
        return $this->personal;
    }

    /**
     * 
     * @return Legal
     */
    public function getCompany() {
        return $this->company;
    }

    public function setDateOper($dateOper) {
        $this->dateOper = $dateOper;
        return $this;
    }

    /**
     * 
     * @param Personal $personal
     * @return $this
     */
    public function setPersonal($personal) {
        $this->personal = $personal;
        $personal->addPersonalAccrual($this);
        return $this;
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

    public function getRowNo() {
        return $this->rowNo;
    }

    public function setRowNo($rowNo) {
        $this->rowNo = $rowNo;
        return $this;
    }

}



