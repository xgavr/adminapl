<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Json\Decoder;

/**
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Application\Repository\ExternalRepository")
 * @ORM\Table(name="auto_db_response")
 * @author Daddy
 */
class AutoDbResponse {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="uri")  
     */
    protected $uri;

    /**
     * @ORM\Column(name="uri_md5")  
     */
    protected $uriMd5;
    
    /**
     * @ORM\Column(name="response")  
     */
    protected $response;

    /**
     * @ORM\Column(name="response_md5")  
     */
    protected $responseMd5;    
    
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

    public function setUri($uri)
    {
        $this->uri = mb_strtoupper(trim($uri), 'UTF-8');
        $this->uriMd5 = md5($this->uri);
    }
    
    public function getUri()
    {
        return $this->uri;
    }

    public function getUriMd5()
    {
        return $this->uriMd5;
    }
        
    public function setResponse($response)
    {
        $this->response = mb_strtoupper(trim($response), 'UTF-8');
        $this->responseMd5 = md5($this->response);
    }
    
    public function getResponse()
    {
        return $this->response;
    }

    public function getResponseMd5()
    {
        return $this->responseMd5;
    }
    
    public function getResponseAsArray()
    {
        if ($this->response){
            return Decoder::decode($this->response, \Zend\Json\Json::TYPE_ARRAY);
        }
        
        return;
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
    
}
