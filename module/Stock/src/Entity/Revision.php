<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Entity;

use Doctrine\ORM\Mapping as ORM;
use User\Entity\User;
use Stock\Entity\Mutual;


/**
 * Description of Revision
 * @ORM\Entity(repositoryClass="\Stock\Repository\RevisionRepository")
 * @ORM\Table(name="revision")
 * @author Daddy
 */
class Revision {
    

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="doc_key")   
     */
    protected $docKey;
    
    /**
     * @ORM\Column(name="doc_type")   
     */
    protected $docType;
    
    /**
     * @ORM\Column(name="doc_id")   
     */
    protected $docId;

    /**
     * @ORM\Column(name="doc_stamp")   
     */
    protected $docStamp;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;
    
    /** 
     * @ORM\Column(name="amount")  
     */
    protected $amount;
        
    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="revisions") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;    
    
   /**
    * @ORM\OneToOne(targetEntity="Stock\Entity\Mutual", mappedBy="revision")
   */
   private $mutual;    

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

    public function setDocKey($docKey) 
    {
        $this->docKey = $docKey;
    }     

    public function getDocKey() 
    {
        return $this->docKey;
    }

    public function getDocType() 
    {
        return $this->docType;
    }

    public function setDocType($docType) 
    {
        $this->docType = $docType;
    }     

    public function getDocId() 
    {
        return $this->docId;
    }

    public function setDocId($docId) 
    {
        $this->docId = $docId;
    }     
    
    public function getDocStamp() 
    {
        return $this->docStamp;
    }

    public function setDocStamp($docStamp) 
    {
        $this->docStamp = $docStamp;
    }     
    
    /**
     * Returns the date of operation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * Sets the date when this oper was created.
     * @param string $dateCreated     
     */
    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
    }    
                
    /**
     * Sets  amount.
     * @param float $amount     
     */
    public function setAmount($amount) 
    {
        $this->amount = $amount;
    }    
    
    /**
     * Returns the amount of doc.
     * @return float     
     */
    public function getAmount() 
    {
        return $this->amount;
    }
    
    /**
     * 
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * 
     * @param User $user
     * @return $this
     */
    public function setUser($user) {
        $this->user = $user;
        return $this;
    }

    /**
     * 
     * @return Mutual
     */
    public function getMutual() {
        return $this->mutual;
    }
}
