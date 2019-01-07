<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Zend\Filter\AbstractFilter;

/**
 * Преобразование массива к строке нужного формата
 *
 * @author Daddy
 */
class IdsFormat extends AbstractFilter
{
    protected $separator;


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
        
        if (!isset($this->options['separator'])){
            $this->options['separator'] = ';';
        }
    }
    
    
    //сравнение строк
    public function filter($value)
    {
        sort($value);
        return implode($this->options['separator'], array_filter($value));                
    }
    
}
