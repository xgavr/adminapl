<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zp\Service;

use Zp\Entity\Accrual;
use Company\Entity\Legal;
use Zp\Entity\Position;

/**
 * Description of ZpManager
 * 
 * @author Daddy
 */
class ZpManager {
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    private $adminManager;

    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
    }
    
    /**
     * Add Accrual
     * @param array $data
     * @return Accrual
     */
    public function addAccrual($data)
    {
        $accrual = new Accrual();
        $accrual->setAplId($data['aplId']);
        $accrual->setBasis($data['basis']);
        $accrual->setComment(empty($data['comment']) ? null:$data['comment']);
        $accrual->setName($data['name']);
        $accrual->setStatus(empty($data['status']) ? Accrual::STATUS_ACTIVE:$data['status']);
        
        $this->entityManager->persist($accrual);
        $this->entityManager->flush();
        
        return $accrual;
    }
    
    /**
     * Update Accrual
     * @param Accrual $accrual
     * @param array $data
     * @return Accrual
     */
    public function updateAccrual($accrual, $data)
    {
        $accrual->setAplId($data['aplId']);
        $accrual->setBasis($data['basis']);
        $accrual->setComment(empty($data['comment']) ? null:$data['comment']);
        $accrual->setName($data['name']);
        $accrual->setStatus(empty($data['status']) ? Accrual::STATUS_ACTIVE:$data['status']);
        
        $this->entityManager->persist($accrual);
        $this->entityManager->flush();
        
        return $accrual;
    }
    
    /**
     * Remove accrual
     * @param Accrual $accrual
     */
    public function removeAccrual($accrual)
    {
        $this->entityManager->remove($accrual);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Add position
     * @param array $data
     * @return Accrual
     */
    public function addPosition($data)
    {
        $position = new Position();
        $position->setAplId($data['aplId']);
        $position->setComment(empty($data['comment']) ? null:$data['comment']);
        $position->setName($data['name']);
        $position->setStatus(empty($data['status']) ? Position::STATUS_ACTIVE:$data['status']);
        $position->setParentPosition(empty($data['parentPosition']) ? null:$data['parentPosition']);
        
        $this->entityManager->persist($position);
        $this->entityManager->flush();
        
        return $accrual;
    }
    
    /**
     * Update Position
     * @param Position $position
     * @param array $data
     * @return Position
     */
    public function updatePosition($position, $data)
    {
        $position->setAplId($data['aplId']);
        $position->setComment(empty($data['comment']) ? null:$data['comment']);
        $position->setName($data['name']);
        $position->setStatus(empty($data['status']) ? Position::STATUS_ACTIVE:$data['status']);
        $position->setParentPosition(empty($data['parentPosition']) ? null:$data['parentPosition']);
        
        $this->entityManager->persist($position);
        $this->entityManager->flush();
        
        return $position;
    }
    
    /**
     * Remove osition
     * @param Position $position
     */
    public function removePosition($position)
    {
        $this->entityManager->remove($position);
        $this->entityManager->flush();
        
        return;
    }
    
}
