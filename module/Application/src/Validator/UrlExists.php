<?php

namespace Application\Validator;

use Laminas\Validator\AbstractValidator;
use Laminas\Filter\UriNormalize;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Проверяет урл
 *
 * @author Daddy
 */
class UrlExists extends AbstractValidator
{
    
    public function __construct($options = null) 
    {
        // Call the parent class constructor
        parent::__construct($options);
    }
    
    /**
     * Возвращает истину, если url работает
     *
     * @param  string $value name
     * @return bool
     */
    public function isValid($value)
    {        
        $uriNormalizeFilter = new UriNormalize(['enforcedScheme' => 'http']);
        $url = $uriNormalizeFilter->filter($value);
        if (!$fp = curl_init($url)) {
            return false;
        } 
        return true;        
    }
    
}
