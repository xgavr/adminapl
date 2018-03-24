<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Filter;

use Zend\Filter\AbstractFilter;

/**
 * Удаляет html и css тэги
 *
 * @author Daddy
 */
class AutoruOrderFilter extends AbstractFilter
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
        $result = str_replace($this->removes, PHP_EOL, $value); //удаляем ненужные фразы
        foreach ($this->newlines as $line){
            $result = str_replace($line, PHP_EOL.$line, $result); //Добавить перенос строки
        }

        return $result;
    }
    
}
