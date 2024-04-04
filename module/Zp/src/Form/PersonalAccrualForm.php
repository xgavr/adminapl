<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Zp\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Zp\Entity\PersonalAccrual;

/**
 * Description of PersonalAccrual
 *
 * @author Daddy
 */
class PersonalAccrualForm extends Form
{
    
    /**
     * Конструктор.     
     */
    public function __construct()
    {
                
        // Определяем имя формы.
        parent::__construct('personal-accrual-form');
     
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
                
        // Добавляем поле "rate"
        $this->add([           
            'type'  => 'text',
            'name' => 'rate',
            'attributes' => [
                'id' => 'rate',
                'value' => 0,
            ],
            'options' => [
                'label' => 'Размер',
            ],
        ]);

        $this->add([           
            'type'  => 'select',
            'name' => 'status',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Статус',
                'value_options' => PersonalAccrual::getStatusList(),
            ],
        ]);
        
        $this->add([           
            'type'  => 'select',
            'name' => 'taxedNdfl',
            'attributes' => [
                'id' => 'taxedNdflSelectForm'
            ],
            'options' => [
                'label' => 'Облагать НДФЛ',
                'value_options' => PersonalAccrual::getTaxedNdflList(),                
            ],
        ]);
        
        $this->add([           
            'type'  => 'select',
            'name' => 'accrual',
            'attributes' => [
                'id' => 'accrualSelectForm'
            ],
            'options' => [
                'label' => 'Вид начисления',
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
                'name'     => 'rate',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                ],
            ]);        

    }    
}
