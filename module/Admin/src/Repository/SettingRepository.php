<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Repository;

use Doctrine\ORM\EntityRepository;
use Admin\Entity\Setting;
/**
 * Description of SettingRepository
 *
 * @author Daddy
 */
class SettingRepository extends EntityRepository{
    
    /**
     * Запрос на выборку записей
     * 
     * @param array $params
     */
    public function findSettings($params)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('s')
            ->from(Setting::class, 's')
            ->orderBy('s.lastMod', 'DESC')
                ;
        if (is_array($params)){
            if (isset($params['status'])){
                if ($params['status'] == Setting::STATUS_ACTIVE_AND_ERROR){
                    $orX = $queryBuilder->expr()->orX();
                    $orX->add($queryBuilder->expr()->eq('s.status', Setting::STATUS_ACTIVE));
                    $orX->add($queryBuilder->expr()->eq('s.status', Setting::STATUS_ERROR));
                    $queryBuilder->andWhere($orX);                    
                } else {
                    $queryBuilder->andWhere('s.status = ?1')
                            ->setParameter('1', $params['status']);
                }    
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('s.'.$params['sort'], $params['order']);
            }
        }
        

//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();                    
    }
    
    public function procCount()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('count(s.id) as procCount')
            ->from(Setting::class, 's')
            ->where('s.status = :status')
            ->setParameter('status', Setting::STATUS_ACTIVE)
            ->andWhere('s.lastMod > :lastMod')    
            ->setParameter('lastMod', date('Y-m-d H:i:s', strtotime('-1 hour')))
                ;        

        $data = $queryBuilder->getQuery()->getResult();
        foreach ($data as $row){
            return $row['procCount']; 
        }
        return 0;                            
    }
}
