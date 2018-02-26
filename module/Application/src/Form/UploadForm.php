<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\FileInput;
/**
 * Description of UploadPrice
 *
 * @author Daddy
 */
class UploadForm extends Form
{

    /**
     * Папка куда положить файл.
     * @var string 
     */
    private $target;
    
    /**
     * Конструктор.     
     */
    public function __construct($target, $options = [])
    {
        // Определяем имя формы.
        parent::__construct('upload-form');
     
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->target = $target;

        $this->addElements();
        $this->addInputFilter();         
    }
    
    /**
    * Этот метод добавляет элементы к форме (поля ввода и кнопку отправки формы).
    */
    protected function addElements() 
    {
                
        // Добавляем поле "file"
        $this->add([           
            'type'  => 'file',
            'name' => 'name',
            'attributes' => [
                'id' => 'file'
            ],
            'options' => [
                'label' => 'Путь к загружаемому файлу',
            ],
        ]);
                
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Загрузить',
                'id' => 'upload-submit-button',
            ],
        ]);        
    }
    
   /**
     * Этот метод создает фильтр входных данных (используется для фильтрации/валидации).
     */
    private function addInputFilter() 
    {
        
        $inputFilter = new InputFilter();  
        
        $fileInput = new FileInput('name');
        $fileInput->setRequired(true);
        $fileInput->getFilterChain()->attachByName(
            'filerenameupload',
            [
                'use_upload_name' => true,
                'use_upload_extension' => true,
                'target'    => $this->target,
                'randomize' => true,
            ]
        );
        $inputFilter->add($fileInput);
        
        $this->setInputFilter($inputFilter);        
    }    
}
