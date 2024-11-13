<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Laminas\InputFilter\InputFilter;
use Application\Entity\SupplierApiSetting;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
/**
 * Description of RequestSettingForm
 *
 * @author Daddy
 */
class SupplierApiSettingForm extends Form 
{

    protected $objectManager;

    protected $entityManager;
        
    
    /**
     * Конструктор.     
     */
    public function __construct($entityManager)
    {
        // Определяем имя формы.
        parent::__construct('supplier-api-setting-form');
        
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
            'type'  => 'select',
            'name' => 'name',
            'attributes' => [
                'id' => 'name'
            ],
            'options' => [
                'label' => 'Наименование',
                'value_options' => SupplierApiSetting::getNameList(),
            ],
        ]);
        
        $this->add([           
            'type'  => Element\Url::class,
            'name' => 'baseUri',
            'attributes' => [
                'id' => 'baseUri'
            ],
            'options' => [
                'label' => 'Сайт',
            ],
        ]);
        
        $this->add([           
            'type'  => Element\Url::class,
            'name' => 'testUri',
            'attributes' => [
                'id' => 'testUri'
            ],
            'options' => [
                'label' => 'Тестовая ссылка',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'login',
            'attributes' => [
                'id' => 'login'
            ],
            'options' => [
                'label' => 'Логин',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'password',
            'attributes' => [
                'id' => 'password'
            ],
            'options' => [
                'label' => 'Пароль',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'userId',
            'attributes' => [
                'id' => 'userId'
            ],
            'options' => [
                'label' => 'Секретный ключ',
            ],
        ]);                
                
        // Add "status" field
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'options' => [
                'label' => 'Статус',
                'value_options' => SupplierApiSetting::getStatusList(),
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
                'name'     => 'name',
                'required' => true,
                'filters'  => [
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=> array_keys(SupplierApiSetting::getNameList())]]
                ],
            ]);
        
        $inputFilter->add([
            'name' => 'baseUri',
            'required' => false,
            'validators' => [
                ['name' => 'uri'],
            ],
        ]);
        
        $inputFilter->add([
            'name' => 'testUri',
            'required' => false,
            'validators' => [
                ['name' => 'uri'],
            ],
        ]);
        
        $inputFilter->add([
                'name'     => 'login',
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
                            'max' => 128
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'password',
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
                            'max' => 128
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'userId',
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
                            'max' => 128
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
                    ['name'=>'InArray', 'options'=>['haystack'=> array_keys(SupplierApiSetting::getStatusList())]]
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
