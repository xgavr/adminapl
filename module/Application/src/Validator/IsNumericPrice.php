<?php

namespace Application\Validator;

use Zend\Validator\AbstractValidator;
use Application\Filter\ToFloat;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * число?
 *
 * @author Daddy
 */
class IsNumericPrice extends AbstractValidator
{
    
    private $toFloatFilter;


    public function __construct($options = null) 
    {
        // Call the parent class constructor
        parent::__construct($options);
        
        $this->toFloatFilter = new ToFloat();
    }    
    
    /**
     * Возвращает истину, если значение число
     *
     * @param string $value
     * 
     * @return bool
     */
    public function isValid($value)
    {
        $digit = $this->toFloatFilter->filter($value);
        
        return is_numeric($digit) && $digit;
    }
    
}
