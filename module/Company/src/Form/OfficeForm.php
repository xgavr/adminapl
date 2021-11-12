<?php
namespace Company\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Company\Entity\Office;


/**
 * The form for collecting information about Role.
 */
class OfficeForm extends Form implements ObjectManagerAwareInterface
{
   
    protected $objectManager;

    protected $entityManager;    
    /**
     * Constructor.     
     */
    public function __construct($entityManager)
    {
        
        // Define form name
        parent::__construct('office-form');
     
        $this->entityManager = $entityManager;        
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
            'name' => 'aplId',
            'attributes' => [
                'id' => 'aplId'
            ],
            'options' => [
                'label' => 'AplId',
            ],
        ]);
        
        $this->add([           
            'type'  => 'number',
            'name' => 'shippingLimit1',
            'attributes' => [
                'id' => 'shippingLimit1',
                'min' => 0,
                'value' => Office::DEFAULT_SHIPPING_LIMIT_1,
            ],
            'options' => [
                'label' => 'Граница стоимости заказа 1',
            ],
        ]);
        
        $this->add([           
            'type'  => 'number',
            'name' => 'shippingLimit2',
            'attributes' => [
                'id' => 'shippingLimit2',
                'min' => 0,
                'value' => Office::DEFAULT_SHIPPING_LIMIT_2,
            ],
            'options' => [
                'label' => 'Граница стоимости заказа 2',
            ],
        ]);
        
        // Add "status" field
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'options' => [
                'label' => 'Статус',
                'value_options' => [
                    1 => 'Действующий',
                    2 => 'Закрыт',                    
                ]
            ],
        ]);
        
                
        // Добавляем поле "region"
        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'region',
            'attributes' => [                
                'id' => 'office_region',
                'data-live-search'=> "true",
                'class' => "selectpicker",
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Company\Entity\Region',
                'label' => 'Регион',
                'property'       => 'name',
                'display_empty_item' => true,
                'empty_item_label'   => '--выберете регион--',                 
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
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 256
                        ],
                    ],
                ],
            ]);                          
        
        $inputFilter->add([
                'name'     => 'aplId',
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
                'name'     => 'region',
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
        
        // Add input for "status" field
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
        
        
    }           
}
