<?php
namespace Company\Service;

use Company\Entity\Tax;

/**
 * This service is responsible for adding/editing roles.
 */
class TaxManager
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
     * Adds a new tax.
     * @param array $data
     * @return Tax
     */
    public function addTax($data)
    {

        $tax = new Tax();
        $tax->setAmount($data['amount']);
        $tax->setDateStart($data['dateStart']);
        $tax->setKind($data['kind']);
        $tax->setStatus($data['status']);
        $tax->setName($data['name']);
        
        $this->entityManager->persist($tax);
        
        // Apply changes to database.
        $this->entityManager->flush();
        
        return $tax;
    }
    
    /**
     * Updates an existing tax.
     * @param Tax $tax
     * @param array $data
     */
    public function updateTax($tax, $data)
    {
        
        $tax->setAmount($data['amount']);
        $tax->setDateStart($data['dateStart']);
        $tax->setKind($data['kind']);
        $tax->setStatus($data['status']);
        $tax->setName($data['name']);
        
        $this->entityManager->persist($tax);
        $this->entityManager->flush();
        
    }
    
    /**
     * Deletes the given tax.
     */
    public function deleteTax($tax)
    {
        $this->entityManager->remove($tax);
        $this->entityManager->flush();
        
    }    
}

