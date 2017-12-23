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
class ShopManager
{
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    public function searchGoodNameAssistant($search)
    {
        $result = [];    
        if (strlen($search) > 2){
            $names = $this->entityManager->getRepository(Goods::class)
                    ->searchNameForSearchAssistant($search);

            foreach ($names as $name){
                $result[] = $name->getName();
            }
        }
        
        return $result;
    }        
}
