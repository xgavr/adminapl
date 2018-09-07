<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Application\Repository\ArticleRepository")
 * @ORM\Table(name="article")
 * @author Daddy
 */
class Article {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="code")   
     */
    protected $code;
    
    /**
     * @ORM\Column(name="fullcode")  
     */
    protected $fullcode;        

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Producer", inversedBy="good") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    protected $good;    

    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Rawprice", mappedBy="code")
    * @ORM\JoinColumn(name="id", referencedColumnName="article_id")
     */
    private $rawprice;
        
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->rawprice = new ArrayCollection();
    }
    

    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getCode() 
    {
        return $this->code;
    }

    public function setCode($code) 
    {
        $this->code = trim($code);
    }     

    public function getFullCode() 
    {
        return $this->fullcode;
    }

    public function setFullCode($fullcode) 
    {
        $this->fullcode = trim($fullcode);
    }     

    /**
     * Возвращает связанный good.
     * @return \Application\Entity\Goods
     */    
    public function getGood() 
    {
        return $this->good;
    }

    /**
     * Задает связанный good.
     * @param \Application\Entity\Goods $good
     */    
    public function setGood($good) 
    {
        $this->good = $good;
        if ($good){
            $good->addArticle($this);
        }    
    }     
    
    /**
     * Returns the array of contacts assigned to this.
     * @return array
     */
    public function getRawprice()
    {
        return $this->rawprice;
    }
        
    /**
     * Assigns.
     */
    public function addRawprice($rawprice)
    {
        $this->rawprice[] = $rawprice;
    }
      
}
