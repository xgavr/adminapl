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
                    ->addOrderBy('g.name')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('g.name < ?2')
                    ->setParameter('2', $params['prev1'])
                    ->addOrderBy('g.name', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['status'])){
                $queryBuilder->andWhere('g.status = ?3')
                    ->setParameter('3', $params['status'])
                        ;
            }            
            if (isset($params['sort'])){
                $queryBuilder->addOrderBy('g.'.$params['sort'], $params['order']);                
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
     * @param \Application\Entity\Goods $good
     */
    public function genericTokenGroup($tokenGroup, $good = null)
    {
        if ($tokenGroup){
            $entityManager = $this->getEntityManager();

            $queryBuilder = $entityManager->createQueryBuilder();

            $queryBuilder->select('gg, count(g.id) as goodCount')
                    ->from(GenericGroup::class, 'gg')
                    ->join('gg.goods', 'g')
                    ->where('g.tokenGroup = ?1')
                    ->andWhere('gg.tdId != 0')
                    ->groupBy('gg.id')
                    ->orderBy('goodCount', 'DESC')
                    ->setParameter('1', $tokenGroup->getId())
                    ;
            if (isset($good)){
                $queryBuilder->andWhere('gg.id != ?2')
                        ->setParameter('2', $good->getId())
                        ;
            }

            return $queryBuilder->getQuery()->getResult();
        }    
        return;        
    }

    /**
     * Выбор группы по группе наименований
     * 
     * @param \Application\Entity\TokenGroup $tokenGroup
     */
    public function findGenericTokenGroup($tokenGroup)
    {
        $data = $this->genericTokenGroup($tokenGroup);

        if ($data){
            if (count($data) == 1){
                foreach ($data as $row){
                    return $row[0];
                }
            }
        }
        
        return;
    }
    
    /**
     * Получить группы апл соответствующую общей групе
     * 
     * @param GenericGroup $genericGroup
     */
    public function getGroupApl($genericGroup)
    {
        if (!$genericGroup->getTdId()){
            return [];
        }
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('g.groupApl, count(g.id) as goodCount')
                ->from(\Application\Entity\Goods::class, 'g')
                ->where('g.genericGroup = ?1')
                ->andWhere('g.groupApl != ?2')
                ->andWhere('g.groupApl != 0')
                ->setParameter('1', $genericGroup->getId())
                ->setParameter('2', \Application\Entity\Goods::DEFAULT_GROUP_APL_ID)
                ->groupBy('g.groupApl')
                ->orderBy('goodCount', 'DESC')
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * НЕИСПОЛЬЗУЕТСЯ
     * Быстрое обновление группы апл в товарах общей группы
     * 
     * @param GenericGroup $genericGroup
     * @return integer
     */
    public function updateGoodsGroupApl($genericGroup)
    {
        if ($genericGroup->getAplId()){
            $entityManager = $this->getEntityManager();

            $queryBuilder = $entityManager->createQueryBuilder();
            $queryBuilder->update(\Application\Entity\Goods::class, 'g')
                    ->where('g.genericGroup = ?1')
                    ->andWhere($queryBuilder->expr()->orX(
                            $queryBuilder->expr()->eq('g.groupApl', 0),
                            $queryBuilder->expr()->eq('g.groupApl', \Application\Entity\Goods::DEFAULT_GROUP_APL_ID)
                        )
                    )
                    ->andWhere('g.groupApl != ?2')
                    ->set('g.groupApl', $genericGroup->getAplId())
                    ->setParameter('1', $genericGroup->getId())
                    ->setParameter('2', $genericGroup->getAplId())
                    ;

            return $queryBuilder->getQuery()->getResult();        
        }
        
        return;
    }
    
    
    /**
     * Обновить группу апл
     * 
     * @param GenericGroup $genericGroup
     * @return type
     */
    public function updateGroupApl($genericGroup)
    {
        $aplGroups = $this->getGroupApl($genericGroup);
        if (count($aplGroups)){
            foreach($aplGroups as $row){
                if ($genericGroup->getTdId() > 0){
                    $this->getEntityManager()->getConnection()->update('generic_group', ['apl_id' => $row['groupApl']], ['id' => $genericGroup->getId()]);
                } else {
                    $this->getEntityManager()->getConnection()->update('generic_group', ['apl_id' => 0], ['id' => $genericGroup->getId()]);                    
                }    
                return;
            }    
        }
        return;
    }
}
