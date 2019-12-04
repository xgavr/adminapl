<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Filter;

use Zend\Filter\AbstractFilter;
use User\Filter\PhoneFilter;
use Zend\Validator\EmailAddress;

/**
 * Парсинг письма с турбо страниц
 *
 * @author Daddy
 */
class TurboOrderFilter extends AbstractFilter
{
    
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
    
    protected function _domTable($html)
    {
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        
        $result = [];
        $row = 0;
        foreach ($xpath->query('//table') as $table){
            foreach ($xpath->query('tr', $table) as $tr){
                foreach ($xpath->query('td', $tr) as $td){
                    $result[$row][] = trim($td->textContent);
                }
                $row++;
            }    
        }
        
        return $result;
    }
    
    public function filter($value)
    {
        $lines = $this->_domTable($value);
        $result = [];
        $$goodStr = false;
        foreach ($lines as $key => $line){
            if ($line[0] == 'product id'){
                $goodStr = true;
                continue;
            }
            if ($line[0] == 'Номер заказа'){
                $goodStr = false;
                continue;
            }
            if ($goodStr){
                $bags[] = [
                    'offerId' => $line[0],
                    'count' => (float) $line[2],
                    'price' => (float) $line[3],
                ];
            }
            if ($line[0] == 'Контакты'){
                $contacts = trim($line[1]);
                $dqs = explode(';', trim($contacts));
                $phoneFilter = new PhoneFilter();
                $emailValidator = new EmailAddress(['allow' => \Zend\Validator\Hostname::ALLOW_DNS, 'useMxCheck' => false]);
                $emails = []; $phones = [];
                foreach ($dqs as $dbStr){
                    if ($emailValidator->isValid(trim($dbStr))){
                        $emails[] = trim($dbStr);                        
                    } else {
                        $phones[] = $phoneFilter->filter(trim($dbStr));                                                
                    }
                }
                $result['email'] = implode(';', $emails);
                $result['phone'] = implode(';', $phones);
            }
            if ($line[0] == 'Комментарий'){
                $info[] = trim($line[1]);
            }
            if ($line[0] == 'Адрес доставки'){
                $result['address'] = trim($line[1]);
            }
            if ($line[0] == 'Имя'){
                $result['name'] = trim($line[1]);
            }
        }
        array_filter($bags);
        array_filter($info);
        $result['text'] = implode(PHP_EOL, $info);
        $result['items'] = $bags;
//        var_dump($result);
        return $result;
    }
    
}
