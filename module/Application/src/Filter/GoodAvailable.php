<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Laminas\Filter\AbstractFilter;
use Application\Entity\Goods;
use Stock\Entity\GoodBalance;
use Application\Entity\Supplier;

/**
 * Наличие товара
 *
 * @author Daddy
 */
class GoodAvailable extends AbstractFilter
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
    
    /**
     * 
     * @param Good $good
     * @return integer
     */
    public function filter($good)
    {
        $rests = $this->entityManager->getRepository(GoodBalance::class)
                ->available($good);
        
        if (count($rests)){
            return Goods::AVAILABLE_APL;
        }

        $goodSuppliers = $this->entityManager->getRepository(Supplier::class)
                ->goodAvailable($good);
        
        if (count($goodSuppliers)){
            return Goods::AVAILABLE_TRUE;
        }
        
        return Goods::AVAILABLE_FALSE;
    }
    
}
