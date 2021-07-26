<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Application\Entity\Ring;
use Application\Entity\RingHelp;

/**
 * Description of RingHelpForm
 *
 * @author Daddy
 */
class RingHelpForm extends Form
{    
    
    /**
     * Конструктор.     
     */
    public function __construct()
    {
        // Определяем имя формы.
        parent::__construct('ring-help-form');
     
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');
        
        $this->addElements();
        $this->addInputFilter();         
    }
    
    /**
    * Этот метод добавляет элементы к форме (поля ввода и кнопку отправки формы).
    */
    protected function addElements() 
    {
                
        // Add "status" field
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'options' => [
                'label' => 'Статус',
                'value_options' => RingHelp::getStatusList(),
                'value' => RingHelp::STATUS_ACTIVE,
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'mode',
            'options' => [
                'label' => 'О чем звонят',
                'value_options' => Ring::getModeList(),
            ],
        ]);
        
        $this->add([           
            'type'  => 'textarea',
            'name' => 'info',
            'attributes' => [
                'id' => 'info'
            ],
            'options' => [
                'label' => 'Описание',
            ],
        ]);
                
        $this->add([           
            'type'  => 'number',
            'name' => 'sort',
            'attributes' => [
                'id' => 'sort'
            ],
            'options' => [
                'label' => 'Сортировка',
            ],
        ]);
                
        $this->add([           
            'type'  => 'search',
            'name' => 'name',
            'attributes' => [
                'id' => 'name',
                'autocomplete' => 'off',
            ],
            'options' => [
                'label' => 'Наименование',
            ],
        ]);
                                                
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'ring_help_group_submitbutton',
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
                            'max' => 256
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
                    ['name'=>'InArray', 'options'=>['haystack'=> array_keys(RingHelp::getStatusList())]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'sort',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    [
                        'name'    => 'IsInt',
                        'options' => [
                            'min' => 0,
                            'locale' => 'ru-Ru'
                        ],
                    ],
                ],
            ]);                          
        
        $inputFilter->add([
                'name'     => 'mode',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=> array_keys(Ring::getModeList())]]
                ],
            ]);         
    }        
}
