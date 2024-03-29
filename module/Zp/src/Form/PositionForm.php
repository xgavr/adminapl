<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Zp\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Zp\Entity\Position;

/**
 * Description of Position
 *
 * @author Daddy
 */
class PositionForm extends Form
{
    
    /**
     * Конструктор.     
     */
    public function __construct()
    {
                
        // Определяем имя формы.
        parent::__construct('position-form');
     
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
            'type'  => 'number',
            'name' => 'num',
            'attributes' => [
                'id' => 'num',
                'min' => 0,
                'value' => 1,
            ],
            'options' => [
                'label' => 'Количество ставок',
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
                'value_options' => Position::getStatusList(),
            ],
        ]);
        
        $this->add([           
            'type'  => 'select',
            'name' => 'kind',
            'attributes' => [
                'id' => 'kind'
            ],
            'options' => [
                'label' => 'Раздел',
                'value_options' => Position::getKindList(),
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
        
        $this->add([           
            'type'  => 'select',
            'name' => 'parentPosition',
            'attributes' => [
                'id' => 'positionSelectForm'
            ],
            'options' => [
                'label' => 'Входит в группу',
            ],
        ]);
        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'position_submitbutton',
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

        $inputFilter->add([
                'name'     => 'parentPosition',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                ],
            ]);  
        
        $inputFilter->add([
                'name'     => 'num',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                ],
            ]);        
    }    
}
