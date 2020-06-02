<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bank\Filter;

use Laminas\Filter\AbstractFilter;

/**
 * Преобразует выписку 1с формата в массив
 * @param string $value Имя файла с выпиской
 * 
 * @return array
 * 
 * @author Daddy
 */
class Statement1cToArray extends AbstractFilter
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

        $text = file_get_contents($value);
        $text = iconv('Windows-1251', 'UTF-8', $text);
        $lines = explode(PHP_EOL, $text);
        
        $statement = [];
        $section = '';
        $i = 0;
        foreach ($lines as $line){
            
            if (mb_substr(trim($line), 0, 5) == 'Конец'){
                $section = '';
                $i++;
                continue;
            }
            
            if ('СекцияРасчСчет' == trim($line)){
                $section = 'account';
                continue;
            }
            
            if (strpos($line, '=')){
                list($key, $value) = explode('=', $line, 2);
            } else {
                $key = $line;
                $value = '';
            }    
            
            if ('СекцияДокумент' == trim($key)){
                $section = 'doc';
                continue;
            }
            
            if ($section == 'account'){
                $statement['accounts'][$i][trim($key)] = trim($value);
            } elseif ($section == 'doc'){
                $statement['docs'][$i][trim($key)] = trim($value);                
            } else {
                $statement[trim($key)] = trim($value);
            }
        }

        return $statement;        
    }
    
}
