<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;

/**
 * Description of Order legal
 *
 * @author Daddy
 */
class OrderLegalForm extends Form
{
    
    protected $objectManager;

    protected $entityManager;
        
    /**
     * Конструктор.     
     */
    public function __construct($entityManager)
    {
        // Определяем имя формы.
        parent::__construct('order-legal-form');
     
        $this->entityManager = $entityManager;
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();         
    }
    
    /**
    * Этот метод добавляет элементы к форме (поля ввода и кнопку отправки формы).
    */
    protected function addElements() 
    {
                

        $this->add([           
            'type'  => 'textarea',
            'name' => 'legalName',
            'attributes' => [
                'rows' => 2,
            ],
            'options' => [
                'label' => 'Покупатель наименование',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'legalInn',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Покупатель ИНН',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'legalKpp',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Покупатель КПП',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'legalOgrn',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Покупатель ОГРН',
            ],
        ]);
        
        $this->add([           
            'type'  => 'textarea',
            'name' => 'legalAddress',
            'attributes' => [
                'rows' => 4,
            ],
            'options' => [
                'label' => 'Покупатель Местонахождение',
            ],
        ]);
        
        $this->add([           
            'type'  => 'hidden',
            'name' => 'legal',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Покупатель ЮЛ',
            ],
        ]);
        
        $this->add([           
            'type'  => 'hidden',
            'name' => 'legalOkpo',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Покупатель ОКПО',
            ],
        ]);
        
        $this->add([           
            'type'  => 'hidden',
            'name' => 'legalHead',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Руководитель',
            ],
        ]);
        
        $this->add([           
            'type'  => 'textarea',
            'name' => 'recipientName',
            'attributes' => [
                'rows' => 2,
            ],
            'options' => [
                'label' => 'Грузополучатель наименование',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'recipientInn',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Грузополучатель ИНН',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'recipientKpp',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Грузополучатель КПП',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'recipientOgrn',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Грузополучатель ОГРН',
            ],
        ]);
        
        $this->add([           
            'type'  => 'hidden',
            'name' => 'recipientOkpo',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Грузополучатель ОКПО',
            ],
        ]);
        
        $this->add([           
            'type'  => 'hidden',
            'name' => 'recipientHead',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Руководитель',
            ],
        ]);
        
        $this->add([           
            'type'  => 'textarea',
            'name' => 'recipientAddress',
            'attributes' => [
                'rows' => 4,
            ],
            'options' => [
                'label' => 'Грузополучатель Местонахождение',
            ],
        ]);
        
        $this->add([           
            'type'  => 'hidden',
            'name' => 'recipient',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Грузополучатель',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'rs',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Расчетный счет',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'ks',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Корр. счет',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'bik',
            'attributes' => [
            ],
            'options' => [
                'label' => 'БИК банка',
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'bankName',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Наименованние банка',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'bankCity',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Город банка',
            ],
        ]);

        $this->add([           
            'type'  => 'hidden',
            'name' => 'bankAccount',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Банк плательщика',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'invoiceInfo',
            'attributes' => [
            ],
            'options' => [
                'label' => 'Комментарий к счету',
            ],
        ]);

        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'order_legal_submitbutton',
            ],
        ]);        
    }
    
   /**
     * Этот метод создает фильтр входных данных (используется для фильтрации/валидации).
     */
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
