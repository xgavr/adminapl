<?php
namespace Company\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Date;


/**
 * The form for collecting information about Role.
 */
class LegalForm extends Form
{
   
    protected $objectManager;

    protected $entityManager;    
    /**
     * Constructor.     
     */
    public function __construct($entityManager)
    {
        
        // Define form name
        parent::__construct('legal-form');
     
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
            'name' => 'inn',
            'attributes' => [
                'id' => 'inn'
            ],
            'options' => [
                'label' => 'ИНН',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'kpp',
            'attributes' => [
                'id' => 'kpp'
            ],
            'options' => [
                'label' => 'КПП',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'ogrn',
            'attributes' => [
                'id' => 'ogrn'
            ],
            'options' => [
                'label' => 'ОГРН',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'okpo',
            'attributes' => [
                'id' => 'okpo'
            ],
            'options' => [
                'label' => 'ОКПО',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'head',
            'attributes' => [
                'id' => 'head'
            ],
            'options' => [
                'label' => 'Руководитель',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'chiefAccount',
            'attributes' => [
                'id' => 'chiefAccount'
            ],
            'options' => [
                'label' => 'Главный бухгалтер',
            ],
        ]);
        
        $this->add([           
            'type'  => 'textarea',
            'name' => 'address',
            'attributes' => [
                'id' => 'address'
            ],
            'options' => [
                'label' => 'Местонахождение',
            ],
        ]);
        
        $this->add([           
            'type'  => 'textarea',
            'name' => 'info',
            'attributes' => [
                'id' => 'info'
            ],
            'options' => [
                'label' => 'Дополнительная информация',
            ],
        ]);
        
        $this->add([           
            'type'  => 'date',
            'name' => 'dateStart',
            'attributes' => [
                'id' => 'dateStart'
            ],
            'options' => [
                'label' => 'Дата начала деятельности',
            ],
        ]);
        
        // Add "status" field
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'options' => [
                'label' => 'Статус',
                'value_options' => [
                    1 => 'Действующее',
                    2 => 'Закрыто',                    
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
                            'max' => 256
                        ],
                    ],
                ],
            ]);                          
        
        $inputFilter->add([
                'name'     => 'inn',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 12
                        ],
                    ],
                ],
            ]);                          
        
        $inputFilter->add([
                'name'     => 'kpp',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 9
                        ],
                    ],
                ],
            ]);                          
        
        $inputFilter->add([
                'name'     => 'ogrn',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 20
                        ],
                    ],
                ],
            ]);                          
        
        $inputFilter->add([
                'name'     => 'okpo',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 20
                        ],
                    ],
                ],
            ]);                          
        
        $inputFilter->add([
                'name'     => 'head',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 128
                        ],
                    ],
                ],
            ]);                          
        
        $inputFilter->add([
                'name'     => 'chiefAccount',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 128
                        ],
                    ],
                ],
            ]);                          
        
        $inputFilter->add([
                'name'     => 'address',
                'required' => false,
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
                'name'     => 'info',
                'required' => false,
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
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]);         
    }           
}
