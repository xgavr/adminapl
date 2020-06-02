<?php

namespace Application\Validator;

use Laminas\Validator\AbstractValidator;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Русское слово?
 *
 * @author Daddy
 */
class IsRU extends AbstractValidator
{
    
    public function __construct($options = null) 
    {
        // Call the parent class constructor
        parent::__construct($options);
    }    
    
    /**
     * Возвращает истину, если слово содержит русские буквы
     *
     * @param string $word
     * 
     * @return bool
     */
    public function isValid($word)
    {
        $ruWord = mb_ereg_replace('[^А-ЯЁ]', '', $word);
        
        return $ruWord != '';
    }
    
}
