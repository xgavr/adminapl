<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Pricelist
 * @ORM\Entity(repositoryClass="\Application\Repository\SupplierRepository")
 * @ORM\Table(name="price_gettings")
 * @author Daddy
 */
class PriceGetting {
    
     // Supplier status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
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
     * @ORM\Column(name="ftp")   
     */
    protected $ftp;
    
    
    /**
     * @ORM\Column(name="ftp_login")   
     */
    protected $ftpLogin;
    
    /**
     * @ORM\Column(name="ftp_password")   
     */
    protected $ftpPassword;
    
    /**
     * @ORM\Column(name="email")   
     */
    protected $email;
    
    /**
     * @ORM\Column(name="email_password")   
     */
    protected $emailPassword;
    
    /**
     * @ORM\Column(name="link")   
     */
    protected $link;
    
    /**
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;    

    /**
     * @ORM\Column(name="status")  
     */
    protected $status;    
       
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Supplier", inversedBy="priceGettings") 
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    private $supplier;    
    
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

    public function getFtp() 
    {
        return $this->ftp;
    }

    public function setFtp($ftp) 
    {
        $this->ftp = $ftp;
    }     


    public function getFtpLogin() 
    {
        return $this->ftpLogin;
    }

    public function setFtpLogin($ftpLogin) 
    {
        $this->ftpLogin = $ftpLogin;
    }     

    public function getFtpPassword() 
    {
        return $this->ftpPassword;
    }

    public function setFtpPassword($ftpPassword) 
    {
        $this->ftpPassword = $ftpPassword;
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

    public function getLink() 
    {
        return $this->link;
    }

    public function setLink($link) 
    {
        $this->link = $link;
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
            self::STATUS_RETIRED => 'Не используется'
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
     * @param \Application\Entity\Supplier $supplier
     */    
    public function setSupplier($supplier) 
    {
        $this->supplier = $supplier;
        $supplier->addPriceGettings($this);
    }    
        
}
