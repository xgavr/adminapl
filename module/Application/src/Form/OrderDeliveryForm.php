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
use User\Filter\PhoneFilter;
use User\Validator\PhoneExistsValidator;
use User\Validator\EmailExistsValidator;

/**
 * Description of Order delivery
 *
 * @author Daddy
 */
class OrderDeliveryForm extends Form
{
    
    protected $objectManager;

    protected $entityManager;
        
    /**
     * Конструктор.     
     */
    public function __construct($entityManager)
    {
        // Определяем имя формы.
        parent::__construct('order-delivery-form');
     
        $this->entityManager = $entityManager;
        // Задает для этой формы метод POST.
        //$this->setAttribute('method', 'post');
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
        
        $this->add([           
            'type'  => 'search',
            'name' => 'phone2',
            'attributes' => [
                'id' => 'phone2',
                'autocomplete' => 'off',
            ],
            'options' => [
                'label' => 'Телефон дополнительный',
            ],
        ]);
        
        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'skiper',
            'attributes' => [                
                'id' => 'skiper',
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'User\Entity\User',
                'label' => 'Водитель',
                'property' => 'fullName',
                'display_empty_item' => true,
                'empty_item_label'   => '--водитель--',
            ],
        ]);
        
        $this->add([           
            'type'  => 'date',
            'name' => 'dateShipment',
            'attributes' => [
                'id' => 'dateShipment',
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
            'type'  => 'hidden',
            'name' => 'rate',
            'attributes' => [
                'id' => 'rate'
            ],
            'options' => [
                'label' => 'Способ расчета доставки',
            ],
        ]);

        $this->add([           
            'type'  => 'hidden',
            'name' => 'shipmentRate1',
            'attributes' => [
                'id' => 'shipmentRate1'
            ],
            'options' => [
                'label' => 'Тариф1',
            ],
        ]);

        $this->add([           
            'type'  => 'hidden',
            'name' => 'shipmentRate2',
            'attributes' => [
                'id' => 'shipmentRate2'
            ],
            'options' => [
                'label' => 'Тариф2',
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
            'name' => 'infoShipping',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Комментарий к доставке',
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
            'type'  => 'hidden',
            'name' => 'orderId',
            'attributes' => [
                'id' => 'orderId'
            ],
            'options' => [
                'label' => 'Id',
            ],
        ]);

        $this->add([           
            'type'  => 'hidden',
            'name' => 'shippingLimit1',
            'attributes' => [
                'id' => 'shippingLimit1'
            ],
            'options' => [
                'label' => 'Лимит для расчета доставки 1',
            ],
        ]);

        $this->add([           
            'type'  => 'hidden',
            'name' => 'shippingLimit2',
            'attributes' => [
                'id' => 'shippingLimit2'
            ],
            'options' => [
                'label' => 'Лимит для расчета доставки 2',
            ],
        ]);

        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'order_delivery_submitbutton',
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
                'name'     => 'courier',
                'required' => false,
                'filters'  => [],                
                'validators' => [],
            ]);        

        $inputFilter->add([
                'name'     => 'address',
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
                'name'     => 'skiper',
                'required' => false,
                'filters'  => [],                
                'validators' => [],
            ]);        

        $inputFilter->add([
                'name'     => 'shipmentDistance',
                'required' => false,
                'filters'  => [],                
                'validators' => [],
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
