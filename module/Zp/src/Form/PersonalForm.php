<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Zp\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Zp\Entity\Accrual;

/**
 * Description of Personal
 *
 * @author Daddy
 */
class PersonalForm extends Form
{
    
    /**
     * Конструктор.     
     */
    public function __construct()
    {
                
        // Определяем имя формы.
        parent::__construct('personal-form');
     
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
            ],
            'options' => [
                'label' => 'Наименование',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'aplId',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Apl Id',
            ],
        ]);
        
        $this->add([           
            'type'  => 'textarea',
            'name' => 'comment',
            'attributes' => [
                'rows' => 2,
            ],
            'options' => [
                'label' => 'Комментарий',
            ],
        ]);

        $this->add([           
            'type'  => 'select',
            'name' => 'status',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Статус',
                'value_options' => Accrual::getStatusList(),
            ],
        ]);
        
        $this->add([           
            'type'  => 'select',
            'name' => 'basis',
            'attributes' => [
            ],
            'options' => [
                'label' => 'База начислений',
                'value_options' => Accrual::getBasisList(),
            ],
        ]);
        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'personal_submitbutton',
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
                ],
            ]);        

        $inputFilter->add([
                'name'     => 'comment',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                ],
            ]);        

        $inputFilter->add([
                'name'     => 'aplId',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                ],
            ]);        
    }    
}
