<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Laminas\Filter\AbstractFilter;

/**
 * Преобразование атрибутов машин от зетасофт
 * @author Daddy
 */
class ZetasoftCarKey extends AbstractFilter
{
    
    // Доступные опции фильтра.
    protected $options = [
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
        switch ($value){
            case 'fuelInjectionType': $result = 'fuelTypeProcess'; break;
            case 'driveType': $result = 'impulsionType'; break;
            case 'engineType': $result = 'motorType'; break;
            case 'cylindersNumber': $result = 'cylinder'; break;
            case 'bodyType': $result = 'constructionType'; break;
            case 'valvesNumber': $result = 'valves'; break;
            case 'cylindersVolumeLiters': $result = 'cylinderCapacityLiter'; break;
            case 'dateFrom': $result = 'yearOfConstrFrom'; break;
            case 'dateTo': $result = 'yearOfConstrTo'; break;
            case 'manufacturerId': $result = 'manuId'; break;
            case 'manufacturerName': $result = 'manuName'; break;
            case 'volumeCCM': $result = 'ccmTech'; break;
            case 'cylindersVolumeCCM': $result = 'cylinderCapacityCcm'; break;
            case 'id': $result = 'carId'; break;
            case 'modelId': $result = 'modId'; break;
            case 'name': $result = 'typeName'; break;
            default: $result = $value; break;
        }
        
        return $result;        
    }
    
}
