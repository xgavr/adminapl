<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Laminas\Filter\AbstractFilter;

/**
 * Определяет разделитель столбцов CSV
 *
 * @author Daddy
 */
class CsvDetectDelimiterFilter extends AbstractFilter
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
        $delimiters = array(
            ';' => 0,
            ',' => 0,
            "\t" => 0,
            "|" => 0
        );

        $handle = fopen($value, "r");
        $i = 0;
        while ($i < 10) {
            $line = fgets($handle);
            if ($line){
                foreach ($delimiters as $delimiter => &$count) {
                    $count += count(str_getcsv($line, $delimiter));
                }
            }    
            $i++;
        }    
        fclose($handle); 
        //var_dump($delimiters); exit;
        return array_search(max($delimiters), $delimiters);    
        
    }
    
}
