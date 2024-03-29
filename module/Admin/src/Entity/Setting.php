<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * Description of PostLog
 * @ORM\Entity(repositoryClass="\Admin\Repository\SettingRepository")
 * @ORM\Table(name="setting")
 * @author Daddy
 */
class Setting {
    
    const STATUS_ACTIVE       = 1; // Active proccess.
    const STATUS_RETIRED      = 2; // Deactive process.
    const STATUS_ERROR      = 3; // Error process.
    const STATUS_ACTIVE_AND_ERROR      = 10; // Active and Error process.
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="controller")   
     */
    protected $controller;
    
    /**
     * @ORM\Column(name="action")   
     */
    protected $action;

    /**
     * @ORM\Column(name="status")   
     */
    protected $status;

    /**
     * @ORM\Column(name="last_mod")   
     */
    protected $lastMod;

    /**
     * @ORM\Column(name="err_code")   
     */
    protected $errorCode;

    /**
     * @ORM\Column(name="err_text")   
     */
    protected $errorText;

    /**
     * @ORM\Column(name="name")   
     */
    protected $name;

    public function __construct() {
    }
   
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getController()
    {
        return $this->controller;
    }
    
    public function setController($controller)
    {
        $this->controller = $controller;
    }
    
    public function getAction()
    {
        return $this->action;
    }
    
    public function setAction($action)
    {
        $this->action = $action;
    }
        
    public function getLastMod()
    {
        return $this->lastMod;
    }
    
    public function setLastMod($lastMod)
    {
        $this->lastMod = $lastMod;
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
            self::STATUS_ACTIVE_AND_ERROR => 'Активные',
            self::STATUS_ACTIVE => 'Запущен',
            self::STATUS_RETIRED => 'Завершен',
            self::STATUS_ERROR => 'Ошибка'    
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
    
    public function getErrorCode()
    {
        return $this->errorCode;
    }
    
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
    }
    
    public function getErrorText()
    {
        return $this->errorText;
    }
    
    public function setErrorText($errorText)
    {
        $this->errorText = $errorText;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
}
