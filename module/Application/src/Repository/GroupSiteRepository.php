<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\GroupSite;
use Application\Entity\TokenGroup;

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

        $queryBuilder->select('gs.id, gs.code, gs.name, gs.sort, gs.level, gs.goodCount')
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

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('sum(tg.goodCount) as goodCount')
                ->from(TokenGroup::class, 'tg')
                ->where('tg.groupSite = :groupSite')
                ->setParameter('groupSite', $groupSite->getId())
                ->setMaxResults(1)
                ;
        
        $result = 0;
        $row = $queryBuilder->getQuery()->getOneOrNullResult();
        if ($row){
           $result = $row['goodCount']; 
        }
        
        $entityManager->getConnection()->update('group_site', ['goodCount' => $result], ['id' => $groupSite->getId()]);
        
        return;
    }
}
