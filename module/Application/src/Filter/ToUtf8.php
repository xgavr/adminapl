<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Zend\Filter\AbstractFilter;

/**
 * Декодирует строку в utf-8
 *
 * @author Daddy
 */
class ToUtf8 extends AbstractFilter
{
    
    // Доступные опции фильтра.
    protected $options = [
    ];    

    // Конструктор.
    public function __construct($options = null) 
    {     
        // Задает опции фильтра (если они предоставлены).
        if(is_array($options)) {
            if(isset($options['format'])){
            }
        }    
    }
    
    public function filter($value)
    {
        if (mb_detect_encoding($value, 'UTF-8', true) == 'UTF-8'){
            return $value;
        } else {        
            return iconv('Windows-1251', 'UTF-8', $value);
        }    
    }
    
}
