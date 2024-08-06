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
 * Description of Zetasoft
 *
 * @author Daddy
 */
class ApiMarketPlaces extends Form 
{
    
    protected $objectManager;    
    
    /**
     * Конструктор.     
     */
    public function __construct()
    {
        // Определяем имя формы.
        parent::__construct('api-market-places');
             
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();         
    }

    protected function addElements() 
    {
        $this->add([            
            'type'  => 'select',
            'name' => 'market_unload',
            'options' => [
                'label' => 'Прайсы для ТП',
                'value_options' => [
                    1 => 'Выгружать',
                    2 => 'Не выгружать',                    
                ]
            ],
        ]);
                
        $this->add([            
            'type'  => 'text',
            'name' => 'ozon_api_key',
            'options' => [
                'label' => 'ОЗОН АПИ ключ',
            ],
        ]);

        $this->add([            
            'type'  => 'text',
            'name' => 'ozon_client_id',
            'options' => [
                'label' => 'ОЗОН ид клиента',
            ],
        ]);

        $this->add([            
            'type'  => 'select',
            'name' => 'get_report',
            'options' => [
                'label' => 'Загрузка отчетов',
                'value_options' => [
                    1 => 'Делать',
                    2 => 'Не делать',                    
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
                'name'     => 'get_report',
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
