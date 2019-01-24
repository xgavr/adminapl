<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Zend\Config\Config;

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
    
    const IS_PRODUCER  = 31; // производитель    
    const IS_ARTICLE   = 32; // артикул производителя    
    
    const IS_UNKNOWN   = 99; // слово неизвестно словарю

    const WHITE_LIST   = 1; // белый список 
    const GRAY_LIST    = 8; // серый список 
    const BLACK_LIST   = 9; // черный список 
    
    const MY_DICT_PATH = './data/dict/'; //путь к локальному словарю
    const MY_DICT_FILE = './data/dict/my_dict.php'; //путь к локальному словарю
    
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
     * @ORM\Column(name="flag")  
     */
    protected $flag = self::WHITE_LIST;        

    /**
     * @ORM\Column(name="frequency")  
     */
    protected $frequency = 9999;        

    /**
     * @ORM\ManyToMany(targetEntity="Application\Entity\Rawprice")
     * @ORM\JoinTable(name="rawprice_token",
     *      joinColumns={@ORM\JoinColumn(name="token_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="rawprice_id", referencedColumnName="id")}
     *      )
     */
    private $rawprice;  
    
     /**
    * @ORM\OneToMany(targetEntity="Application\Entity\ArticleToken", mappedBy="lemma")
    * @ORM\JoinColumn(name="lemma", referencedColumnName="lemma")
     */
    private $articleTokens;

    /**
     * @ORM\ManyToMany(targetEntity="Application\Entity\TokenGroup")
     * @ORM\JoinTable(name="token_group_token",
     *      joinColumns={@ORM\JoinColumn(name="token_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="token_group_id", referencedColumnName="id")}
     *      )
     */
    private $tokenGroups;
    

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

    public function setLemma($lemma) 
    {
        $this->lemma = mb_strcut(trim($lemma), 0, 64, 'UTF-8');
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
            self::IS_DICT => 'RU словарь',
            self::IS_RU => 'RU',
            self::IS_RU_1 => 'RU, 1 буква',
            self::IS_RU_ABBR => 'RU, аббревиатура',
            
            self::IS_EN_DICT => 'EN словарь',
            self::IS_EN => 'EN',
            self::IS_EN_1 => 'EN, 1 буква',
            self::IS_EN_ABBR => 'EN, аббревиатура',
            
            self::IS_NUMERIC => 'Число',

            self::IS_PRODUCER => 'Производитель',
            self::IS_ARTICLE => 'Артикул',

            self::IS_UNKNOWN => 'Неизвестно',
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
        if (isset($list[$this->flag]))
            return $list[$this->flag];
        
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
     * Returns the array of rawprice assigned to this oemRaw.
     * @return array
     */
    public function getRawprice()
    {
        return $this->rawprice;
    }        

    /**
     * Returns the array of article tokens assigned to this token.
     * @return array
     */
    public function getArticleTokens()
    {
        return $this->articleTokens;
    }        

    /**
     * Returns the array of tokenGroups assigned to this oemRaw.
     * @return array
     */
    public function getTokenGroups()
    {
        return $this->tokenGroups;
    }        
    
    public function wordInMyDict($word)
    {
        if (file_exists(self::MY_DICT_FILE)){
            $dict = new Config(include self::MY_DICT_FILE, true);
            return $dict->get($word) !== null;            
        }        
        
        return false;
    }
    
    public function inMyDict()
    {
        return $this->wordInMyDict($this->getLemma());
    }
}
