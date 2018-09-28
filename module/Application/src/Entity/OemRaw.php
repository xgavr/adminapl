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
 * @ORM\Table(name="oem_raw")
 * @author Daddy
 */
class OemRaw {
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
     * @ORM\ManyToOne(targetEntity="Application\Entity\Article", inversedBy="oemRaw") 
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    protected $article;    

    /**
     * @ORM\ManyToMany(targetEntity="Application\Entity\Rawprice")
     * @ORM\JoinTable(name="rawprice_oem_raw",
     *      joinColumns={@ORM\JoinColumn(name="oem_raw_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="rawprice_id", referencedColumnName="id")}
     *      )
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
        $this->code = mb_strcut(trim($code), 0, 24, 'UTF-8');
    }     

    public function getFullCode() 
    {
        return trim($this->fullcode, " '`");
    }

    public function setFullCode($fullcode) 
    {
        $this->fullcode = mb_strcut(trim($fullcode), 0, 36, 'UTF-8');
    }     

    /**
     * Возвращает связанный article.
     * @return \Application\Entity\Article
     */    
    public function getArticle() 
    {
        return $this->article;
    }

    /**
     * Задает связанный article.
     * @param \Application\Entity\Article $article
     */    
    public function setArticle($article) 
    {
        $this->article = $article;
        $article->addOemRaw($this);
    }           
    
    /**
     * Returns the array of rawprice assigned to this oem.
     * @return array
     */
    public function getRawprice()
    {
        return $this->rawprice;
    }
    
    /**
     * Assigns a rawprice to oem.
     */
    public function addRawprice($rawprice)
    {
        $this->rawprice->add($rawprice);
    }
    
}
