<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Zend\Filter\AbstractFilter;

/**
 * Приводит артикул товара к нужному виду
 * только цыфры и буквы
 *
 * @author Daddy
 */
class ArticleCode extends AbstractFilter
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
        $result = mb_strtoupper(preg_replace("/[^a-zA-ZА-Яа-я0-9\s]/","", $value));
        
        return $result;
    }
    
}
