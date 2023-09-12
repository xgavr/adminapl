<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
/**
 * Description of Selection
 *
 * @author Daddy
 */
class SelectionForm extends Form
{
    
    private $entityManager;
        
    /**
     * Конструктор.     
     */
    public function __construct($entityManager = null)
    {
        
        $this->entityManager = $entityManager;
        
        // Определяем имя формы.
        parent::__construct('selection-form');
     
        // Задает для этой формы метод POST.
        //$this->setAttribute('method', 'post');
        $this->setAttribute('onsubmit', 'return false;');
                
        $this->addElements();
        $this->addInputFilter();         
    }
    
    /**
    * Этот метод добавляет элементы к форме (поля ввода и кнопку отправки формы).
    */
    protected function addElements() 
    {
                
        $this->add([           
            'type'  => 'textarea',
            'name' => 'info',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Что нужно',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'oem',
            'attributes' => [
            ],
            'options' => [
                'label' => 'OE',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'oemInfo',
            'attributes' => [
            ],
            'options' => [
                'label' => 'OE',
            ],
        ]);
        

        // Добавляем поле "comment"
        $this->add([           
            'type'  => 'text',
            'name' => 'comment',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Комментарий',
            ],
        ]);
        

        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'selection_submitbutton',
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
                    ['name' => 'StripTags'],
                    ['name' => 'StripNewlines'],
                ],                
                'validators' => [
                ],
            ]);        

        $inputFilter->add([
                'name'     => 'oemInfo',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                    ['name' => 'StripNewlines'],
                ],                
                'validators' => [
                ],
            ]);        
    }    
}
