<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Laminas\Filter\AbstractFilter;

/**
 * Русские буквы преобразовать в латиницу
 *
 * @author Daddy
 */
class KeyboardTranslit extends AbstractFilter
{
    
    // Доступные опции фильтра.
    protected $options = [
    ];    
    
    protected $tr = [
        "Й"=>"Q","Ц"=>"W","У"=>"E","К"=>"R","Е"=>"T","Н"=>"Y","Г"=>"U","Ш"=>"I","Щ"=>"O","З"=>"P","Х"=>"{","Ъ"=>"}",
        "Ф"=>"A","Ы"=>"S","В"=>"D","А"=>"F","П"=>"G","Р"=>"H","О"=>"J","Л"=>"K","Д"=>"L","Ж"=>":","Э"=>"'",
        "Я"=>"Z","Ч"=>"X","С"=>"C","М"=>"V","И"=>"B","Т"=>"N","Ь"=>"M","Б"=>"<","Ю"=>">",","=>"?",
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
        return strtr(mb_strtoupper($value), $this->tr);
    }
    
}
