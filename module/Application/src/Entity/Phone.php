<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Application\Entity\Contact;
use User\Filter\PhoneFilter;
use User\Entity\User;
use Application\Entity\Client;
use Application\Entity\Supplier;
use Company\Entity\Office;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\OrderPhone;

/**
 * Description of Phone
 * @ORM\Entity(repositoryClass="\Application\Repository\ContactRepository")
 * @ORM\Table(name="phone")
 * @author Daddy
 */
class Phone {
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
     * @ORM\Column(name="comment")   
     */
    protected $comment;
    
    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Contact", inversedBy="phone") 
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    protected $contact;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\OrderPhone", mappedBy="phone")
    * @ORM\JoinColumn(name="id", referencedColumnName="phone_id")
     */
    private $orders;    

    public function __construct() 
    {
        $this->orders = new ArrayCollection();
    }    
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getName($format = PhoneFilter::PHONE_FORMAT_RU) 
    {
        $filter = new PhoneFilter();
        
        if ($format){
            $filter->setFormat($format);
        }
        
        return $filter->filter($this->name);
    }

    public function setName($name) 
    {
        $filter = new PhoneFilter();
        $filter->setFormat(PhoneFilter::PHONE_FORMAT_DB);
        $this->name = $filter->filter($name);
    }     

    public function getComment() 
    {
        return $this->comment;
    }

    public function setComment($comment) 
    {
        $this->comment = $comment;
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
     * @return Contact
     */    
    public function getContact() 
    {
        return $this->contact;
    }
    
    public function getContactLink()
    {
        return $this->contact->getParetnLink();
    }

    /**
     * Задает связанный contact.
     * @param Contact $contact
     */    
    public function setContact($contact) 
    {
        $this->contact = $contact;
        $contact->addPhone($this);
    }     
    
    /**
     * 
     * @return OrderPhone
     */
    public function getOrders() {
        return $this->orders;
    }

    /**
     * 
     * @param OrderPhone $orderPhone
     */
    public function addOrder($orderPhone)
    {
        $this->orders[] = $orderPhone;
    }
    
    /**
     * 
     * @return array
     */
    public function toLog()
    {
        return [
            'name' => $this->getName(),
            'comment' => $this->getComment(),
            'id' => $this->getId(),
            'contact' => $this->getContact()->getId(),
        ];
    }        
}
