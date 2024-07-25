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
 * Description of PtGood
 *
 * @author Daddy
 */
class PtGoodForm extends Form 
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
        parent::__construct('pt-good-form');
     
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
            'name' => 'quantity',
            'attributes' => [                
                'id' => 'quantity',
                'value' => 1,
                'min' => 1
            ],
            'options' => [
                'label' => 'Количество',
            ],
       ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'amount',
            'attributes' => [                
                'id' => 'amount',
                'value' => 0,
            ],
            'options' => [
                'label' => 'Сумма',
            ],
       ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'price',
            'attributes' => [                
                'id' => 'price',
                'value' => 0,
            ],
            'options' => [
                'label' => 'Цена',
            ],
       ]);

        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'pt_good_submitbutton',
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
        
        $inputFilter->add([
                'name'     => 'quantity',
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
                            'max' => 64
                        ],
                    ],
                    [    
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min' => 0,
                            'inclusive' => false
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'amount',
                'required' => false,
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
                            'max' => 64
                        ],
                    ],
                    [    
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min' => 0,
                            'inclusive' => false
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'price',
                'required' => false,
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
                            'max' => 64
                        ],
                    ],
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
