<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
/**
 * Description of RequestSettingForm
 *
 * @author Daddy
 */
class SupplySettingForm extends Form implements ObjectManagerAwareInterface
{

    protected $objectManager;

    protected $entityManager;
        
    
    /**
     * Конструктор.     
     */
    public function __construct($entityManager)
    {
        // Определяем имя формы.
        parent::__construct('supply-setting-form');
        
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
                'data-live-search'=> "true",
                'class' => "selectpicker",
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Company\Entity\Office',
                'label' => 'Офис',
                'property'       => 'name',
                'display_empty_item' => true,
                'empty_item_label'   => '--выберете офис--',                 
            ],
       ]);
        
        
                
        // Добавляем поле "supplyTime"
        $this->add([           
            'type'  => 'number',
            'name' => 'supplyTime',
            'attributes' => [
                'id' => 'supply-time'
            ],
            'options' => [
                'label' => 'Время подвоза в часах',
            ],
        ]);
        
        $this->add([           
            'type'  => 'time',
            'name' => 'orderBefore',
            'attributes' => [
                'id' => 'order-before',
                'value' => '12:00',
            ],
            'options' => [
                'label' => 'Заказть до',
                'format' => 'H:i',
            ],
        ]);
                
        // Add "status" field
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'options' => [
                'label' => 'Статус',
                'value_options' => [
                    1 => 'Использовать',
                    2 => 'Не использовать',                    
                ]
            ],
        ]);
        
        // Add "supply" field
        $this->add([            
            'type'  => 'select',
            'name' => 'supplySat',
            'options' => [
                'label' => 'Возможна доставка в субботу',
                'value_options' => [
                    1 => 'Возможна',
                    2 => 'Не возможна',                    
                ]
            ],
        ]);
        
                
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'submitbutton',
            ],
        ]);        
        
        $this->add([
            'type' => 'csrf',
            'name' => 'csrf',
            'options' => [
                'csrf_options' => [
                'timeout' => 600
                ]
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
                'name'     => 'office',
                'required' => true,
                'filters'  => [
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min' => 0,
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'supplyTime',
                'required' => false,
                'filters'  => [
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min' => 0,
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'orderBefore',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],
                ],                
//                'validators' => [
//                    [
//                        'name'    => 'Date',
//                        'options' => [
//                            'format' => 'H:i',
//                        ],
//                    ],
//                ],
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
        
        // Add input for "supplySat" field
        $inputFilter->add([
                'name'     => 'supplySat',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
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
