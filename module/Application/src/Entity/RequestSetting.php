<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Filter\UriNormalize;


/**
 * Description of Pricelist
 * @ORM\Entity(repositoryClass="\Application\Repository\SupplierRepository")
 * @ORM\Table(name="request_setting")
 * @author Daddy
 */
class RequestSetting {
    
     // Supplier status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    const MODE_MANUALLY       = 1; // Заявка в ручную.
    const MODE_API            = 2; // Заявкачерез Апи.
    
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
     * @ORM\Column(name="site")   
     */
    protected $site;
    
    /**
     * @ORM\Column(name="login")   
     */
    protected $login;
    
    /**
     * @ORM\Column(name="password")   
     */
    protected $password;
    
    /**
     * @ORM\Column(name="description")   
     */
    protected $description;
    
    /**
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;    

    /**
     * @ORM\Column(name="status")  
     */
    protected $status;    
       
    /**
     * @ORM\Column(name="mode")  
     */
    protected $mode;    
       
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Supplier", inversedBy="requestSettings") 
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

    public function getSite() 
    {
        return $this->site;
    }

    public function getSiteNormalize() 
    {
        $filter =  new UriNormalize(['enforcedScheme' => 'http']);
        return $filter->filter($this->site);
    }

    public function setSite($site) 
    {
        $this->site = $site;
    }     

    public function getLogin() 
    {
        return $this->login;
    }

    public function setLogin($login) 
    {
        $this->login = $login;
    }     

    public function getPassword() 
    {
        return $this->password;
    }

    public function setPassword($password) 
    {
        $this->password = $password;
    }     

    public function getDescription() 
    {
        return $this->description;
    }

    public function setDescription($description) 
    {
        $this->description = $description;
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
    
    /**
     * Returns mode.
     * @return int     
     */
    public function getMode() 
    {
        return $this->mode;
    }
    
    /**
     * Returns possible modes as array.
     * @return array
     */
    public static function getModeList() 
    {
        return [
            self::MODE_MANUALLY => 'В ручную',
            self::MODE_API => 'Api'
        ];
    }    
    
    /**
     * Returns user mode as string.
     * @return string
     */
    public function getModeAsString()
    {
        $list = self::getModeList();
        if (isset($list[$this->mode]))
            return $list[$this->mode];
        
        return 'Unknown';
    }   
    
    /**
     * Вывести описание
     */
    public function getAsText()
    {
        $result = $this->name
                . "<br/>"
                . "Способ заказа: <span>"
                . $this->getModeAsString()
                . "</span><br/>"
                . "Сайт: <a href='"
                . $this->getSiteNormalize()
                . "' target='block'>"
                . $this->site
                . "</a>"
                . "<br/>"
                . "Логин: "
                . $this->login
                . "<br/>"
                . "Пароль: "
                . $this->password
                . "<br/>"
                . nl2br($this->description);
        
        return $result;
}
    
    /**
     * Sets mode.
     * @param int $mode     
     */
    public function setMode($mode) 
    {
        $this->mode = $mode;
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
        $supplier->addRequestSetting($this);
    }    
        
}
