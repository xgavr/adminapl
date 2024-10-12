<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace GoodMap\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Application\Entity\Goods;

/**
 * Description of FoldDocForm
 *
 * @author Daddy
 */
class FoldDocForm extends Form 
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
        parent::__construct('fold-doc-form');
     
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
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'office',
            'attributes' => [                
                'id' => 'office',
                'data-live-search'=> "true",
                'class' => "selectpicker",
                'value' => $this->office->getId(),
            ],
            'options' => [
                'object_manager' => $this->entityManager,
                'target_class'   => 'Company\Entity\Office',
                'label' => 'Офис',
                'property'       => 'name',
                'display_empty_item' => true,
                'empty_item_label'   => '--выберете офис--',
            ],
        ]);
        
        $this->add([
            'type'  => 'select',
            'name' => 'fold',
            'attributes' => [                
                'id' => 'fold',
            ],
            'options' => [
                'label' => 'Хранение',
            ],
        ]);
                
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
        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'fold-doc_submitbutton',
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
