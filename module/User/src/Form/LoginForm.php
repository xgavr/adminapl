<?php
namespace User\Form;

use Laminas\Form\Form;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilter;
use User\Filter\PhoneFilter;

/**
 * This form is used to collect user's login, password and 'Remember Me' flag.
 */
class LoginForm extends Form
{
    /**
     * Constructor.     
     */
    public function __construct()
    {
        // Define form name
        parent::__construct('login-form');
     
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
        // Add "ident" field
        $this->add([            
            'type'  => 'text',
            'name' => 'ident',
            'options' => [
                'label' => 'Ваш E-mail или телефон',
            ],
        ]);
        
        // Add "email" field
        $this->add([            
            'type'  => 'text',
            'name' => 'email',
            'options' => [
                'label' => 'Ваш E-mail',
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
        
        // Add "password" field
        $this->add([            
            'type'  => 'password',
            'name' => 'password',
            'options' => [
                'label' => 'Пароль',
            ],
        ]);
        
        // Add "remember_me" field
        $this->add([            
            'type'  => 'checkbox',
            'name' => 'remember_me',
            'options' => [
                'label' => 'Запомнить меня',
                'use_hidden_element' => false,
                'checked_value' => '1',
                'unchecked_value' => '0',
            ],
            'attributes' => [
                 'value' => 0,
            ],
        ]);
        
        // Add "redirect_url" field
        $this->add([            
            'type'  => 'hidden',
            'name' => 'redirect_url'
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
        
        // Add the Submit button
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Войти',
                'id' => 'submit',
            ],
        ]);
    }
    
    /**
     * This method creates input filter (used for form filtering/validation).
     */
    private function addInputFilter() 
    {
        // Create main input filter
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
                
        // Add input for "ident" field
        $inputFilter->add([
                'name'     => 'ident',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
            ]);     
        
        // Add input for "email" field
        $inputFilter->add([
                'name'     => 'email',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                    [
                        'name' => 'EmailAddress',
                        'options' => [
                            'allow' => \Laminas\Validator\Hostname::ALLOW_DNS,
                            'useMxCheck' => false,                            
                        ],
                    ],
                ],
            ]);     
        
        $inputFilter->add([
                'name'     => 'phone',
                'required' => false,
                'filters'  => [
                    [
                        'name' => PhoneFilter::class,
                        'options' => [
                            'format' => PhoneFilter::PHONE_FORMAT_RU,
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
        
        // Add input for "password" field
        $inputFilter->add([
                'name'     => 'password',
                'required' => true,
                'filters'  => [                    
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 4,
                            'max' => 64
                        ],
                    ],
                ],
            ]);     
        
        // Add input for "remember_me" field
        $inputFilter->add([
                'name'     => 'remember_me',
                'required' => false,
                'filters'  => [                    
                ],                
                'validators' => [
                    [
                        'name'    => 'InArray',
                        'options' => [
                            'haystack' => [0, 1],
                        ]
                    ],
                ],
            ]);
        
        // Add input for "redirect_url" field
        $inputFilter->add([
                'name'     => 'redirect_url',
                'required' => false,
                'filters'  => [
                    ['name'=>'StringTrim']
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 2048
                        ]
                    ],
                ],
            ]);
    }        
}

