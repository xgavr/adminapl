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
 * Description of AiSettings
 *
 * @author Daddy
 */
class AiSettings extends Form implements ObjectManagerAwareInterface
{
    
    protected $objectManager;    
    
    /**
     * Конструктор.     
     */
    public function __construct()
    {
        // Определяем имя формы.
        parent::__construct('ai-sbp-settings');
             
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();         
    }

    protected function addElements() 
    {
        $this->add([            
            'type'  => 'text',
            'name' => 'gigachat_client_id',
            'options' => [
                'label' => 'Gigachat Client ID',
            ],
        ]);
                
        $this->add([            
            'type'  => 'text',
            'name' => 'Авторизационные данные',
            'options' => [
                'label' => 'Gigachat Client Secret',
            ],
        ]);

        $this->add([            
            'type'  => 'text',
            'name' => 'gigachat_score',
            'options' => [
                'label' => 'Gigachat Score',
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
