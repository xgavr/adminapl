<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Laminas\Filter\AbstractFilter;

/**
 * Преобразует строку в число
 *
 * @author Daddy
 */
class ToFloat extends AbstractFilter
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
        $value = htmlentities($value);
        $value = str_replace([' ', '&nbsp;'], "", $value); //  

        if(strstr($value, ",")) { 
            $value = str_replace(".", "", $value); // replace dots (thousand seps) with blancs 
            $value = str_replace(",", ".", $value); // replace ',' with '.' 
        } 
  
        if(preg_match("#([0-9\.\-]+)#", $value, $match)) { // search for number that may contain '.' 
            return floatval($match[0]); 
        } else { 
            return floatval($value); // take some last chances with floatval 
        } 
    }
    
}
