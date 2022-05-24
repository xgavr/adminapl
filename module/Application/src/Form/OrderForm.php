<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Application\Entity\Client;
use Application\Entity\Order;

/**
 * Description of Order
 *
 * @author Daddy
 */
class OrderForm extends Form
{
    
    protected $objectManager;

    protected $entityManager;
        
    /**
     * Конструктор.     
     */
    public function __construct($entityManager)
    {
        // Определяем имя формы.
        parent::__construct('order-form');
     
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
                
        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'office',
            'attributes' => [                
                'id' => 'office',
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Company\Entity\Office',
                'label' => 'Офис',
                'property'       => 'name',
                'display_empty_item' => false,
                'empty_item_label'   => '--выберете офис--',
            ],
        ]);

        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'courier',
            'attributes' => [                
                'id' => 'courier',
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Application\Entity\Courier',
                'label' => 'ТК',
                'property' => 'name',
                'display_empty_item' => true,
                'empty_item_label'   => '--выберете ТК--',
            ],
        ]);
        
        // Добавляем поле "name"
        $this->add([           
            'type'  => 'search',
            'name' => 'name',
            'attributes' => [
                'id' => 'name',
                'autocomplete' => 'off',
            ],
            'options' => [
                'label' => 'Имя',
            ],
        ]);
        
        $this->add([           
            'type'  => 'search',
            'name' => 'phone',
            'attributes' => [
                'id' => 'phone',
                'autocomplete' => 'off',
            ],
            'options' => [
                'label' => 'Телефон',
            ],
        ]);
        
        $this->add([           
            'type'  => 'search',
            'name' => 'email',
            'attributes' => [
                'id' => 'email',
                'autocomplete' => 'off',
            ],
            'options' => [
                'label' => 'Email',
            ],
        ]);
                
        $this->add([           
            'type'  => 'text',
            'name' => 'vin',
            'attributes' => [
                'id' => 'vin',
                'autocomplete' => 'off',
            ],
            'options' => [
                'label' => 'VIN',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'make',
            'attributes' => [
                'id' => 'make',
                'autocomplete' => 'off',
            ],
            'options' => [
                'label' => 'Авто',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'makeComment',
            'attributes' => [
                'id' => 'makeComment'
            ],
            'options' => [
                'label' => 'Дополнительно о машине',
            ],
        ]);

        $this->add([           
            'type'  => 'textarea',
            'name' => 'info',
            'attributes' => [
                'id' => 'info'
            ],
            'options' => [
                'label' => 'Что нужно',
            ],
        ]);
        
        // Добавляем поле "address"
        $this->add([           
            'type'  => 'textarea',
            'name' => 'address',
            'attributes' => [
                'id' => 'address'
            ],
            'options' => [
                'label' => 'Адрес доставки',
            ],
        ]);
        
        $this->add([           
            'type'  => 'date',
            'name' => 'dateShipment',
            'attributes' => [
                'id' => 'dateShipment',
//                'min' => date('Y-m-d'),
                'value' => date('Y-m-d'),
            ],
            'options' => [
                'label' => 'Дата доставки',
            ],
        ]);

        $this->add([           
            'type'  => 'select',
            'name' => 'timeShipment',
            'attributes' => [
                'id' => 'timeShipment'
            ],
            'options' => [
                'label' => 'Время доставки',
                'value_options' => [13 => 'к 13', 15 => 'к 15', 17 => 'к 17', 19 => 'к 19'],
            ],
        ]);

        $this->add([           
            'type'  => 'select',
            'name' => 'shipping',
            'attributes' => [
                'id' => 'shipping'
            ],
            'options' => [
                'label' => 'Вариант доставки',
            ],
        ]);

        $this->add([           
            'type'  => 'hidden',
            'name' => 'shipmentRate',
            'attributes' => [
                'id' => 'shipmentRate'
            ],
            'options' => [
                'label' => 'Тариф',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'shipmentDistance',
            'attributes' => [
                'id' => 'shipmentDistance'
            ],
            'options' => [
                'label' => 'Расстояние',
            ],
        ]);

        $this->add([           
            'type'  => 'hidden',
            'name' => 'rateDistance',
            'attributes' => [
                'id' => 'rateDistance'
            ],
            'options' => [
                'label' => 'Цена за км',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'shipmentTotal',
            'attributes' => [
                'id' => 'shipmentTotal'
            ],
            'options' => [
                'label' => 'Стоимость доставки',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'trackNumber',
            'attributes' => [
                'id' => 'trackNumber'
            ],
            'options' => [
                'label' => 'Накладная ТК',
            ],
        ]);

        $this->add([
            'type' => 'csrf',
            'name' => 'csrf',
            'options' => [
                'csrf_options' => [
                'timeout' => 3600
                ]
            ],
        ]);
        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'order_submitbutton',
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
