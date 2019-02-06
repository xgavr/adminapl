<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Make;

/**
 * Description of MakeService
 *
 * @author Daddy
 */
class MakeManager
{
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    /**
     * External manager.
     * @var Application\Entity\ExternalManager
     */
    private $externalManager;
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $externalManager)
    {
        $this->entityManager = $entityManager;
        $this->externalManager = $externalManager;
    }
    
    public function addMake($data)
    {
        $make = $this->entityManager->getRepository(Make::class)
                ->findOneBy(['tdId' => $data['tdId']]);

        if ($make == null){
            $make = new Make();
            $make->setAplId($data['aplId']);
            $make->setTdId($data['tdId']);
            $make->setFullName($data['fullName']);
            $make->setName($data['name']);
            $make->setUpdateStatus(Make::STATUS_NEED_UPDATE);

            $this->entityManager->persist($make);
            $this->entityManager-flush();
        }
        
        return $make;        
    }
    
    public function fillMakes()
    {
        $data = $this->externalManager->partsApi('makes');
        foreach ($data as $row){
            $make = $this->addMake([
                'tdId' => $row['id'],
                'aplId' => 0,
                'name' => $row['name'],
                'fullName' => '',
            ]);
            var_dump($make); exit;
        }
        
        return;
    }
}
