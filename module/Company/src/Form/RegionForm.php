<?php
namespace Company\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

/**
 * The form for collecting information about Role.
 */
class RegionForm extends Form
{
    /**
     * Constructor.     
     */
    public function __construct()
    {
        
        // Define form name
        parent::__construct('region-form');
     
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
                'label' => 'Сокращение',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'fullName',
            'attributes' => [
                'id' => 'fullName'
            ],
            'options' => [
                'label' => 'Наименование полное',
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
                            'max' => 10
                        ],
                    ],
                ],
            ]);                          
        
        $inputFilter->add([
                'name'     => 'fullName',
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
    }           
}
