<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Zend\Filter\AbstractFilter;
use Application\Entity\Token;
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
     * Все слова должны быть из словаря, 
     * 
     * @param string $word
     * @param phpMorphy $morphy
     */
    protected function predictWord($word, $morphy)
    {
//        $collection = $morphy->findWord($word, phpMorphy::IGNORE_PREDICT);
        $collection = $this->_searchWord($word, $morphy);
        if (false !== $collection){
            $predicts = [$word];        
            return $predicts;
        }
        
        $wordPredict = $word;
        while (mb_strlen($wordPredict) > 3){
            $wordLen = mb_strlen($wordPredict);
            $wordPredict = mb_substr($wordPredict, 0, $wordLen-1);
//            $collection = $morphy->findWord($wordPredict, phpMorphy::IGNORE_PREDICT);
            $collection = $this->_searchWord($wordPredict, $morphy);
            if (false !== $collection){
                $predicts[] = $wordPredict;
                return array_merge($predicts, $this->predictWord(str_replace($wordPredict, '', $word), $morphy));
            }
        }
        
        if (mb_strlen($word) > 3){
            $wordPredict = $word;
            while (mb_strlen($wordPredict) > 3){
                $wordPredict = mb_substr($wordPredict, 1);
//                var_dump($wordPredict);
                return $this->predictWord($wordPredict, $morphy);
//                $collection = $morphy->findWord($wordPredict, phpMorphy::IGNORE_PREDICT);
//                if (false !== $collection){
//                    $predicts[] = $wordPredict;
//                    return array_merge($predicts, $this->predictWord(str_replace($wordPredict, '', $word), $morphy));
//                }
            }    
        }
        
        return [$word];
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
//        $result = [
//            Token::IS_DICT => [], //ru словарь
//            Token::IS_RU => [], //ru не словарь
//            Token::IS_RU_1 => [], //ru 1 буква
//            Token::IS_RU_ABBR => [], //ru аббревиатура
//            Token::IS_EN_DICT => [], //en словарь
//            Token::IS_EN => [], //en 
//            Token::IS_EN_1 => [], //en 1 
//            Token::IS_EN_ABBR => [], //en abbr 
//            Token::IS_NUMERIC => [], //число 
//            Token::IS_PRODUCER => [], //производитель 
//            Token::IS_ARTICLE => [], //артикул 
//            Token::IS_UNKNOWN => [], //нечто 
//        ];
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
        return $result;
    }
    
}
