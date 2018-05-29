<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Contact
 * @ORM\Entity(repositoryClass="\Application\Repository\ContactRepository")
 * @ORM\Table(name="messenger")
 * @author Daddy
 */
class Messenger {
    
    // Константы доступности.
    const STATUS_ACTIVE       = 1; // Active
    const STATUS_RETIRED      = 2; // Retired
    
    // Константы типов.
    const TYPE_ICQ            = 1;
    const TYPE_TELEGRAM       = 2;
    const TYPE_VIBER          = 3;
    const TYPE_WHATSAPP       = 4;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="ident")   
     */
    protected $ident;
    
    /**
     * @ORM\Column(name="status")   
     */
    protected $status;   
    
    /**
     * @ORM\Column(name="type")   
     */
    protected $type;   
    
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Contact", inversedBy="messengers") 
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    protected $contact;


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

    public function getIdent() 
    {
        return $this->ident;
    }

    public function setIdent($ident) 
    {
        $this->ident = $ident;
    }     

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
            self::STATUS_ACTIVE => 'Доступен',
            self::STATUS_RETIRED => 'Не доступен'
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
    

    public function setStatus($status) 
    {
        $this->status = $status;
    }
    
    public function getType() 
    {
        return $this->type;
    }
    
    /**
     * Returns possible types as array.
     * @return array
     */
    public static function getTypesList() 
    {
        return [
            self::TYPE_ICQ => 'ICQ',
            self::TYPE_TELEGRAM => 'Телеграм',
            self::TYPE_VIBER => 'Viber',
            self::TYPE_WHATSAPP => 'WhatsApp',
        ];
    }    
    
    /**
     * Returns user types as string.
     * @return string
     */
    public function getTypesAsString()
    {
        $list = self::getTypesList();
        if (isset($list[$this->type]))
            return $list[$this->type];
        
        return 'Unknown';
    }    
    

    public function setType($type) 
    {
        $this->type = $type;
    }
            

    /*
     * Возвращает связанный contact.
     * @return \Application\Entity\Contact
     */    
    public function getContact() 
    {
        return $this->contact;
    }

    /**
     * Задает связанный contact.
     * @param \Application\Entity\Contact $contact
     */    
    public function setContact($contact) 
    {
        $this->contact = $contact;
        $contact->addMessenger($this);
    }     
    
}
