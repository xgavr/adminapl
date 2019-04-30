<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Zend\Filter\AbstractFilter;
use Application\Filter\ToUtf8;
use Zend\Filter\StripNewlines;
use Zend\Filter\StringTrim;

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
        $result = mb_ereg_replace('[^A-ZА-ЯЁ0-9]', '', mb_strtoupper($value, 'utf-8'));
        
        $s = array('Å', 'Ö');
        $r = array('A', 'O');
        $result = mb_ereg_replace($s, $r, $result);
        
        return $result;
    }
    
}
