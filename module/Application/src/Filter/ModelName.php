<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Laminas\Filter\AbstractFilter;

/**
 * Приводит название модели машины нужному виду
 * 
 *
 * @author Daddy
 */
class ModelName extends AbstractFilter
{
    
    // Доступные опции фильтра.
    protected $options = [
    ];    

    // Конструктор.
    public function __construct($options = null) 
    {     
        // Задает опции фильтра (если они предоставлены).
        if(is_array($options)) {
            $this->options = $options;
        }    
    }
    
    public function _explodeWord($str, $separator)
    {
        $words = explode($separator, $str);
        $result = [];
        foreach ($words as $word){
            if (strpos($word, '-')){
                $result[] = $this->_explodeWord($word, '-');
            } elseif (strpos($word, '/')){
                $result[] = $this->_explodeWord($word, '/');
            } else {
                if (strlen($word) > 3 && !in_array($word, ['VIII', 'VIIII'])){
                    $result[] = ucfirst(strtolower($word));
                } else {
                    $result[] = $word; 
                }
            }    
        }    
        return trim(implode($separator, $result));
    }
    
    public function filter($value)
    {
        if (isset($this->options['body'])){
            $result = str_replace($this->options['body'], '', $value);
        } else {
            $result = $value;
        }
        $result = str_replace(' c ', ' с ', $result);
        $result = preg_replace('/[^a-zA-Z0-9 \-\+\/]/u', '', preg_replace('/\(.*?$/', '', $result));
        
        return $this->_explodeWord($result, ' ');
    }
    
}
