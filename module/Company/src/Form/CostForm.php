<?php
namespace Company\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Company\Entity\Cost;

/**
 * The form for collecting information about Cost.
 */
class CostForm extends Form
{
    /**
     * Constructor.     
     */
    public function __construct()
    {
        
        // Define form name
        parent::__construct('cost-form');
     
        // Set POST method for this form
        $this->setAttribute('method', 'post');
        
        $this->addElements();
        $this->addInputFilter();          
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
            'type'  => 'number',
            'name' => 'aplId',
            'attributes' => [
                'id' => 'aplId',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Код АПЛ',
            ],
        ]);
                                
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'value' => Cost::STATUS_ACTIVE,
            'attributes' => [                
                'required' => 'required',
                'id' => 'status',
            ],
            'options' => [
                'label' => 'Статус',
                'value_options' => Cost::getStatusList(),
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
                'name'     => 'status',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=> array_keys(Cost::getStatusList())]]
                ],
            ]); 

        
        $inputFilter->add([
                'name'     => 'aplId',
                'required' => true,
                'filters'  => [
                    ['name' => 'ToInt'],                   
                ],                
//                'validators' => [
//                    [
//                        'name'    => 'StringLength',
//                        'options' => [
//                            'min' => 1,
//                            'max' => 256
//                        ],
//                    ],
//                ],
            ]);                                  
    }           
}
