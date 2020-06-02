<?php

namespace Application\Validator;

use Laminas\Validator\AbstractValidator;
use Application\Entity\PriceGetting;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Попадает ли число в диапазон сигма3
 *
 * @author Daddy
 */
class Sigma3 extends AbstractValidator
{
    
    public function __construct($options = null) 
    {
        // Call the parent class constructor
        parent::__construct($options);
    }    
    
    /**
     * Возвращает истину, если число попадает в интервал сигма3(2 точнее)
     *
     * @param float $price
     * @param float $meanPrice
     * @param float $dispersion
     * 
     * @return bool
     */
    public function isValid($price, $meanPrice = 0.0, $dispersion = 0.0)
    {
        if ($meanPrice && $dispersion){
            if ($dispersion/$meanPrice < 0.01){
                return true;
            }
        }        
        
        $minPrice = $meanPrice - 3*$dispersion;
        $maxPrice = $meanPrice + 3*$dispersion;
        
        if ($minPrice < $dispersion){
            return false;
        }
        
        return $price >= $minPrice && $price <= $maxPrice;
    }
    
}
