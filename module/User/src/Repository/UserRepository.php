<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace User\Repository;

use Doctrine\ORM\EntityRepository;
use User\Entity\User;
use User\Entity\Role;
use User\Filter\PhoneFilter;

class UserRepository  extends EntityRepository
{
    public function findUsersByRole($roleId)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('u')
            ->from(User::class, 'u')
            ->join("u.roles", 'r', 'WITH')    
            ->where('r.id = ?1')    
            ->setParameter('1', $roleId)
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }    
    
    public function findOneByEmail($email)
    {
        $cacheDriver = new \Doctrine\Common\Cache\ArrayCache();
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('u')
            ->from(User::class, 'u')
            ->where('u.email = ?1')    
            ->setParameter('1', $email)
                ;
        
        return $queryBuilder->getQuery()->getOneOrNullResult();
    }    
    
    /**
     * Выборка для формы
     * 
     * @param array params
     */
    public function formFind($params)
    {
        $user = null;
        if (!empty($params['user'])){
            $user = $params['user'];
        }

        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('u')
            ->from(User::class, 'u')
            ->where('u.id = ?1')    
            ->setParameter('1', -1)    
                ;
        if ($user){
            $queryBuilder->setParameter(1, $user->getId());
        }

        return $queryBuilder->getQuery()->getResult();       
    }
    
    /**
     * Запрос по поиска
     * 
     * @param array $params
     * @return object
     */
    public function liveSearch($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('u.id, u.fullName, p.name')
            ->from(User::class, 'u')
            ->join('u.contacts', 'c')
            ->join('c.phones', 'p')
            ->where('u.id = 0')    
                ;
//        var_dump($params); exit;
        if (is_array($params)){
            if (isset($params['search'])){
                $q = preg_replace('#[^0-9]#', '', $params['search']);
                if ($q){
                    $queryBuilder
                        ->where('p.name like :code')                           
                        ->setParameter('code', '%'.$q.'%')    
                            ;
                }    
            }
            if (isset($params['limit'])){
                $queryBuilder->setMaxResults($params['limit']);
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('u.'.$params['sort'], $params['order']);                
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }    
}