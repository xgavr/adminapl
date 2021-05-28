<?php
namespace Company\Service;

use Company\Entity\Cost;

/**
 * This service is responsible for adding/editing roles.
 */
class CostManager
{
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;  
        
    /**
     * Constructs the service.
     */
    public function __construct($entityManager) 
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Adds a new cost.
     * @param array $data
     */
    public function addCost($data)
    {
        $existingCost = $this->entityManager->getRepository(Cost::class)
                ->findOneByName($data['name']);
        if ($existingCost!=null) {
            throw new \Exception('Статья с таким наименованием уже есть');
        }
        
        $cost = new Cost();
        $cost->setName($data['name']);
        $cost->setAplId($data['aplId']);
        $cost->setStatus($data['status']);
        
        $this->entityManager->persist($cost);
        
        // Apply changes to database.
        $this->entityManager->flush();
        
    }
    
    /**
     * Updates an existing cost.
     * @param Cost $cost
     * @param array $data
     */
    public function updateCost($cost, $data)
    {
        $existingCost = $this->entityManager->getRepository(Cost::class)
                ->findOneByName($data['name']);
        if ($existingCost!=null && $existingCost!=$cost) {
            throw new \Exception('Другая статья с таким наименованием уже есть');
        }
        
        $cost->setName($data['name']);
        $cost->setAplId($data['aplId']);
        $cost->setStatus($data['status']);
        
        $this->entityManager->persist($cost);
        $this->entityManager->flush();
        
    }
    
    /**
     * Deletes the given cost.
     * @param Cost $cost
     */
    public function deleteCost($cost)
    {
        $this->entityManager->remove($cost);
        $this->entityManager->flush();
        
    }    
}

