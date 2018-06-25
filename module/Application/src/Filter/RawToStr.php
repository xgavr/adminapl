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
 * Декодирует строку в utf-8
 *
 * @author Daddy
 */
class RawToStr extends AbstractFilter
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
        if (is_array($value)){
            
            $toUtf8filter = new ToUtf8();
            $stripNewLinesFilter = new StripNewlines();
            $stringTrimFilter = new StringTrim();
            
            $result = [];
            foreach ($value as $key=>$row){
                if (is_scalar($row)){
                    $result[] = str_replace(';', ' ', $row);
                } else {
                    $result[] = '';
                }            
            }

            $filtered = implode(';', $result);
            
        } else {
            $filtered = $value;
        }
        
        $filtered = $toUtf8filter->filter($filtered);
        $filtered = $stringTrimFilter->filter($filtered);
        $filtered = $stripNewLinesFilter->filter($filtered);

        return $filtered;
    }
    
}
