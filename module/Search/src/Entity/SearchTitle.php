<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Search\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of SearchTitle
 * @ORM\Entity(repositoryClass="\Search\Repository\SearchRepository")
 * @ORM\Table(name="search_title")
 * @author Daddy
 */
class SearchTitle {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="title")  
     */
    protected $title;

    /**
     * @ORM\Column(name="title_md5")  
     */
    protected $titleMd5;
    
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
    
    /**
     * Возвращает форматированную строку поиска 
     * @param string $searchStr
     */
    public static function titleStr($searchStr)
    {
        return mb_strtoupper(trim($searchStr), 'UTF-8');
    }

    /**
     * Возвращает строку поиска в формате md5 
     * @param string $searchStr
     */
    public static function titleStrMd5($searchStr)
    {
        return md5($this->titleStr($searchStr));
    }

    public function setTitle($title)
    {
        $this->title = $this->titleStr($title);
        $this->titleMd5 = $this->titleMd5($title);
    }
    
    public function getTitle()
    {
        return $this->title;
    }

    public function getTitleMd5()
    {
        return $this->titleMd5;
    }
        
    public function getDateCreated() {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated) {
        $this->dateCreated = $dateCreated;
        return $this;
    }
}
