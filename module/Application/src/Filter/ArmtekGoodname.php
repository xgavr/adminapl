<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Zend\Filter\AbstractFilter;

/**
 * Наименование товара поставщика Армтек
 *
 * @author Daddy
 */
class ArmtekGoodname extends AbstractFilter
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
    
    /**
     * Проверка вхождения символа в строку
     * 
     * @param string $str
     * @param string $char
     */
    private function checkChar($str, $char)
    {
        if (is_string($str)){
            $pos = strpos($str, $char);
            if ($pos === false){
                return false;
            } else {
                return true;
            }
        }
        
        return false;
    }

        /**
     * 
     * @param string $value
     * @return array
     */
    public function filter($value)
    {
        $result['goodname'] = $value;
        $result['car'] = '';

        if ($this->checkChar($value, '_')){
            list($strArticle, $strDesc) = explode('_', $value);
            if ($this->checkChar($strDesc, ']')){
                list($strDesc0, $strDesc2) = explode(']', $strDesc);
                $strDesc = $strDesc2;
            }
            if ($this->checkChar($strDesc, '\\')){
                list($goodname, $car) = explode('\\', $strDesc); 

                $result['goodname'] = trim(str_replace('!', ' ', $goodname));
                $result['car'] = trim($car);
            }
        }

        return $result;
    }
    
}
