<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiMarketPlace\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Bid
 * @ORM\Entity(repositoryClass="\ApiMarketPlace\Repository\MarketplaceRepository")
 * @ORM\Table(name="marketplace")
 * @author Daddy
 */
class Marketplace {
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
     * @ORM\Column(name="comment")   
     */
    protected $comment;

    /**
     * @ORM\Column(name="status")   
     */
    protected $status;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;  
        
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getPrice() 
    {
        return $this->price;
    }

    public function setPrice($price) 
    {
        $this->price = $price;
    }     

    public function getNum() 
    {
        return $this->num;
    }

    public function setNum($num) 
    {
        $this->num = $num;
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
     * Возвращает связанный good.
     * @return \Application\Entity\Goods
     */
    
    public function getGood() 
    {
        return $this->good;
    }

    /**
     * Задает связанный good.
     * @param \Application\Entity\Goods $good
     */    
    public function setGood($good) 
    {
        $this->good = $good;
    }     
    
    /*
     * Возвращает связанный user.
     * @return \User\Entity\User
     */
    
    public function getUser() 
    {
        return $this->user;
    }

    /**
     * Задает связанный user.
     * @param \User\Entity\User $user
     */    
    public function setUser($user) 
    {
        $this->user = $user;
    }     
    
    /*
     * Возвращает связанный client.
     * @return \Application\Entity\Client
     */
    
    public function getClient() 
    {
        return $this->client;
    }

    /**
     * Задает связанный client.
     * @param \Application\Entity\Client $client
     */    
    public function setClient($client) 
    {
        $this->client = $client;
        $client->addCart($this);
    }     
    
}
