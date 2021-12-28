<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Admin\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Description of AplExchange
 *
 * @author Daddy
 */
class AplExchangeForm extends Form implements ObjectManagerAwareInterface
{
    
    protected $objectManager;    
    
    /**
     * Конструктор.     
     */
    public function __construct()
    {
        // Определяем имя формы.
        parent::__construct('apl-exchange-form');
             
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();         
    }

    protected function addElements() 
    {
        
        $this->add([           
            'type'  => 'text',
            'name' => 'apl_secret_key',
            'attributes' => [
                'id' => 'apl_secret_key'
            ],
            'options' => [
                'label' => 'Пароль api apl',
            ],
        ]);
                
        
                        
        $this->add([            
            'type'  => 'select',
            'name' => 'get_acquiring',
            'options' => [
                'label' => 'Выгрузка эквайринга',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'get_good_id',
            'options' => [
                'label' => 'Обновлять Apl Id товаров',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'get_group_apl',
            'options' => [
                'label' => 'Обновлять Id групп товаров',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'get_producer_id',
            'options' => [
                'label' => 'Обновлять Apl Id производителей',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'get_car_id',
            'options' => [
                'label' => 'Обновлять Apl Id машин',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'rawprice',
            'options' => [
                'label' => 'Обновлять строки прайсов',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'image',
            'options' => [
                'label' => 'Обновлять картинки',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'group',
            'options' => [
                'label' => 'Обновлять группы',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'oem',
            'options' => [
                'label' => 'Обновлять номера',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'attribute',
            'options' => [
                'label' => 'Обновлять атрибуты',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'car',
            'options' => [
                'label' => 'Обновлять машины',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'good_name',
            'options' => [
                'label' => 'Обновлять наименования',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'good_price',
            'options' => [
                'label' => 'Обновлять цены',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'ptu',
            'options' => [
                'label' => 'Загружать документы',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'order',
            'options' => [
                'label' => 'Загружать заказы',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'cash',
            'options' => [
                'label' => 'Загружать платежи',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'market',
            'options' => [
                'label' => 'Выгружать парйс листы в ТП',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);

        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'submit_button',
            ],
        ]);        
    }
    
    private function addInputFilter() 
    {
        
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
                
        $inputFilter->add([
                'name'     => 'apl_secret_key',
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
                'name'     => 'get_acquiring',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'get_good_id',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'get_group_apl',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'get_producer_id',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'get_car_id',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'rawprice',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'image',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'group',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'oem',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'attribute',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'car',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'good_name',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'good_price',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'ptu',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'order',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'cash',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'market',
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
