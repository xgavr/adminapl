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
use Stock\Entity\Revise;
use User\Filter\PhoneFilter;
use User\Validator\PhoneExistsValidator;

/**
 * Description of Revise
 *
 * @author Daddy
 */
class ReviseForm extends Form implements ObjectManagerAwareInterface
{
    
    
    protected $objectManager;

    protected $entityManager;
        
    /**
     * Конструктор.     
     */
    public function __construct($entityManager)
    {
        // Определяем имя формы.
        parent::__construct('revise-form');
     
        $this->entityManager = $entityManager;
        
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();         
    }

    protected function addElements() 
    {
                

        $this->add([
            'type'  => 'date',
            'name' => 'docDate',
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
            'type'  => 'number',
            'name' => 'amount',
            'attributes' => [                
                'id' => 'amount',
                'required' => 'required',
//                'min' => 0,
                'step' => 0.01,
                'autocomplete' => 'off',
            ],
            'options' => [
                'label' => 'Сумма',
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
            'value' => Revise::STATUS_ACTIVE,
            'attributes' => [                
                'required' => 'required',                
            ],
            'options' => [
                'label' => 'Статус',
                'value_options' => Revise::getStatusList(),
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
            'type'  => 'select',
            'name' => 'supplier',
            'attributes' => [                
                'id' => 'supplier'
            ],
            'options' => [
                'label' => 'Поставщик',
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'legal',
            'attributes' => [                
                'id' => 'legal'
            ],
            'options' => [
                'label' => 'Юр. лицо',
                'disable_inarray_validator' => true,
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'contract',
            'attributes' => [                
                'id' => 'contract'
            ],
            'options' => [
                'label' => 'Договор',
                'disable_inarray_validator' => true,
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'office',
            'attributes' => [
                'id' => 'office',
            ],
            'options' => [
                'label' => 'Офис',
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'company',
            'attributes' => [
                'id' => 'company',
            ],
            'options' => [
                'label' => 'Предприятие',
            ],
        ]);
        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'cash_in_submitbutton',
            ],
        ]);        

        // Add the CSRF field
//        $this->add([
//            'type' => 'csrf',
//            'name' => 'csrf',
//            'options' => [
//                'csrf_options' => [
//                'timeout' => 600
//                ]
//            ],
//        ]);
        
    }
    
    private function addInputFilter() 
    {
        
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);

        $inputFilter->add([
                'name'     => 'phone',
                'required' => false,
                'filters'  => [
                    [
                        'name' => PhoneFilter::class,
                        'options' => [
                            'format' => PhoneFilter::PHONE_FORMAT_DB,
                        ]
                    ],
                ],                
                'validators' => [
                    [
                        'name'    => 'PhoneNumber',
                        'options' => [
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
