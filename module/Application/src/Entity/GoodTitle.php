<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Application\Repository\TokenRepository")
 * @ORM\Table(name="good_title")
 * @author Daddy
 */
class GoodTitle {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="title")  
     */
    protected $title;

    /**
     * @ORM\Column(name="title_md5")  
     */
    protected $titleMd5;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="goodTitles") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
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

    public function setTitle($title)
    {
        $this->title = mb_strtoupper(trim($title), 'UTF-8');
        $this->titleMd5 = md5($this->title);
    }
    
    public function getTitle()
    {
        return $this->title;
    }

    public function getTitleMd5()
    {
        return $this->titleMd5;
    }
    /**
     * Возвращает связанный good.
     * @return \Application\Entity\Goods
     */    
    public function getGood() 
    {
        return $this->goods;
    }

    /**
     * Задает связанный good.
     * @param \Application\Entity\Goods $good
     */    
    public function setGood($good) 
    {
        $this->good = $good;
    }           
    
}
