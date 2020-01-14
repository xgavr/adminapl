<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Filter\IdsFormat;
use Application\Entity\Rate;

/**
 * Description of NameGroup
 * @ORM\Entity(repositoryClass="\Application\Repository\TokenRepository")
 * @ORM\Table(name="token_group")
 * @author Daddy
 */
class TokenGroup {
    
    const FREQUENCY_MIN   = 5000; // минимальная чатота токена

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
     * @ORM\Column(name="lemms")   
     */
    protected $lemms;

    /**
     * @ORM\Column(name="ids")   
     */
    protected $ids;

    /**
     * @ORM\Column(name="good_count")   
     */
    protected $goodCount = 0;

    
   /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\Goods", mappedBy="tokenGroup")
    * @ORM\JoinColumn(name="id", referencedColumnName="token_group_id")
   */
   private $goods;

     /**
     * @ORM\ManyToMany(targetEntity="\Application\Entity\Token")
     * @ORM\JoinTable(name="token_group_token",
     *      joinColumns={@ORM\JoinColumn(name="token_group_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="token_id", referencedColumnName="id")}
     *      )
     */
    private $tokens;
    
   /**
    * @ORM\OneToMany(targetEntity="Rate", mappedBy="tokenGroup")
    * @ORM\JoinColumn(name="id", referencedColumnName="token_group_id")
   */
   private $rates;        


    public function __construct() {
        $this->goods = new ArrayCollection();
        $this->tokens = new ArrayCollection();
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
        $this->name = trim($name);
    }  
    
    public function getLemms() 
    {
        return $this->lemms;
    }

    public function setLemms($lemms) 
    {
        $filter = new IdsFormat(['separator' => ' ']);
        $this->lemms = $filter->filter($lemms);
    }  
    
    public function getGoodCount() 
    {
        return $this->goodCount;
    }

    public function setGoodCount($goodCount) 
    {
        $this->goodCount = $goodCount;
    }  
    
    public function getIds() 
    {
        return $this->ids;
    }

    /**
     * 
     * @param array $ids
     */
    public function setIds($ids) 
    {        
        $filter = new IdsFormat();
        $this->ids = md5($filter->filter($ids));
    }  
    
    /**
     * Возвращает goods для этого tokenGroup.
     * @return array
     */   
   public function getGoods() {
      return $this->goods;
   }    
   
    /**
     * Добавляет новый goods к этому tokenGroup.
     * @param Application\Entity\Goods $good
     */   
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
     * @param Application\Entity\Token $token
     * @return bool
     */
    public function hasToken($token)
    {
        return $this->tokens->contains($token);
    }

    /**
     * 
     * @param Application\Entity\Token $token
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
