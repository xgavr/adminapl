<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Make;
use Application\Entity\Model;

/**
 * Description of MakeService
 *
 * @author Daddy
 */
class MakeManager
{
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    /**
     * External manager.
     * @var \Application\Entity\ExternalManager
     */
    private $externalManager;
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $externalManager)
    {
        $this->entityManager = $entityManager;
        $this->externalManager = $externalManager;
    }
        
    /**
     * Заполнить машины
     * 
     * @return null
     */
    public function fillMakes()
    {
        $this->externalManager->fillMakes();
        return;
    }

    
    /**
     * Заполнить модели машины
     * 
     * @param Application\Entity\Make $make 
     * @return null
     */
    public function fillModels($make)
    {
        $this->externalManager->fillModels($make);
        return;
    }
    
    /**
     * Заполнить модели у всех производителей
     */
    public function fillAllModels()
    {
        set_time_limit(1800);
        
        $makes = $this->entityManager->getRepository(Make::class)
                ->findBy([]);
        foreach ($makes as $make){
            $this->fillModels($make);
        }
        
        return;
    }
    
    /**
     * Обновить полное наименование
     * 
     * @param Make $make
     * @param string $fullName
     */
    public function updateFullName($make, $fullName)
    {
        $make->setFullName($fullName);
        $this->entityManager->persist($make);
        $this->entityManager->flush($make);
        
        return;
    }
    
    /**
     * Обновить полное наименование модели
     * 
     * @param Model $model
     * @param string $fullName
     */
    public function updateModelFullName($model, $fullName)
    {
        $model->setFullName($fullName);
        $this->entityManager->persist($model);
        $this->entityManager->flush($model);
        
        return;
    }
    
}
