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
    
    const IS_D  = 1; //описание
    const IS_F  = 2; //характеристики
    const IS_A  = 2; //применимость
    const IS_S  = 4; //служебное
    const IS_O  = 5; //прочее
    
    const IS_SD  = 14; //описание + служебное
    const IS_DF  = 12; //описание + характеристик
    const IS_FA  = 22; //характеристики + применимость
    const IS_AS  = 24; //применимость + служебное
    
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
