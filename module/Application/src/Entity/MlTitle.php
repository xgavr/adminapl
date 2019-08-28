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
 * @ORM\Table(name="ml_title")
 * @author Daddy
 */
class MlTitle {
    
    const STATUS_BAD  = 1; //плохо
    const STATUS_MID  = 2; //ничего
    const STATUS_EX   = 3;//хорошо
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="status")   
     */
    protected $status;    
    
     /**
    * @ORM\OneToOne(targetEntity="Application\Entity\Rawprice")
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

    public function getStatus() 
    {
        return $this->status;
    }

    public function setStatus($status) 
    {
        $this->status = $status;
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
