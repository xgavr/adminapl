<?php

namespace Application\Validator;

use Laminas\Validator\AbstractValidator;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Число слово?
 *
 * @author Daddy
 */
class IsNUM extends AbstractValidator
{
    
    public function __construct($options = null) 
    {
        // Call the parent class constructor
        parent::__construct($options);
    }    
    
    /**
     * Возвращает истину, если слово содержит цифры
     *
     * @param string $word
     * 
     * @return bool
     */
    public function isValid($word)
    {
        $numWord = mb_ereg_replace('[^0-9]', '', $word);
        
        return $numWord != '';
    }
    
}
