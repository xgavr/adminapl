<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Application\Entity\Shipping;

/**
 * Description of Shipping
 *
 * @author Daddy
 */
class ShippingForm extends Form
{
    
    protected $objectManager;

    protected $entityManager;
        
    /**
     * Конструктор.     
     */
    public function __construct($entityManager)
    {
        // Определяем имя формы.
        parent::__construct('shipping-form');
     
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
        
        // Добавляем поле "rateTrip"
        $this->add([           
            'type'  => 'number',
            'name' => 'rateTrip',
            'attributes' => [
                'id' => 'rateTrip',
                'min'  => '0',
                'step' => '10', 
                'value' => 0,
            ],
            'options' => [
                'label' => 'Стоимость за поездку',
            ],
        ]);
        
        // Добавляем поле "rateTrip"
        $this->add([           
            'type'  => 'number',
            'name' => 'rateTrip1',
            'attributes' => [
                'id' => 'rateTrip1',
                'min'  => '0',
                'step' => '10', 
                'value' => 0,
            ],
            'options' => [
                'label' => 'Стоимость за поездку 1',
            ],
        ]);
        
        // Добавляем поле "rateTrip"
        $this->add([           
            'type'  => 'number',
            'name' => 'rateTrip2',
            'attributes' => [
                'id' => 'rateTrip2',
                'min'  => '0',
                'step' => '10', 
                'value' => 0,
            ],
            'options' => [
                'label' => 'Стоимость за поездку 2',
            ],
        ]);
        
        // Добавляем поле "rateDistance"
        $this->add([           
            'type'  => 'number',
            'name' => 'rateDistance',
            'attributes' => [
                'id' => 'rateDistance',
                'min'  => '0',
                'step' => '10',
                'value' => 0,
            ],
            'options' => [
                'label' => 'Стоимость за км',
            ],
        ]);
        
        // Добавляем поле "sorting"
        $this->add([           
            'type'  => 'number',
            'name' => 'sorting',
            'attributes' => [
                'id' => 'sorting',
                'min'  => '0',
                'step' => '10', 
                'value' => 0,
            ],
            'options' => [
                'label' => 'Сортировка',
            ],
        ]);
        
        // Add "status" field
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'options' => [
                'label' => 'Статус',
                'value_options' => Shipping::getStatusList(),
            ],
        ]);
        
        // Add "rate" field
        $this->add([            
            'type'  => 'select',
            'name' => 'rate',
            'options' => [
                'label' => 'Тариф',
                'value_options' => Shipping::getRatesList(),
            ],
        ]);
        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'shipping_submitbutton',
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
                    ['name'=>'InArray', 'options'=>['haystack'=> array_keys(Shipping::getStatusList())]]
                ],
            ]); 
        
        // Add input for "rate" field
        $inputFilter->add([
                'name'     => 'rate',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=> array_keys(Shipping::getRatesList())]]
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
