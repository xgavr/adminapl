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
use Application\Entity\Order;
use Company\Entity\Office;

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
     * Запрос по сотрудникам
     * 
     * @param array $params
     * @return query
     */
    public function findAllUser($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('u')
            ->from(User::class, 'u')
//            ->leftJoin('u.office', 'o')    
                ;
        
        if (is_array($params)){
            if (isset($params['sort'])){
                $queryBuilder->orderBy('u.'.$params['sort'], $params['order']);
            }            
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                
                if ($office){
                    $queryBuilder->andWhere('u.office = ?1')
                            ->setParameter('1', $office->getId());
                }
            }
            if (!empty($params['status'])){
                if (is_numeric($params['status'])){
                    $queryBuilder->andWhere('u.status = :status')
                            ->setParameter('status', $params['status']);
                }    
            }
            if (!empty($params['userId'])){
                if (is_numeric($params['userId'])){
                    $queryBuilder->andWhere('u.id = :userId')
                            ->setParameter('userId', $params['userId']);
                }    
            }
        }
//        var_dump($queryBuilder->getQuery()->getSql()); 
        return $queryBuilder->getQuery();
    }      
    
    /**
     * Запрос по количеству записей
     * 
     * @param array $params
     * @return query
     */
    public function findAllUserTotal($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(u.id) as countUser')
            ->from(User::class, 'u')
                ;
        
        if (is_array($params)){
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('u.office = ?1')
                            ->setParameter('1', $office->getId());
                }
            }
            if (!empty($params['status'])){
                if (is_numeric($params['status'])){
                    $queryBuilder->andWhere('u.status = :status')
                            ->setParameter('status', $params['status']);
                }    
            }
        }
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['countUser'];
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

        $queryBuilder->select('u.id, u.fullName, p.name as phone, concat(p.name, \' \', u.fullName) as name')
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
    
    /**
     * Продажники
     */
    public function managers()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('u')
                ->from(User::class, 'u')
                ->where('u.orderCount > 0')
                ->orderBy('u.status', 'ASC')
                ->addOrderBy('u.orderCount', 'DESC')
                ;
        return $queryBuilder->getQuery()->getResult();        
    }
            
    
    /**
     * Обновить количество заказов
     * @param User $user
     */
    public function updateOrderCount($user)
    {
        $entityManager = $this->getEntityManager();
        $orderCount = $entityManager->getRepository(Order::class)
                ->count(['user' => $user->getId()]);
        $entityManager->getConnection()->update('user', ['order_count' => $orderCount], ['id' => $user->getId()]);
        
        return;
    }
    
    /**
     * Обновить количество заказов
     * 
     */
    public function updateOrderCounts()
    {
        $entityManager = $this->getEntityManager();
        $users = $entityManager->getRepository(User::class)
                ->findAll();
        
        foreach ($users as $user){
            $this->updateOrderCount($user);
        }
        
        return;
    }    
}