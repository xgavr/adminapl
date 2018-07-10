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
class Basename extends AbstractFilter
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
        
        setlocale(LC_ALL,'ru_RU.UTF-8');

        return pathinfo($value, PATHINFO_BASENAME);
        
    }
    
}
