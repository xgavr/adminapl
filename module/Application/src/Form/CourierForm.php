<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Application\Entity\Courier;

/**
 * Description of Courier
 *
 * @author Daddy
 */
class CourierForm extends Form
{
    
    protected $objectManager;

    protected $entityManager;
        
    /**
     * Конструктор.     
     */
    public function __construct($entityManager)
    {
        // Определяем имя формы.
        parent::__construct('courier-form');
     
        $this->entityManager = $entityManager;
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
                'id' => 'name'
            ],
            'options' => [
                'label' => 'Наименование',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'aplId',
            'attributes' => [
                'id' => 'aplId'
            ],
            'options' => [
                'label' => 'AplId',
            ],
        ]);
        
        
        // Добавляем поле "comment"
        $this->add([           
            'type'  => 'textarea',
            'name' => 'comment',
            'attributes' => [
                'id' => 'comment'
            ],
            'options' => [
                'label' => 'Описание',
            ],
        ]);
        
        // Добавляем поле "site"
        $this->add([           
            'type'  => 'url',
            'name' => 'site',
            'attributes' => [
                'id' => 'site'
            ],
            'options' => [
                'label' => 'Сайт',
            ],
        ]);
        
        // Добавляем поле "track"
        $this->add([           
            'type'  => 'url',
            'name' => 'track',
            'attributes' => [
                'id' => 'track'
            ],
            'options' => [
                'label' => 'Отслеживание',
            ],
        ]);
        
        // Добавляем поле "calculator"
        $this->add([           
            'type'  => 'url',
            'name' => 'calculator',
            'attributes' => [
                'id' => 'calculator'
            ],
            'options' => [
                'label' => 'Калькулятор',
            ],
        ]);

        // Add "status" field
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'options' => [
                'label' => 'Статус',
                'value_options' => Courier::getStatusList(),
            ],
        ]);
        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'courier_submitbutton',
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
                'name'     => 'aplId',
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
                            'max' => 512
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
                    ['name'=>'InArray', 'options'=>['haystack'=> array_keys(Courier::getStatusList())]]
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
