<?php
namespace Zp\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Company\Entity\Legal;

/**
 * This class represents a zp.
 * @ORM\Entity(repositoryClass="\Zp\Repository\ZpRepository")
 * @ORM\Table(name="position")
 */
class Position
{
    const STATUS_ACTIVE       = 1; //.
    const STATUS_RETIRED      = 2; // .
    
    const KIND_ADM       = 1; // администрация
    const KIND_RETAIL       = 2; // розница
    const KIND_TP   = 3; // тп
    
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
     * @ORM\Column(name="name")  
     */
    protected $name;
    
    /** 
     * @ORM\Column(name="comment")  
     */
    protected $comment;
    
    /** 
     * @ORM\Column(name="sort")  
     */
    protected $sort;
    
    /** 
     * @ORM\Column(name="num")  
     */
    protected $num;
    
    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;

    /**
     * @ORM\Column(name="kind")   
     */
    protected $kind;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="positions") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;
    
    /**
     * @ORM\ManyToOne(targetEntity="Zp\Entity\Position", inversedBy="childPositions") 
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parentPosition;
    
    /**
     * @ORM\OneToMany(targetEntity="Zp\Entity\Position", mappedBy="parentPosition") 
     * @ORM\JoinColumn(name="id", referencedColumnName="parent_id")
     */
    private $childPositions;
    
    /**
    * @ORM\OneToMany(targetEntity="Zp\Entity\Personal", mappedBy="position")
    * @ORM\JoinColumn(name="id", referencedColumnName="position_id")
     */
    private $personal;    
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
       $this->childPositions = new ArrayCollection();
       $this->personal = new ArrayCollection();
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getAplId() {
        return $this->aplId;
    }
    
    public function getName() {
        return $this->name;
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

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setName($name) {
        $this->name = $name;
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
 
    public function getSort() {
        return $this->sort;
    }

    public function setSort($sort) {
        $this->sort = $sort;
        return $this;
    }

    /**
     * 
     * @return Position
     */
    public function getParentPosition() {
        return $this->parentPosition;
    }

    /**
     * 
     * @param Position $parentPosition
     * @return $this
     */
    public function setParentPosition($parentPosition) {
        $this->parentPosition = $parentPosition;
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getChildPositions() {
        return $this->childPositions;
    }

    /**
     * 
     * @param Position $position
     */
    public function addChildPosition($position)
    {
        $this->childPositions[] = $position;
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
    
    public function getNum() {
        return $this->num;
    }

    public function setNum($num) {
        $this->num = $num;
        return $this;
    }
    
    public function getKind() {
        return $this->kind;
    }

    /**
     * Returns possible kinds as array.
     * @return array
     */
    public static function getKindList() 
    {
        return [
            self::KIND_ADM => 'Администрация',
            self::KIND_RETAIL => 'Розница',
            self::KIND_TP => 'ТП',
        ];
    }    

    /**
     * Returns kind as string.
     * @return string
     */
    public function getKindAsString()
    {
        $list = self::getKindList();
        if (isset($list[$this->kind]))
            return $list[$this->kind];
        
        return 'Unknown';
    }    
    
    public function setKind($kind) {
        $this->kind = $kind;
        return $this;
    }    
    
    /**
     * 
     * @return array
     */
    public function getPersonal() {
        return $this->personal;
    }
    
}



