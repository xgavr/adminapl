<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Admin\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Description of SbpSettings
 *
 * @author Daddy
 */
class SbpSettings extends Form implements ObjectManagerAwareInterface
{
    
    protected $objectManager;    
    
    /**
     * Конструктор.     
     */
    public function __construct()
    {
        // Определяем имя формы.
        parent::__construct('api-sbp-settings');
             
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();         
    }

    protected function addElements() 
    {
        $this->add([            
            'type'  => 'select',
            'name' => 'account',
            'options' => [
                'label' => 'Идентификатор счёта юрлица',
                'value_options' => [
                ]
            ],
        ]);

        $this->add([            
            'type'  => 'text',
            'name' => 'customer_code',
            'options' => [
                'label' => 'Уникальный код клиента',
            ],
        ]);
                
        $this->add([            
            'type'  => 'text',
            'name' => 'legal_id',
            'options' => [
                'label' => 'Идентификатор юрлица в СБП',
            ],
        ]);

        $this->add([            
            'type'  => 'text',
            'name' => 'merchant_id',
            'options' => [
                'label' => 'Идентификатор ТСП в СБП ',
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
