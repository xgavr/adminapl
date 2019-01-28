<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

use Zend\Config\Config;

/**
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Application\Repository\TokenRepository")
 * @ORM\Table(name="ml_title")
 * @author Daddy
 */
class MlTitle {
    
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    
     /**
    * @ORM\OneToOne(targetEntity="Application\Entity\Rawprice", mappedBy="mlTitles")
    * @ORM\JoinColumn(name="rawprice_id", referencedColumnName="id")
     */
    private $rawprice;    

    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    /**
     * Returns the array of rawprice assigned to this rawprice.
     * @return array
     */
    public function getRawprice()
    {
        return $this->rawprice;
    }        
}
