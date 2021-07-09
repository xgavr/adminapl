<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Application\Entity\ContactCar;
use Application\Entity\Ring;

/**
 * Description of RingForm
 *
 * @author Daddy
 */
class RingForm extends Form
{
    
    
    /**
     * Конструктор.     
     */
    public function __construct()
    {
        // Определяем имя формы.
        parent::__construct('ring-form');
     
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
                
        // Add "status" field
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'options' => [
                'label' => 'Статус',
                'value_options' => Ring::getStatusList(),
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'mode',
            'options' => [
                'label' => 'О чем звонят',
                'value_options' => Ring::getModeList(),
            ],
        ]);
        
        $this->add([           
            'type'  => 'text',
            'name' => 'name',
            'attributes' => [
                'id' => 'name'
            ],
            'options' => [
                'label' => 'Как к Вам обращатся',
            ],
        ]);
                
        $this->add([           
            'type'  => 'text',
            'name' => 'phone',
            'attributes' => [
                'id' => 'phone'
            ],
            'options' => [
                'label' => 'Телефон',
            ],
        ]);
                
        $this->add([           
            'type'  => 'text',
            'name' => 'phone2',
            'attributes' => [
                'id' => 'phone2'
            ],
            'options' => [
                'label' => 'Номер телефона для поска контакта',
            ],
        ]);
                
        $this->add([           
            'type'  => 'text',
            'name' => 'vin',
            'attributes' => [
                'id' => 'vin'
            ],
            'options' => [
                'label' => 'VIN',
            ],
        ]);
                
        $this->add([           
            'type'  => 'text',
            'name' => 'make',
            'attributes' => [
                'id' => 'make'
            ],
            'options' => [
                'label' => 'Машина',
            ],
        ]);
                
        
        
        $this->add([           
            'type'  => 'text',
            'name' => 'gds',
            'attributes' => [
                'id' => 'gds'
            ],
            'options' => [
                'label' => 'Артикул',
            ],
        ]);
                
        $this->add([           
            'type'  => 'text',
            'name' => 'info',
            'attributes' => [
                'id' => 'info'
            ],
            'options' => [
                'label' => 'Запрос',
            ],
        ]);                        
        
        $this->add([           
            'type'  => 'text',
            'name' => 'order',
            'attributes' => [
                'id' => 'order'
            ],
            'options' => [
                'label' => '№ заказа',
            ],
        ]);                        
        
        $this->add([           
            'type'  => 'text',
            'name' => 'manager',
            'attributes' => [
                'id' => 'manager'
            ],
            'options' => [
                'label' => 'Менеджер',
            ],
        ]);                        
        

        // Добавляем поле "comment"
        $this->add([           
            'type'  => 'text',
            'name' => 'comment',
            'attributes' => [
                'id' => 'comment'
            ],
            'options' => [
                'label' => 'Описание',
            ],
        ]);
                
        // Добавляем поле "yocm"
        $this->add([           
            'type'  => 'number',
            'name' => 'yocm',
            'attributes' => [
                'id' => 'yocm'
            ],
            'options' => [
                'label' => 'Год',
            ],
            'attributes' => [
                'min' => '0',
                'max' => date('Y'),
//                'step' => '1', // default step interval is 1
            ],
        ]);
                
        // Add "wheel" field
        $this->add([            
            'type'  => 'select',
            'name' => 'wheel',
            'attributes' => [
                'value' => ContactCar::WHEEL_LEFT,
            ],
            'options' => [
                'label' => 'Руль',
                'value_options' => ContactCar::getWheelList(),
            ],
        ]);
        
        // Add "tm" field
        $this->add([            
            'type'  => 'select',
            'name' => 'tm',
            'attributes' => [
                'value' => ContactCar::TM_UNKNOWN,
            ],
            'options' => [
                'label' => 'Коробка',
                'value_options' => ContactCar::getTmList(),
            ],
        ]);
        
        // Add "ac" field
        $this->add([            
            'type'  => 'select',
            'name' => 'ac',
            'attributes' => [
                'value' => ContactCar::AC_UNKNOWN,
            ],
            'options' => [
                'label' => 'Кондиционер',
                'value_options' => ContactCar::getAcList(),
            ],
        ]);
        
        // Добавляем поле "md"
        $this->add([           
            'type'  => 'text',
            'name' => 'md',
            'attributes' => [
                'id' => 'md'
            ],
            'options' => [
                'label' => 'Двигатель',
            ],
        ]);
                
        // Добавляем поле "ed"
        $this->add([           
            'type'  => 'text',
            'name' => 'ed',
            'attributes' => [
                'id' => 'ed'
            ],
            'options' => [
                'label' => 'Объем',
            ],
        ]);

        // Добавляем поле "ep"
        $this->add([           
            'type'  => 'text',
            'name' => 'ep',
            'attributes' => [
                'id' => 'ep'
            ],
            'options' => [
                'label' => 'Мощность',
            ],
        ]);
                
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'contact_car_submitbutton',
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
}
