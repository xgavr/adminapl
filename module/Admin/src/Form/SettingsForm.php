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
class SettingsForm extends Form implements ObjectManagerAwareInterface
{
    
    protected $objectManager;    
    
    protected $entityManager;    
    
    /**
     * Конструктор.     
     */
    public function __construct($entityManager)
    {
        // Определяем имя формы.
        parent::__construct('settings-form');
             
        $this->entityManager = $entityManager;
        
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();         
    }

    protected function addElements() 
    {
                        
        // Добавляем поле "name"
        $this->add([           
            'type'  => 'text',
            'name' => 'sms_ru_api_id',
            'attributes' => [
                'id' => 'sms_ru_api_id'
            ],
            'options' => [
                'label' => 'SMS.RU api_id',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'sms_ru_url',
            'attributes' => [
                'id' => 'sms_ru_url'
            ],
            'options' => [
                'label' => 'SMS.RU api url',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'tamtam_access_token',
            'attributes' => [
                'id' => 'tamtam_access_token'
            ],
            'options' => [
                'label' => 'ТамТам access token',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'tamtam_chat_id',
            'attributes' => [
                'id' => 'tamtam_chat_id'
            ],
            'options' => [
                'label' => 'ТамТам чат Id',
            ],
        ]);

                
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'taxsubmitbutton',
            ],
        ]);        
    }
    
    private function addInputFilter() 
    {
        
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
                
        $inputFilter->add([
                'name'     => 'sms_ru_api_id',
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
                'name'     => 'sms_ru_url',
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
                'name'     => 'tamtam_chat_id',
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
                'name'     => 'tamtam_access_token',
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
