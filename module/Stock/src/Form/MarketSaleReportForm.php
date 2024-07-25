<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Stock\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ApiMarketPlace\Entity\MarketSaleReport;

/**
 * Description of ComitentForm
 *
 * @author Daddy
 */
class MarketSaleReportForm extends Form 
{
    
    
    protected $objectManager;

    protected $entityManager;
        
    /**
     * Конструктор.     
     */
    public function __construct($entityManager)
    {
        // Определяем имя формы.
        parent::__construct('market-sale-report-form');
     
        $this->entityManager = $entityManager;
        
        // Задает для этой формы метод POST.
        $this->setAttribute('method', 'post');
                
        $this->addElements();
        $this->addInputFilter();         
    }

    protected function addElements() 
    {
                

        $this->add([
            'type'  => 'date',
            'name' => 'docDate',
            'attributes' => [                
                'id' => 'docDate',
                'step' => 1,
                'required' => 'required',                
                'value' => date('Y-m-d'),
            ],
            'options' => [
                'label' => 'Дата документа',
                'format' => 'Y-m-d',
            ],
        ]);
        
        $this->add([
            'type'  => 'text',
            'name' => 'num',
            'attributes' => [                
                'id' => 'num',
                'required' => 'required',                
            ],
            'options' => [
                'label' => 'Номер документа',
            ],
        ]);
        
        $this->add([            
            'type'  => 'select',
            'name' => 'marketplace',
            'attributes' => [                
                'id' => 'marketplace'
            ],
            'options' => [
                'label' => 'Торговая площадка',
            ],
        ]);        
        
        $this->add([
            'type'  => 'text',
            'name' => 'comment',
            'attributes' => [                
                'id' => 'comment'
            ],
            'options' => [
                'label' => 'Комментарий',
            ],
       ]);
                
        $this->add([            
            'type'  => 'select',
            'name' => 'status',
            'value' => MarketSaleReport::STATUS_ACTIVE,
            'attributes' => [                
                'required' => 'required',                
            ],
            'options' => [
                'label' => 'Статус',
                'value_options' => MarketSaleReport::getStatusList(),
            ],
        ]);
        
//        $this->add([            
//            'type'  => 'select',
//            'name' => 'reportType',
//            'value' => MarketSaleReport::TYPE_COMPENSATION,
//            'attributes' => [                
//                'required' => 'required',                
//            ],
//            'options' => [
//                'label' => 'Тип отчета',
//                'value_options' => MarketSaleReport::getReportTypeList(),
//            ],
//        ]);
        
        // Добавляем кнопку отправки формы
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Сохранить',
                'id' => 'market_sale_report_submitbutton',
            ],
        ]);        

    }
    
    private function addInputFilter() 
    {
        
        $inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);

        $inputFilter->add([
                'name'     => 'marketplace',
                'required' => true,
                'filters'  => [
                ],                
                'validators' => [
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
