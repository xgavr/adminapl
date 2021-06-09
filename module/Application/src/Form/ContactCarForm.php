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
    private $entityManager = null;    
        
    /**
     * Current contact.
     * @var \Aplication\Entity\Contact 
     */
    private $contact = null;
    
    /**
     * Current car.
     * @var \Aplication\Entity\Car 
     */
    private $car = null;
    
    /**
     * Current model.
     * @var \Aplication\Entity\Model
     */
    private $model = null;
    
    /**
     * Current make.
     * @var \Aplication\Entity\Make 
     */
    private $make = null;
    
    /**
     * Конструктор.     
     */
    public function __construct($entityManager = null, $contact = null, 
            $make = null, $model = null, $car = null)
    {
        // Определяем имя формы.
        parent::__construct('contact-car-form');
     
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
        
        $this->entityManager = $entityManager;    
        $this->contact = $contact;
        $this->make = $make;
        $this->model = $model;
        $this->car = $car;
                
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
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'make',
            'attributes' => [                
                'id' => 'make',
                'data-live-search'=> "true",
                'class' => "selectpicker",
                //'value' => $this->make->getId(),
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Application\Entity\Make',
                'label' => 'Марка',
                'property'       => 'name',
                'display_empty_item' => true,
                'empty_item_label'   => '--выберете марку--',
            ],
        ]);

        // Добавляем поле "model"
        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'model',
            'attributes' => [                
                'id' => 'model',
                'data-live-search'=> "true",
                'class' => "selectpicker",
                //'value' => $this->model->getId(),
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Application\Entity\Model',
                'label' => 'Модель',
                'property'       => 'name',
                'display_empty_item' => true,
                'empty_item_label'   => '--выберете модель--',
            ],
        ]);

        // Добавляем поле "car"
        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'car',
            'attributes' => [                
                'id' => 'car',
                'data-live-search'=> "true",
                'class' => "selectpicker",
                //'value' => $this->car->getId(),
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Application\Entity\Car',
                'label' => 'Модификация',
                'property'       => 'name',
                'display_empty_item' => true,
                'empty_item_label'   => '--выберете модификацию--',
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
            'type'  => 'text',
            'name' => 'comment',
            'attributes' => [
                'id' => 'comment'
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
                'label' => 'Год выпуска',
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
            'options' => [
                'label' => 'Руль',
                'value_options' => ContactCar::getWheelList(),
            ],
        ]);
        
        // Add "tm" field
        $this->add([            
            'type'  => 'select',
            'name' => 'tm',
            'options' => [
                'label' => 'Коробка',
                'value_options' => ContactCar::getTmList(),
            ],
        ]);
        
        // Add "ac" field
        $this->add([            
            'type'  => 'select',
            'name' => 'ac',
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
                'label' => 'Модель двигателя',
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
                'label' => 'Рабочий объем двигателя',
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
                'label' => 'Мощность двигателя',
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
