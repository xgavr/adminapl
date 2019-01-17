<?php

namespace Application\Validator;

use Zend\Validator\AbstractValidator;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Сравнение двух имен с удалением всего кроме букв и цифр
 *
 * @author Daddy
 */
class NameValidator extends AbstractValidator
{
    
    public function __construct($options = null) 
    {
        // Call the parent class constructor
        parent::__construct($options);
    }

    protected function prepareStr($str)
    {
//        var_dump($str);
//        var_dump(mb_ereg_replace('[^A-ZА-ЯЁ0-9]', '', mb_strtoupper($str, 'utf-8')));
        return mb_ereg_replace('[^A-ZА-ЯЁ0-9]', '', mb_strtoupper($str, 'utf-8'));
    }
    
    /**
     * Возвращает истину, если строки после очистки совпадают
     *
     * @param  string $value name
     * @param  string $value2 name
     * @return bool
     */
    public function isValid($value, $value2 = null)
    {        
        return $this->prepareStr($value) == $this->prepareStr($value2);
    }
    
}
