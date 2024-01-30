<?php
namespace Zp\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zp\Entity\PersonalAccrual;
use Zp\Entity\Position;
use User\Entity\User;

/**
 * This class represents a personal.
 * @ORM\Entity(repositoryClass="\Zp\Repository\ZpRepository")
 * @ORM\Table(name="personal")
 */
class Personal
{
    const STATUS_ACTIVE       = 1; //принят
    const STATUS_RETIRED      = 2; //уволен
    const STATUS_UPDATE      = 3; //изменение
    
    /**
     * @ORM\Id
     * @ORM\Column(name="id")
     * @ORM\GeneratedValue
     */
    protected $id;

    /** 
     * @ORM\Column(name="apl_id")  
     */
    protected $aplId;

    /** 
     * @ORM\Column(name="doc_date")  
     */
    protected $docDate;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;
    
    /** 
     * @ORM\Column(name="comment")  
     */
    protected $comment;
    
    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;

    /** 
     * @ORM\Column(name="position_num")  
     */
    protected $positionNum;

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="personal") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;
    
    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="personal") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="Zp\Entity\Position", inversedBy="personal") 
     * @ORM\JoinColumn(name="position_id", referencedColumnName="id")
     */
    private $position;
    
    /**
     * @ORM\OneToMany(targetEntity="Zp\Entity\PersonalAccrual", mappedBy="personal") 
     * @ORM\JoinColumn(name="id", referencedColumnName="personal_id")
     */
    private $personalAccruals;    
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->personalAccruals = new ArrayCollection();
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getAplId() {
        return $this->aplId;
    }
    
    public function getComment() {
        return $this->comment;
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
            self::STATUS_ACTIVE => 'Принят',
            self::STATUS_UPDATE => 'Изменение',
            self::STATUS_RETIRED => 'Увольнение',
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

    public function getDocDate() {
        return $this->docDate;
    }

    public function getDateCreated() {
        return $this->dateCreated;
    }

    public function getCompany() {
        return $this->company;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setComment($comment) {
        $this->comment = $comment;
        return $this;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }
    
    public function setAplId($aplId) {
        $this->aplId = $aplId;
        return $this;
    }
    
    public function getPositionNum() {
        return $this->positionNum;
    }

    public function setPositionNum($positionNum) {
        $this->positionNum = $positionNum;
        return $this;
    }
    
    public function setDocDate($docDate) {
        $this->docDate = $docDate;
        return $this;
    }

    public function setDateCreated($dateCreated) {
        $this->dateCreated = $dateCreated;
        return $this;
    }

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
     * @return array
     */
    public function getPersonalAccruals() {
        return $this->personalAccruals;
    }

    /**
     * 
     * @param PersonalAccrual $personalAccrual
     */
    public function addPersonalAccrual($personalAccrual)
    {
        $this->personalAccruals[] = $personalAccrual;
    }
}



