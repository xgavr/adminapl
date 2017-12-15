<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Application\Entity\Goods;
use Application\Entity\Producer;
use Application\Entity\Tax;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Description of Goods
 *
 * @author Daddy
 */
class GoodsForm extends Form implements ObjectManagerAwareInterface
{
    
    
    protected $objectManager;

    protected $entityManager;
        
    
    /**
     * Конструктор.     
     */
    public function __construct($entityManager)
    {
        // Определяем имя формы.
        parent::__construct('goods-form');
     
        $this->entityManager = $entityManager;
        
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();         
    }

    protected function addElements() 
    {
                
        // Добавляем поле "name"
        $this->add([           
            'type'  => 'text',
            'name' => 'name',
            'attributes' => [
                'id' => 'goodsname'
            ],
            'options' => [
                'label' => 'Наименование',
            ],
        ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'code',
            'attributes' => [                
                'id' => 'goodscode'
            ],
            'options' => [
                'label' => 'Аритикул',
            ],
       ]);
        
        $this->add([
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'producer',
            'attributes' => [                
                'id' => 'goodsproducer',
                'data-live-search'=> "true",
                'class' => "selectpicker",
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Application\Entity\Producer',
                'label' => 'Производитель',
                'property'       => 'name',
                'display_empty_item' => true,
                'empty_item_label'   => '--выберете производителя--',                 
            ],
       ]);
        
        $this->add([
            'options' => [
                'label' => 'Налог',
            ],
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'tax',
            'attributes' => [                
                'id' => 'goodstax',
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Application\Entity\Tax',
                'label' => 'Налог',
                'property'       => 'name',
                'value' => 1,
            ],
       ]);
        
        $this->add([
            'type'  => 'select',
            'name' => 'available',
            'attributes' => [                
                'id' => 'goodavailable'
            ],
            'options' => [
                'label' => 'Доступность',
                'value_options' => [
                    Goods::AVAILABLE_TRUE => 'Доступно',
                    Goods::AVAILABLE_FALSE => 'Недоступно',
                ]
            ],
       ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'description',
            'attributes' => [                
                'id' => 'goodsdescription'
            ],
            'options' => [
                'label' => 'Описание',
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
                'name'     => 'code',
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
                            'max' => 1024
                        ],
                    ],
                ],
            ]);          

        $inputFilter->add([
                'name'     => 'producer',
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
                'name'     => 'tax',
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
                'name'     => 'description',
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
                            'max' => 1024
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
