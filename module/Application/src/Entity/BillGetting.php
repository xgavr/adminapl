<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Application\Entity\Supplier;

/**
 * Description of BillGetting
 * @ORM\Entity(repositoryClass="\Application\Repository\SupplierRepository")
 * @ORM\Table(name="bill_gettings")
 * @author Daddy
 */
class BillGetting {
    
     // Supplier status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    const STATUS_COMMON_BOX      = 3; // Общий ящик.
    
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
     * @ORM\Column(name="email")   
     */
    protected $email;
    
    /**
     * @ORM\Column(name="email_password")   
     */
    protected $emailPassword;
    
    /**
     * @ORM\Column(name="app_password")   
     */
    protected $appPassword;

    /**
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;    

    /**
     * @ORM\Column(name="status")  
     */
    protected $status;    
       
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Supplier", inversedBy="billGettings") 
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    private $supplier;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Supplier", inversedBy="realSupplierBillGettings") 
     * @ORM\JoinColumn(name="real_supplier_id", referencedColumnName="id")
     */
    private $realSupplier;    
    
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

    public function getEmail() 
    {
        return $this->email;
    }

    public function setEmail($email) 
    {
        $this->email = $email;
    }     

    public function getEmailPassword() 
    {
        return $this->emailPassword;
    }

    public function setEmailPassword($emailPassword) 
    {
        $this->emailPassword = $emailPassword;
    }     

    public function getAppPassword() 
    {
        return $this->appPassword;
    }

    public function setAppPassword($appPassword) 
    {
        $this->appPassword = $appPassword;
    }     

    public function getDateCreated() 
    {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
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
            self::STATUS_ACTIVE => 'Используется',
            self::STATUS_RETIRED => 'Не используется',
            self::STATUS_COMMON_BOX => 'Используется обший ящик'
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
    
    /*
     * Возвращает связанный supplier.
     * @return \Application\Entity\Supplier
     */    
    public function getSupplier() 
    {
        return $this->supplier;
    }

    /**
     * Задает связанный supplier.
     * @param Supplier $supplier
     */    
    public function setSupplier($supplier) 
    {
        $this->supplier = $supplier;
        $supplier->addBillGettings($this);
    }    
        
    /**
     * 
     * @return Supplier
     */
    public function getRealSupplier() {
        if ($this->realSupplier){
            return $this->realSupplier;
        }
        
        return $this->getSupplier();
    }

    /**
     * 
     * @param Supplier $realSupplier
     * @return $this
     */
    public function setRealSupplier($realSupplier) {
        $this->realSupplier = $realSupplier;
        return $this;
    }

}
