<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Laminas\Filter\AbstractFilter;
use Application\Entity\Order;

/**
 * 
 * Получить заказ
 *
 * @author Daddy
 */
class OrderFromIdZ extends AbstractFilter
{

    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    // Доступные опции фильтра.
    protected $options = [
    ];    

    // Конструктор.
    public function __construct($entityManager, $options = null) 
    {     
        $this->entityManager = $entityManager;
        
        // Задает опции фильтра (если они предоставлены).
        if(is_array($options)) {
            $this->options = $options;
        }    
    }
    
    public function filter($value)
    {
        $order = null;
        $pos = strpos($value, 'Z');
        if ($pos === false){        
            if ($value > 0) {
                $order = $this->entityManager->getRepository(Order::class)
                        ->findOneByAplId($value);
            }
        } else {
            $orderId = preg_replace("/[^0-9]/", '', $value);
            if ($orderId > 0) {
                $order = $this->entityManager->getRepository(Order::class)
                        ->find($value);
            }            
        }    
        
        return $order;
    }
    
}
