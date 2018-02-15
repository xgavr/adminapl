<?php
namespace Company\Service;

use Company\Entity\Region;

/**
 * This service is responsible for adding/editing roles.
 */
class RegionManager
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
     * Adds a new region.
     * @param array $data
     */
    public function addRegion($data)
    {
        $existingRegion = $this->entityManager->getRepository(Region::class)
                ->findOneByName($data['name']);
        if ($existingRegion!=null) {
            throw new \Exception('Регион с таким наименованием уже есть');
        }
        
        $region = new Region;
        $region->setName($data['name']);
        $region->setFullName($data['fullName']);
        
        $this->entityManager->persist($region);
        
        // Apply changes to database.
        $this->entityManager->flush();
        
    }
    
    /**
     * Updates an existing region.
     * @param Region $region
     * @param array $data
     */
    public function updateRegion($region, $data)
    {
        $existingRegion = $this->entityManager->getRepository(Region::class)
                ->findOneByName($data['name']);
        if ($existingRegion!=null && $existingRegion!=$region) {
            throw new \Exception('Другой регион с таким наименованием уже есть');
        }
        
        $region->setName($data['name']);
        $region->setFullName($data['fullName']);
        
        $this->entityManager->flush();
        
    }
    
    /**
     * Deletes the given region.
     */
    public function deleteRegion($region)
    {
        $this->entityManager->remove($region);
        $this->entityManager->flush();
        
    }    
}

