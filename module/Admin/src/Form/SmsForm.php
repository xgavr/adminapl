<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Admin\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
/**
 * Description of Sms
 *
 * @author Daddy
 */
class SmsForm extends Form
{
    
    private $entityManager;
        
    /**
     * Конструктор.     
     */
    public function __construct($entityManager = null)
    {
        
        $this->entityManager = $entityManager;
        
        // Определяем имя формы.
        parent::__construct('sms-form');
     
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
        $this->setAttribute('onsubmit', 'return false;');
                
        $this->addElements();
        $this->addInputFilter();         
    }
    
    /**
    * Этот метод добавляет элементы к форме (поля ввода и кнопку отправки формы).
    */
    protected function addElements() 
    {
                
        $this->add([           
            'type'  => 'text',
            'name' => 'phone',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Телефон',
            ],
        ]);

        $this->add([           
            'type'  => 'select',
            'name' => 'mode',
            'attributes' => [
                'id' => 'smsMode',
            ],
            'options' => [
                'label' => 'Способ связи',
                'value_options' => [1 => 'SMS', 2 => 'WhatsApp']
            ],
        ]);

        // Добавляем поле "message"
        $this->add([           
            'type'  => 'textarea',
            'name' => 'message',
            'attributes' => [
                'id' => 'message'
            ],
            'options' => [
                'label' => 'Сообщение',
            ],
        ]);
        
        $this->add([           
            'type'  => 'hidden',
            'name' => 'orderId',
            'attributes' => [
            ],
            'options' => [
            ],
        ]);
        
        $this->add([           
            'type'  => 'hidden',
            'name' => 'attachment',
            'attributes' => [
            ],
            'options' => [
            ],
        ]);

        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'sms_submitbutton',
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
                'name'     => 'message',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                    ['name' => 'StripNewlines'],
                ],                
                'validators' => [
                ],
            ]);        
    }    
}
