<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ApiMarketPlace\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
/**
 * Description of Comment
 *
 * @author Daddy
 */
class MarketplaceOrderForm extends Form
{
    
    private $entityManager;
        
    /**
     * Конструктор.     
     */
    public function __construct($entityManager = null)
    {
        
        $this->entityManager = $entityManager;
        
        // Определяем имя формы.
        parent::__construct('marketplace-order-form');
     
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
            'type'  => 'select',
            'name' => 'marketplace',
            'attributes' => [
                'required' => 'required',                
            ],
            'options' => [
                'label' => 'Торговая площадка',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'postingNumber',
            'attributes' => [
                'required' => 'required',                
            ],
            'options' => [
                'label' => 'Номер отправления',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'orderNumber',
            'attributes' => [
                //'required' => 'required',                
            ],
            'options' => [
                'label' => 'Номер заказа',
            ],
        ]);

        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
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
                'name'     => 'postingNumber',
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
                'name'     => 'orderNumber',
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
