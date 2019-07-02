<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Zend\Filter\AbstractFilter;

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
        $s = array('Å', 'Á', 'Ë', 'Ö', 'Ü');
        $r = array('A', 'A', 'E', 'O', 'U');
        $result = str_replace($s, $r, mb_ereg_replace('[^A-ZА-ЯЁ0-9ÅÖËÜ]', '', mb_strtoupper($value, 'utf-8')));
//        var_dump($result);
        return $result;
    }
    
}
