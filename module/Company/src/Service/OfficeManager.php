<?php
namespace Company\Service;

use Company\Entity\Office;
use Company\Entity\Region;

/**
 * This service is responsible for adding/editing roles.
 */
class OfficeManager
{
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
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
     * Adds a new office.
     * @param array $data
     */
    public function addOffice($data)
    {
        $existingOffice = $this->entityManager->getRepository(Office::class)
                ->findOneByName($data['name']);
        if ($existingOffice!=null) {
            throw new \Exception('Офис с таким наименованием уже есть');
        }
        
        $region = $this->entityManager->getRepository(Region::class)
                    ->findOneById($data['region']);
        if ($region == null){
            throw new \Exception('Не указан регион');
        }

        $office = new Office();
        $office->setName($data['name']);
        $office->setFullName($data['name']);
        $office->setAplId($data['aplId']);
        $office->setStatus($data['status']);
        
        $currentDate = date('Y-m-d H:i:s');        
        $office->setDateCreated($currentDate);
        
        $office->setRegion($region);
        
        $this->entityManager->persist($office);
        
        // Apply changes to database.
        $this->entityManager->flush();
        
        return $office;
    }
    
    /**
     * Updates an existing office.
     * @param Office $office
     * @param array $data
     */
    public function updateOffice($office, $data)
    {
        $existingOffice = $this->entityManager->getRepository(Office::class)
                ->findOneByName($data['name']);
        
        if ($existingOffice!=null && $existingOffice!=$office) {
            throw new \Exception('Другой офис с таким наименованием уже есть');
        }
        
        $region = $this->entityManager->getRepository(Region::class)
                    ->findOneById($data['region']);
        
        if ($region == null){
            throw new \Exception('Не указан регион');
        }
        
        $office->setName($data['name']);
        $office->setFullName($data['name']);
        $office->setAplId($data['aplId']);
        $office->setStatus($data['status']);
        
        $office->setRegion($region);
        
        $this->entityManager->persist($office);
        $this->entityManager->flush();
        
    }
    
    /**
     * Удалить офис
     * 
     * @param Office $office
     */
    public function deleteOffice($office)
    {
        foreach ($office->getContacts() as $contact){
            $this->entityManager->remove($contact);
        }
        foreach ($office->getContracts() as $contract){
            $this->entityManager->remove($contract);
        }
        foreach ($office->getRates() as $rate){
            $this->entityManager->remove($rate);
        }
        $this->entityManager->remove($office);
        $this->entityManager->flush();
        
    }    
}

