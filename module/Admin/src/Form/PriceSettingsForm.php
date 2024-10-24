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
class PriceSettingsForm extends Form 
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
            'type'  => 'number',
            'name' => 'remove_day',
            'attributes' => [
                'min' => 3,
                'max' => 10,
                'value' => 7,
            ],
            'options' => [
                'label' => 'Прайс хранить дней',
            ],
        ]);

        $this->add([            
            'type'  => 'select',
            'name' => 'remove_raw',
            'options' => [
                'label' => 'Удалять прайсы из БД',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Остановить',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'uploading_raw',
            'options' => [
                'label' => 'Загрузка прайса',
                'value_options' => [
                    1 => 'Сейчас не идет',
                    2 => 'Сейчас идет',                    
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
                'label' => 'Разборка артикулов производителей',
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
                'label' => 'Разборка номеров производителей',
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
                'label' => 'Разборка наименований товаров',
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
                
        $this->add([            
            'type'  => 'select',
            'name' => 'update_good_price',
            'options' => [
                'label' => 'Рассчитывать цены',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Остановить',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'assembly_group_name',
            'options' => [
                'label' => 'Сборка групп наименований',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Остановить',                    
                ]
            ],
        ]);

//        $this->add([            
//            'type'  => 'select',
//            'name' => 'good_token',
//            'options' => [
//                'label' => 'Разборка токенов товаров',
//                'value_options' => [
//                    1 => 'Делать',
//                    2 => 'Остановить',                    
//                ]
//            ],
//        ]);

        $this->add([            
            'type'  => 'select',
            'name' => 'update_good_name',
            'options' => [
                'label' => 'Обновление наименований товаров',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Остановить',                    
                ]
            ],
        ]);

        $this->add([            
            'type'  => 'email',
            'name' => 'image_mail_box',
            'attributes' => [
                'id' => 'image_mail_box'
            ],
            'options' => [
                'label' => 'Email для получения картинок',
            ],
        ]);
                
        $this->add([            
            'type'  => 'text',
            'name' => 'image_mail_box_password',
            'attributes' => [
                'id' => 'image_mail_box_password'
            ],
            'options' => [
                'label' => 'Пароль на email для картинок',
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'image_mail_box_check',
            'options' => [
                'label' => 'Проверка ящика для картинок',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Остановить',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'email',
            'name' => 'cross_mail_box',
            'attributes' => [
                'id' => 'cross_mail_box'
            ],
            'options' => [
                'label' => 'Email для получения кроссов',
            ],
        ]);
                
        $this->add([            
            'type'  => 'text',
            'name' => 'cross_mail_box_password',
            'attributes' => [
                'id' => 'cross_mail_box_password'
            ],
            'options' => [
                'label' => 'Пароль на email для кроссов',
            ],
        ]);
                
        $this->add([            
            'type'  => 'text',
            'name' => 'cross_mail_app_password',
            'attributes' => [
                'id' => 'cross_mail_app_password'
            ],
            'options' => [
                'label' => 'Пароль приложения для кроссов',
            ],
        ]);

        $this->add([            
            'type'  => 'select',
            'name' => 'cross_mail_box_check',
            'options' => [
                'label' => 'Проверка ящика для кроссов',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Остановить',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'email',
            'name' => 'sup_email',
            'attributes' => [
                'id' => 'sup_email'
            ],
            'options' => [
                'label' => 'Общий email для прайсов',
            ],
        ]);
                
        $this->add([            
            'type'  => 'text',
            'name' => 'sup_email_password',
            'attributes' => [
                'id' => 'sup_email_password'
            ],
            'options' => [
                'label' => 'Пароль на email для прайсов',
            ],
        ]);
                
        $this->add([            
            'type'  => 'text',
            'name' => 'sup_app_password',
            'attributes' => [
                'id' => 'sup_app_password'
            ],
            'options' => [
                'label' => 'Пароль приложения для прайсов',
            ],
        ]);        
        
        $this->add([            
            'type'  => 'email',
            'name' => 'b_email',
            'attributes' => [
                'id' => 'b_email'
            ],
            'options' => [
                'label' => 'Общий email для накладных',
            ],
        ]);
                
        $this->add([            
            'type'  => 'text',
            'name' => 'b_email_password',
            'attributes' => [
                'id' => 'b_email_password'
            ],
            'options' => [
                'label' => 'Пароль на email для накладных',
            ],
        ]);
                
        $this->add([            
            'type'  => 'text',
            'name' => 'b_app_password',
            'attributes' => [
                'id' => 'b_app_password'
            ],
            'options' => [
                'label' => 'Пароль приложения для накладных',
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
                'name'     => 'remove_raw',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'uploading_raw',
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
        
        $inputFilter->add([
                'name'     => 'update_good_price',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
//        $inputFilter->add([
//                'name'     => 'good_token',
//                'required' => true,
//                'filters'  => [                    
//                    ['name' => 'ToInt'],
//                ],                
//                'validators' => [
//                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
//                ],
//            ]); 
        
        $inputFilter->add([
                'name'     => 'assembly_group_name',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'update_good_name',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'image_mail_box',
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
                'name'     => 'image_mail_box_password',
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
                'name'     => 'image_mail_box_check',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'cross_mail_box',
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
                'name'     => 'cross_mail_box_password',
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
                'name'     => 'cross_mail_app_password',
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
                'name'     => 'cross_mail_box_check',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'sup_email',
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
                'name'     => 'sup_email_password',
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
                'name'     => 'sup_app_password',
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
                'name'     => 'b_email',
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
                'name'     => 'b_email_password',
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
                'name'     => 'b_app_password',
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
