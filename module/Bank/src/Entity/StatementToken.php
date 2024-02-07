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
use Application\Entity\Token;

/**
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Bank\Repository\BankRepository")
 * @ORM\Table(name="statement_token")
 * @author Daddy
 */
class StatementToken {
    

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
    protected $status = Token::IS_RU;        

    /**
     * @ORM\Column(name="frequency")  
     */
    protected $frequency = 9999;        

    /**
     * @ORM\ManyToMany(targetEntity="Bank\Entity\Statement")
     * @ORM\JoinTable(name="statement_token_token",
     *      joinColumns={@ORM\JoinColumn(name="statement_token_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="statement_id", referencedColumnName="id")}
     *      )
     */
    private $statements;    
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->statements = new ArrayCollection();
    }    

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
        return Token::getStatusList();
    }    

    /**
     * Returns use statuses as array.
     * @return array
     */
    public static function getUseStatusList() 
    {
        return [
            Token::IS_DICT,
            Token::IS_EN,
            Token::IS_EN_ABBR,
            Token::IS_EN_DICT,
            Token::IS_RU,
            Token::IS_RU_ABBR,
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
     * 
     * @return ArrayCollection
     */
    public function getStatements() {
        return $this->statements;
    }
    
}
