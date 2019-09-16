<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Zend\Filter\AbstractFilter;

/**
 * Разбивает предложение товара на токены
 * 
 *
 * @author Daddy
 */
class Tokenizer extends AbstractFilter
{
    
    // Доступные опции фильтра.
    protected $options = [
    ];  
    
    protected $searchReplace = ['(', ')', '_', '/', '\\'];
    
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
        $text = str_replace($this->searchReplace, ' ', $value);        
        
        //$words = preg_split('/[\pZ\pC]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
//        $result = preg_split('/[^А-ЯЁA-Z0-9.,->]/u', mb_strtoupper($text, 'utf-8'), -1, PREG_SPLIT_NO_EMPTY);
//        if ($result === false) {
//            throw new InvalidArgumentException('preg_split failed on: '.$text);
//        }

        $tokens = [];
        preg_match_all('/\w+/u', $text, $tokens);
//        var_dump($tokens);
        return $tokens[0];
    }
    
}
