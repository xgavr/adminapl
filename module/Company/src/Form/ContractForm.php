<?php
namespace Company\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Company\Entity\Contract;
use Company\Entity\Office;

/**
 * The form for collecting information about Role.
 */
class ContractForm extends Form
{
    
    protected $objectManager;

    protected $entityManager;    
    
    /**
     *
     * @var Office 
     */
    protected $office;
    
    /**
     * Constructor.     
     */
    public function __construct($entityManager, $office)
    {
        
        // Define form name
        parent::__construct('contract-form');

        $this->entityManager = $entityManager;   
        $this->office = $office;
     
        // Set POST method for this form
        $this->setAttribute('method', 'post');
        
        $this->addElements();
        $this->addInputFilter();          
    }

    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }      
    
    
    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements() 
    {
        // Add "name" field
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
            'name' => 'act',
            'attributes' => [
                'id' => 'act',
                'value' => 'б/н',
            ],
            'options' => [
                'label' => 'Номер',  
            ],
        ]);
                        
        $this->add([           
            'type'  => 'date',
            'name' => 'dateStart',
            'attributes' => [
                'id' => 'dateStart',
                'value' => '2012-05-15',
            ],
            'options' => [
                'label' => 'Дата',
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'value' => Contract::STATUS_ACTIVE,
            'options' => [
                'label' => 'Статус',
                'value_options' => Contract::getStatusList(),
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'kind',
            'value' => Contract::KIND_SUPPLIER,
            'options' => [
                'label' => 'Тип',
                'value_options' => Contract::getKindList(),
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'pay',
            'value' => Contract::PAY_CASH,
            'options' => [
                'label' => 'Оплата',
                'value_options' => Contract::getPayList(),
            ],
        ]);
        
        // Добавляем поле "office"
        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'office',
            'attributes' => [                
                'id' => 'contract_office',
                'data-live-search'=> "true",
                'class' => "selectpicker",
//                'disabled' => 'disabled',
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
        
        // Добавляем поле "company"
        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'company',
            'attributes' => [                
                'id' => 'contract_company',
                'data-live-search'=> "true",
                'class' => "selectpicker",
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Company\Entity\Legal',
                'label' => 'Компания',
                'property'       => 'name',
                'is_method'      => true,
                'find_method'    => [
                   'name'   => 'formOfficeLegals',
                   'params' => [
                       'params' => ['officeId' => $this->office->getId()],
                   ],
                ],                
                'display_empty_item' => true,
                'empty_item_label'   => '--выберете компанию--',                 
            ],
       ]);
        
        // Add the Submit button
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'submit',
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
    
    /**
     * This method creates input filter (used for form filtering/validation).
     */
    private function addInputFilter() 
    {
        // Create input filter
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
        
        // Add input for "name" field
        $inputFilter->add([
                'name'     => 'name',
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
                ],
            ]);                          
        
        $inputFilter->add([
                'name'     => 'act',
                'required' => true,
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
                ],
            ]);         
        
        $inputFilter->add([
                'name'     => 'dateStart',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                    [
                        'name'    => 'Date',
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
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 

        $inputFilter->add([
                'name'     => 'kind',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2, 3]]]
                ],
            ]); 

        $inputFilter->add([
                'name'     => 'pay',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2, 3]]]
                ],
            ]); 

        $inputFilter->add([
                'name'     => 'office',
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
        
    }           
}
