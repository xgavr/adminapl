<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Fasade\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Fasade\Entity\GroupSite;

/**
 * Description of GroupSite
 *
 * @author Daddy
 */
class GroupSiteForm extends Form 
{
    
    
    protected $objectManager;

    protected $entityManager;
        
    /**
     * Конструктор.     
     */
    public function __construct($entityManager)
    {
        // Определяем имя формы.
        parent::__construct('group-site-form');
     
        $this->entityManager = $entityManager;

        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();         
    }

    protected function addElements() 
    {
                
        $this->add([
            'type'  => 'text',
            'name' => 'name',
            'attributes' => [                
                'id' => 'rackName'
            ],
            'options' => [
                'label' => 'Наименование',
            ],
       ]);
        
        $this->add([
            'type'  => 'textarea',
            'name' => 'description',
            'attributes' => [                
                'id' => 'description',
                'rows' => 6,
            ],
            'options' => [
                'label' => 'Описание',
            ],
       ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'groupSite',
            'attributes' => [                
//                'required' => 'required',
                'id' => 'groupSite',
//                'disabled' => true,
            ],
            'options' => [
                'label' => 'Входит в категорию',
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'value' => GroupSite::STATUS_ACTIVE,
            'attributes' => [                
//                'required' => 'required',
                'id' => 'status',
            ],
            'options' => [
                'label' => 'Статус',
                'value_options' => GroupSite::getStatusList(),
            ],
        ]);
        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'groupSiteSubmit',
            ],
        ]);        

    }
    
    private function addInputFilter() 
    {
        
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
        
        $inputFilter->add([
                'name'     => 'name',
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
                            'max' => 56
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
        
        $inputFilter->add([
                'name'     => 'groupSite',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
            ]); 
        
        $inputFilter->add([
                'name'     => 'status',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=> array_keys(GroupSite::getStatusList())]]
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
