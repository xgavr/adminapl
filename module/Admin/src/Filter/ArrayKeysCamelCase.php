<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Filter;

use Laminas\Filter\AbstractFilter;
use Laminas\Filter\Word\CamelCaseToUnderscore;

/**
 * Преобразует ключи массива CamelCase к camel_case 
 *
 * @author Daddy
 */
class ArrayKeysCamelCase extends AbstractFilter
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
        $result = []; 
        $filter = new CamelCaseToUnderscore();
        
        foreach ($value as $key => $value){
            $row = $value;
            if (is_array($value)){
                $row = $this->filter($value);
            }
            $result[strtolower($filter->filter($key))] = $row;            
        }
        
//        var_dump($result);
        return $result;
    }
    
}
