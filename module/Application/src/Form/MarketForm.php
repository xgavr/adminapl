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
use Application\Entity\ScaleTreshold;

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
            ],
        ]);        

        $this->add([            
            'type'  => 'select',
            'name' => 'supplier',
            'options' => [
                'label' => 'Поставщик',
            ],
        ]);

        $this->add([            
            'type'  => 'select',
            'name' => 'shipping',
            'attributes' => [                
                'id' => 'shipping',
            ],
            'options' => [
                'label' => 'Доставка',
            ],
        ]);

        $this->add([            
            'type'  => 'select',
            'name' => 'rates',
            'attributes' => [
                'multiple' => 'multiple',
            ],
            'options' => [
                'label' => 'Расценки',
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
                'label' => 'Картинок',
            ],
        ]);
        
        $this->add([            
            'type'  => 'hidden',
            'name' => 'supplierSetting',
            'attributes' => [
                'value' => MarketPriceSetting::SUPPLIER_ALL,
            ],    
//            'options' => [
//                'label' => 'Поставщики',
//                'value_options' => MarketPriceSetting::getSupplierSettingList(),
//            ],
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
            'type'  => 'select',
            'name' => 'nameSetting',
            'attributes' => [
                'value' => MarketPriceSetting::NAME_ALL,
            ],    
            'options' => [
                'label' => 'Наименования',
                'value_options' => MarketPriceSetting::getNameSettingList(),
            ],
        ]);
                                
        $this->add([            
            'type'  => 'hidden',
            'name' => 'restSetting',
            'attributes' => [
                'value' => MarketPriceSetting::REST_ALL,
            ],    
//            'options' => [
//                'label' => 'Остатки',
//                'value_options' => MarketPriceSetting::getRestSettingList(),
//            ],
        ]);
                                
        $this->add([            
            'type'  => 'select',
            'name' => 'tdSetting',
            'attributes' => [
                'value' => MarketPriceSetting::TD_IGNORE,
            ],    
            'options' => [
                'label' => 'Текдок',
                'value_options' => MarketPriceSetting::getTdSettingList(),
            ],
        ]);
                                
        $this->add([            
            'type'  => 'select',
            'name' => 'pricecol',
            'attributes' => [
                'value' => 0,
            ],    
            'options' => [
                'label' => 'Колонка цен',
                'value_options' => ScaleTreshold::getPricecolList(),
            ],
        ]);
                                
        $this->add([            
            'type'  => 'number',
            'name' => 'movementLimit',
            'attributes' => [
                'value' => MarketPriceSetting::MOVEMENT_LIMIT,
                'min' => 0,
                'step' => 100,
            ],    
            'options' => [
                'label' => 'Фильтр движений',
            ],
        ]);
                                        
        $this->add([           
            'type'  => 'number',
            'name' => 'minPrice',
            'attributes' => [
                'id' => 'minPrice',
                'value' => 300,
                'step' => 50,
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
                'step' => 1000,
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
                'label' => 'Строк в прайсе',
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
                'label' => 'Строк в блоке',
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
                'name'     => 'rates',
                'required' => false,
                'filters'  => [],                
                'validators' => [],
            ]);
        
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
