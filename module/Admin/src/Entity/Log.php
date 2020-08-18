<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;
use User\Entity\User;
use Laminas\Json\Decoder;
use Laminas\Json\Json;


/**
 * Description of Log
 * @ORM\Entity(repositoryClass="\Admin\Repository\LogRepository")
 * @ORM\Table(name="log")
 * @author Daddy
 */
class Log {
    
    const STATUS_NEW       = 1; // Добавление.
    const STATUS_UPDATE    = 2; // Изменение.
    const STATUS_DELETE    = 3; // Удаление.
    const STATUS_INFO      = 4; // Информация.
    
    const PRIORITY_EMEGR    = 0; // Emergency: system is unusable
    const PRIORITY_ALERT    = 1; // Alert: action must be taken immediately
    const PRIORITY_CRIT     = 2; // Critical: critical conditions
    const PRIORITY_ERR      = 3; // Error: error conditions
    const PRIORITY_WARN     = 4; // Warning: warning conditions
    const PRIORITY_NOTICE   = 5; // Notice: normal but significant condition
    const PRIORITY_INFO     = 6; // Informational: informational messages
    const PRIORITY_DEBUG    = 7; // Debug: debug messages
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="log_key")   
     */
    protected $logKey;
    
    /**
     * @ORM\Column(name="message")   
     */
    protected $message;
    
    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;
    
    /**
     * @ORM\Column(name="status")   
     */
    protected $status;

    /**
     * @ORM\Column(name="priority")   
     */
    protected $priority;

    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="logs") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

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

    public function getLogKey() 
    {
        return $this->logKey;
    }

    public function getIdFromLogKey() 
    {
        $ek = explode(':', $this->logKey);
        return $ek[1];
    }

    public function setLogKey($logKey) 
    {
        $this->logKey = $logKey;
    }     

    public function getMessage()
    {
        return $this->message;
    }
    
    public function getMessageAsArray()
    {
        return Decoder::decode($this->message, Json::TYPE_ARRAY);
    }
    
    public function setMessage($message)
    {
        $this->message = $message;
    }
    
    /**
     * Returns the date of creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * Sets the date when this log was created.
     * @param string $dateCreated     
     */
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
            self::STATUS_NEW => 'Новая запись',
            self::STATUS_UPDATE => 'Изменение',
            self::STATUS_DELETE => 'Удаление',
            self::STATUS_INFO => 'Информация',
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
     * Returns priority.
     * @return int     
     */
    public function getPriority() 
    {
        return $this->priority;
    }

    /**
     * Returns possible priority as array.
     * @return array
     */
    public static function getPriorityList() 
    {
        return [
            self::PRIORITY_EMEGR => 'Emergency: system is unusable',
            self::PRIORITY_ALERT => 'Alert: action must be taken immediately',
            self::PRIORITY_CRIT => 'Critical: critical conditions',
            self::PRIORITY_ERR => 'Error: error conditions',
            self::PRIORITY_WARN => 'Warning: warning conditions',
            self::PRIORITY_NOTICE => 'Notice: normal but significant condition',
            self::PRIORITY_INFO => 'Informational: informational messages',
            self::PRIORITY_DEBUG => 'Debug: debug messages',
        ];
    }    
    
    /**
     * Returns priority as string.
     * @return string
     */
    public function getPriorityAsString()
    {
        $list = self::getPriorityList();
        if (isset($list[$this->priority]))
            return $list[$this->priority];
        
        return 'Unknown';
    }    
    
    /**
     * Sets priority.
     * @param int $priority     
     */
    public function setPriority($priority) 
    {
        $this->priority = $priority;
    }   

    /**
     * Returns the user.
     * @return User     
     */
    public function getUser() 
    {
        return $this->user;
    }
        
}
