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
class TelegramSettingsForm extends Form implements ObjectManagerAwareInterface
{
    
    protected $objectManager;    
    
    /**
     * Конструктор.     
     */
    public function __construct()
    {
        // Определяем имя формы.
        parent::__construct('telegram_settings-form');
             
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();         
    }

    protected function addElements() 
    {
                        
        $this->add([           
            'type'  => 'text',
            'name' => 'telegram_api_key',
            'attributes' => [
                'id' => 'telegram_api_key'
            ],
            'options' => [
                'label' => 'Телеграм api key',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'telegram_admin_chat_id',
            'attributes' => [
                'id' => 'telegram_admin_chat_id'
            ],
            'options' => [
                'label' => 'Телеграм чат администратора',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'telegram_group_chat_id',
            'attributes' => [
                'id' => 'telegram_group_chat_id'
            ],
            'options' => [
                'label' => 'Телеграм чат группы АПЛ',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'telegram_bot_name',
            'attributes' => [
                'id' => 'telegram_bot_name'
            ],
            'options' => [
                'label' => 'Телеграм имя бота',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'telegram_hook_url',
            'attributes' => [
                'id' => 'telegram_hook_url'
            ],
            'options' => [
                'label' => 'Телеграм hook url',
            ],
        ]);
                
        $this->add([           
            'type'  => 'text',
            'name' => 'telegram_proxy',
            'attributes' => [
                'id' => 'telegram_proxy'
            ],
            'options' => [
                'label' => 'Телеграм прокси',
            ],
        ]);

        $this->add([           
            'type'  => 'select',
            'name' => 'send_pospone_msg',
            'attributes' => [
                'id' => 'send_pospone_msg'
            ],
            'options' => [
                'label' => 'Посылать отложенные сообщения',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Остановить',                    
                ]
            ],
        ]);

        $this->add([           
            'type'  => 'select',
            'name' => 'sending_pospone_msg',
            'attributes' => [
                'id' => 'sending_pospone_msg'
            ],
            'options' => [
                'label' => 'Отправка отложенных сообщений',
                'value_options' => [
                    1 => 'Сейчас не идет',
                    2 => 'Идет',                    
                ]
            ],
        ]);

        $this->add([           
            'type'  => 'select',
            'name' => 'auto_check_proxy',
            'attributes' => [
                'id' => 'auto_check_proxy'
            ],
            'options' => [
                'label' => 'Автопроверка прокси',
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
                'id' => 'telegram_settings_submit_button',
            ],
        ]);        
    }
    
    private function addInputFilter() 
    {
        
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
                
        $inputFilter->add([
                'name'     => 'telegram_hook_url',
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
                'name'     => 'telegram_bot_name',
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
                'name'     => 'telegram_api_key',
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
                'name'     => 'telegram_admin_chat_id',
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
                'name'     => 'telegram_group_chat_id',
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
                'name'     => 'telegram_proxy',
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
                'name'     => 'auto_check_proxy',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'send_pospone_msg',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'sending_pospone_msg',
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
