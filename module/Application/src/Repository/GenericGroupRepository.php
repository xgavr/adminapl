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
    
    /**
     * Запрос по группам по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllGroup($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('g')
            ->from(GenericGroup::class, 'g')
                ;
        
        if (is_array($params)){
            if (isset($params['q'])){
                $queryBuilder->where('g.name like :search')
                    ->setParameter('search', '%' . $params['q'] . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->where('g.name > ?1')
                    ->setParameter('1', $params['next1'])
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('g.name < ?2')
                    ->setParameter('2', $params['prev1'])
                    ->orderBy('g.name', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['status'])){
                $queryBuilder->andWhere('g.status = ?3')
                    ->setParameter('3', $params['status'])
                        ;
            }            
            if (isset($params['sort'])){
                $queryBuilder->orderBy('g.'.$params['sort'], $params['order']);                
            }            
        }

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
     * Обновление количества товаров в группах
     */
    public function updateGoodCount()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('gg.id, count(g.id) as gCount')
            ->from(GenericGroup::class, 'gg')
            ->leftJoin('gg.goods', 'g')    
            ->groupBy('gg.id')                
                ;
        
        $data = $queryBuilder->getQuery()->getResult();

        foreach ($data as $row){
            if ($row['gCount']){
                $status = GenericGroup::STATUS_ACTIVE;
            } else {
                $status = GenericGroup::STATUS_RETIRED;
            }
            $this->getEntityManager()->getConnection()->update('generic_group', ['good_count' => $row['gCount'], 'status' => $status], ['id' => $row['id']]);            
        }        
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
    
    /**
     * Поиск группы по группе наименований
     * 
     * @param \Application\Entity\TokenGroup $tokenGroup
     */
    public function findGenericTokenGroup($tokenGroup)
    {
        if ($tokenGroup){
            $entityManager = $this->getEntityManager();

            $queryBuilder = $entityManager->createQueryBuilder();

            $queryBuilder->select('gg')
                    ->distinct()
                    ->from(\Application\Entity\Goods::class, 'g')
                    ->join(GenericGroup::class, 'gg', 'WITH', 'gg.id = g.genericGroup')
                    ->where('g.tokenGroup = ?1')
                    ->andWhere('gg.tdId != 0')
                    ->setParameter('1', $tokenGroup->getId())
                    ;

            $data = $queryBuilder->getQuery()->getResult();

            if (count($data) == 1){
                foreach ($data as $row){
                    return $row;
                }
            }
        }    
        return;        
    }
}
