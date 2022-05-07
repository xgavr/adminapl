<?php

namespace Company\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Commission
 * @ORM\Entity(repositoryClass="\Company\Repository\CommissionRepository")
 * @ORM\Table(name="commission")
 *
 * @author Daddy
 */
class Commission {

    const STATUS_HEAD       = 1; // глава.
    const STATUS_MEMBER      = 2; // член.
    const STATUS_SIGN      = 3; // член, подписывает УПД.
    
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
     * @ORM\Column(name="position")   
     */
    protected $position;

    /**
     * @ORM\Column(name="status")   
     */
    protected $status;
        
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="commission") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;    
    
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
        $this->name = trim($name);
    }     

    public function getPosition() 
    {
        return $this->position;
    }

    public function setPosition($position) 
    {
        $this->position = $position;
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
            self::STATUS_HEAD => 'Председатель',
            self::STATUS_SIGN => 'Подписывает УПД',
            self::STATUS_MEMBER => 'Член'
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
     * Sets status.
     * @param int $status     
     */
    public function setStatus($status) 
    {
        $this->status = $status;
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
     * Sets  office.
     * @param Office $office     
     */
    public function setOffice($office) 
    {
        $this->office = $office;
        $office->addCommisar($this);
    }        
}
