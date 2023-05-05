<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ApiMarketPlace\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use ApiMarketPlace\Entity\Marketplace;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Description of Abcp
 *
 * @author Daddy
 */
class MarketplaceSetting extends Form implements ObjectManagerAwareInterface
{
    
    protected $objectManager;    
    
    /**
     * Конструктор.     
     */
    public function __construct()
    {
        // Определяем имя формы.
        parent::__construct('marketplace-form');
             
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();         
    }

    protected function addElements() 
    {
                        
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
            'name' => 'site',
            'attributes' => [
                'id' => 'site'
            ],
            'options' => [
                'label' => 'Сайт',
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
            'name' => 'merchantId',
            'attributes' => [
                'id' => 'merchantId'
            ],
            'options' => [
                'label' => 'ID Личного кабинета',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'apiToken',
            'attributes' => [
                'id' => 'apiToken'
            ],
            'options' => [
                'label' => 'Авторизационный токен',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'comment',
            'attributes' => [
                'id' => 'comment'
            ],
            'options' => [
                'label' => 'Комментарий',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'phone',
            'attributes' => [
                'id' => 'phone'
            ],
            'options' => [
                'label' => 'Телефон',
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
            'type'  => 'select',
            'name' => 'contract',
            'attributes' => [                
                'id' => 'contract'
            ],
            'options' => [
                'label' => 'Договор с комитентом',
            ],
        ]);
        
        // Add "status" field
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'attributes' => [
                'id' => 'status',
                'value' => Marketplace::STATUS_ACTIVE,
            ],
            'options' => [
                'label' => 'Статус',
                'value_options' => Marketplace::getStatusList(),
            ],
        ]);

        // Add "status" field
        $this->add([            
            'type'  => 'select',
            'name' => 'marketType',
            'attributes' => [
                'id' => 'marketType',
                'value' => Marketplace::TYPE_UNKNOWN,
            ],
            'options' => [
                'label' => 'Тип ТП',
                'value_options' => Marketplace::getMarketTypeList(),
            ],
        ]);

        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'marketplace_settings_submit_button',
            ],
        ]);        
    }
    
    private function addInputFilter() 
    {
        
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
                
        $inputFilter->add([
                'name'     => 'site',
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
                            'max' => 1024
                        ],
                    ],
                ],
            ]);          
        
        $inputFilter->add([
                'name'     => 'apiToken',
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
                            'max' => 1024
                        ],
                    ],
                ],
            ]);     

        $inputFilter->add([
                'name'     => 'phone',
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
                'name'     => 'contact',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                ],
            ]); 

        $inputFilter->add([
                'name'     => 'contract',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
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
