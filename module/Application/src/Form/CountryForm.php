<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Application\Entity\Country;
/**
 * Description of Tax
 *
 * @author Daddy
 */
class CountryForm extends Form
{
    /**
     * Конструктор.     
     */
    public function __construct()
    {
        // Определяем имя формы.
        parent::__construct('country-form');
     
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();         
    }
    
    /**
    * Этот метод добавляет элементы к форме (поля ввода и кнопку отправки формы).
    */
    protected function addElements() 
    {
                
        // Добавляем поле "name"
        $this->add([           
            'type'  => 'text',
            'name' => 'name',
            'attributes' => [
                'id' => 'countryname'
            ],
            'options' => [
                'label' => 'Наименование',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'fullname',
            'attributes' => [
                'id' => 'countryfullname'
            ],
            'options' => [
                'label' => 'Наименование полное',
            ],
        ]);
        
        // Добавляем поле "code"
        $this->add([
            'type'  => 'text',
            'name' => 'code',
            'attributes' => [                
                'id' => 'countrycode'
            ],
            'options' => [
                'label' => 'Код',
            ],
       ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'alpha2',
            'attributes' => [                
                'id' => 'countryalpha2'
            ],
            'options' => [
                'label' => 'Код Альфа2',
            ],
       ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'alpha3',
            'attributes' => [                
                'id' => 'countryalpha3'
            ],
            'options' => [
                'label' => 'Код Альфа3',
            ],
       ]);

        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'countrysubmitbutton',
            ],
        ]);        
    }
    
   /**
     * Этот метод создает фильтр входных данных (используется для фильтрации/валидации).
     */
    private function addInputFilter() 
    {
        
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
        
        $inputFilter->add([
                'name'     => 'name',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                    ['name' => 'StripNewlines'],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 1024
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'fullname',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                    ['name' => 'StripNewlines'],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 1024
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'code',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                    ['name' => 'StripNewlines'],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 1024
                        ],
                    ],
                ],
            ]);        

        $inputFilter->add([
                'name'     => 'alpha2',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                    ['name' => 'StripNewlines'],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 2,
                            'max' => 2
                        ],
                    ],
                ],
            ]);        

        $inputFilter->add([
                'name'     => 'alpha3',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                    ['name' => 'StripNewlines'],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 3,
                            'max' => 3
                        ],
                    ],
                ],
            ]);        
    }    
}
