<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Company\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Company\Entity\Tax;
/**
 * Description of Tax
 *
 * @author Daddy
 */
class TaxForm extends Form
{
    /**
     * Конструктор.     
     */
    public function __construct()
    {
        // Определяем имя формы.
        parent::__construct('tax-form');
     
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
                'id' => 'taxname'
            ],
            'options' => [
                'label' => 'Наименование',
            ],
        ]);
        
        // Добавляем поле "amount"
        $this->add([
            'type'  => 'text',
            'name' => 'amount',
            'attributes' => [                
                'id' => 'taxamount'
            ],
            'options' => [
                'label' => 'Размер, %',
            ],
       ]);
                
        $this->add([
            'type'  => 'select',
            'name' => 'status',
            'attributes' => [                
                'id' => 'status'
            ],
            'options' => [
                'label' => 'Статус',
                'value_options' => Tax::getStatusList(),
            ],
       ]);
                
        $this->add([
            'type'  => 'select',
            'name' => 'kind',
            'attributes' => [                
                'id' => 'kind'
            ],
            'options' => [
                'label' => 'Вид',
                'value_options' => Tax::getKindList(),
            ],
       ]);
                
        $this->add([
            'type'  => 'date',
            'name' => 'dateStart',
            'attributes' => [                
                'id' => 'dateStart',
                'value' => date('Y-m-d'),
            ],
            'options' => [
                'label' => 'С даты',
            ],
       ]);
                
        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'taxsubmitbutton',
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
                    ['name' => 'StripTags'],
                    ['name' => 'StripNewlines'],
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 1024
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'amount',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'StripTags'],
                ],                
                'validators' => [
                    [
                        'name'    => 'Float',
                        'options' => [
                        'min' => 0,
                        'locale' => 'en_US'
                        ],
                    ],
                ],
            ]);          
    }    
}
