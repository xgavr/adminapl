<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Ntd
 * @ORM\Entity(repositoryClass="\Stock\Repository\StockRepository")
 * @ORM\Table(name="ntd")
 * @author Daddy
 */
class Ntd {
        

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="ntd")   
     */
    protected $ntd;


    /**
     * Constructor.
     */
    public function __construct() 
    {
    }
    
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getNtd() 
    {
        return $this->ntd;
    }

    public function setNtd($ntd) 
    {
        $this->ntd = $ntd;
    }     
}
