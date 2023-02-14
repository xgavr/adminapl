<?php
namespace Company\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Company\Entity\LegalLocation;


/**
 * The form for collecting information about Role.
 */
class LocationForm extends Form
{
   
    protected $objectManager;

    protected $entityManager;    
    /**
     * Constructor.     
     */
    public function __construct($entityManager)
    {
        
        // Define form name
        parent::__construct('location-form');
     
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

        $this->add([           
            'type'  => 'textarea',
            'name' => 'address',
            'attributes' => [
                'id' => 'address'
            ],
            'options' => [
                'label' => 'Адрес',
            ],
        ]);
        
        $this->add([           
            'type'  => 'date',
            'name' => 'dateStart',
            'attributes' => [
                'id' => 'dateStart',
                'value' => date('Y-m-d'),
            ],
            'options' => [
                'label' => 'Дата начала использования',
            ],
        ]);
        
        // Add "status" field
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'options' => [
                'label' => 'Статус',
                'value_options' => LegalLocation::getStatusList(),
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
                
        $inputFilter->add([
                'name'     => 'address',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 1024
                        ],
                    ],
                ],
            ]);                          
        

        $inputFilter->add([
                'name'     => 'dateStart',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                    [
                        'name'    => 'Date',
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
                    ['name'=>'InArray', 'options'=> ['haystack'=> array_keys(LegalLocation::getStatusList())]]
                ],
            ]);         
    }           
}
