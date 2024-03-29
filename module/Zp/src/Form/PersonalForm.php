<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Zp\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Zp\Entity\Personal;

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
            'type'  => 'text',
            'name' => 'positionNum',
            'attributes' => [
                'id' => 'positionNum',
                'value' => 1,
                'min' => 0,
                'max' => 1,
            ],
            'options' => [
                'label' => 'Ставка',
            ],
        ]);
        
        $this->add([           
            'type'  => 'date',
            'name' => 'docDate',
            'attributes' => [
                'value' => date('Y-m-d'),
            ],
            'options' => [
                'label' => 'Дата',
            ],
        ]);
        
        $this->add([           
            'type'  => 'textarea',
            'name' => 'comment',
            'attributes' => [
                'rows' => 1,
            ],
            'options' => [
                'label' => 'Комментарий',
            ],
        ]);

        $this->add([           
            'type'  => 'select',
            'name' => 'user',
            'attributes' => [
                'id' => 'userSelectForm'
            ],
            'options' => [
                'label' => 'Сотрудник',
            ],
        ]);
        
        $this->add([           
            'type'  => 'select',
            'name' => 'position',
            'attributes' => [
                'id' => 'positionSelectForm'
            ],
            'options' => [
                'label' => 'Должность',
            ],
        ]);
        
        $this->add([           
            'type'  => 'select',
            'name' => 'status',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Статус',
                'value_options' => Personal::getStatusList(),
            ],
        ]);
        
        $this->add([           
            'type'  => 'select',
            'name' => 'company',
            'attributes' => [
                'id' => 'companySelectForm'
            ],
            'options' => [
                'label' => 'Компания',
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
