<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Zp\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Zp\Entity\PersonalRevise;

/**
 * Description of PersonalMutual
 *
 * @author Daddy
 */
class PersonalReviseForm extends Form
{
    
    /**
     * Конструктор.     
     */
    public function __construct()
    {
                
        // Определяем имя формы.
        parent::__construct('personal-revise-form');
     
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
            'name' => 'docNum',
            'attributes' => [
                'id' => 'docNum',
            ],
            'options' => [
                'label' => 'Номер документа',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'comment',
            'attributes' => [
                'id' => 'comment',
            ],
            'options' => [
                'label' => 'Комментарий',
            ],
        ]);

        $this->add([           
            'type'  => 'date',
            'name' => 'docDate',
            'attributes' => [
                'id' => 'docDate',
                'value' => date('Y-m-d'),
            ],
            'options' => [
                'label' => 'Дата документа',
            ],
        ]);

        $this->add([           
            'type'  => 'text',
            'name' => 'amount',
            'attributes' => [
                'id' => 'amount',
            ],
            'options' => [
                'label' => 'Сумма',
            ],
        ]);

        $this->add([           
            'type'  => 'select',
            'name' => 'status',
            'attributes' => [
                'id' => 'status',
            ],
            'options' => [
                'label' => 'Статус',
                'value_options' => PersonalRevise::getStatusList(),
            ],
        ]);
        
        $this->add([           
            'type'  => 'select',
            'name' => 'kind',
            'attributes' => [
                'id' => 'kind',
            ],
            'options' => [
                'label' => 'Операция',
                'value_options' => PersonalRevise::getKindList(),
            ],
        ]);
        
        $this->add([           
            'type'  => 'select',
            'name' => 'company',
            'attributes' => [
                'id' => 'companySelectForm'
            ],
            'options' => [
                'label' => 'Компания',
            ],
        ]);
        
        $this->add([           
            'type'  => 'select',
            'name' => 'user',
            'attributes' => [
                'id' => 'userSelectForm'
            ],
            'options' => [
                'label' => 'Сотрудник',
            ],
        ]);
        
        $this->add([           
            'type'  => 'date',
            'name' => 'vacationFrom',
            'attributes' => [
                'id' => 'vacationFrom',
                'value' => date('Y-m-d'),
            ],
            'options' => [
                'label' => 'Отпуск с',
            ],
        ]);

        $this->add([           
            'type'  => 'date',
            'name' => 'vacationTo',
            'attributes' => [
                'id' => 'vacationTo',
                'value' => date('Y-m-d'),
            ],
            'options' => [
                'label' => 'Отпуск по',
            ],
        ]);

        $this->add([           
            'type'  => 'date',
            'name' => 'vacationTo',
            'attributes' => [
                'id' => 'vacationTo',
                'value' => date('Y-m-d'),
            ],
            'options' => [
                'label' => 'Отпуск по',
            ],
        ]);

        $this->add([           
            'type'  => 'number',
            'name' => 'vacationPeriod',
            'attributes' => [
                'id' => 'vacationPeriod',
                'value' => 0,
                'min' => 0,
            ],
            'options' => [
                'label' => 'Отпуск дней',
            ],
        ]);

        $this->add([           
            'type'  => 'hidden',
            'name' => 'info',
            'attributes' => [
                'id' => 'info',
            ],
        ]);

        
//        $this->add([           
//            'type'  => 'select',
//            'name' => 'accrual',
//            'attributes' => [
//                'id' => 'accrualSelectForm'
//            ],
//            'options' => [
//                'label' => 'Вид начисления',
//            ],
//        ]);
//        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'personal_submitbutton',
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
                'name'     => 'docNum',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                ],
            ]);        

        $inputFilter->add([
                'name'     => 'comment',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                ],
            ]);        

        $inputFilter->add([
                'name'     => 'vacationFrom',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                ],
            ]);        

        $inputFilter->add([
                'name'     => 'vacationTo',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                ],
            ]);        

        $inputFilter->add([
                'name'     => 'vacationPeriod',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                ],
            ]);        

        $inputFilter->add([
                'name'     => 'info',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                ],
            ]);        

    }    
}
