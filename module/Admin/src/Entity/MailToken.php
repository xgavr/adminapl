<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Laminas\Config\Config;

/**
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Admin\Repository\PostLogRepository")
 * @ORM\Table(name="mail_token")
 * @author Daddy
 */
class MailToken {
    
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
    const MY_DICT_FILE = './data/dict/mail_dict.php'; //путь к локальному словарю
    const MY_BLACK_LIST = './data/dict/mail_black_list.php'; //путь к черному списку
    const MY_GRAY_LIST = './data/dict/mail_gray_list.php'; //путь к серому списку
    
    const PART_ADJECTIVE = 1; //прилагательное
    const PART_PARTICIPLE = 2; //причастие
    const PART_VERB = 3; //глагол
    const PART_NOUN = 4; //существительное
    const PART_ADVERB = 5; //наречие
    const PART_NUMERAL = 6; //числительное
    const PART_UNION = 7; //союз
    const PART_PREPOSITION = 8; //предлог
    const PART_UNKNOWN = 99; //неизвестно

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
    protected $idf = 0;

    /**
     * @ORM\Column(name="gf")   
     */
    protected $gf = 0;

    /**
     * @ORM\Column(name="correct")   
     */
    protected $correct;

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
    * @ORM\OneToMany(targetEntity="Admin\Entity\MailPostToken", mappedBy="mailToken")
    * @ORM\JoinColumn(name="mail_token_id", referencedColumnName="id")
     */
    private $mailPostTokens;

    /**
     * @ORM\ManyToMany(targetEntity="Admin\Entity\MailTokenGroup")
     * @ORM\JoinTable(name="mail_token_group_token",
     *      joinColumns={@ORM\JoinColumn(name="mail_token_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="mail_token_group_id", referencedColumnName="id")}
     *      )
     */
    private $mailTokenGroups;
    

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
            $this->correct = mb_strtoupper(mb_strcut(trim($str), 0, 64, 'UTF-8'));
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
     * Returns the array of mail post tokens assigned to this token.
     * @return array
     */
    public function getMailPostTokens()
    {
        return $this->mailPostTokens;
    }        

    /**
     * Returns the array of mailTokenGroups assigned to this oemRaw.
     * @return array
     */
    public function getMailTokenGroups()
    {
        return $this->mailTokenGroups;
    }        
    
    public function wordInMyDict($word)
    {
        if (file_exists(self::MY_DICT_FILE)){
            $dict = new Config(include self::MY_DICT_FILE, true);
            return $dict->get($word) !== null;            
        }        
        
        return false;
    }
    
    public function fromMyDict()
    {
        return $this->wordFromMyDict($this->lemma);
    }

    public function fromMyDictAsArray()
    {
        return explode(' ', $this->wordFromMyDict($this->lemma));
    }

    public function wordFromMyDict($word)
    {
        if (file_exists(self::MY_DICT_FILE)){
            $dict = new Config(include self::MY_DICT_FILE, true);
            return $dict->get($word);            
        }                
        return;
    }
    
    public function inMyDict()
    {
        return $this->wordInMyDict($this->getLemma());
    }

    public function wordInBlackList($word)
    {
        if (file_exists(self::MY_BLACK_LIST)){
            $dict = new Config(include self::MY_BLACK_LIST, true);
            return $dict->get($word) !== null;            
        }        
        
        return false;
    }
    
    public function inBlackList()
    {
        return $this->wordInBlackList($this->getLemma());
    }

    public function wordInGrayList($word)
    {
        if (file_exists(self::MY_GRAY_LIST)){
            $dict = new Config(include self::MY_GRAY_LIST, true);
            return $dict->get($word) !== null;            
        }        
        
        return false;
    }
    
    public function inGrayList()
    {
        return $this->wordInGrayList($this->getLemma());
    }
}
