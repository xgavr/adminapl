<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\Rate;

/**
 * Description of Make
 * @ORM\Entity(repositoryClass="\Application\Repository\GenericGroupRepository")
 * @ORM\Table(name="generic_group")
 * @author Daddy
 */

class GenericGroup {
    
     // Make status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
     // Make car constants.
    const CAR_ACTIVE       = 1; // Active. Загружать машины
    const CAR_RETIRED      = 2; // Retired. Не загружать
    
    const MIN_GOOD_COUNT      = 5; //минимально товаров в группе для подбора
        
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="name")   
     */
    protected $name;
        
    /**
     * @ORM\Column(name="td_id")   
     */
    protected $tdId;
    
    /**
     * @ORM\Column(name="apl_id")   
     */
    protected $aplId;
    
    /**
     * @ORM\Column(name="assembly_group")  
     */
    protected $assemblyGroup;    

    /**
     * @ORM\Column(name="master_name")   
     */
    protected $masterName;
    /**
     * @ORM\Column(name="usage_name")   
     */
    protected $usageName;

    /**
     * @ORM\Column(name="status")  
     */
    protected $status;    
           
    /**
     * @ORM\Column(name="car_upload")  
     */
    protected $carUpload;    
           
    /**
     * @ORM\Column(name="good_count")  
     */
    protected $goodCount;    

    /**
     * @ORM\Column(name="movement")  
     */
    protected $movement;    

    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Goods", mappedBy="genericGroup")
    * @ORM\JoinColumn(name="id", referencedColumnName="generic_group_id")
     */
    protected $goods;    
    
     /**
     * @ORM\ManyToMany(targetEntity="Application\Entity\Token")
     * @ORM\JoinTable(name="generic_group_token",
     *      joinColumns={@ORM\JoinColumn(name="generic_group_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="token_id", referencedColumnName="id")}
     *      )
     */
    private $tokens;
    
   /**
    * @ORM\OneToMany(targetEntity="Rate", mappedBy="genericGroup")
    * @ORM\JoinColumn(name="id", referencedColumnName="generic_group_id")
   */
   private $rates;    

    public function __construct() {
       $this->goods = new ArrayCollection();
       $this->rates = new ArrayCollection();
    }    
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getName() 
    {
        return $this->name;
    }

    public function setName($name) 
    {
        $this->name = $name;
    }     

    public function getAssemblyGroup() 
    {
        return $this->assemblyGroup;
    }

    public function setAssemblyGroup($assemblyGroup) 
    {
        $this->assemblyGroup = $assemblyGroup;
    }     

    public function getTdId() 
    {
        if ($this->tdId){
            return $this->tdId;
        }
        
        return -1;
    }

    public function setTdId($tdId) 
    {
        $this->tdId = $tdId;
    }     

    public function getAplId() 
    {
        return $this->aplId;
    }

    public function setAplId($aplId) 
    {
        $this->aplId = $aplId;
    }     
    
    public function getMasterName() 
    {
        return $this->masterName;
    }

    public function setMasterName($masterName) 
    {
        $this->masterName = $masterName;
    }     
    
    public function getUsageName() 
    {
        return $this->usageName;
    }

    public function setUsageName($usageName) 
    {
        $this->usageName = $usageName;
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
            self::STATUS_ACTIVE => 'Используется',
            self::STATUS_RETIRED => 'Не используется'
        ];
    }    
    
    /**
     * Returns make status as string.
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
     * Returns carUpload.
     * @return int     
     */
    public function getCarUpload() 
    {
        return $this->carUpload;
    }

    
    /**
     * Returns possible carUpload as array.
     * @return array
     */
    public static function getCarUploadList() 
    {
        return [
            self::CAR_ACTIVE => 'Машины загружать',
            self::CAR_RETIRED => 'Машины не загружать'
        ];
    }    
    
    /**
     * Returns make carUpload as string.
     * @return string
     */
    public function getCarUploadAsString()
    {
        $list = self::getCarUploadList();
        if (isset($list[$this->carUpload]))
            return $list[$this->carUpload];
        
        return 'Unknown';
    }    
    
    /**
     * Sets carUpload.
     * @param int $carUpload     
     */
    public function setCarUpload($carUpload) 
    {
        $this->carUpload = $carUpload;
    }   
    
    public function getGoodCount() 
    {
        return $this->goodCount;
    }

    public function setGoodCount($goodCount) 
    {
        $this->goodCount = $goodCount;
    }     
    
    public function getMovement() 
    {
        return $this->movement;
    }

    public function setMovement($movement) 
    {
        $this->movement = $movement;
    }      
    
    // Возвращает товары, связанные с данной машиной.
    public function getGoods() 
    {
        return $this->goods;
    }
    
    // Добавляет товар в коллекцию товаров, связанных с этой машиной.
    public function addGood($good) 
    {
        $this->goods[] = $good;        
    }     
    
    public function getTokens() {
       return $this->tokens;
    }    
   
    /**
     * Содержет ли строка токен?
     * 
     * @param \Application\Entity\Token $token
     * @return bool
     */
    public function hasToken($token)
    {
        return $this->tokens->contains($token);
    }

    /**
     * 
     * @param \Application\Entity\Token $token
     */
    public function addToken($token)
    {
        //if (!$this->hasToken($token)){
            $this->tokens->add($token);
        //}    
    }

    
    public function getTokenView()
    {
        $result = [];
        
        foreach ($this->tokens as $token){
            $result[] = $token->getLemma();
        }
        
        if (count($result)){
            return implode(' ', $result);
        }
        
        return 'NaN';
    }    
    
    /*
     * Возвращает связанный rates.
     * @return Rate
     */    
    public function getRates() 
    {
        return $this->rates;
    }

    public function addRate($rate) 
    {
        $this->rates[] = $rate;
    }         
}
