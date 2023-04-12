<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Application\Entity\ContactCar;

/**
 * Description of contactCarForm
 *
 * @author Daddy
 */
class ContactCarForm extends Form
{
    
    protected $objectManager;
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager 
     */
    private $entityManager;    
            
    
    /**
     * Конструктор.     
     */
    public function __construct($entityManager)
    {
        // Определяем имя формы.
        parent::__construct('contact-car-form');
     
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
        
        $this->entityManager = $entityManager;    
                
        $this->addElements();
        $this->addInputFilter();         
    }
    
    /**
    * Этот метод добавляет элементы к форме (поля ввода и кнопку отправки формы).
    */
    protected function addElements() 
    {
                
        // Добавляем поле "make"
        $this->add([           
            'type'  => 'hidden',
            'name' => 'make',
            'attributes' => [
                'id' => 'make'
            ],
            'options' => [
                'label' => 'Машина',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'makeName',
            'attributes' => [
                'id' => 'makeName'
            ],
            'options' => [
                'label' => 'Машина',
            ],
        ]);

        // Добавляем поле "model"
        $this->add([           
            'type'  => 'select',
            'name' => 'model',
            'attributes' => [
                'id' => 'model'
            ],
            'options' => [
                'label' => 'Модель',
            ],
        ]);

        // Добавляем поле "car"
        $this->add([           
            'type'  => 'select',
            'name' => 'car',
            'attributes' => [
                'id' => 'car'
            ],
            'options' => [
                'label' => 'Модификация',
            ],
        ]);

        
        // Добавляем поле "vin"
        $this->add([           
            'type'  => 'text',
            'name' => 'vin',
            'attributes' => [
                'id' => 'vin'
            ],
            'options' => [
                'label' => 'VIN',
            ],
        ]);
                
        // Добавляем поле "vin2"
        $this->add([           
            'type'  => 'text',
            'name' => 'vin2',
            'attributes' => [
                'id' => 'vin2'
            ],
            'options' => [
                'label' => 'VIN дополнительный',
            ],
        ]);

        // Добавляем поле "comment"
        $this->add([           
            'type'  => 'textarea',
            'name' => 'comment',
            'attributes' => [
                'id' => 'comment',
                'rows' => 5
            ],
            'options' => [
                'label' => 'Описание',
            ],
        ]);
                
        // Добавляем поле "yocm"
        $this->add([           
            'type'  => 'number',
            'name' => 'yocm',
            'attributes' => [
                'id' => 'yocm'
            ],
            'options' => [
                'label' => 'Год',
            ],
            'attributes' => [
                'min' => '0',
                'max' => date('Y'),
//                'step' => '1', // default step interval is 1
            ],
        ]);
                
        // Add "status" field
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'options' => [
                'label' => 'Статус',
                'value_options' => ContactCar::getStatusList(),
            ],
        ]);
        
        // Add "wheel" field
        $this->add([            
            'type'  => 'select',
            'name' => 'wheel',
            'attributes' => [
                'value' => ContactCar::WHEEL_LEFT,
            ],
            'options' => [
                'label' => 'Руль',
                'value_options' => ContactCar::getWheelList(),
            ],
        ]);
        
        // Add "tm" field
        $this->add([            
            'type'  => 'select',
            'name' => 'tm',
            'attributes' => [
                'value' => ContactCar::TM_UNKNOWN,
            ],
            'options' => [
                'label' => 'Коробка',
                'value_options' => ContactCar::getTmList(),
            ],
        ]);
        
        // Add "ac" field
        $this->add([            
            'type'  => 'select',
            'name' => 'ac',
            'attributes' => [
                'value' => ContactCar::AC_UNKNOWN,
            ],
            'options' => [
                'label' => 'Кондиционер',
                'value_options' => ContactCar::getAcList(),
            ],
        ]);
        
        // Добавляем поле "md"
        $this->add([           
            'type'  => 'text',
            'name' => 'md',
            'attributes' => [
                'id' => 'md'
            ],
            'options' => [
                'label' => 'Двигатель',
            ],
        ]);
                
        // Добавляем поле "ed"
        $this->add([           
            'type'  => 'text',
            'name' => 'ed',
            'attributes' => [
                'id' => 'ed'
            ],
            'options' => [
                'label' => 'Объем',
            ],
        ]);

        // Добавляем поле "ep"
        $this->add([           
            'type'  => 'text',
            'name' => 'ep',
            'attributes' => [
                'id' => 'ep'
            ],
            'options' => [
                'label' => 'Мощность',
            ],
        ]);
                
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'contact_car_submitbutton',
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
                'name'     => 'make',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    [
                        'name'    => 'IsInt',
                        'options' => [
                            'min' => 0,
                            'locale' => 'ru-Ru'
                        ],
                    ],
                ],
            ]);          
        
        $inputFilter->add([
                'name'     => 'model',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    [
                        'name'    => 'IsInt',
                        'options' => [
                            'min' => 0,
                            'locale' => 'ru-Ru'
                        ],
                    ],
                ],
            ]);          
        
        $inputFilter->add([
                'name'     => 'car',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    [
                        'name'    => 'IsInt',
                        'options' => [
                            'min' => 0,
                            'locale' => 'ru-Ru'
                        ],
                    ],
                ],
            ]);          
        
        
        $inputFilter->add([
                'name'     => 'vin',
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
                            'max' => 17
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'vin2',
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
                            'max' => 17
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
                            'max' => 1024
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
                    ['name'=>'InArray', 'options'=>['haystack'=>array_keys(ContactCar::getStatusList())]]
                ],
            ]); 
        
        // Add input for "wheel" field
        $inputFilter->add([
                'name'     => 'wheel',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>array_keys(ContactCar::getWheelList())]]
                ],
            ]); 
        
        // Add input for "tm" field
        $inputFilter->add([
                'name'     => 'tm',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>array_keys(ContactCar::getTmList())]]
                ],
            ]); 
        
        // Add input for "ac" field
        $inputFilter->add([
                'name'     => 'ac',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>array_keys(ContactCar::getAcList())]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'yocm',
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
                            'max' => 4
                        ],
                    ],
                    [    
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min' => 1980,
                            'inclusive' => false
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'md',
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
                            'max' => 64
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'ed',
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
                            'max' => 64
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'ep',
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
                            'max' => 64
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
