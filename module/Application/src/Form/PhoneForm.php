<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use User\Filter\PhoneFilter;
use User\Validator\PhoneExistsValidator;
/**
 * Description of Phone
 *
 * @author Daddy
 */
class PhoneForm extends Form
{
    
    private $entityManager;
    
    private $phone;
    
    /**
     * Конструктор.     
     */
    public function __construct($entityManager = null, $phone = null)
    {
        
        $this->entityManager = $entityManager;
        $this->phone = $phone;
        
        // Определяем имя формы.
        parent::__construct('phone-form');
     
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
                'id' => 'phone_name'
            ],
            'options' => [
                'label' => 'Телефон',
            ],
        ]);
        
        // Добавляем поле "comment"
        $this->add([           
            'type'  => 'text',
            'name' => 'comment',
            'attributes' => [
                'id' => 'phone_comment'
            ],
            'options' => [
                'label' => 'Комментарий',
            ],
        ]);
        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'phone_submitbutton',
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
                    [
                        'name' => PhoneFilter::class,
                        'options' => [
                            'format' => PhoneFilter::PHONE_FORMAT_DB,
                        ]
                    ],
                ],                
                'validators' => [
                    [
                        'name'    => 'PhoneNumber',
                        'options' => [
                        ],
                    ],
                    [
                        'name' => PhoneExistsValidator::class,
                        'options' => [
                            'entityManager' => $this->entityManager,
                            'phone' => $this->phone
                        ],
                    ],
                ],
            ]);        
        
        $inputFilter->add([
                'name'     => 'comment',
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
        
        
    }    
}
