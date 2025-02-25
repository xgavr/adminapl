<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Application\Entity\Oem;

/**
 * Description of Oem
 *
 * @author Daddy
 */
class OemForm extends Form
{
    /**
     * Конструктор.     
     */
    public function __construct()
    {
        // Определяем имя формы.
        parent::__construct('oem-form');
     
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
                
        // Добавляем поле "oeNumber"
        $this->add([           
            'type'  => 'text',
            'name' => 'oeNumber',
            'attributes' => [
                'id' => 'oeNumber'
            ],
            'options' => [
                'label' => 'Оригинальный номер',
            ],
        ]);
        
        // Добавляем поле "brandName"
        $this->add([
            'type'  => 'text',
            'name' => 'brandName',
            'attributes' => [                
                'id' => 'brandName'
            ],
            'options' => [
                'label' => 'Бренд',
            ],
       ]);

        // Добавляем поле "status"
        $this->add([
            'type'  => 'select',
            'name' => 'source',
            'attributes' => [                
                'id' => 'source',
                'value' => Oem::SOURCE_MAN,
            ],
            'options' => [
                'label' => 'Источник номера',
                'value_options' => Oem::getSourceList(),
            ],
       ]);
                
        $this->add([
            'type'  => 'select',
            'name' => 'supplier',
            'attributes' => [                
                'id' => 'supplier',
            ],
            'options' => [
                'label' => 'Поставщик (если вводим номер поставщика)',
                'value_options' => [],
            ],
       ]);
                
        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'oemsubmitbutton',
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
                'name'     => 'oeNumber',
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
                'name'     => 'brandName',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'StripTags'],
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
                'name'     => 'supplier',
                'required' => false,
                'filters'  => [                    
                ],                
                'validators' => [
                ],
            ]);          
    }    
}
