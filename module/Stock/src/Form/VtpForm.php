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
use Stock\Entity\Vtp;

/**
 * Description of Vtp
 *
 * @author Daddy
 */
class VtpForm extends Form
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
     * @var \Application\Entity\Supplier 
     */
    protected $supplier;
    
    /**
     *
     * @var \Company\Entity\Legal 
     */
    protected $company;

    /**
     *
     * @var \Company\Entity\Legal 
     */
    protected $legal;
    /**
     * Конструктор.     
     */
    public function __construct($entityManager, $office, $supplier = null, $company = null, $legal = null)
    {
        // Определяем имя формы.
        parent::__construct('vtp-form');
     
        $this->entityManager = $entityManager;
        
        $this->office = $office;        
        $this->supplier = $supplier;
        $this->company = $company;        
        $this->legal = $legal;
        
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
                'display_empty_item' => true,
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

        // Добавляем поле "supplier"
        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'supplier',
            'attributes' => [                
                'id' => 'supplier',
                'data-live-search'=> "true",
                'class' => "selectpicker",
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Application\Entity\Supplier',
                'label' => 'Поставщик',
                'property'       => 'name',
                'display_empty_item' => true,
                'empty_item_label'   => '--выберете поставщика--',                 
            ],
        ]);
        
        // Добавляем поле "legal"
        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'legal_id',
            'attributes' => [                
                'id' => 'legal',
                'data-live-search'=> "true",
                'class' => "selectpicker",
                'required' => 'required',
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Company\Entity\Legal',
                'label' => 'Поставщик (юр. лицо)',
                'property'       => 'name',
                'display_empty_item' => true,
                'empty_item_label'   => '--выберете поставщика--',  
                'is_method' => true,
                'find_method'    => [
                   'name'   => 'formSupplierLegals',
                   'params' => [
                       'params' => ['supplierId' => ($this->supplier) ? $this->supplier->getId():null],
                   ],
                ],                
                
            ],
        ]);
        
        // Добавляем поле "contract"
        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'contract_id',
            'attributes' => [                
                'id' => 'contract',
                'data-live-search'=> "true",
                'class' => "selectpicker",
                'required' => 'required',
                ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Company\Entity\Contract',
                'label' => 'Договор',
                'property'       => 'name',
                'display_empty_item' => true,
                'empty_item_label'   => '--выберете договор--',    
                'find_method'    => [
                   'name'   => 'formOfficeLegalContracts',
                   'params' => [
                       'params' => [
                           'companyId' => ($this->company) ? $this->company->getId():null,
                           'legalId' => ($this->legal) ? $this->legal->getId():null,
                        ],
                   ],
                ],                                
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
            'type'  => 'text',
            'name' => 'cause',
            'attributes' => [                
                'id' => 'cause'
            ],
            'options' => [
                'label' => 'Причина возврата',
            ],
       ]);

        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'attributes' => [                
                'required' => 'required',                
                'value' => Vtp::STATUS_ACTIVE,
            ],
            'options' => [
                'label' => 'Статус документа',
                'value_options' => Vtp::getStatusList(),
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'statusDoc',
            'attributes' => [                
                'required' => 'required',                
                'value' => Vtp::STATUS_DOC_NEW,
            ],
            'options' => [
                'label' => 'Статус возврата',
                'value_options' => Vtp::getStatusDocList(),
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'vtpType',
            'attributes' => [                
                'required' => 'required',                
                'value' => Vtp::TYPE_NO_NEED,
            ],
            'options' => [
                'label' => 'Тип возврата',
                'value_options' => Vtp::getVtpTypeList(),
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'ptu',
            'attributes' => [                
                'required' => 'required',                
            ],
            'options' => [
                'label' => 'ПТУ',
            ],
        ]);
                
        $this->add([
            'type'  => 'number',
            'name' => 'quantity',
            'attributes' => [                
                'id' => 'quantity',
                'value' => 1,
                'min' => 1
            ],
            'options' => [
                'label' => 'Количество',
            ],
       ]);
        
        $this->add([
            'type'  => 'number',
            'name' => 'price',
            'attributes' => [                
                'id' => 'price',
                'value' => 0,
                'min' => 0,
                'step' => 0.01,
            ],
            'options' => [
                'label' => 'Цена',
            ],
       ]);

        $this->add([
            'type'  => 'number',
            'name' => 'amount',
            'attributes' => [                
                'id' => 'amount',
                'value' => 0,
                'min' => 0,
                'step' => 0.01,
            ],
            'options' => [
                'label' => 'Сумма',
            ],
       ]);

        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'vtp_submitbutton',
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
                'name'     => 'supplier',
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
                'name'     => 'legal_id',
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
                'name'     => 'contract_id',
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
                'name'     => 'cause',
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
                    ['name'=>'InArray', 'options'=>['haystack'=> array_keys(Vtp::getStatusList())]]
                ],
            ]); 

        $inputFilter->add([
                'name'     => 'statusDoc',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=> array_keys(Vtp::getStatusDocList())]]
                ],
            ]); 

        $inputFilter->add([
                'name'     => 'vtpType',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=> array_keys(Vtp::getVtpTypeList())]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'quantity',
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
                    [    
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min' => 0,
                            'inclusive' => false
                        ],
                    ],
                ],
            ]);

        $inputFilter->add([
                'name'     => 'price',
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
                    [    
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min' => 0,
                            'inclusive' => false
                        ],
                    ],
                ],
            ]);

        $inputFilter->add([
                'name'     => 'amount',
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
                    [    
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min' => 0,
                            'inclusive' => false
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
