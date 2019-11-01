<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Zend\Filter\AbstractFilter;
use Application\Entity\Token;
use Application\Entity\Bigram;
use Application\Validator\IsRU;
use Application\Validator\IsEN;
use Application\Validator\IsNum;
use phpMorphy;

/**
 * Вспомогательный класс
 */
class myDict
{
    protected $wordForm;

    function __construct($word)
    {
        $this->wordForm = $word;
    }
    
    public function getBaseForm()
    {
        return $this->wordForm;
    }
}

/**
 * Получить базовую форму слов в предложении
 * 
 *
 * @author Daddy
 */
class Lemma extends AbstractFilter
{
    
    // Доступные опции фильтра.
    protected $options = [
        // storage type, follow types supported
        // PHPMORPHY_STORAGE_FILE - use file operations(fread, fseek) for dictionary access, this is very slow...
        // PHPMORPHY_STORAGE_SHM - load dictionary in shared memory(using shmop php extension), this is preferred mode
        // PHPMORPHY_STORAGE_MEM - load dict to memory each time when phpMorphy intialized, this useful when shmop ext. not activated. Speed same as for PHPMORPHY_STORAGE_SHM type
        //'storage' => PHPMORPHY_STORAGE_FILE,
        // Enable prediction by suffix
        'predict_by_suffix' => true, 
        // Enable prediction by prefix
        'predict_by_db' => true,
        // TODO: comment this
        'graminfo_as_text' => false,
    ];

    protected $dictsPath = 'vendor/cijic/phpmorphy/libs/phpmorphy/dicts';
    
    protected $myDict;
    
    private $entityManager;
    
    // Конструктор.
    public function __construct($entityManager, $options = null) 
    {     
        // Задает опции фильтра (если они предоставлены).
        if(is_array($options)) {
            $this->options = $options;
        }    
        
        if (file_exists(Token::MY_DICT_FILE)){
            //$this->myDict = new Config(include Token::MY_DICT_FILE, true);
        }    
        
        $this->entityManager = $entityManager;
    }
    
    /**
     * Проверить слово на корректировку
     * 
     * @param string $word
     * @return \Application\Filter\myDict|bool
     */
    protected function correctWord($word)
    {
        $token = $this->entityManager->getRepository(Token::class)
                ->findOneByLemma($word);
        if ($token){
            if ($token->getCorrect()){
                $result = [];
                $lemms = $token->getCorrectAsArray();
                foreach ($lemms as $lemma){
                  $paradigm = new myDict($lemma);
                  $result[] = $paradigm;
                }          
                return $result;
            }
        }                     
        
        return false;
    }            
    
    /**
     * Поиск слова в словарях
     * 
     * @param string $word
     * @param phpMorphy $morphy
     * @return phpMorphy_WordDescriptor_Collection|array|bool
     */
    protected function _searchWord($word, $morphy)
    {
        $collection = $morphy->findWord($word, phpMorphy::IGNORE_PREDICT);               
        
        if (false === $collection) {
            return $this->correctWord($word);
        } else {
            $result = [];
            foreach($collection as $paradigm) {
                $correct = $this->correctWord($paradigm->getBaseForm());
                if (FALSE === $correct){
                    $result[] = $paradigm;
                    return $result;
                }
                return $correct;
            }    
        }
        return $collection;
    }
    
    /**
     * Получить статус леммы
     * @param string $lemma
     * @return integer 
     */
    protected function _lemmaStatus($lemma)
    {
        $isRu = new IsRU();
        $isEn = new IsEN();
        $isNum = new IsNum();
        
        if ($isRu->isValid($lemma)){
            return Token::IS_DICT;
        }
        if ($isEn->isValid($lemma)){
            return Token::IS_EN_DICT;
        }
        if ($isNum->isValid($lemma)){
            return Token::IS_NUMERIC;
        }
        
        return Token::IS_UNKNOWN;
    }
    
    /**
     * Корректировка биграмами
     * 
     * @param string $lemms
     * @return array
     */
    protected function _correctBigram($lemms)
    {
//        var_dump($lemms);
        $result = [];
        $preWord = null;
        foreach ($lemms as $k => $words){
            foreach ($words as $key => $word){
                $result[$k][$key] = $word;
                if ($k > 0){
                    $bigram = $this->entityManager->getRepository(Bigram::class)
                                    ->findBigram($preWord, $word, false);
                    if ($bigram){
//                        var_dump($bigram->getCorrect());
                        if ($bigram->getCorrect()){
                            $bilemma = $bigram->getCorrectAsArray();
                            unset($result[$k-1]);
                            unset($result[$k]);
                            
                            $result[$k-1][$this->_lemmaStatus($bilemma[0])] = $bilemma[0];
                            if (isset($bilemma[1])){
                                $result[$k][$this->_lemmaStatus($bilemma[1])] = $bilemma[1];                                
                                $word = $bilemma[1];
                            }
                        }
                    }
                }
                $preWord = $word;
            }
        }    
        ksort($result);
//        var_dump($result); exit;
        return $result;
    }
    
    public function filter($value)
    {
        if(function_exists('iconv')) {
            foreach($value as &$word) {
                $word = mb_strtoupper($word, 'utf-8');        
            }
            unset($word);
        }        
//
        $result = [];
        
        
        $morphyRU = new phpMorphy($this->dictsPath, 'ru_RU', $this->options);
        $morphyEN = new phpMorphy($this->dictsPath, 'en_EN', $this->options);
        $i = 0;
        
        foreach ($value as $word){
            
            $ruWord = mb_ereg_replace('[^А-ЯЁ]', '', $word);
            $enWord = mb_ereg_replace('[^A-Z]', '', $word);
            $nuWord = mb_ereg_replace('[^0-9]', '', $word);
            $unWord = mb_ereg_replace('[A-ZА-ЯЁ0-9]', '', $word);
            
            if (is_numeric($unWord)){
                $result[$i][Token::IS_UNKNOWN] = $unWord;
                $i++;
            }
            
            if (is_numeric($nuWord)){
                $result[$i][Token::IS_NUMERIC] = $nuWord;
                $i++;
            }
            
            if ($ruWord){                
                if (mb_strlen($ruWord, 'utf-8') === 1){
                    $result[$i][Token::IS_RU_1] = $ruWord;
                    $i++;
                } else {
                
                    $collectionRU = $this->_searchWord($ruWord, $morphyRU);

                    if (false === $collectionRU) {
                        $result[$i][Token::IS_RU] = $ruWord;                    
                        $i++;
                    } else {
                        foreach($collectionRU as $paradigm) {         
                            $result[$i][Token::IS_DICT] = $paradigm->getBaseForm();
                            $i++;
                        }
                    }
                }    
            }    
            if ($enWord){                
                if (mb_strlen($enWord, 'utf-8') === 1){
                    $result[$i][Token::IS_EN_1] = $enWord;
                    $i++;
                } else {
                
                    $collectionEN = $this->_searchWord($enWord, $morphyEN);

                    if (false === $collectionEN) {
                        $result[$i][Token::IS_EN] = $enWord;                    
                        $i++;
                    } else {
                        foreach($collectionEN as $paradigm) {                
                            $result[$i][Token::IS_EN_DICT] = $paradigm->getBaseForm();
                            $i++;
                        }
                    }
                }    
            }             
        }    
        ksort($result);
        $result = $this->_correctBigram($result);
        return $result;
    }
    
}
