<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;

/**
 * Description of contact
 *
 * @author Daddy
 */
class AddressForm extends Form
{
    
    
    /**
     * Конструктор.     
     */
    public function __construct()
    {
        // Определяем имя формы.
        parent::__construct('address-form');
     
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
                'id' => 'address_name'
            ],
            'options' => [
                'label' => 'Наименование',
            ],
        ]);
                
        // Add "address" field
        $this->add([            
            'type'  => 'textarea',
            'name' => 'address',
            'attributes' => [
                'id' => 'address'
            ],
            'options' => [
                'label' => 'Адрес для документов',
            ],
        ]);
        
        // Add "addressSMS" field
        $this->add([            
            'type'  => 'textarea',
            'name' => 'addressSms',
            'attributes' => [
                'id' => 'addressSms'
            ],
            'options' => [
                'label' => 'Адрес для СМС',
            ],
        ]);
                        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'address_submitbutton',
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
                'required' => false,
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
                            'max' => 128
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'address',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 1024
                        ],
                    ],
                ],
            ]);                  
        
        $inputFilter->add([
                'name'     => 'addressSms',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 256
                        ],
                    ],
                ],
            ]);                  
        
        
    }    
        
}
