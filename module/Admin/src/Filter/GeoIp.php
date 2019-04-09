<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Filter;

use Zend\Filter\AbstractFilter;

/**
 * Страна по ип
 *
 * @author Daddy
 */
class GeoIp extends AbstractFilter
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
    
    /**
     * 
     * @param string $ip
     * @return array
     * {
        "query": "24.48.0.1",
        "status": "success",
        "country": "Canada",
        "countryCode": "CA",
        "region": "QC",
        "regionName": "Quebec",
        "city": "Montreal",
        "zip": "H1S",
        "lat": 45.5808,
        "lon": -73.5825,
        "timezone": "America/Toronto",
        "isp": "Le Groupe Videotron Ltee",
        "org": "Videotron Ltee",
        "as": "AS5769 Videotron Telecom Ltee"
      }
     */
    public function filter($ip)
    {
        $parsedUrl = parse_url($ip);
        
        $result = \Zend\Json\Json::decode(file_get_contents("http://ip-api.com/json/{$parsedUrl['host']}?lang=ru"), \Zend\Json\Json::TYPE_ARRAY);
//var_dump($result);
        return $result;
    }
    
}
