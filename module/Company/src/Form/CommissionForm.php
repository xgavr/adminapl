<?php
namespace Company\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Company\Entity\Commission;

/**
 * The form for collecting information about Commission.
 */
class CommissionForm extends Form
{
    /**
     * Constructor.     
     */
    public function __construct()
    {
        
        // Define form name
        parent::__construct('commission-form');
     
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
                'label' => 'ФИО',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'position',
            'attributes' => [
                'id' => 'position'
            ],
            'options' => [
                'label' => 'Должность',
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'attributes' => [                
                'required' => 'required',
                'id' => 'status',
                'value' => Commission::STATUS_MEMBER,
            ],
            'options' => [
                'label' => 'Статус',
                'value_options' => Commission::getStatusList(),
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
                            'max' => 64
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
                    ['name'=>'InArray', 'options'=>['haystack'=> array_keys(Commission::getStatusList())]]
                ],
            ]);         
    }           
}
