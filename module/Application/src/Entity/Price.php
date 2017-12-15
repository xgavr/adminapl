<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Price
 * @ORM\Entity(repositoryClass="\Application\Repository\SupplierRepository")
 * @ORM\Table(name="price")
 * @author Daddy
 */
class Price {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="price")   
     */
    protected $price;

    /**
     * @ORM\Column(name="num")   
     */
    protected $num;

    /**
     * @ORM\Column(name="source")   
     */
    protected $source;

    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getPrice() 
    {
        return $this->price;
    }

    public function setPrice($price) 
    {
        $this->price = $price;
    }     

    public function getNum() 
    {
        return $this->num;
    }

    public function setNum($num) 
    {
        $this->num = $num;
    }     

    public function getSource() 
    {
        return $this->source;
    }

    public function setSource($source) 
    {
        $this->source = $source;
    }     

}
