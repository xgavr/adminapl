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
 * Description of IsVendorCodeValidator
 *
 * @author Daddy
 */
class PriceNameValidator extends AbstractValidator
{
    
    public function __construct($options = null) 
    {
        // Call the parent class constructor
        parent::__construct($options);
    }    
    
    /**
     * Возвращает истину, если наименование файла соответсвуте параметрам в настройках получения прайса
     *
     * @param  string $value price file name
     * @param  Application\Entity\Pricegetting  $priceGetting
     * @return bool
     */
    public function isValid($value, $priceGetting = null)
    {
        if ($priceGetting){
            if ($priceGetting->getStatusFilename() != PriceGetting::STATUS_FILENAME_NONE){
                $phrase = $priceGetting->getFilename();
                if ($phrase){
                    if ($priceGetting->getStatusFilename() == PriceGetting::STATUS_FILENAME_IN){
                        if (mb_stripos($value, $phrase) !== false){
                            return true; //Если фраза содержится в имени файла - пропускаем
                        } else {
                            return false;    
                        }
                    }                    
                    if ($priceGetting->getStatusFilename() == PriceGetting::STATUS_FILENAME_EX){
                        if (mb_stripos($value, $phrase) !== false){
                            return false; //Если фраза содержится в имени файла - исключаем
                        } else {
                            return true;
                        }    
                    }
                }
            }
        }
        
        return true;
    }
    
}
