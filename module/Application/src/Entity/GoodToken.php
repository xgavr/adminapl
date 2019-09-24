<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Application\Repository\TokenRepository")
 * @ORM\Table(name="good_token")
 * @author Daddy
 */
class GoodToken {
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
    protected $status;        

    /**
     * @ORM\Column(name="tf")  
     */
    protected $tf;        

    /**
     * @ORM\Column(name="tf_idf")  
     */
    protected $tfidf;        

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="goodTokens") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    protected $good;    
    

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

    public function isIntersectLemma()
    {
        if (is_numeric($this->lemma)){
            return false;
        }

        if (mb_strlen($this->lemma, 'utf-8') < 4){
            return false;
        }        
        
        return true;
    }

    public function getStatus() 
    {
        return $this->status;
    }

    public function setStatus($status) 
    {
        $this->status = $status;
    }     

    public function getTf() 
    {
        return $this->tf;
    }

    public function setTf($tf) 
    {
        $this->tf = $tf;
    }     

    public function getTfIdf() 
    {
        return $this->tfidf;
    }

    public function setTfIdf($tfidf) 
    {
        $this->tfidf = $tfidf;
    }     

    /**
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
        
}
