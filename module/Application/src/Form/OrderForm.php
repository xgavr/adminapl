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
        
        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'user',
            'attributes' => [                
                'id' => 'user',
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'User\Entity\User',
                'label' => 'Менеджер',
                'property' => 'fullName',
                'display_empty_item' => true,
                'empty_item_label'   => '--ответственный--',
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
            'type'  => 'select',
            'name' => 'status',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Статус',
                'value_options' => Order::getStatusList(),
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
            'type'  => 'hidden',
            'name' => 'contactCar',
            'attributes' => [
            ],
            'options' => [
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'vin',
            'attributes' => [
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
            ],
            'options' => [
                'label' => 'Дополнительно о машине',
            ],
        ]);

        $this->add([           
            'type'  => 'textarea',
            'name' => 'info',
            'attributes' => [
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
            'type'  => 'text',
            'name' => 'note',
            'attributes' => [
                'id' => 'note',
                //'autocomplete' => 'off',
            ],
            'options' => [
                'label' => 'Комментарий менеджера',
            ],
        ]);

        $this->add([           
            'type'  => 'hidden',
            'name' => 'contact',
            'attributes' => [
                'id' => 'contact'
            ],
            'options' => [
                'label' => 'Контакт',
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
            'type'  => 'text',
            'name' => 'aplId',
            'attributes' => [
                'id' => 'aplId',
                'readOnly' => true,
            ],
            'options' => [
                'label' => 'aplId',
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

//        $this->add([
//            'type' => 'csrf',
//            'name' => 'csrf',
//            'options' => [
//                'csrf_options' => [
//                    'timeout' => 60*60*24,
//                ]
//            ],
//        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'legalName',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Покупатель наименование',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'legalInn',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Покупатель ИНН',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'legalKpp',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Покупатель КПП',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'legalOgrn',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Покупатель ОГРН',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'legalAddress',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Покупатель Местонахождение',
            ],
        ]);
        
        $this->add([           
            'type'  => 'hidden',
            'name' => 'legal',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Покупатель ЮЛ',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'recipientName',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Грузополучатель наименование',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'recipientInn',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Грузополучатель ИНН',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'recipientKpp',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Грузополучатель КПП',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'recipientOgrn',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Грузополучатель ОГРН',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'recipientAddress',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Грузополучатель Местонахождение',
            ],
        ]);
        
        $this->add([           
            'type'  => 'hidden',
            'name' => 'recipient',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Грузополучатель',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'rs',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Расчетный счет',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'ks',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Корр. счет',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'bik',
            'attributes' => [
            ],
            'options' => [
                'label' => 'БИК банка',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'bankName',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Наименованние банка',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'bankCity',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Город банка',
            ],
        ]);

        $this->add([           
            'type'  => 'hidden',
            'name' => 'bankAccount',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Банк плательщика',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'invoiceInfo',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Комментарий к счету',
            ],
        ]);

        $this->add([           
            'type'  => 'hidden',
            'name' => 'selections',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Подбор',
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
                'name'     => 'user',
                'required' => false,
                'filters'  => [],                
                'validators' => [],
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

        $inputFilter->add([
                'name'     => 'phone',
                'required' => false,
                'filters'  => [
                    [
                        'name' => PhoneFilter::class,
                        'options' => [
                            'format' => PhoneFilter::PHONE_FORMAT_DB,
                        ]
                    ],
                ],                
                'validators' => [],
            ]);        

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
                            'allow' => \Laminas\Validator\Hostname::ALLOW_DNS,
                            'useMxCheck'    => false,                            
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
