<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cash\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Cash\Entity\Cash;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Description of Cash
 *
 * @author Daddy
 */
class CashForm extends Form 
{
    
    protected $objectManager;    
    
    /**
     * Конструктор.     
     */
    public function __construct()
    {
        // Определяем имя формы.
        parent::__construct('cash-form');
             
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
                'id' => 'name'
            ],
            'options' => [
                'label' => 'Наименование',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'aplId',
            'attributes' => [
                'id' => 'name'
            ],
            'options' => [
                'label' => 'Код Апл',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'accountNumber',
            'attributes' => [
                'id' => 'accountNumber',
                'value' => '50.01',
            ],
            'options' => [
                'label' => 'Счет кассы',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'bankInn',
            'attributes' => [
                'id' => 'bankInn',
                'value' => '',
            ],
            'options' => [
                'label' => 'ИНН сервиса',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'commission',
            'attributes' => [
                'id' => 'commission',
                'min' => 0,
                'step' => 0.01,
                'value' => 0
            ],
            'options' => [
                'label' => 'Комиссия',
            ],
        ]);

        // Add "status" field
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'attributes' => [
                'id' => 'status',
                'value' => Cash::STATUS_ACTIVE,
            ],
            'options' => [
                'label' => 'Статус',
                'value_options' => Cash::getStatusList(),
            ],
        ]);

        $this->add([            
            'type'  => 'select',
            'name' => 'restStatus',
            'attributes' => [
                'id' => 'restStatus',
                'value' => Cash::REST_RETIRED,
            ],
            'options' => [
                'label' => 'Остаток',
                'value_options' => Cash::getRestStatusList(),
            ],
        ]);

        $this->add([            
            'type'  => 'select',
            'name' => 'tillStatus',
            'attributes' => [
                'id' => 'tillStatus',
                'value' => Cash::TILL_RETIRED,
            ],
            'options' => [
                'label' => 'В кассе',
                'value_options' => Cash::getTillStatusList(),
            ],
        ]);

        $this->add([            
            'type'  => 'select',
            'name' => 'orderStatus',
            'attributes' => [
                'id' => 'orderStatus',
                'value' => Cash::ORDER_RETIRED,
            ],
            'options' => [
                'label' => 'В заказе',
                'value_options' => Cash::getOrderStatusList(),
            ],
        ]);

        $this->add([            
            'type'  => 'select',
            'name' => 'refillStatus',
            'attributes' => [
                'id' => 'refillStatus',
                'value' => Cash::REFILL_ACTIVE,
            ],
            'options' => [
                'label' => 'Пополнение',
                'value_options' => Cash::getRefillStatusList(),
            ],
        ]);

        $this->add([            
            'type'  => 'select',
            'name' => 'supplierStatus',
            'attributes' => [
                'id' => 'supplierStatus',
                'value' => Cash::SUPPLIER_ACTIVE,
            ],
            'options' => [
                'label' => 'Расчеты с поставщиками',
                'value_options' => Cash::getSupplierStatusList(),
            ],
        ]);

        $this->add([            
            'type'  => 'select',
            'name' => 'checkStatus',
            'attributes' => [
                'id' => 'checkStatus',
                'value' => Cash::CHECK_IGNORE,
            ],
            'options' => [
                'label' => 'Чек',
                'value_options' => Cash::getCheckStatusList(),
            ],
        ]);

        $this->add([            
            'type'  => 'select',
            'name' => 'payment',
            'attributes' => [
                'id' => 'payment',
                'value' => Cash::PAYMENT_CASH,
            ],
            'options' => [
                'label' => 'Оплата',
                'value_options' => Cash::getPaymentList(),
            ],
        ]);

        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'cash_submit_button',
            ],
        ]);        
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
