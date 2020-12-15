<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Laminas\Form\Form;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Laminas\InputFilter\InputFilter;
use Application\Entity\PriceGetting;

/**
 * Description of PriceGetting
 *
 * @author Daddy
 */
class PriceGettingForm extends Form
{
    
    protected $objectManager;

    protected $entityManager;
    
    /**
     * Конструктор.     
     */
    public function __construct($entityManager)
    {
        // Определяем имя формы.
        parent::__construct('price-getting-form');

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
            'type'  => 'text',
            'name' => 'name',
            'attributes' => [
                'id' => 'ps_name'
            ],
            'options' => [
                'label' => 'Наименование',
                'value' => 'Получение прайса',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'ftp',
            'attributes' => [
                'id' => 'ftp'
            ],
            'options' => [
                'label' => 'FTP сервер',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'ftpDir',
            'attributes' => [
                'id' => 'ftpDir'
            ],
            'options' => [
                'label' => 'Папка на FTP сервере',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'ftpLogin',
            'attributes' => [
                'id' => 'ftp-login'
            ],
            'options' => [
                'label' => 'FTP логин',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'ftpPassword',
            'attributes' => [
                'id' => 'ftp-password'
            ],
            'options' => [
                'label' => 'FTP пароль',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'email',
            'attributes' => [
                'id' => 'email'
            ],
            'options' => [
                'label' => 'Email для прайса',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'emailPassword',
            'attributes' => [
                'id' => 'email-password'
            ],
            'options' => [
                'label' => 'Email пароль',
            ],
        ]);
        
        // Добавляем поле "link"
        $this->add([           
            'type'  => 'text',
            'name' => 'link',
            'attributes' => [
                'id' => 'link'
            ],
            'options' => [
                'label' => 'Ссылка на файл прайса',
            ],
        ]);
        
        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'priceSupplier',
            'attributes' => [                
                'id' => 'supplier',
                'data-live-search'=> true,
                'class' => "selectpicker",
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Application\Entity\Supplier',
                'label' => 'Использовать прайс:',
                'property'       => 'name',
                'display_empty_item' => true,
                'empty_item_label'   => '--использовать свой прайс--',                 
            ],
        ]);        
        
        // Добавляем поле "filename"
        $this->add([           
            'type'  => 'text',
            'name' => 'filename',
            'attributes' => [
                'id' => 'filename'
            ],
            'options' => [
                'label' => 'Фраза в имени файла',
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'statusFilename',
            'options' => [
                'label' => 'Фильтр наименований файлов прайсов',
                'value_options' => [
                    1 => 'Принимать файлы с любым наименованием',
                    2 => 'Принимать наименования, содержащие фразу',
                    3 => 'Принимать наименования, не содержащие фразу',                    
                ]
            ],
        ]);
        
        // Add "status" field
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'options' => [
                'label' => 'Статус',
                'value_options' => [
                    1 => 'Использовать',
                    2 => 'Не использовать',                    
                ]
            ],
        ]);
        
        // Add "orderToApl" field
        $this->add([            
            'type'  => 'select',
            'name' => 'orderToApl',
            'options' => [
                'label' => 'Обмен с АПЛ',
                'value_options' => [
                    2 => 'Не закачивать файл прайса на сервер АПЛ',                    
                    1 => 'Закачивать файл прайса на сервер АПЛ',
                ]
            ],
        ]);
        
                
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'supplier_submitbutton',
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
                'name'     => 'ftp',
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
                'name'     => 'ftpDir',
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
                'name'     => 'ftpLogin',
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
                'name'     => 'ftpPassword',
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
                            'max' => 64
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'email',
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
                'name'     => 'emailPassword',
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
                            'max' => 64
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'link',
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
                'name'     => 'priceSupplier',
                'required' => false,
                'filters'  => [],                
                'validators' => [],
            ]);          
        
        
        $inputFilter->add([
                'name'     => 'filename',
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
                'name'     => 'statusFilename',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2, 3]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'status',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'orderToApl',
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
