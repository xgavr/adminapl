<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Filter;

use Zend\Filter\AbstractFilter;

/**
 * Получаем email из строки
 *
 * @author Daddy
 */
class EmailFromStr extends AbstractFilter
{    
    
    // Доступные опции фильтра.
    protected $options = [
    ];    
    
    protected $emails = [];

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
        
        $this->emails = [];
        
        $pattern = "/[-a-z0-9!#$%&'*_`{|}~]+[-a-z0-9!#$%&'*_`{|}~\.=?]*@[a-zA-Z0-9_-]+[a-zA-Z0-9\._-]+/i";
        preg_match_all($pattern, $value, $ar);
        $r = array_unique(array_map(function ($i) { return $i; }, $ar));
        array_walk_recursive($r, function ($item, $key) {
            if ($item){
                $this->emails[] = $item;
            }        
        });
        
        if (count($this->emails)){
            return $this->emails[0];
        }    
        
        return $value;
    }
    
}
