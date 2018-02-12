<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Zend\Filter\AbstractFilter;
use Zend\Json\Json;
/**
 * Предназначен для получения информции о банке по коду БИК
 * с использованием вебсервиса www.bik-info.ru
 *
 * @author Daddy
 */
class BikFilter extends AbstractFilter
{
    const WEB_SERVICE = 'http://www.bik-info.ru/api.html?type=json&bik=';
    
    // Доступные опции фильтра.
    protected $options = [
    ];    

    // Конструктор.
    public function __construct($options = null) 
    {     
        // Задает опции фильтра (если они предоставлены).
        if(is_array($options)) {
            if(isset($options['format'])){
            }
        }    
    }
    
    public function filter($value)
    {
        $result = [];
        
        if ($value){
            $data = Json::decode(file_get_contents(self::WEB_SERVICE.$value));
            if (!$data->error){
                $result['bik'] = $data->bik;
                $result['ks'] = $data->ks;
                $result['name'] = $data->name.' г. '.$data->city;
            }
        }
        
        return $result;
    }
    
}
