<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Application\Entity\Goods;

/**
 * Description of BindGoodForm
 *
 * @author Daddy
 */
class BindGoodForm extends Form 
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
        parent::__construct('bind-good-form');
     
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
            'type'  => 'text',
            'name' => 'oem',
            'attributes' => [                
                'id' => 'oem'
            ],
            'options' => [
                'label' => 'OEM номер',
            ],
       ]);

        $this->add([
            'type'  => 'text',
            'name' => 'oem_brand',
            'attributes' => [                
                'id' => 'oem_brand'
            ],
            'options' => [
                'label' => 'OEM бренд',
            ],
       ]);

        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'bind_good_submitbutton',
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
                'name'     => 'oem',
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
                'name'     => 'oem_brand',
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
