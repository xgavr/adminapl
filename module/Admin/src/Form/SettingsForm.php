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
 * Description of Goods
 *
 * @author Daddy
 */
class SettingsForm extends Form 
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

//        $this->add([           
//            'type'  => 'text',
//            'name' => 'wamm_url',
//            'attributes' => [
//                'id' => 'wamm_url'
//            ],
//            'options' => [
//                'label' => 'WAMM url',
//            ],
//        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'wamm_api_id',
            'attributes' => [
                'id' => 'wamm_api_id'
            ],
            'options' => [
                'label' => 'WAMM токен',
            ],
        ]);

        $this->add([           
            'type'  => 'select',
            'name' => 'wamm_read',
            'attributes' => [
                'id' => 'wamm_read'
            ],
            'options' => [
                'label' => 'WAMM читать',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'dadata_api_key',
            'attributes' => [
                'id' => 'dadata_api_key'
            ],
            'options' => [
                'label' => 'Дадата API-ключ',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'dadata_standart_key',
            'attributes' => [
                'id' => 'dadata_standart_key'
            ],
            'options' => [
                'label' => 'Дадата Секретный ключ',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'ftp_apl_suppliers_price',
            'attributes' => [
                'id' => 'ftp_apl_suppliers_price'
            ],
            'options' => [
                'label' => 'FTP АПЛ',
            ],
        ]);
                
        $this->add([           
            'type'  => 'text',
            'name' => 'ftp_apl_suppliers_price_login',
            'attributes' => [
                'id' => 'ftp_apl_suppliers_price_login'
            ],
            'options' => [
                'label' => 'Логин на FTP АПЛ для прайсов поставщиков',
            ],
        ]);
                
        $this->add([           
            'type'  => 'text',
            'name' => 'ftp_apl_suppliers_price_password',
            'attributes' => [
                'id' => 'ftp_apl_suppliers_price_password'
            ],
            'options' => [
                'label' => 'Пароль на FTP АПЛ для прайсов поставщиков',
            ],
        ]);
                
        $this->add([           
            'type'  => 'text',
            'name' => 'ftp_apl_market_price_login',
            'attributes' => [
                'id' => 'ftp_apl_market_price_login'
            ],
            'options' => [
                'label' => 'Логин на FTP АПЛ для прайсов маркетов',
            ],
        ]);
                
        $this->add([           
            'type'  => 'text',
            'name' => 'ftp_apl_market_price_password',
            'attributes' => [
                'id' => 'ftp_apl_market_price_password'
            ],
            'options' => [
                'label' => 'Пароль на FTP АПЛ для прайсов маркетов',
            ],
        ]);
                
        $this->add([            
            'type'  => 'email',
            'name' => 'autoru_email',
            'attributes' => [
                'id' => 'autoru_email'
            ],
            'options' => [
                'label' => 'Email для получения заказов AutoRu',
            ],
        ]);
                
        $this->add([            
            'type'  => 'text',
            'name' => 'autoru_email_password',
            'attributes' => [
                'id' => 'autoru_email_password'
            ],
            'options' => [
                'label' => 'Пароль на email AutoRu',
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'hello_check',
            'options' => [
                'label' => 'Проверка почты',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);
        
        $this->add([            
            'type'  => 'email',
            'name' => 'hello_email',
            'attributes' => [
                'id' => 'hello_email'
            ],
            'options' => [
                'label' => 'Email менджеров',
            ],
        ]);
                
        $this->add([            
            'type'  => 'text',
            'name' => 'hello_email_password',
            'attributes' => [
                'id' => 'hello_email_password'
            ],
            'options' => [
                'label' => 'Пароль на email менджеров',
            ],
        ]);
                
        $this->add([            
            'type'  => 'text',
            'name' => 'hello_app_password',
            'attributes' => [
                'id' => 'hello_app_password'
            ],
            'options' => [
                'label' => 'Пароль приложения почты менджеров',
            ],
        ]);

        $this->add([            
            'type'  => 'email',
            'name' => 'info_email',
            'attributes' => [
                'id' => 'info_email'
            ],
            'options' => [
                'label' => 'Email общий',
            ],
        ]);
                
        $this->add([            
            'type'  => 'text',
            'name' => 'info_email_password',
            'attributes' => [
                'id' => 'info_email_password'
            ],
            'options' => [
                'label' => 'Пароль на email общий',
            ],
        ]);
                
        $this->add([            
            'type'  => 'text',
            'name' => 'info_app_password',
            'attributes' => [
                'id' => 'info_app_password'
            ],
            'options' => [
                'label' => 'Пароль приложения общей почты',
            ],
        ]);

        $this->add([            
            'type'  => 'email',
            'name' => 'telefonistka_email',
            'attributes' => [
                'id' => 'telefonistka_email'
            ],
            'options' => [
                'label' => 'Email для получения звонков телефонистки',
            ],
        ]);
                
        $this->add([            
            'type'  => 'text',
            'name' => 'telefonistka_email_password',
            'attributes' => [
                'id' => 'telefonistka_email_password'
            ],
            'options' => [
                'label' => 'Пароль на email телефонистки',
            ],
        ]);
                       
        $this->add([            
            'type'  => 'date',
            'name' => 'allow_date',
            'attributes' => [
                'id' => 'allow_date',
                'step' => 1,
            ],
            'options' => [
                'label' => 'Дата запрета изменения данных',
                'format' => 'Y-m-d'
            ],
        ]);

        $this->add([            
            'type'  => 'select',
            'name' => 'doc_actualize',
            'options' => [
                'label' => 'Восстановление последовательности',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'mail_token',
            'options' => [
                'label' => 'Обрабатка токенов писем',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'text',
            'name' => 'turbo_passphrase',
            'attributes' => [
                'id' => 'turbo_passphrase'
            ],
            'options' => [
                'label' => 'Пароль шифрования (турбоссылка, пароли почты)',
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'job',
            'options' => [
                'label' => 'Задания',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'zp',
            'options' => [
                'label' => 'Зарплата',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'fin',
            'options' => [
                'label' => 'Фин. отчеты',
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
                'id' => 'settings_submit_button',
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
                'name'     => 'wamm_api_id',
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
        
//        $inputFilter->add([
//                'name'     => 'wamm_url',
//                'required' => false,
//                'filters'  => [
//                    ['name' => 'StringTrim'],
//                    ['name' => 'StripTags'],
//                    ['name' => 'StripNewlines'],
//                ],                
//                'validators' => [
//                    [
//                        'name'    => 'StringLength',
//                        'options' => [
//                            'min' => 1,
//                            'max' => 1024
//                        ],
//                    ],
//                ],
//            ]);          
//        
        $inputFilter->add([
                'name'     => 'dadata_api_key',
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
                'name'     => 'dadata_standart_key',
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
                'name'     => 'ftp_apl_suppliers_price',
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
                'name'     => 'ftp_apl_suppliers_price_login',
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
                'name'     => 'ftp_apl_suppliers_price_password',
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
                'name'     => 'ftp_apl_market_price_login',
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
                'name'     => 'ftp_apl_market_price_password',
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
                'name'     => 'autoru_email',
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
        
        $inputFilter->add([
                'name'     => 'autoru_email_password',
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
                            'max' => 32
                        ],
                    ],
                ],
            ]);          
        
        $inputFilter->add([
                'name'     => 'hello_email',
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
        
        $inputFilter->add([
                'name'     => 'hello_email_password',
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
                            'max' => 32
                        ],
                    ],
                ],
            ]);          
        

        $inputFilter->add([
                'name'     => 'telefonistka_email',
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
        
        $inputFilter->add([
                'name'     => 'telefonistka_email_password',
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
                            'max' => 32
                        ],
                    ],
                ],
            ]);          
        
        $inputFilter->add([
                'name'     => 'allow_date',
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
                            'max' => 32
                        ],
                    ],
                ],
            ]);          
        
        $inputFilter->add([
                'name'     => 'mail_token',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 

        $inputFilter->add([
                'name'     => 'hello_check',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'job',
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
