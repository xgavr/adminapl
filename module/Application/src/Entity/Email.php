<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Email
 * @ORM\Entity(repositoryClass="\Application\Repository\ContactRepository")
 * @ORM\Table(name="email")
 * @author Daddy
 */
class Email {
    
    const EMAIL_SUPPLIER = 1;
    const EMAIL_CLIENT = 2;
    const EMAIL_USER = 3;
    const EMAIL_OFFICE = 4;
    const EMAIL_UNKNOWN = 9;
    
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
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Contact", inversedBy="email") 
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    protected $contact;

    
    
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

    /**
     * Returns the date of user creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * Sets the date when this user was created.
     * @param string $dateCreated     
     */
    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
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
     * @param \Application\Entity\Supplier $contact
     */    
    public function setContact($contact) 
    {
        $this->contact = $contact;
        $contact->addEmail($this);
    }     
          
    /**
     * Возвращает тип адреса
     * @return int
     */
    public function getType()
    {
        if ($this->getContact()->getSupplier()){
            return self::EMAIL_SUPPLIER;
        }
        if ($this->getContact()->getClient()){
            return self::EMAIL_CLIENT;
        }
        if ($this->getContact()->getUser()){
            return self::EMAIL_USER;
        }
        if ($this->getContact()->getOffice()){
            return self::EMAIL_OFFICE;
        }        
        
        return self::EMAIL_UNKNOWN;
    }

    /**
     * Returns possible types as array.
     * @return array
     */
    public static function getTypeList() 
    {
        return [
            self::EMAIL_SUPPLIER => 'Поставщик',
            self::EMAIL_CLIENT => 'Клиент',
            self::EMAIL_USER => 'Сотрудник',
            self::EMAIL_OFFICE => 'Офис',
            self::EMAIL_UNKNOWN => 'Неизвестно',
        ];
    }    
    
    /**
     * Returns email types as string.
     * @return string
     */
    public function getTypeAsString()
    {
        $list = self::getTypeList();
        $type = $this->getType();
        if (isset($list[$type])){
            return $list[$type];
        }    
        
        return 'Unknown';
    }    

    /**
     * Returns email types as string.
     * @param int $type
     * @return string
     */
    public static function typeAsString($type)
    {
        $list = self::getTypeList();
        if (isset($list[$type])){
            return $list[$type];
        }    
        
        return 'Unknown';
    }    

}
