<?php
namespace Company\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;

/**
 * The form for collecting information about Role.
 */
class BankAccountForm extends Form
{
    /**
     * Constructor.     
     */
    public function __construct()
    {
        
        // Define form name
        parent::__construct('bank-account-form');
     
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
                'label' => 'Наименование банка',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'city',
            'attributes' => [
                'id' => 'city',
            ],
            'options' => [
                'label' => 'Город',               
            ],
        ]);
                                
        $this->add([           
            'type'  => 'text',
            'name' => 'bik',
            'attributes' => [
                'id' => 'bik'
            ],
            'options' => [
                'label' => 'БИК',
            ],
        ]);
                                
        $this->add([           
            'type'  => 'text',
            'name' => 'ks',
            'attributes' => [
                'id' => 'ks'
            ],
            'options' => [
                'label' => 'Корреспондентский счет',
            ],
        ]);
                                
        $this->add([           
            'type'  => 'text',
            'name' => 'rs',
            'attributes' => [
                'id' => 'rs'
            ],
            'options' => [
                'label' => 'Расчетный счет',
            ],
        ]);
                 
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
        
        $this->add([            
            'type'  => 'select',
            'name' => 'api',
            'options' => [
                'label' => 'API',
                'value_options' => [
                    2 => 'Нет api',                    
                    1 => 'Точка api',
                ]
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'statement',
            'options' => [
                'label' => 'Выписка по счету',
                'value_options' => [
                    2 => 'Недоступна',                    
                    1 => 'Доступна',
                ]
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
                            'max' => 128
                        ],
                    ],
                ],
            ]);                          
        
        $inputFilter->add([
                'name'     => 'city',
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
                'name'     => 'bik',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'Digits'],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 9
                        ],
                    ],
                ],
            ]);                                  

        $inputFilter->add([
                'name'     => 'rs',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                    ['name' => 'Digits'],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 20
                        ],
                    ],
                ],
            ]);                                  

        $inputFilter->add([
                'name'     => 'ks',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                    ['name' => 'Digits'],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 20
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
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'api',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 

        $inputFilter->add([
                'name'     => 'statement',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        
    }           
}
