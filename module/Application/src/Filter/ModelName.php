<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Zend\Filter\AbstractFilter;

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
    
    public function filter($value)
    {
        $result = preg_replace("/[^ a-zA-Z0-9]/u","", preg_replace('/\(.*?\)/', '', $value));
        
        return trim($result);
    }
    
}
