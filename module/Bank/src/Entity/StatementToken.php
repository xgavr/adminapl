<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bank\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Laminas\Config\Config;

/**
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Bank\Repository\BankRepository")
 * @ORM\Table(name="statement_token")
 * @author Daddy
 */
class StatementToken {
    

    const STATUS_WHITE_LIST   = 1; // белый список 
    const STATUS_GRAY_LIST    = 8; // серый список 
    const STATUS_BLACK_LIST   = 9; // черный список 
    
    const MIN_DF = 5; // минимальная частота
    const MAX_TOKENS_FOR_GROUP = 6; // максимальное количество токенов для группы
    const MIN_TFIDF_FOR_GROUP = 0.003; // максимальный tfidf для группы

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
     * @ORM\Column(name="idf")   
     */
    protected $idf;

    /**
     * @ORM\Column(name="correct")   
     */
    protected $correct;

    /**
     * @ORM\Column(name="status")  
     */
    protected $status = self::STATUS_WHITE_LIST;        

    /**
     * @ORM\Column(name="frequency")  
     */
    protected $frequency = 9999;        


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
        $this->lemma = mb_strcut(trim($lemma), 0, 256, 'UTF-8');
    }     
    
    public function getCorrect() 
    {
        return $this->correct;
    }
    
    public function getCorrectAsArray()
    {
        return explode(' ', $this->correct);
    }
        
    public function setCorrect($str) 
    {
        if ($str){
            $this->correct = mb_strtoupper(mb_strcut(trim($str), 0, 256, 'UTF-8'));
        } else {
            $this->correct = null;
        }    
    }     
    
    public function setIdf($idf)
    {
        $this->idf = $idf;
    }
    
    public function getIdf()
    {
        return $this->idf;
    }

    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
    }
    
    public function getFrequency()
    {
        return $this->frequency;
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
            self::STATUS_WHITE_LIST => 'Белый список',
            self::STATUS_GRAY_LIST  => 'Серый список',
            self::STATUS_BLACK_LIST => 'Черный список',
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
}
