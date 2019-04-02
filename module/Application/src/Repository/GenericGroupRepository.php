<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\GenericGroup;
/**
 * Description of GenericGroupRepository
 *
 * @author Daddy
 */
class GenericGroupRepository extends EntityRepository{

    public function findAllGenericGroup()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('g')
            ->from(GenericGroup::class, 'g')
            ->orderBy('g.id')
                ;

        return $queryBuilder->getQuery();
    }

    public function updateZeroGroup()
    {
        $zeroGroup = $this->getEntityManager()->getRepository(GenericGroup::class)
                ->findOneByTdId(0);
        
        $this->getEntityManager()->getConnection()->update('goods', ['generic_group_id' => $zeroGroup->getId()], ['generic_group_id' => 0]);
        
        return;
    }

    /**
     * Добавить группу товаров
     * 
     * @param array $data
     */
    public function addGenericGroup($data)
    {
       $genericGroup = $this->getEntityManager()->getRepository(GenericGroup::class)
               ->findOneByTdId($data['td_id']);
       
       if ($genericGroup == null){
           $this->getEntityManager()->getConnection()->insert('generic_group', $data);
       }
       
       return;
    }
}
