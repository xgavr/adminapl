<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Application\Entity\Contact;
use User\Validator\UserExistsValidator;
use User\Filter\PhoneFilter;

/**
 * Description of contact
 *
 * @author Daddy
 */
class ContactForm extends Form
{
    
    protected $objectManager;
    
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager 
     */
    private $entityManager = null;    
        
    /**
     * Current user.
     * @var User\Entity\User 
     */
    private $user = null;
    
    /**
     * Конструктор.     
     */
    public function __construct($entityManager = null, $user = null)
    {
        // Определяем имя формы.
        parent::__construct('contact-form');
     
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
        
        $this->entityManager = $entityManager;    
        $this->user = $user;
                
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
                'id' => 'contact_name'
            ],
            'options' => [
                'label' => 'ФИО',
            ],
        ]);
                
        // Добавляем поле "description"
        $this->add([           
            'type'  => 'text',
            'name' => 'description',
            'attributes' => [
                'id' => 'contact_description'
            ],
            'options' => [
                'label' => 'Описание',
            ],
        ]);
                
        // Добавляем поле "phone"
        $this->add([           
            'type'  => 'text',
            'name' => 'phone',
            'attributes' => [
                'id' => 'contact_phone'
            ],
            'options' => [
                'label' => 'Телефон',
            ],
        ]);
                
        // Add "email" field
        $this->add([            
            'type'  => 'text',
            'name' => 'email',
            'disabled' => 'disabled',
            'options' => [
                'label' => 'E-mail (обязательно, если нужен доступ на сайт)',
            ],
        ]);

        // Add "icq" field
        $this->add([            
            'type'  => 'text',
            'name' => 'icq',
            'disabled' => 'disabled',
            'options' => [
                'label' => 'Icq номер',
            ],
        ]);

        // Add "telegramm" field
        $this->add([            
            'type'  => 'text',
            'name' => 'telegramm',
            'disabled' => 'disabled',
            'options' => [
                'label' => 'Telegramm номер',
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
        
        // Add "password" field
        $this->add([            
            'type'  => 'text',
            'name' => 'password',
            'options' => [
                'label' => 'Пароль (если нужен доступ на сайт - минимум 6 символов)',
            ],
        ]);
        
        // Add "status" field
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'options' => [
                'label' => 'Статус',
                'value_options' => [
                    1 => 'Active',
                    2 => 'Retired',                    
                ]
            ],
        ]);
        
                
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'contact_submitbutton',
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
                'name'     => 'description',
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
                            'max' => 1024
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'phone',
                'required' => false,
                'filters'  => [
                    [
                        'name' => PhoneFilter::class,
                        'options' => [
                            'format' => PhoneFilter::PHONE_FORMAT_RU,
                        ]
                    ],
                ],                
                'validators' => [
                    [
                        'name'    => 'PhoneNumber',
                        'options' => [
                        ],
                    ],
                ],
            ]);        
        
        // Add input for "email" field
        $inputFilter->add([
                'name'     => 'email',
                'required' => false,
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
                            'allow' => \Zend\Validator\Hostname::ALLOW_DNS,
                            'useMxCheck'    => false,                            
                        ],
                    ],
                    [
                        'name' => UserExistsValidator::class,
                        'options' => [
                            'entityManager' => $this->entityManager,
                            'user' => $this->user
                        ],
                    ],                    
                ],
            ]);     
        
        // Add input for "password" field
        $inputFilter->add([
                'name'     => 'password',
                'required' => false,
                'filters'  => [                        
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 6,
                            'max' => 64
                        ],
                    ],
                ],
            ]);

        // Add input for "confirm_password" field
        $inputFilter->add([
                'name'     => 'confirm_password',
                'required' => false,
                'filters'  => [                        
                ],                
                'validators' => [
                    [
                        'name'    => 'Identical',
                        'options' => [
                            'token' => 'password',                            
                        ],
                    ],
                ],
            ]);
        
        // Add input for "status" field
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
        
        // Add input for "icq" field
        $inputFilter->add([
                'name'     => 'icq',
                'required' => false,
//                'filters'  => [                    
//                    ['name' => 'ToInt'],
//                ],                
//                'validators' => [
//                    ['name'=>'Digits'],
//                ],
            ]); 
        
        // Add input for "icq" field
        $inputFilter->add([
                'name'     => 'telegramm',
                'required' => false,
//                'filters'  => [                    
//                    ['name' => 'ToInt'],
//                ],                
//                'validators' => [
//                    ['name'=>'IsFloat'],
//                ],
            ]);         
        
        $inputFilter->add([
                'name'     => 'address',
                'required' => false,
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
    
    
    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }    

    
}
