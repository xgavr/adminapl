<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Filter;

use Laminas\Filter\AbstractFilter;
use User\Filter\PhoneFilter;
use Laminas\Validator\EmailAddress;

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
//        var_dump($html); exit;
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        
        $result = [];
        $row = 0;
        foreach ($xpath->query('//table') as $table){
            foreach ($xpath->query('//tr', $table) as $tr){
                foreach ($xpath->query('td', $tr) as $td){
                    $result[$row][] = trim($td->textContent);
                }
                $row++;
            }    
        }
        
        return $result;
    }
    
    protected function _fromHtml($value)
    {
        $lines = $this->_domTable($value);
        $result = [];
        $bags = [];
        $info = [];
        $goodStr = false;
//        var_dump($lines); exit;
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
                $emailValidator = new EmailAddress(['allow' => \Laminas\Validator\Hostname::ALLOW_DNS, 'useMxCheck' => false]);
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

    protected function _fromPlain($value)
    {
        $lines = explode("\n", $value);
        $result = [];
        $bags = [];
        $info = [];
        $offerId = $price = null;
//        var_dump($lines); exit;
        foreach ($lines as $key => $line){
            if ($line == 'ID предложения'){
                $offerId = trim($lines[$key+1]);
            }
            if ($line == 'Цена'){
                $price = (float) trim($lines[$key+1]);
            }
            if ($line == 'Контакты'){
                $contacts = trim($lines[$key+1]);
                $contacts = str_replace(['email:', ['phone:']], '', $contacts);
                $dqs = explode(';', trim($contacts));
                $phoneFilter = new PhoneFilter();
                $emailValidator = new EmailAddress(['allow' => \Laminas\Validator\Hostname::ALLOW_DNS, 'useMxCheck' => false]);
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
                $info[] = trim($lines[$key+1]);
            }
            if ($line == 'Адрес доставки'){
                $result['address'] = trim($lines[$key+1]);
            }
            if ($line == 'Имя'){
                $result['name'] = trim($lines[$key+1]);
            }
        }
        $bags[] = [
            'offerId' => $offerId,
            'count' => 1,
            'price' => $price,
        ];
        array_filter($bags);
        array_filter($info);
        $result['text'] = implode(PHP_EOL, $info);
        $result['items'] = $bags;
//        var_dump($result); exit;
        return $result;
        
    }
    
    public function filter($value, $html = true)
    {        
//        var_dump($value); exit;
        if ($html){
            return $this->_fromHtml($value);
        } else {
            return $this->_fromPlain($value);
        }
    }
    
}
