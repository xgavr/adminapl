<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Zend\Filter\AbstractFilter;
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
        
        $lang = 'ru_RU';
        
        if(function_exists('iconv')) {
            foreach($value as &$word) {
                $word = mb_strtoupper($word, 'utf-8');        
            }
            unset($word);
        }        
        
        $morphy = new phpMorphy($this->dictsPath, $lang, $this->options);
        
        $result = [
            0 => [],
            1 => [],
        ];
        
        foreach ($value as $word){
            $collection = $morphy->findWord($word);

            if (false === $collection) {
                $result[0][] = $word;
                continue;
            }

            foreach($collection as $paradigm) {                
                $result[1][] = $paradigm->getBaseForm();
            }
        }    
        
        return $result;
    }
    
}
