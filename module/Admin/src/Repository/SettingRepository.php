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
                ;
        if (is_array($params)){
            if (isset($params['status'])){
                $queryBuilder->andWhere('s.status = ?1')
                        ->setParameter('1', $params['status']);
            }
        }
        

        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
        
    }
    
}
