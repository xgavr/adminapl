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
use Stock\Entity\St;

/**
 * Description of St
 *
 * @author Daddy
 */
class StForm extends Form implements ObjectManagerAwareInterface
{
    
    
    protected $objectManager;

    protected $entityManager;
        
    /**
     *
     * @var \Company\Entity\Office 
     */
    protected $office;
    
    /**
     *
     * @var \Company\Entity\Legal 
     */
    protected $company;

    /**
     *
     * @var \User\Entity\User 
     */
    protected $user;

    /**
     *
     * @var \Company\Entity\Cost 
     */
    protected $cost;

    /**
     * Конструктор.     
     */
    public function __construct($entityManager, $office, $company = null, 
            $user = null, $cost = null)
    {
        // Определяем имя формы.
        parent::__construct('st-form');
     
        $this->entityManager = $entityManager;
        
        $this->office = $office;        
        $this->company = $company;        
        $this->user = $user;
        $this->cost = $cost;
        
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
            'name' => 'office_id',
            'attributes' => [                
                'id' => 'office',
                'data-live-search'=> "true",
                'class' => "selectpicker",
                'value' => $this->office->getId(),
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
                'display_empty_item' => false,
                'empty_item_label'   => '--выберете компанию--',                 
                'is_method' => true,
                'find_method'    => [
                   'name'   => 'formOfficeLegals',
                   'params' => [
                       'params' => ['officeId' => $this->office->getId()],
                   ],
                ],                
            ],
        ]);

        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'user',
            'attributes' => [                
                'id' => 'user',
            ],
            'options' => [
                'disable_inarray_validator' => true,
                'object_manager' => $this->entityManager,
                'target_class'   => 'User\Entity\User',
                'label' => 'Сотрудник',
                'property'       => 'fullName',
                'display_empty_item' => false,
                'empty_item_label'   => '---',                 
                'is_method' => false,
                'find_method'    => [
                   'name'   => 'formFind',
                   'params' => [
                       'params' => ['user' => $this->user],
                   ],
                ],                
                'label_generator' => function ($targetEntity) {
                    return $targetEntity->getFullName();
                },
            ],
        ]);

        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'cost',
            'attributes' => [                
                'id' => 'cost',
            ],
            'options' => [
                'disable_inarray_validator' => true,
                'object_manager' => $this->entityManager,
                'target_class'   => 'Company\Entity\Cost',
                'label' => 'Статья затрат',
                'property'       => 'name',
                'display_empty_item' => true,
                'empty_item_label'   => null,                 
                'is_method' => true,
                'find_method'    => [
                   'name'   => 'formFind',
                   'params' => [
                       'params' => ['cost' => $this->cost],
                   ],
                ],                
                'label_generator' => function ($targetEntity) {
                    return $targetEntity->getName();
                },
            ],
        ]);

        $this->add([
            'type'  => 'date',
            'name' => 'doc_date',
            'attributes' => [                
                'id' => 'docDate',
                'step' => 1,
                'required' => 'required',                
                'value' => date('Y-m-d'),
            ],
            'options' => [
                'label' => 'Дата документа',
                'format' => 'Y-m-d',
            ],
        ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'doc_no',
            'attributes' => [                
                'id' => 'docNo',
//                'required' => 'required',                
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
            'value' => St::STATUS_ACTIVE,
            'attributes' => [                
                'required' => 'required',
                'id' => 'status',
            ],
            'options' => [
                'label' => 'Статус',
                'value_options' => St::getStatusList(),
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'writeOff',
            'value' => St::WRITE_PAY,
            'attributes' => [                
                'required' => 'required',
                'id' => 'writeOff',
            ],
            'options' => [
                'label' => 'Вид затрат',
                'value_options' => St::getWriteOffList(),
            ],
        ]);
        
        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'st_submitbutton',
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
                'name'     => 'office_id',
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
                'name'     => 'user',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
//                'validators' => [
//                    [    
//                        'name'    => 'GreaterThan',
//                        'options' => [
//                            'min' => 0,
//                            'inclusive' => false
//                        ],
//                    ],
//                ],
            ]);          
        
        $inputFilter->add([
                'name'     => 'cost',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
//                'validators' => [
//                    [    
//                        'name'    => 'GreaterThan',
//                        'options' => [
//                            'min' => 0,
//                            'inclusive' => false
//                        ],
//                    ],
//                ],
            ]);          
        
        $inputFilter->add([
                'name'     => 'doc_date',
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
                'name'     => 'doc_no',
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
                'name'     => 'comment',
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
                'name'     => 'status',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=> array_keys(St::getStatusList())]]
                ],
            ]); 

        $inputFilter->add([
                'name'     => 'writeOff',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=> array_keys(St::getWriteOffList())]]
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
