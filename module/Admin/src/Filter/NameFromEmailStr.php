<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Filter;

use Laminas\Filter\AbstractFilter;

/**
 * Получаем имя из строки почты
 *
 * @author Daddy
 */
class NameFromEmailStr extends AbstractFilter
{    
    
    // Доступные опции фильтра.
    protected $options = [
    ];    
    
    protected $delimetr = '<';
    
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
        $pos = strpos($value, $this->delimetr);
        if ($pos === false){
            return;
        }
        
        $names = explode($this->delimetr, $value);
        
        return trim($names[0]);
    }
    
}
