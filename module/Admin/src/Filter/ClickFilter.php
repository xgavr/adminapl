<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Filter;

use Laminas\Filter\AbstractFilter;

/**
 * Короткая ссылка
 *
 * @author Daddy
 */
class ClickFilter extends AbstractFilter
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
     * 
     */
    public function filter($url)
    {
        return file_get_contents("https://clck.ru/--?url=$url");
    }
    
}
