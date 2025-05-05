<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Fasade\Repository;

use Doctrine\ORM\EntityRepository;
use Fasade\Entity\GroupSite;
use Application\Entity\TokenGroup;
use Application\Entity\Goods;

/**
 * Description of GroupSiteRepository
 *
 * @author Daddy
 */
class GroupSiteRepository extends EntityRepository{

    /**
     * Запрос по Rack
     * 
     * @param array $params
     * @return query
     */
    public function queryAllGroupSite($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('gs.id, gs.code, gs.name, gs.sort, gs.level, gs.goodCount, gs.saleCount')
            ->addSelect('gs.status, identity(gs.siteGroup) as pid')
            ->addSelect('gs.hasChild, gs.fullName')
            ->from(GroupSite::class, 'gs')
                ;
        
        if (is_array($params)){
            if (!empty($params['hasChild'])){
                if (is_numeric($params['hasChild'])){
                    $queryBuilder->andWhere('gs.hasChild = :hasChild')
                        ->setParameter('hasChild', $params['hasChild'])
                     ;
                }    
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('gs.'.$params['sort'], $params['order']);
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }    
    
    /**
     * Обновить количество товаров
     * 
     * @param GroupSite $groupSite
     */
    public function updateGroupSiteGoodCount($groupSite)
    {
        $entityManager = $this->getEntityManager();
        $result = [
            'saleCount' => 0,
            'goodCount' => 0,
        ];
        
        if ($groupSite->getHasChild() === GroupSite::HAS_NO_CHILD){

            $queryBuilder = $entityManager->createQueryBuilder();

            $queryBuilder->select('sum(g.retailCount) as saleCount, count(g.id) as goodCount')
                    ->from(Goods::class, 'g')
                    ->join('g.categories', 'c')
                    ->where('c.id = :groupSite')
                    ->setParameter('groupSite', $groupSite->getId())
                    ->setMaxResults(1)
                    ;
            
            $row = $queryBuilder->getQuery()->getOneOrNullResult();
            if ($row){
               $result['saleCount'] = empty($row['saleCount']) ? 0:$row['saleCount'];
               $result['goodCount'] = empty($row['goodCount']) ? 0:$row['goodCount'];
            }

        } else {
            $queryBuilder = $entityManager->createQueryBuilder();

            $queryBuilder->select('sum(gs.goodCount) as goodCount, sum(gs.saleCount) as saleCount')
                    ->from(GroupSite::class, 'gs')
                    ->where('gs.siteGroup = :groupSite')
                    ->setParameter('groupSite', $groupSite->getId())
                    ->setMaxResults(1)
                    ;

            $row = $queryBuilder->getQuery()->getOneOrNullResult();
            if ($row){
               $result['saleCount'] = empty($row['saleCount']) ? 0:$row['saleCount'];
               $result['goodCount'] = empty($row['goodCount']) ? 0:$row['goodCount'];
            }

        }   
        
//        var_dump($result); exit;
        $entityManager->getConnection()->update('group_site', ['good_count' => $result['goodCount'], 'sale_count' => $result['saleCount']], ['id' => $groupSite->getId()]);            
        
        if ($groupSite->getSiteGroup()){
            $this->updateGroupSiteGoodCount($groupSite->getSiteGroup());
        }
        
        return;
    }
    
    /*
     * Пересчитать количество товаров в категориях
     */
    public function updateGroupSiteGoodCounts()
    {
        ini_set('memory_limit', '512M');
        
        $entityManager = $this->getEntityManager();
        $categories = $entityManager->getRepository(GroupSite::class)
                ->findAll();
        
        foreach ($categories as $category){
            $this->updateGroupSiteGoodCount($category);
        }
        
        return;
    }
}
