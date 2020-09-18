<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Stock\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Stock\Entity\Ptu;

/**
 * Description of Ptu
 *
 * @author Daddy
 */
class PtuForm extends Form implements ObjectManagerAwareInterface
{
    
    
    protected $objectManager;

    protected $entityManager;
        
    
    /**
     * Конструктор.     
     */
    public function __construct($entityManager)
    {
        // Определяем имя формы.
        parent::__construct('ptu-form');
     
        $this->entityManager = $entityManager;
        
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();         
    }

    protected function addElements() 
    {
                
        // Добавляем поле "office"
        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'office',
            'attributes' => [                
                'id' => 'office',
                'data-live-search'=> "true",
                'class' => "selectpicker",
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Company\Entity\Office',
                'label' => 'Офис',
                'property'       => 'name',
                'display_empty_item' => true,
                'empty_item_label'   => '--выберете офис--',                 
            ],
        ]);

        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'company',
            'attributes' => [                
                'id' => 'company',
                'data-live-search'=> "true",
                'class' => "selectpicker",
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Company\Entity\Legal',
                'label' => 'Компания',
                'property'       => 'name',
                'display_empty_item' => true,
                'empty_item_label'   => '--выберете компанию--',                 
            ],
        ]);

        // Добавляем поле "legal"
        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'legal',
            'attributes' => [                
                'id' => 'legal',
                'data-live-search'=> "true",
                'class' => "selectpicker",
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Company\Entity\Legal',
                'label' => 'Поставщик',
                'property'       => 'name',
                'display_empty_item' => true,
                'empty_item_label'   => '--выберете поставщика--',                 
            ],
        ]);
        
        // Добавляем поле "contract"
        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'contract',
            'attributes' => [                
                'id' => 'contract',
                'data-live-search'=> "true",
                'class' => "selectpicker",
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Company\Entity\Contract',
                'label' => 'Договор',
                'property'       => 'name',
                'display_empty_item' => true,
                'empty_item_label'   => '--выберете договор--',                 
            ],
        ]);

        $this->add([
            'type'  => 'date',
            'name' => 'docDate',
            'attributes' => [                
                'id' => 'docDate',
                'step' => 1,
            ],
            'options' => [
                'label' => 'Дата документа',
                'format' => 'Y-m-d',
            ],
        ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'docNo',
            'attributes' => [                
                'id' => 'docNo'
            ],
            'options' => [
                'label' => 'Номер документа',
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
            'type'  => 'select',
            'name' => 'status',
            'value' => Ptu::STATUS_ACTIVE,
            'options' => [
                'label' => 'Статус',
                'value_options' => Ptu::getStatusList(),
            ],
        ]);
        
        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'ptu_submitbutton',
            ],
        ]);        

        // Add the CSRF field
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
    
    private function addInputFilter() 
    {
        
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
        
        $inputFilter->add([
                'name'     => 'office',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    [
                        'name'    => 'IsInt',
                        'options' => [
                            'min' => 0,
                            'locale' => 'ru-Ru'
                        ],
                    ],
                ],
            ]);          
        
        $inputFilter->add([
                'name'     => 'company',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    [
                        'name'    => 'IsInt',
                        'options' => [
                            'min' => 0,
                            'locale' => 'ru-Ru'
                        ],
                    ],
                ],
            ]);          
        
        $inputFilter->add([
                'name'     => 'legal',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    [
                        'name'    => 'IsInt',
                        'options' => [
                            'min' => 0,
                            'locale' => 'ru-Ru'
                        ],
                    ],
                ],
            ]);          
        
        $inputFilter->add([
                'name'     => 'contract',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    [
                        'name'    => 'IsInt',
                        'options' => [
                            'min' => 0,
                            'locale' => 'ru-Ru'
                        ],
                    ],
                ],
            ]);          
        
        $inputFilter->add([
                'name'     => 'docDate',
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
                            'max' => 24
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'docNo',
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
                            'max' => 64
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'comment',
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
                            'max' => 1024
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'status',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=> array_keys(Ptu::getStatusList())]]
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
