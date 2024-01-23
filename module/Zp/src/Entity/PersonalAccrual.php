<?php
namespace Zp\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use User\Entity\User;
use Zp\Entity\Accrual;

/**
 * This class represents a position accrual.
 * @ORM\Entity(repositoryClass="\Zp\Repository\ZpRepository")
 * @ORM\Table(name="personal_accrual")
 */
class PersonalAccrual
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
     * @ORM\Column(name="rate")  
     */
    protected $rate;
    
    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;

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

    public function setStatus($status) {
        $this->status = $status;
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

}



