<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Admin\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Description of Goods
 *
 * @author Daddy
 */
class BankSettingsForm extends Form implements ObjectManagerAwareInterface
{
    
    protected $objectManager;    
    
    /**
     * Конструктор.     
     */
    public function __construct()
    {
        // Определяем имя формы.
        parent::__construct('settings-form');
             
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();         
    }

    protected function addElements() 
    {
                        
        $this->add([            
            'type'  => 'select',
            'name' => 'statement_by_api',
            'options' => [
                'label' => 'Выписки по апи',
                'value_options' => [
                    1 => 'Получать',
                    2 => 'Не получать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'statement_by_file',
            'options' => [
                'label' => 'Выписки из файла по почте',
                'value_options' => [
                    1 => 'Получать',
                    2 => 'Не получать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'doc_by_api',
            'options' => [
                'label' => 'Платежки в банк по апи',
                'value_options' => [
                    1 => 'Отправлять',
                    2 => 'Не отправлять',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'tarnsfer_apl',
            'options' => [
                'label' => 'Обмен а АПЛ',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Остановить',                    
                ]
            ],
        ]);
                
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'submit_button',
            ],
        ]);        
    }
    
    private function addInputFilter() 
    {
        
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
                
        $inputFilter->add([
                'name'     => 'statement_by_api',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'statement_by_file',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'doc_by_api',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'tarnsfer_apl',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
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
