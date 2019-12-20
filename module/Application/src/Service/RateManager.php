<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Goods;

/**
 * Description of ShopService
 *
 * @author Daddy
 */
class RateManager
{
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }    
    
    /**
     * Обновить фиксированную цену
     * 
     * @param Goods $good
     * @param float $fixPrice
     * @return null
     */
    public function updateFixPrice($good, $fixPrice)
    {
        $good->setFixPrice($fixPrice);
        $this->entityManager->persist($good);
        $this->entityManager->flush($good);
        return;
    }
}
