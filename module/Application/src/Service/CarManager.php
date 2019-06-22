<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Application\Entity\CarAttributeGroup;
use Application\Entity\CarAttributeType;
use Application\Entity\CarAttributeValue;
use Application\Entity\Car;

/**
 * Description of CarService
 *
 * @author Daddy
 */
class CarManager
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
     * @param Application\Entity\Model $model 
     * @return null
     */
    public function fillCars($model)
    {
        $this->externalManager->fillCars($model);
        return;
    }
    
    /**
     * Обновить атрибут
     * 
     * @param \Application\Entity\CarAttributeType $attribute
     * @param array $data
     */
    public function updateAttributeType($attribute, $data)
    {
        if (is_array($data)){
            if (!empty($data['value'])){
                $attribute->setNameApl($data['value']);
                $this->entityManager->persist($attribute);
                $this->entityManager->flush();
            }
        }
        return;
    }
}
