<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Admin\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Description of Goods
 *
 * @author Daddy
 */
class PriceSettingsForm extends Form implements ObjectManagerAwareInterface
{
    
    protected $objectManager;    
    
    /**
     * Конструктор.     
     */
    public function __construct()
    {
        // Определяем имя формы.
        parent::__construct('settings-form');
             
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();         
    }

    protected function addElements() 
    {
                        
        $this->add([            
            'type'  => 'select',
            'name' => 'receiving_mail',
            'options' => [
                'label' => 'Прайсы по почте',
                'value_options' => [
                    1 => 'Получать',
                    2 => 'Не получать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'receiving_link',
            'options' => [
                'label' => 'Прайсы по ссылке',
                'value_options' => [
                    1 => 'Скачивать',
                    2 => 'Не скачивать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'upload_raw',
            'options' => [
                'label' => 'Загружать прайсы в БД',
                'value_options' => [
                    1 => 'Загружать',
                    2 => 'Не загружать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'parse_raw',
            'options' => [
                'label' => 'Разборка загруженных прайсов',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Остановить',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'parse_producer',
            'options' => [
                'label' => 'Разборка производителей из прайсов',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Остановить',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'parse_article',
            'options' => [
                'label' => 'Разборка номеров производителей из прайсов',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Остановить',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'parse_oem',
            'options' => [
                'label' => 'Разборка номеров производителей из прайсов',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Остановить',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'parse_name',
            'options' => [
                'label' => 'Разборка наименований товаров из прайсов',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Остановить',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'assembly_producer',
            'options' => [
                'label' => 'Создавать производителей',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Остановить',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'assembly_good',
            'options' => [
                'label' => 'Создавать товары',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Остановить',                    
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
                'name'     => 'receiving_mail',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'receiving_link',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'upload_raw',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'parse_raw',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'parse_raw',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'parse_producer',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'parse_article',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'parse_oem',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'parse_name',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'assembly_producer',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'assembly_good',
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
