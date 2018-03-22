<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Filter;

use Zend\Filter\AbstractFilter;
use Zend\Json\Json;
/**
 * Удаляет html и css тэги
 *
 * @author Daddy
 */
class HtmlFilter extends AbstractFilter
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
        $result = preg_replace('/<style(.*)<\/style>/s', '', $value); //удаляем стили
        $result = strip_tags($result); //удаляем тэги
        $result = trim(preg_replace('/\s{2,}/', ' ', $result)); //удаляем лишние пробелы

        return $result;
    }
    
}
