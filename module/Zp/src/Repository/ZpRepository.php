<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zp\Repository;

use Doctrine\ORM\EntityRepository;
use Zp\Entity\Accrual;
use Zp\Entity\Personal;
use Zp\Entity\Position;
use Zp\Entity\PersonalAccrual;
        
/**
 * Description of ZpRepository
 *
 * @author Daddy
 */
class ZpRepository extends EntityRepository
{

    /**
     * Получить Наисления
     * @param array $params
     * @return query
     */
    public function findAccrual($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('a')
            ->from(Accrual::class, 'a')
                ;
        if (is_array($params)){
            if (isset($params['sort'])){
                $queryBuilder->orderBy('a.'.$params['sort'], $params['order']);
            }            
        }    
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();       
    }
    
    /**
     * Получить Штат
     * @param array $params
     * @return query
     */
    public function findPosition($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('p, pp')
            ->from(Position::class, 'p')
            ->leftJoin('p.parentPosition', 'pp')
            ->orderBy('p.sort')    
            ->addOrderBy('p.id')    
                ;
        if (is_array($params)){
            if (!empty($params['company'])){
                $queryBuilder->andWhere('p.company = :company')
                        ->setParameter('company', $params['company'])
                        ;
            }            
            if (isset($params['sort'])){
                $queryBuilder->addOrderBy('p.'.$params['sort'], $params['order']);
            }            
        }    
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();       
    }
    
    /**
     * Сортировка
     * @param array $params
     * @return int
     */
    public function findMaxSortPosition($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('max(p.sort) as maxSort')
            ->from(Position::class, 'p')
            ->orderBy('p.sort')                    
                ;
        if (is_array($params)){
            if (!empty($params['company'])){
                $queryBuilder->andWhere('p.company = :company')
                        ->setParameter('company', $params['company'])
                        ;
            }            
            if (!empty($params['parentPosition'])){
                $queryBuilder->andWhere('p.parentPosition = :parentPosition')
                        ->setParameter('parentPosition', $params['parentPosition'])
                        ;
            }            
        }    
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        
        if ($result){
            return $result['maxSort'];
        }
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return 0;       
    }
    
    /**
     * Получить parentPositions
     * @param array $params
     * @return query
     */
    public function findParentPositions($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('p')
            ->from(Position::class, 'p')
            ->where('p.parentPosition is null')    
                ;
            if (!empty($params['company'])){
                $queryBuilder->andWhere('p.company = :company')
                        ->setParameter('company', $params['company'])
                        ;
            }            
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();       
    }

    /**
     * Обновить parentPositions num
     * @param Position $position
     * @return query
     */
    public function updateParentPositionNum($position)
    {
        $parentPosition = $position->getParentPosition();
        
        if ($parentPosition){
            $parentPosition->setNum(0);
                    
            $entityManager = $this->getEntityManager();

            $queryBuilder = $entityManager->createQueryBuilder();

            $queryBuilder->select('sum(p.num) as totalNum')
                ->from(Position::class, 'p')
                ->where('p.parentPosition = :parentPosition')    
                ->andWhere('p.status = :status')
                ->setParameter('parentPosition', $parentPosition)    
                ->setParameter('status', Position::STATUS_ACTIVE)    
                ->setMaxResults(1)    
                    ;
            $data = $queryBuilder->getQuery()->getOneOrNullResult();

            if (!empty($data['totalNum'])){
                $parentPosition->setNum($data['totalNum']);
            }
            
            $entityManager->persist($parentPosition);
            $entityManager->flush();
        }    
        
        return;       
    }
    
    
    
    /**
     * Получить Штат
     * @param array $params
     * @return query
     */
    public function findPersonal($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('p, u, pos')
            ->from(Personal::class, 'p')
            ->join('p.user', 'u')
            ->join('p.position', 'pos')
                ;
        if (is_array($params)){
            if (!empty($params['company'])){
                if (is_numeric($params['company'])){
                    $queryBuilder->andWhere('p.company = :company')
                            ->setParameter('company', $params['company'])
                            ;
                }    
            }            
            if (!empty($params['user'])){
                if (is_numeric($params['user'])){
                    $queryBuilder->andWhere('p.user = :user')
                            ->setParameter('user', $params['user'])
                            ;
                }    
            }            
            if (!empty($params['status'])){
                if (is_numeric($params['status'])){
                    $queryBuilder->andWhere('p.status = :status')
                            ->setParameter('status', $params['status'])
                            ;
                }    
            }            
            if (!empty($params['position'])){
                if (is_numeric($params['position'])){
                    $queryBuilder->andWhere('p.position = :position')
                            ->setParameter('position', $params['position'])
                            ;
                }
            }            
            if (isset($params['sort'])){
                $queryBuilder->orderBy('p.'.$params['sort'], $params['order']);
            }            
        }    
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();       
    }   

    /**
     * Запрос начислений по персоналу
     * 
     * @param integer $personalId
     * @param array $params
     * @return query
     */
    public function findPersonalAccruals($personalId, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('pa, u, a')
            ->from(PersonalAccrual::class, 'pa')
            ->join('pa.user', 'u')    
            ->join('pa.accrual', 'a')    
            ->where('pa.personal = ?1')
            ->setParameter('1', $personalId)    
                ;
        
        if (is_array($params)){
            if (isset($params['sort'])){
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();
    }      
    
    /**
     * Список для формы
     * @param array $params
     * @return array
     */
    public function accrualListForm($params)
    {
        $result = [];
        
        $data = $this->findAccrual($params)->getResult();
        
        foreach ($data as $row){
            $result[$row->getId()] = $row->getName();
        }
        
        return $result;
    }

    /**
     * Список для формы
     * @param array $params
     * @return array
     */
    public function positionListForm($params)
    {
        $result = [];
        if (!empty($params['all'])){
            $result[] = $params['all'];
        }
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('p.id, p.name as name, pp.name as groupName')
            ->from(Position::class, 'p')
            ->join('p.parentPosition', 'pp')
            ->orderBy('p.sort')    
            ->addOrderBy('p.id')    
                ;
        if (is_array($params)){
            if (!empty($params['company'])){
                $queryBuilder->andWhere('p.company = :company')
                        ->setParameter('company', $params['company'])
                        ;
            }            
            if (!empty($params['status'])){
                $queryBuilder->andWhere('p.status = :status')
                        ->setParameter('status', $params['status'])
                        ;
            }            
            if (isset($params['sort'])){
                $queryBuilder->addOrderBy('p.'.$params['sort'], $params['order']);
            }            
        }    
        
        $data = $queryBuilder->getQuery()->getResult();
        
        foreach ($data as $row){
            $result[$row['id']] = $row['name'].' ('.$row['groupName'].')';
        }
        
        return $result;
    }
    
}