<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Zend\Filter\AbstractFilter;

/**
 * Определяет разделитель для оригинальных номеров в строке прайса
 *
 * @author Daddy
 */
class OemDetectDelimiterFilter extends AbstractFilter
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
    
    /**
     * 
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        $delimiters = array(
            ';' => 0,
            ',' => 0,
            "\t" => 0,
            "|" => 0,
            "/" => 0,
            "\\" => 0,
        );

        foreach ($delimiters as $delimiter => &$count) {
            $count = count(str_getcsv($value, $delimiter));
        }    

        return array_search(max($delimiters), $delimiters);;        
    }
    
}
