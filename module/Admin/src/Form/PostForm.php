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
 * Description of Post
 *
 * @author Daddy
 */
class PostForm extends Form
{
    
    private $entityManager;
        
    /**
     * Конструктор.     
     */
    public function __construct($entityManager = null)
    {
        
        $this->entityManager = $entityManager;
        
        // Определяем имя формы.
        parent::__construct('post-form');
     
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
                
        $this->add([           
            'type'  => 'text',
            'name' => 'toEmail',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Кому',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'fromEmail',
            'attributes' => [
            ],
            'options' => [
                'label' => 'От кого',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'subject',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Тема',
            ],
        ]);

        // Добавляем поле "message"
        $this->add([           
            'type'  => 'textarea',
            'name' => 'message',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Сообщение',
            ],
        ]);
        
        $this->add([           
            'type'  => 'checkbox',
            'name' => 'copyMe',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Счет',
            ],
        ]);
        
        $this->add([           
            'type'  => 'checkbox',
            'name' => 'bill',
            'attributes' => [                
            ],
            'options' => [
                'label' => 'Счет',
            ],
        ]);
        
        $this->add([           
            'type'  => 'hidden',
            'name' => 'orderId',
            'attributes' => [                
            ],
            'options' => [
                'label' => 'Заказ',
            ],
        ]);
        
        $this->add([           
            'type'  => 'checkbox',
            'name' => 'offer',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Коммерческое предложение',
            ],
        ]);

        $this->add([           
            'type'  => 'checkbox',
            'name' => 'showCode',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Показывать артикли',
            ],
        ]);

        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'email_submitbutton',
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
                'name'     => 'toEmail',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 128
                        ],
                    ],
                    [
                        'name' => 'EmailAddress',
                        'options' => [
                            'allow' => \Laminas\Validator\Hostname::ALLOW_DNS,
                            'useMxCheck'    => false,                            
                        ],
                    ],
                ],
            ]);        
        
        $inputFilter->add([
                'name'     => 'fromEmail',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 128
                        ],
                    ],
                    [
                        'name' => 'EmailAddress',
                        'options' => [
                            'allow' => \Laminas\Validator\Hostname::ALLOW_DNS,
                            'useMxCheck'    => false,                            
                        ],
                    ],
                ],
            ]);        
        
        $inputFilter->add([
                'name'     => 'subject',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                    ['name' => 'StripNewlines'],
                ],                
                'validators' => [
                ],
            ]);        
        
        $inputFilter->add([
                'name'     => 'message',
                'required' => false,
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
