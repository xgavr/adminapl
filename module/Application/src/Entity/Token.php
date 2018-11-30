<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Application\Repository\TokenRepository")
 * @ORM\Table(name="token")
 * @author Daddy
 */
class Token {
    
    const IS_DICT      = 1; // RU
    const IS_RU        = 2; // RU, не в словаре
    const IS_RU_1      = 3; // RU, 1 буква
    const IS_RU_ABBR   = 4; // RU, аббревиатура

    const IS_EN_DICT   = 11; // EN из словаря
    const IS_EN        = 12; // EN
    const IS_EN_1      = 13; // EN, 1 буква
    const IS_EN_ABBR   = 14; // EN, аббревиатура

    const IS_NUMERIC   = 21; // число
    const IS_UNKNOWN   = 99; // слово неизвестно словарю

    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="lemma")   
     */
    protected $lemma;
    
    /**
     * @ORM\Column(name="status")  
     */
    protected $status = self::IS_UNKNOWN;        

     /**
     * @ORM\ManyToMany(targetEntity="Application\Entity\Rawprice")
     * @ORM\JoinTable(name="rawprice_token",
     *      joinColumns={@ORM\JoinColumn(name="token_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="rawprice_id", referencedColumnName="id")}
     *      )
     */
    private $rawprice;  
    

    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getLemma() 
    {
        return $this->lemma;
    }

    public function setLemma($lemma) 
    {
        $this->lemma = mb_strcut(trim($lemma), 0, 64, 'UTF-8');
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
            self::IS_DICT => 'RU словарь',
            self::IS_RU => 'RU',
            self::IS_RU_1 => 'RU, 1 буква',
            self::IS_RU_ABBR => 'RU, аббревиатура',
            
            self::IS_EN_DICT => 'EN словарь',
            self::IS_EN => 'EN',
            self::IS_EN_1 => 'EN, 1 буква',
            self::IS_ABBR => 'EN, аббревиатура',
            
            self::IS_NUMERIC => 'Число',
            self::STATUS_UNKNOWN => 'Неизвестно',
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
    
    public function getStatusName($status)
    {
        $list = self::getStatusList();
        if (isset($list[$status]))
            return $list[$status];
        
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
     * Returns the array of rawprice assigned to this oemRaw.
     * @return array
     */
    public function getRawprice()
    {
        return $this->rawprice;
    }        
}
