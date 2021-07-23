<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Application\Entity\Ring;

/**
 * Description of RingManager
 *
 * @author Daddy
 */
class RingManager
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
     * Добавить новый звонок
     * 
     * @param array $data
     * @return Ring
     */
    public function addRing($data)
    {
        $ring = new Ring();
        
        $contactId = empty($data['contact']) ? null:$data['contact'];
        $contactCarId = empty($data['contactCar']) ? null:$data['contactCar'];
        
        $ring->setContact($contactId);
        $ring->setContactCar($contactCarId);
        $ring->setDateCreated(date('Y-m-d H:i:s'));
        $ring->setGds(empty($data['gds']) ? null:$data['gds']);
        $ring->setInfo(empty($data['info']) ? null:$data['info']);
        $ring->setManager(empty($data['manager']) ? null:$data['manager']);
        $ring->setMode($data['mode']);
        $ring->setName(empty($data['name']) ? null:$data['name']);
        $ring->setOffice(empty($data['office']) ? null:$data['office']);
        $ring->setOrder(empty($data['order']) ? null:$data['order']);
        $ring->setPhone(empty($data['phone']) ? null:$data['phone']);
        $ring->setStatus(empty($data['status']) ? Ring::STATUS_ACTIVE:$data['status']);
        $ring->setUser(empty($data['order']) ? null:$data['order']);
        $ring->setVin(empty($data['vin']) ? null:$data['vin']);
        
        
        
        return $ring;
    }
}
