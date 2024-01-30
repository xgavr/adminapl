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
use Zp\Entity\Personal;
use Zp\Entity\PersonalAccrual;
use User\Entity\User;

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
        $accrual->setKind($data['kind']);
        $accrual->setPayment($data['payment']);
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
        $accrual->setKind($data['kind']);
        $accrual->setPayment($data['payment']);
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
        $position->setCompany($data['company']);
        $position->setComment(empty($data['comment']) ? null:$data['comment']);
        $position->setName($data['name']);
        $position->setStatus(empty($data['status']) ? Position::STATUS_ACTIVE:$data['status']);
        $position->setParentPosition(empty($data['parentPosition']) ? null:$data['parentPosition']);
        $position->setNum(empty($data['num']) ? 0:$data['num']);
        
        $parentPosition = $data['parentPosition'];

        $maxSort = $this->entityManager->getRepository(Position::class)
                ->findMaxSortPosition(['company' => $data['company']->getId(), 'parentPosition' => empty($parentPosition) ? null:$parentPosition->getId()]);
        
        list($parentSort, $sort) = explode('_', $maxSort);
        if (empty($sort)){
            $sort = 0;
        }
        if (empty($parentSort)){
            $parentSort = 0;
        }
        if (empty($parentPosition)){
            $position->setSort($parentSort + 1000);
        } else {
            $position->setSort($parentPosition->getSort().'_'.($sort + 1000));
        }   
        
        $this->entityManager->persist($position);
        $this->entityManager->flush();
        
        $this->entityManager->getRepository(Position::class)
                ->updateParentPositionNum($position);
        
        return $position;
    }
    
    /**
     * Update Position
     * @param Position $position
     * @param array $data
     * @return Position
     */
    public function updatePosition($position, $data)
    {
        $position->setStatus(Position::STATUS_RETIRED);
        $this->entityManager->persist($position);
        $this->entityManager->flush();
        
        $this->entityManager->getRepository(Position::class)
                ->updateParentPositionNum($position);
        
        $position->setAplId($data['aplId']);
        $position->setCompany($data['company']);
        $position->setComment(empty($data['comment']) ? null:$data['comment']);
        $position->setName($data['name']);
        $position->setStatus(empty($data['status']) ? Position::STATUS_ACTIVE:$data['status']);
        $position->setParentPosition(empty($data['parentPosition']) ? null:$data['parentPosition']);
        $position->setNum(empty($data['num']) ? 0:$data['num']);
        
        $parentPosition = $data['parentPosition'];
        
        $maxSort = $this->entityManager->getRepository(Position::class)
                ->findMaxSortPosition(['company' => $data['company']->getId(), 'parentPosition' => empty($parentPosition) ? null:$parentPosition->getId()]);
        list($parentSortMax, $sortMax) = explode('_', $maxSort);
        if (empty($sortMax)){
            $sortMax = 0;
        }
        if (empty($parentSortMax)){
            $parentSortMax = 0;
        }

        list($parentSort, $sort) = explode('_', $position->getSort());
        if (empty($sort)){
            $sort = 0;
        }
        if (empty($parentSort)){
            $parentSort = 0;
        }
                
        if (empty($parentPosition)){
            $position->setSort(empty($parentSort) ? ($parentSortMax+1000):$parentSort);
        } else {
            $position->setSort($parentPosition->getSort().'_'.(empty($sort) ? ($sortMax + 1000):$sort));
        }   

        
        $this->entityManager->persist($position);
        $this->entityManager->flush();
        
        $this->entityManager->getRepository(Position::class)
                ->updateParentPositionNum($position);
        
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
    
    /**
     * Добавить плановое начисление
     * @param array $data
     * @return Personal
     */
    public function addPersonal($data)
    {
        $personal = new Personal();
        $personal->setAplId(empty($data['aplId']) ? null:$data['aplId']);
        $personal->setComment(empty($data['comment']) ? null:$data['comment']);
        $personal->setCompany($data['company']);
        $personal->setUser($data['user']);
        $personal->setPosition($data['position']);
        $personal->setPositionNum($data['positionNum']);
        $personal->setDateCreated(date('Y-m-d'));
        $personal->setDocDate($data['docDate']);
        $personal->setStatus($data['status']);
        
        $this->entityManager->persist($personal);
        
        $rowNo = 1;
        if (!empty($data['accruals'])){
            foreach ($data['accruals'] as $accrualData){
                $accrualData['accrual'] = $this->entityManager->getRepository(Accrual::class)
                        ->find($accrualData['accrual']);

                $personalAccrual = new PersonalAccrual();
                $personalAccrual->setAccrual($accrualData['accrual']);
                $personalAccrual->setCompany($data['company']);
                $personalAccrual->setDateOper($data['docDate']);
                $personalAccrual->setPersonal($personal);
                $personalAccrual->setRate($accrualData['rate']);
                $personalAccrual->setRowNo($rowNo);
                $personalAccrual->setStatus($accrualData['status']);
                $personalAccrual->setUser($data['user']);

                $this->entityManager->persist($personalAccrual);
                
                $rowNo++;
            }
        }
        
        $this->entityManager->flush();
        
        return $personal;
    }
    
    /**
     * Удалить строки планового начисления
     * @param Personal $personal
     * @return type
     */
    public function removePersonalAccurals($personal)
    {
        foreach ($personal->getPersonalAccruals() as $personalAccrual){
            $this->entityManager->remove($personalAccrual);
        }
        
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Обновить плановое начисление
     * @param Personal $personal
     * @param array $data
     * @return Personal
     */
    public function updatePersonal($personal, $data)
    {
        $this->removePersonalAccurals($personal);
        
        $personal->setAplId(empty($data['aplId']) ? null:$data['aplId']);
        $personal->setComment(empty($data['comment']) ? null:$data['comment']);
        $personal->setCompany($data['company']);
        $personal->setUser($data['user']);
        $personal->setPosition($data['position']);
        $personal->setPositionNum($data['positionNum']);
        $personal->setDocDate($data['docDate']);
        $personal->setStatus($data['status']);
        
        $this->entityManager->persist($personal);
        
        $rowNo = 1;
        if (!empty($data['accruals'])){
            foreach ($data['accruals'] as $accrualData){
                $accrualData['accrual'] = $this->entityManager->getRepository(Accrual::class)
                        ->find($accrualData['accrual']);

                $personalAccrual = new PersonalAccrual();
                $personalAccrual->setAccrual($accrualData['accrual']);
                $personalAccrual->setCompany($data['company']);
                $personalAccrual->setDateOper($data['docDate']);
                $personalAccrual->setPersonal($personal);
                $personalAccrual->setRate($accrualData['rate']);
                $personalAccrual->setRowNo($rowNo);
                $personalAccrual->setStatus($accrualData['status']);
                $personalAccrual->setUser($data['user']);

                $this->entityManager->persist($personalAccrual);
                
                $rowNo++;
            }
        }
        
        $this->entityManager->flush();
        
        return $personal;
    }
    
    /**
     * Удалить плановое начисление
     * @param Personal $personal
     */
    public function removePersonal($personal) 
    {
        $this->removePersonalAccurals($personal);
        $this->entityManager->remove($personal);
        
        $this->entityManager->flush();
        
        return;
    }
}
