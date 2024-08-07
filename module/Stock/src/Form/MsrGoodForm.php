<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Stock\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Description of MsrGood
 *
 * @author Daddy
 */
class MsrGoodForm extends Form
{
    
    
    protected $objectManager;

    protected $entityManager;
    
    protected $good; 
        
    /**
     * Конструктор.     
     */
    public function __construct($entityManager, $good = null)
    {
        // Определяем имя формы.
        parent::__construct('msr-good-form');
     
        $this->entityManager = $entityManager;
        $this->good = $good;
                
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();         
    }

    protected function addElements() 
    {
                
        $this->add([
            'type'  => 'hidden',
            'name' => 'good',
            'attributes' => [                
                'id' => 'good'
            ],
            'options' => [
                'label' => 'Товар Ид',
            ],
       ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'aplId',
            'attributes' => [                
                'id' => 'aplId'
            ],
            'options' => [
                'label' => 'Апл Id',
            ],
        ]);        

        $this->add([
            'type'  => 'text',
            'name' => 'code',
            'attributes' => [                
                'id' => 'code'
            ],
            'options' => [
                'label' => 'Артикул',
            ],
        ]);        

        $this->add([
            'type'  => 'text',
            'name' => 'goodInputName',
            'attributes' => [                
                'id' => 'goodInputName'
            ],
            'options' => [
                'label' => 'Товар',
            ],
       ]);        

        $this->add([
            'type'  => 'number',
            'name' => 'saleQty',
            'attributes' => [                
                'id' => 'saleQty',
                'min' => 0,
                'value' => 0,
            ],
            'options' => [
                'label' => 'Количество продано',
            ],
       ]);        

        $this->add([
            'type'  => 'number',
            'name' => 'returnQty',
            'attributes' => [                
                'id' => 'returnQty',
                'min' => 0,
                'value' => 0,
            ],
            'options' => [
                'label' => 'Количество возврат',
            ],
       ]);        

        $this->add([
            'type'  => 'text',
            'name' => 'priceSale',
            'attributes' => [                
                'id' => 'priceSale'
            ],
            'options' => [
                'label' => 'Цена',
            ],
       ]);        

        $this->add([
            'type'  => 'text',
            'name' => 'saleAmount',
            'attributes' => [                
                'id' => 'saleAmount'
            ],
            'options' => [
                'label' => 'Сумма продажи',
            ],
       ]);        

        $this->add([
            'type'  => 'text',
            'name' => 'returnAmount',
            'attributes' => [                
                'id' => 'returnAmount'
            ],
            'options' => [
                'label' => 'Сумма возврата',
            ],
       ]);        


        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'msr_good_submitbutton',
            ],
        ]);        

        // Add the CSRF field
        $this->add([
            'type' => 'csrf',
            'name' => 'csrf',
            'options' => [
                'csrf_options' => [
                'timeout' => 600
                ]
            ],
        ]);
        
    }
    
    private function addInputFilter() 
    {
        
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
        
        $inputFilter->add([
                'name'     => 'good',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    [    
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min' => 0,
                            'inclusive' => false
                        ],
                    ],
                ],
            ]);          
    }    
    
    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }        
    
}
