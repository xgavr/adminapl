<?php
namespace User\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use User\Filter\PhoneFilter;
use User\Validator\PhoneNoExistsValidator;
use User\Validator\TokenNoExistsValidator;

/**
 * This form is used to collect user's E-mail address (used to recover password).
 */
class PasswordResetPhoneForm extends Form
{

    private $entityManager;
    
    /**
     * Constructor.     
     */
    public function __construct($entityManager = null)
    {
        
        $this->entityManager = $entityManager;        
        // Define form name
        parent::__construct('password-reset-phone-form');
     
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
        // Add "phone" field
        $this->add([            
            'type'  => 'text',
            'name' => 'phone',
            'options' => [
                'label' => 'Ваш телефон',
            ],
        ]);
        
        // Add "token" field
        $this->add([            
            'type'  => 'text',
            'name' => 'token',
            'options' => [
                'label' => 'Код из SMS',
            ],
        ]);
        
//        // Add the CAPTCHA field
//        $this->add([
//            'type' => 'captcha',
//            'name' => 'captcha',
//            'options' => [
//                'label' => 'Проверка на человека',
//                'captcha' => [
//                    'class' => 'Image',
//                    'imgDir' => 'public/img/captcha',
//                    'suffix' => '.png',
//                    'imgUrl' => '/img/captcha/',
//                    'imgAlt' => 'CAPTCHA Image',
//                    'font' => './data/font/thorne_shaded.ttf',
//                    'fsize' => 24,
//                    'width' => 350,
//                    'height' => 100,
//                    'expiration' => 600,
//                    'dotNoiseLevel' => 40,
//                    'lineNoiseLevel' => 3
//                ],
//            ],
//        ]);
//        
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
                'value' => 'Сбросить пароль',
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
                
        // Add input for "phone" field
        $inputFilter->add([
                'name'     => 'phone',
                'required' => true,
                'filters'  => [
                    [
                        'name' => PhoneFilter::class,
                        'options' => [
                            'format' => PhoneFilter::PHONE_FORMAT_DB,
                        ]
                    ],
                ],                
                'validators' => [
                    [
                        'name'    => 'PhoneNumber',
                        'options' => [
                        ],
                    ],
                    [
                        'name' => PhoneNoExistsValidator::class,
                        'options' => [
                            'entityManager' => $this->entityManager,
                        ],
                    ],
                ],
            ]);                     
        
        // Add input for "token" field
        $inputFilter->add([
                'name'     => 'token',
                'required' => true,
                'validators' => [
                    [
                        'name' => TokenNoExistsValidator::class,
                        'options' => [
                            'entityManager' => $this->entityManager,
                        ],
                    ],
                ],
            ]);                     
    }        
}
