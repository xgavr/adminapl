<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Company\Entity\Office;

/**
 * Description of Pt
 * @ORM\Entity(repositoryClass="\Stock\Repository\PtRepository")
 * @ORM\Table(name="pt_sheduler")
 * @author Daddy
 */
class PtSheduler {
        
     // Ptu status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
   
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /** 
     * @ORM\Column(name="generatorTime")  
     */
    protected $generatorTime;    

    /**
     * @ORM\Column(name="generatorDay")   
     */
    protected $generatorDay;

    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="pt") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="pt") 
     * @ORM\JoinColumn(name="office2_id", referencedColumnName="id")
     */
    private $office2;
    
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

    public function getGeneratorTime() 
    {
        return $this->generatorTime;
    }

    public function setGeneratorTime($generatorTime) 
    {
        $this->generatorTime = $generatorTime;
    }     

    public function getGeneratorDay() 
    {
        return $this->generatorDay;
    }

    public function setGeneratorDay($generatorDay) 
    {
        $this->generatorDay = $generatorDay;
    }     

    /**
     * Returns status.
     * @return int     
     */
    public function getStatus() 
    {
        return $this->status;
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusList() 
    {
        return [
            self::STATUS_ACTIVE => 'Активный',
            self::STATUS_RETIRED => 'Удален',
        ];
    }    
    
    /**
     * Returns user status as string.
     * @return string
     */
    public function getStatusAsString()
    {
        $list = self::getStatusList();
        if (isset($list[$this->status]))
            return $list[$this->status];
        
        return 'Unknown';
    }    
    
    /**
     * Returns possible apl statuses as array.
     * @return array
     */
    public static function getAplStatusList() 
    {
        return [
            self::STATUS_ACTIVE => 1,
            self::STATUS_RETIRED => 0,
        ];
    }    
    
    
    /**
     * Returns apl status as string.
     * @return string
     */
    public function getAplStatusAsString()
    {
        $list = self::getAplStatusList();
        if (isset($list[$this->status]))
            return $list[$this->status];
        
        return 0;
    }    
    
    /**
     * Sets status.
     * @param int $status     
     */
    public function setStatus($status) 
    {
        $this->status = $status;
    }   
    
    /**
     * Sets office.
     * @param Office $office     
     */
    public function setOffice($office) 
    {
        $this->office = $office;
    }    

    /**
     * Returns the office.
     * @return Office     
     */
    public function getOffice() 
    {
        return $this->office;
    }
    
    /**
     * Sets office2.
     * @param Office $office2     
     */
    public function setOffice2($office2) 
    {
        $this->office2 = $office2;
    }    

    /**
     * Returns the office2.
     * @return Office     
     */
    public function getOffice2() 
    {
        return $this->office2;
    }
    
    /**
     * Лог
     * @return array
     */
    public function toLog()
    {
        return [
            'generatorTime' => $this->getGeneratorTime(),
            'generatorDay' => $this->getGeneratorDay(),
            'office' => $this->getOffice()->getId(),
            'office2' => $this->getOffice2()->getId(),
            'status' => $this->getStatus(),
        ];
    }
}
