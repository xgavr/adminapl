<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Zend\Filter\AbstractFilter;

/**
 * Вычисление похожести двух строк
 *
 * @author Daddy
 */
class StrSimilar extends AbstractFilter
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
    
    //нормализация текста
    private function _strNormalize($str) {
        $n = str_word_count(mb_strtolower($str), 1, '1234567890абвгдеёжзийклмнопрстуфхцчшщъыьэюя');
        sort($n, SORT_LOCALE_STRING );
        return implode(' ', $n);
    }
    
    //сравнение строк
    public function filter($str1, $str2 = '')
    {
       // setlocale(LC_ALL,'ru_RU.UTF-8');
        $per = null;
        similar_text($this->_strNormalize($str1), $this->_strNormalize($str2), $per);        
        return $per;
    }
    
}
