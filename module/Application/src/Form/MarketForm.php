<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Application\Entity\MarketPriceSetting;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
/**
 * Description of MarketForm
 *
 * @author Daddy
 */
class MarketForm extends Form
{
    
    protected $objectManager;

    protected $entityManager;
    
    /**
     * Конструктор.     
     */
    public function __construct($entityManager)
    {
        // Определяем имя формы.
        parent::__construct('market-form');
        
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
            'name' => 'region',
            'attributes' => [                
                'id' => 'region',
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Company\Entity\Region',
                'label' => 'Регион',
                'property'       => 'name',
//                'value' => 1,
            ],
       ]);        
                
        // Добавляем поле "name"
        $this->add([           
            'type'  => 'text',
            'name' => 'info',
            'attributes' => [
                'id' => 'info'
            ],
            'options' => [
                'label' => 'Описание',
            ],
        ]);
        
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
        
        // Add "status" field
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'options' => [
                'label' => 'Состояние',
                'value_options' => MarketPriceSetting::getStatusList(),
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'filename',
            'attributes' => [
                'id' => 'filename'
            ],
            'options' => [
                'label' => 'Наименование файла',
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'format',
            'attributes' => [
                'value' => MarketPriceSetting::FORMAT_YML,
            ],    
            'options' => [
                'label' => 'Тип файла',
                'value_options' => MarketPriceSetting::getFormatList(),
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'goodSetting',
            'attributes' => [
                'value' => MarketPriceSetting::IMAGE_MATH,
            ],    
            'options' => [
                'label' => 'Картинки',
                'value_options' => MarketPriceSetting::getGoodSettingList(),
            ],
        ]);
        
        $this->add([           
            'type'  => 'number',
            'name' => 'imageCount',
            'attributes' => [
                'id' => 'imageCount',
                'value' => 1,
                'min' => 0,
             ],
            'options' => [
                'label' => 'Количество картинок',
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'supplierSetting',
            'attributes' => [
                'value' => MarketPriceSetting::SUPPLIER_ALL,
            ],    
            'options' => [
                'label' => 'Поставщики',
                'value_options' => MarketPriceSetting::getSupplierSettingList(),
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'producerSetting',
            'attributes' => [
                'value' => MarketPriceSetting::PRODUCER_ALL,
            ],    
            'options' => [
                'label' => 'Производители',
                'value_options' => MarketPriceSetting::getProducerSettingList(),
            ],
        ]);
                                
        $this->add([            
            'type'  => 'select',
            'name' => 'groupSetting',
            'attributes' => [
                'value' => MarketPriceSetting::GROUP_ALL,
            ],    
            'options' => [
                'label' => 'Группы ТД',
                'value_options' => MarketPriceSetting::getGroupSettingList(),
            ],
        ]);
                                
        $this->add([            
            'type'  => 'select',
            'name' => 'tokenGroupSetting',
            'attributes' => [
                'value' => MarketPriceSetting::TOKEN_GROUP_ALL,
            ],    
            'options' => [
                'label' => 'Группы наименований',
                'value_options' => MarketPriceSetting::getTokenGroupSettingList(),
            ],
        ]);
                                
        $this->add([           
            'type'  => 'number',
            'name' => 'minPrice',
            'attributes' => [
                'id' => 'minPrice',
                'value' => 300,
                'min' => 0,
            ],
            'options' => [
                'label' => 'Цена минимальная',
            ],
        ]);
        
        $this->add([           
            'type'  => 'number',
            'name' => 'maxPrice',
            'attributes' => [
                'id' => 'maxPrice',
                'value' => 35000,
                'min' => 0,
            ],
            'options' => [
                'label' => 'Цена максимальная',
            ],
        ]);
        
        $this->add([           
            'type'  => 'number',
            'name' => 'maxRowCount',
            'attributes' => [
                'id' => 'maxRowCount',
                'value' => 0,
                'min' => 0,
            ],
            'options' => [
                'label' => 'Всего строк в прайсе',
            ],
        ]);
        
        $this->add([           
            'type'  => 'number',
            'name' => 'blockRowCount',
            'attributes' => [
                'id' => 'blockRowCount',
                'value' => 0,
                'min' => 0,
            ],
            'options' => [
                'label' => 'Количество строк в блоке',
            ],
        ]);
        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'market_submitbutton',
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
                            'max' => 128
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'info',
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
        
        $inputFilter->add([
                'name'     => 'filename',
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
                            'max' => 128
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
