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
    
    protected $removes = [
        'mail Размещение на Авто.ру. Здравствуйте! Вам поступила заявка на товар',
        '[Объявление на Авто.ру]',
        'Счастливого пути. Команда Запчасти на Авто.ру Письмо отправлено автоматически. Если вы считаете, что получили его по ошибке свяжитесь с нами.',        
    ];
    
    protected $newlines = [
        'Автомобиль:',
        'Номер запчасти:',
        'Информация о покупателе:',
        'Телефон:',
        'Email:',
        'Адрес доставки:',
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
        
        $result['text'] = str_replace($this->removes, PHP_EOL, $value); //удаляем ненужные фразы
        foreach ($this->newlines as $line){
            $result['text'] = str_replace($line, PHP_EOL.$line, $result['text']); //Добавить перенос строки
        }
        
        $strgs = explode(PHP_EOL, $result['text']);
        foreach ($strgs as $strg){
            $dqs = explode(':', $strg);
            if (trim($dqs[0]) == 'Телефон'){
                $phoneFilter = new PhoneFilter();
                $result['phone'] = $phoneFilter->filter(trim($dqs[1]));
            }
            if (trim($dqs[0]) == 'Email'){                
                if ($dqs[1]){
                    $emailValidator = new EmailAddress(['allow' => \Zend\Validator\Hostname::ALLOW_DNS, 'useMxCheck' => false]);
                    if ($emailValidator->isValid(trim($dqs[1]))){
                        $result['email'] = trim($dqs[1]);
                    }    
                }    
            }
            if (trim($dqs[0]) == 'Адрес доставки'){
                $result['address'] = trim($dqs[1]);
            }
        }

        return $result;
    }
    
}
