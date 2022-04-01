<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Application\Entity\BillSetting;

/**
 * Description of BillSetting
 *
 * @author Daddy
 */
class BillSettingForm extends Form
{
    
    private $entityManager;
        
    /**
     * Конструктор.     
     */
    public function __construct($entityManager = null)
    {
        
        $this->entityManager = $entityManager;
        
        // Определяем имя формы.
        parent::__construct('bill-setting-form');
     
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
            'type'  => 'text',
            'name' => 'name',
            'attributes' => [
                'id' => 'name',
                'disabled' => true,
            ],
            'options' => [
                'label' => 'Наименование',
            ],
        ]);

        // Add "status" field
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'attributes' => [
                'id' => 'status',
                'value' => BillSetting::STATUS_ACTIVE,
            ],
            'options' => [
                'label' => 'Статус',
                'value_options' => BillSetting::getStatusList(),
            ],
        ]);
        
        // Добавляем поле "description"
        $this->add([           
            'type'  => 'text',
            'name' => 'description',
            'attributes' => [
                'id' => 'description'
            ],
            'options' => [
                'label' => 'Коментарий',
            ],
        ]);
        
        $this->add([           
            'type'  => 'number',
            'name' => 'docNumCol',
            'attributes' => [
                'id' => 'docNumCol',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Номер документа',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'docNumRow',
            'attributes' => [
                'id' => 'docNumRow',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Номер документа',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'docDateCol',
            'attributes' => [
                'id' => 'docDateCol',
                'min' => 0,                
            ],
            'options' => [
                'label' => 'Дата документа',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'docDateRow',
            'attributes' => [
                'id' => 'docDateRow',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Дата документа',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'corNumCol',
            'attributes' => [
                'id' => 'corNumCol',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Номер корректировки',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'corNumRow',
            'attributes' => [
                'id' => 'corNumRow',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Номер корректировки',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'corDateCol',
            'attributes' => [
                'id' => 'corDateCol',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Дата корректировки',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'corDateRow',
            'attributes' => [
                'id' => 'corDateRow',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Дата корректировки',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'idNumCol',
            'attributes' => [
                'id' => 'idNumCol',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Номер отгрузки',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'idNumRow',
            'attributes' => [
                'id' => 'idNumRow',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Номер отгрузки',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'idDateCol',
            'attributes' => [
                'id' => 'idDateCol',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Дата отгрузки',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'idDateRow',
            'attributes' => [
                'id' => 'idDateRow',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Дата отгрузки',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'contractCol',
            'attributes' => [
                'id' => 'contractCol',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Номер договора',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'contractRow',
            'attributes' => [
                'id' => 'contractRow',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Номер договора',
            ],
        ]);
        
        $this->add([           
            'type'  => 'number',
            'name' => 'tagNoCashCol',
            'attributes' => [
                'id' => 'tagNoCashCol',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Оплата безнал',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'tagNoCashRow',
            'attributes' => [
                'id' => 'tagNoCashRow',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Оплата безнал',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'tagNoCashValue',
            'attributes' => [
                'id' => 'tagNoCashValue'
            ],
            'options' => [
                'label' => 'Оплата безнал',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'initTabRow',
            'attributes' => [
                'id' => 'initTabRow',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Строка начала таблицы товаров',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'articleCol',
            'attributes' => [
                'id' => 'articleCol',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Артикул',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'supplierIdCol',
            'attributes' => [
                'id' => 'supplierIdCol',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Номер у поставщика',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'goodNameCol',
            'attributes' => [
                'id' => 'goodNameCol',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Наименование товара',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'producerCol',
            'attributes' => [
                'id' => 'producerCol',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Производитель',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'quantityCol',
            'attributes' => [
                'id' => 'quantityCol',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Количество',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'priceCol',
            'attributes' => [
                'id' => 'priceCol',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Цена с НДС',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'amountCol',
            'attributes' => [
                'id' => 'amountCol',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Сумма с НДС',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'packageCodeCol',
            'attributes' => [
                'id' => 'packageCodeCol',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Упаковка, код',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'packcageCol',
            'attributes' => [
                'id' => 'packcageCol',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Упаковка',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'countryCodeCol',
            'attributes' => [
                'id' => 'countryCodeCol',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Страна, код',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'countryCol',
            'attributes' => [
                'id' => 'countryCol',
                'min' => 0,
            ],
            'options' => [
                'label' => 'Страна',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'ntdCol',
            'attributes' => [
                'id' => 'ntdCol',
                'min' => 0,
            ],
            'options' => [
                'label' => 'НТД',
            ],
        ]);

        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'comment_submitbutton',
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
        
        $inputFilter->add([
                'name'     => 'description',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                    ['name' => 'StripNewlines'],
                ],                
                'validators' => [
                ],
            ]);        
    }    
}
