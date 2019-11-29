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
    
    public function filter($value)
    {
        $result = [];
        
        $strgs = explode(PHP_EOL, $value);
        $goodStr = false;
        $bags = [];
        $info = [];
        foreach ($strgs as $strg){
            if (mb_strpos($strg, 'product') !== false) {
                $goodStr = true;
                continue;
            }
            if (mb_strpos($strg, 'Номер заказа') !== false) {
                $goodStr = false;
                continue;
            }
            if ($goodStr){
                $info[] = trim($strg);                
                $products = explode(' ', trim($strg));
                $bags[] = $products[0];
            }
            if (mb_strpos($strg, 'Контакты') !== false) {
                $contacts = str_replace('Контакты', '', $strg);
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
            if (mb_strpos($strg, 'Комментарий') !== false) {
                $info[] = trim(str_replace('Комментарий', '', $strg));
            }
            if (mb_strpos($strg, 'Адрес доставки') !== false) {
                $result['address'] = trim(str_replace('Адрес доставки', '', $strg));
            }
            if (mb_strpos($strg, 'Имя') !== false) {
                $result['name'] = trim(str_replace('Имя', '', $strg));
            }
        }
        array_filter($bags);
        array_filter($info);
        $result['info'] = implode(';', $info);
        $result['items'] = implode(';', $bags);
        var_dump($result);
        return $result;
    }
    
}
