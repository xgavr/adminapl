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
        'graminfo_as_text' => true,
    ];

    protected $dictsPath = 'vendor/cijic/phpmorphy/libs/phpmorphy/dicts';

    // Конструктор.
    public function __construct($options = null) 
    {     
        // Задает опции фильтра (если они предоставлены).
        if(is_array($options)) {
            $this->options = $options;
        }    
    }
    
    public function filter($value)
    {
        
        if(function_exists('iconv')) {
            foreach($value as &$word) {
                $word = mb_strtoupper($word, 'utf-8');        
            }
            unset($word);
        }        
        
        $morphyRU = new phpMorphy($this->dictsPath, 'ru_RU', $this->options);
        $morphyEN = new phpMorphy($this->dictsPath, 'en_EN', $this->options);
        
        $result = [
            Token::IS_DICT => [], //ru словарь
            Token::IS_RU => [], //ru не словарь
            Token::IS_RU_1 => [], //ru 1 буква
            Token::IS_RU_ABBR => [], //ru аббревиатура
            Token::IS_EN_DICT => [], //en словарь
            Token::IS_EN => [], //en 
            Token::IS_EN_1 => [], //en 1 
            Token::IS_EN_ABBR => [], //en abbr 
            Token::IS_NUMERIC => [], //число 
            Token::IS_UNKNOWN => [], //нечто 
        ];
        
        foreach ($value as $word){
            
            $ruWord = mb_ereg_replace('[^А-ЯЁ]', '', $word);
            $enWord = mb_ereg_replace('[^A-Z]', '', $word);
            $nuWord = mb_ereg_replace('[^0-9]', '', $word);
            $unWord = mb_ereg_replace('[A-ZА-ЯЁ0-9]', '', $word);
            
            if (is_numeric($unWord)){
                $result[Token::IS_UNKNOWN][] = $unWord;
            }
            
            if (is_numeric($nuWord)){
                $result[Token::IS_NUMERIC][] = $nuWord;
            }
            
            if ($ruWord){
                
                if (mb_strlen($ruWord, 'utf-8') === 1){
                    $result[Token::IS_RU_1][] = $ruWord;
                } else {
                
                    $collectionRU = $morphyRU->findWord($ruWord, phpMorphy::IGNORE_PREDICT);

                    if (false === $collectionRU) {
                        $result[Token::IS_RU][] = $ruWord;                    
                    } else {
                        foreach($collectionRU as $paradigm) {                
                            $result[Token::IS_DICT][] = $paradigm->getBaseForm();
                        }
                    }
                }    
            }    
            if ($enWord){
                
                if (mb_strlen($enWord, 'utf-8') === 1){
                    $result[Token::IS_EN_1][] = $enWord;
                } else {
                
                    $collectionEN = $morphyEN->findWord($enWord, phpMorphy::IGNORE_PREDICT);

                    if (false === $collectionEN) {
                        $result[Token::IS_EN][] = $enWord;                    
                    } else {
                        foreach($collectionEN as $paradigm) {                
                            $result[Token::IS_EN_DICT][] = $paradigm->getBaseForm();
                        }
                    }
                }    
            }    
        }    
        
        return $result;
    }
    
}
