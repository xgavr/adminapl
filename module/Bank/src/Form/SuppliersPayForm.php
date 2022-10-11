<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Bank\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Bank\Entity\Payment;

/**
 * Description of suppliers pay form
 *
 * @author Daddy
 */
class SuppliersPayForm extends Form implements ObjectManagerAwareInterface
{
    
    
    protected $objectManager;

    protected $entityManager;
        
    /**
     * Конструктор.     
     */
    public function __construct($entityManager)
    {
        // Определяем имя формы.
        parent::__construct('suppliers-pay-form');
     
        $this->entityManager = $entityManager;
        
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();         
    }

    protected function addElements() 
    {
                
        $this->add([
            'type'  => 'select',
            'name' => 'bankAccount',
            'attributes' => [                
            ],
            'options' => [
                'label' => 'Счет списания',
            ],
        ]);        


        $this->add([
            'type'  => 'date',
            'name' => 'paymentDate',
            'attributes' => [                
                'step' => 1,
                'value' => date('Y-m-d'),
                'min' => date('Y-m-d'),
            ],
            'options' => [
                'label' => 'Дата платежа',
//                'format' => 'Y-m-d',
            ],
        ]);
                
        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'ot_submitbutton',
            ],
        ]);        

        // Add the CSRF field
    }
    
    private function addInputFilter() 
    {
        
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
        
        
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
