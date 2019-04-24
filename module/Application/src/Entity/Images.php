<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Images
 * @ORM\Entity(repositoryClass="\Application\Repository\ImageRepository")
 * @ORM\Table(name="images")
 * @author Daddy
 */
class Images {
    
    const STATUS_UNKNOWN    = 1; // источник картики неопределен.
    const STATUS_TD   = 2; // картинка из текдока.
    const STATUS_SUP   =3; //картинка от поставщика
    const STATUS_HAND   =4; //картинка добавлена вручную
    
    const SIMILAR_UNKNOWN = 1; //картинка не определена 
    const SIMILAR_MATCH = 2; //картинка совпадает
    const SIMILAR_SIMILAR = 3; //картинка похожа
    
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
     * @ORM\Column(name="path")   
     */
    protected $path;

    /**
     * @ORM\Column(name="status")   
     */
    protected $status;
    
    /**
     * @ORM\Column(name="similar")   
     */
    protected $similar;
    
    /**
    * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="images")
    * @ORM\JoinColumn(name="good_id", referencedColumnName="id")    
    * 
    */
    protected $good;
     
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

    public function getPath() 
    {
        return $this->path;
    }

    public function getPublicPath() 
    {
        if (file_exists($this->path)){
            return dirname($this->path, -1);
        }
        
        return;
    }

    public function setPath($path) 
    {
        $this->path = $path;
    }     
    
    public function getStatus() 
    {
        return $this->status;
    }

    public function setStatus($status) 
    {
        $this->status = $status;
    }     
    
    public function getSimilar() 
    {
        return $this->similar;
    }

    public function setSimilar($similar) 
    {
        $this->similar = $similar;
    }   
    
    /**
     * Возвращает связанный товар.
     * @return \Application\Entity\Goods
     */
    public function getGood() 
    {
        return $this->good;
    }
    
    /**
     * Задает связанный товар.
     * @param \Application\Entity\Goods $good
     */
    public function setGood($good) 
    {
        $this->good = $good;
        $good->addImage($this);
    }    
}
