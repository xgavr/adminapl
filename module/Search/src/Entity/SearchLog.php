<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Search\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of SearchLog
 * @ORM\Entity(repositoryClass="\Search\Repository\SearchRepository")
 * @ORM\Table(name="search_log")
 * @author Daddy
 */
class SearchLog {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="content")  
     */
    protected $content;

    /**
     * @ORM\Column(name="ip_address")  
     */
    protected $ipAddress;

    /**
     * @ORM\Column(name="device")  
     */
    protected $device;

    /**
     * @ORM\Column(name="found")  
     */
    protected $found;
    
    /**
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;
    
    /**
     * @ORM\ManyToOne(targetEntity="Search\Entity\SearchTitle", inversedBy="searchLogs") 
     * @ORM\JoinColumn(name="search_title_id", referencedColumnName="id")
     */
    protected $searchTitle;        

    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     
    
    /**
     * Строка запроса
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
    
    /**
     * 
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
    
    public function getIpAddress() {
        return $this->ipAddress;
    }

    public function getDevice() {
        return $this->device;
    }

    public function setIpAddress($ipAddress) {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function setDevice($device) {
        $this->device = $device;
        return $this;
    }
    
    public function getFound() {
        return $this->found;
    }

    public function setFound($found) {
        $this->found = $found;
        return $this;
    }
    
    public function getDateCreated() {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated) {
        $this->dateCreated = $dateCreated;
        return $this;
    }
    
    public function getSearchTitle() {
        return $this->searchTitle;
    }

    public function setSearchTitle($searchTitle) {
        $this->searchTitle = $searchTitle;
        return $this;
    }
}
