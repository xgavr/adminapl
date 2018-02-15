<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use User\Validator\EmailExistsValidator;
/**
 * Description of Email
 *
 * @author Daddy
 */
class EmailForm extends Form
{
    
    private $entityManager;
    
    private $email;
    
    /**
     * Конструктор.     
     */
    public function __construct($entityManager = null, $email = null)
    {
        
        $this->entityManager = $entityManager;
        $this->email = $email;
        
        // Определяем имя формы.
        parent::__construct('email-form');
     
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();         
    }
    
    /**
    * Этот метод добавляет элементы к форме (поля ввода и кнопку отправки формы).
    */
    protected function addElements() 
    {
                
        // Добавляем поле "name"
        $this->add([           
            'type'  => 'text',
            'name' => 'name',
            'attributes' => [
                'id' => 'email_name'
            ],
            'options' => [
                'label' => 'Email',
            ],
        ]);
        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'email_submitbutton',
            ],
        ]);        
    }
    
   /**
     * Этот метод создает фильтр входных данных (используется для фильтрации/валидации).
     */
    private function addInputFilter() 
    {
        
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
        
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
                    [
                        'name' => 'EmailAddress',
                        'options' => [
                            'allow' => \Zend\Validator\Hostname::ALLOW_DNS,
                            'useMxCheck'    => false,                            
                        ],
                    ],
                    [
                        'name' => EmailExistsValidator::class,
                        'options' => [
                            'entityManager' => $this->entityManager,
                            'email' => $this->email
                        ],
                    ],                    
                ],
            ]);        
    }    
}
