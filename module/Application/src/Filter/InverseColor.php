<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Zend\Filter\AbstractFilter;

/**
 * Генерирует  color (#000000) противоположный введенному
 * https://www.jonasjohn.de/snippets/php/color-inverse.htm
 * @author Daddy
 */
class InverseColor extends AbstractFilter
{
    
    // Доступные опции фильтра.
    protected $options = [
        'bw' => true,
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
        
        $color = str_replace('#', '', $value);
        if (strlen($color) != 6){ return '000000'; }
        $rgb = '';
        
        for ($x=0;$x<3;$x++){
            $c = 255 - hexdec(substr($color,(2*$x),2));
            $c = ($c < 0) ? 0 : dechex($c);
            $rgb .= (strlen($c) < 2) ? '0'.$c : $c;
        }
        
        return '#'.$rgb;        
    }
    
}
