<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Application\Entity\Messenger;

/**
 * Description of contact
 *
 * @author Daddy
 */
class MessengerForm extends Form
{
    
    
    /**
     * Конструктор.     
     */
    public function __construct()
    {
        // Определяем имя формы.
        parent::__construct('messenger-form');
     
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
            'name' => 'ident',
            'attributes' => [
                'id' => 'ident'
            ],
            'options' => [
                'label' => 'Идентификатор пользователя',
            ],
        ]);
                
        // Add "status" field
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'options' => [
                'label' => 'Status',
                'value_options' => [
                    1 => 'Используется',
                    2 => 'Не используется',                    
                ]
            ],
        ]);
                        
        // Add "type" field
        $this->add([            
            'type'  => 'select',
            'name' => 'type',
            'options' => [
                'label' => 'Status',
                'value_options' => Messenger::getTypesList(),
            ],
        ]);
                        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'submitbutton',
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
                'name'     => 'type',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2, 3, 4]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'ident',
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
                'name'     => 'status',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        
    }    
        
}
