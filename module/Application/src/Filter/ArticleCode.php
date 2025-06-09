<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Laminas\Filter\AbstractFilter;
use Application\Entity\OemRaw;
use Cocur\Slugify\Slugify;

/**
 * Приводит артикул товара к нужному виду
 * только цыфры и буквы
 *
 * @author Daddy
 */
class ArticleCode extends AbstractFilter
{
    
    // Доступные опции фильтра.
    protected $options = [
    ];    

    protected $slugify;
            
    // Конструктор.
    public function __construct($options = null) 
    {     
        // Задает опции фильтра (если они предоставлены).
        if(is_array($options)) {
            $this->options = $options;
        }    
        
        $this->slugify = new Slugify();
    }
    
    public function filter($value)
    {
        $result = mb_strtoupper(preg_replace("/[^a-zA-ZА-Яа-я0-9]/u","", $value), 'utf-8');
        
        if (mb_strlen($result, 'utf-8') > 24){
            $result = OemRaw::LONG_CODE;
        }        
        
        return $this->slugify->slugify($result);;
    }
    
}
