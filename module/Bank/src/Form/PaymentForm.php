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
 * Description of Ot
 *
 * @author Daddy
 */
class PaymentForm extends Form 
{
    
    
    protected $objectManager;

    protected $entityManager;
        
    /**
     * Конструктор.     
     */
    public function __construct($entityManager)
    {
        // Определяем имя формы.
        parent::__construct('payment-form');
     
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
            'type'  => 'select',
            'name' => 'supplier',
            'attributes' => [  
                'id' => 'supplier'
            ],
            'options' => [
                'label' => 'Оплата поставщику',
            ],
        ]);        

        $this->add([
            'type'  => 'text',
            'name' => 'counterpartyAccountNumber',
            'attributes' => [                
            ],
            'options' => [
                'label' => 'Счет получателя',
            ],
        ]);

        $this->add([
            'type'  => 'text',
            'name' => 'counterpartyBankCorrAccount',
            'attributes' => [                
            ],
            'options' => [
                'label' => 'Кор. счёт банка получателя',
            ],
        ]);

        $this->add([
            'type'  => 'text',
            'name' => 'counterpartyBankBik',
            'attributes' => [                
            ],
            'options' => [
                'label' => 'БИК банка получателя',
            ],
        ]);

        $this->add([
            'type'  => 'text',
            'name' => 'counterpartyBank',
            'attributes' => [                
            ],
            'options' => [
                'label' => 'Банка получателя',
            ],
        ]);

        $this->add([
            'type'  => 'text',
            'name' => 'counterpartyInn',
            'attributes' => [                
            ],
            'options' => [
                'label' => 'ИНН получателя',
            ],
        ]);

        $this->add([
            'type'  => 'text',
            'name' => 'counterpartyKpp',
            'attributes' => [                
            ],
            'options' => [
                'label' => 'КПП получателя',
            ],
        ]);

        $this->add([
            'type'  => 'text',
            'name' => 'counterpartyName',
            'attributes' => [                
            ],
            'options' => [
                'label' => 'Получатель',
            ],
        ]);

        $this->add([
            'type'  => 'number',
            'name' => 'amount',
            'attributes' => [    
                'min' => 0,
                'step' => 0.01,
            ],
            'options' => [
                'label' => 'Сумма',
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
        
        $this->add([
            'type'  => 'text',
            'name' => 'purpose',
            'attributes' => [                
            ],
            'options' => [
                'label' => 'Назначение',
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'nds',
            'attributes' => [                
                'value' => Payment::NDS_20,
            ],
            'options' => [
                'label' => 'НДС',
                'value_options' => Payment::getNdsList(),
            ],
        ]);

        $this->add([
            'type'  => 'text',
            'name' => 'supplierBillId',
            'attributes' => [                
                'value' => 0,
            ],
            'options' => [
                'label' => 'Код УИН',
            ],
       ]);

        $this->add([
            'type'  => 'text',
            'name' => 'taxInfoDocumentDate',
            'attributes' => [                
                'value' => 0,
            ],
            'options' => [
                'label' => 'Дата бюджетного документа',
            ],
       ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'taxInfoDocumentNumber',
            'attributes' => [                
                'value' => 0,
            ],
            'options' => [
                'label' => 'Номер документа',
            ],
       ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'taxInfoKbk',
            'attributes' => [                
                'value' => 0,
            ],
            'options' => [
                'label' => 'КБК',
            ],
       ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'taxInfoOkato',
            'attributes' => [                
            ],
            'options' => [
                'label' => 'Код ОКАТО/ОКТМО',
            ],
       ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'taxInfoPeriod',
            'attributes' => [   
                'value' => 0,
            ],
            'options' => [
                'label' => 'Налоговый период',
            ],
       ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'taxInfoReasonCode',
            'attributes' => [                
                'value' => 0,
            ],
            'options' => [
                'label' => 'Основание платежа',
            ],
       ]);
        
        $this->add([
            'type'  => 'select',
            'name' => 'taxInfoStatus',
            'attributes' => [
                'value' => Payment::TAX_STATUS_01,
            ],
            'options' => [
                'label' => 'Статус плательщика',
                'value_options' => Payment::getTaxInfoStatusList(),
            ],
       ]);
        

        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'attributes' => [                
                'value' => Payment::STATUS_ACTIVE,
            ],
            'options' => [
                'label' => 'Статус',
                'value_options' => Payment::getStatusList(),
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'paymentType',
            'attributes' => [ 
                'id' => 'paymentType',
                'value' => Payment::PAYMENT_TYPE_NORMAL,
            ],
            'options' => [
                'label' => 'Тип платежа',
                'value_options' => Payment::getPaymentTypeList(),
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'paymentAuto',
            'attributes' => [                
                'value' => Payment::PAYMENT_AUTO_ONE,
            ],
            'options' => [
                'label' => 'Включить автоплатеж',
                'value_options' => Payment::getPaymentAutoList(),
            ],
        ]);

        $this->add([            
            'type'  => 'select',
            'name' => 'paymentAutoDay',
            'attributes' => [                
                'value' => 1,
            ],
            'options' => [
                'label' => 'День платежа',
                'value_options' => range(1, 31),
            ],
        ]);

        $this->add([            
            'type'  => 'date',
            'name' => 'paymentAutoStopDate',
            'attributes' => [                
            ],
            'options' => [
                'label' => 'Дата окончания автоплатежа',
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
        
        
        $inputFilter->add([
                'name'     => 'status',
                'required' => true,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=> array_keys(Payment::getStatusList())]]
                ],
            ]); 

        $inputFilter->add([
                'name'     => 'supplier',
                'required' => false,
                'filters'  => [                    
                ],                
                'validators' => [
                ],
            ]); 

        $inputFilter->add([
                'name'     => 'paymentAutoStopDate',
                'required' => false,
                'filters'  => [                    
                ],                
                'validators' => [
                ],
            ]); 
        
        $inputFilter->add([
                'name'     => 'paymentAutoDay',
                'required' => false,
                'filters'  => [                    
                    ['name' => 'ToInt'],
                ],                
                'validators' => [
                    ['name'=>'InArray', 'options'=>['haystack'=> range(1, 31)]]
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
