<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Laminas\Filter\AbstractFilter;

/**
 * Приводит наименование к единому виду
 *
 * @author Daddy
 */
class ProducerName extends AbstractFilter
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
        if (is_string($value)){
            $s = array('Å', 'Á', 'Â', 'É', 'Ë', 'Ö', 'Ü');
            $r = array('A', 'A', 'A', 'E', 'E', 'O', 'U');
            $result = mb_ereg_replace('[^A-ZА-ЯЁ0-9]', '', str_replace($s, $r, mb_strtoupper($value, 'utf-8')));
    //        var_dump($result);
            return $result;
        }
        
        return $value;
    }
    
}
