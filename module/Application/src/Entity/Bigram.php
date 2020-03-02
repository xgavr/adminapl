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
 * @ORM\Entity(repositoryClass="\Application\Repository\BigramRepository")
 * @ORM\Table(name="bigram")
 * @author Daddy
 */
class Bigram {
    
    const RU_RU       = 10; // RU
    const RU_EN       = 20; // RU + EN
    const RU_NUM      = 30; // RU + NUM
    const EN_EN       = 40; // RU + NUM
    const EN_NUM      = 50; // RU + NUM
    const NUM_NUM     = 60; // RU + NUM

    const WHITE_LIST   = 1; // белый список 
    const GRAY_LIST    = 8; // серый список 
    const BLACK_LIST   = 9; // черный список 
    
    const MIN_FREQUENCY = 10; //минимальная частота
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="bilemma")   
     */
    protected $bilemma;
    
    /**
     * @ORM\Column(name="bilemma_md5")   
     */
    protected $bilemmaMd5;
    
    /**
     * @ORM\Column(name="correct")   
     */
    protected $correct;

    /**
     * @ORM\Column(name="status")  
     */
    protected $status;        

    /**
     * @ORM\Column(name="flag")  
     */
    protected $flag = self::WHITE_LIST;        

    /**
     * @ORM\Column(name="frequency")  
     */
    protected $frequency = -1;        

    /**
     * @ORM\Column(name="idf")   
     */
    protected $idf;

    /**
     * @ORM\Column(name="gf")   
     */
    protected $gf;

    /**
     * @ORM\ManyToMany(targetEntity="Application\Entity\TokenGroup")
     * @ORM\JoinTable(name="token_group_bigram",
     *      joinColumns={@ORM\JoinColumn(name="bigram_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="token_group_id", referencedColumnName="id")}
     *      )
     */
    private $tokenGroups;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\ArticleBigram", mappedBy="bigram")
    * @ORM\JoinColumn(name="id", referencedColumnName="bigram_id")
     */
    private $articleBigrams;
    

    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getBilemma() 
    {
        return $this->bilemma;
    }
    
    public function setBilemma($bilemma) 
    {
        $this->bilemma = $bilemma;
        $this->bilemmaMd5 = md5($bilemma);
    }     
    
    public function getBilemmaAsArray()
    {
        return explode(' ', $this->bilemma);
    }
        
    public function getBilemmaMd5() 
    {
        return $this->bilemmaMd5;
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

    public function setGf($gf)
    {
        $this->gf = $gf;
    }
    
    public function getGf()
    {
        return $this->gf;
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
            self::RU_RU => 'RU+RU',
            self::RU_EN => 'RU+EN',
            self::RU_NUM => 'RU+NUM',
            self::EN_EN => 'EN+EN',
            self::EN_NUM => 'EN+NUM',
            self::NUM_NUM => 'NUM+NUM',
        ];
    }    
    
    /**
     * Returns user status as string.
     * @return string
     */
    public function getStatusAsString()
    {
        $list = self::getStatusList();
        if (isset($list[$this->status])) {
            return $list[$this->status];
        }

        return 'Unknown';
    }  
    
    public function getStatusName($status)
    {
        $list = self::getStatusList();
        if (isset($list[$status])) {
            return $list[$status];
        }

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
     * Returns flag.
     * @return int     
     */
    public function getFlag() 
    {
        return $this->flag;
    }

    /**
     * Returns possible flags as array.
     * @return array
     */
    public static function getFlagList() 
    {
        return [
            self::WHITE_LIST => 'Белый список',
            self::GRAY_LIST  => 'Серый список',
            self::BLACK_LIST => 'Черный список',
        ];
    }    
    
    /**
     * Returns lemma flag as string.
     * @return string
     */
    public function getFlagAsString()
    {
        $list = self::getFlagList();
        if (isset($list[$this->flag])) {
            return $list[$this->flag];
        }

        return 'Unknown';
    }  
    
    public function getFlagName($flag)
    {
        $list = self::getFlagList();
        if (isset($list[$flag]))
            return $list[$flag];
        
        return 'Unknown';        
    }

    /**
     * Sets flag.
     * @param int $flag     
     */
    public function setFlag($flag) 
    {
        $this->flag = $flag;
    }           
    
    /**
     * Returns the array of tokenGroups assigned to this oemRaw.
     * @return array
     */
    public function getTokenGroups()
    {
        return $this->tokenGroups;
    }        
    
    /**
     * Returns the array of article bigrams assigned to this bigram.
     * @return array
     */
    public function getArticleBigrams()
    {
        return $this->articleBigrams;
    }        
    
}
