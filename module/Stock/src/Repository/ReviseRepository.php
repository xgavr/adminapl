<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Repository;

use Doctrine\ORM\EntityRepository;
use Company\Entity\Office;
use Stock\Entity\Revise;
use Application\Entity\Supplier;

/**
 * Description of ReviseRepository
 *
 * @author Daddy
 */
class ReviseRepository extends EntityRepository
{
    /**
     * Запрос по документам
     * 
     * @param array $params
     * @return query
     */
    public function findAllRevise($dateOper, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r, l, o, c')
            ->from(Revise::class, 'r')
            ->join('r.legal', 'l')
            ->join('r.office', 'o')    
            ->join('r.contract', 'c')    
                ;
        
        if (is_array($params)){
            if (isset($params['sort'])){
                $queryBuilder->orderBy('p.'.$params['sort'], $params['order']);
            }            
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('r.office = ?1')
                            ->setParameter('1', $office->getId());
                }
            }
            if (!empty($params['supplierId'])){
                $supplier = $entityManager->getRepository(Supplier::class)
                        ->findOneById($params['supplierId']);
                if ($supplier){
                    $orX = $queryBuilder->expr()->orX();
                    foreach ($supplier->getLegalContact()->getLegals() as $legal){
                        $orX->add($queryBuilder->expr()->eq('r.legal', $legal->getId()));
                    }    
                    $queryBuilder->andWhere($orX);
                }    
            }            
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(r.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(r.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
        }

        return $queryBuilder->getQuery();
    }      
    
    /**
     * Запрос по все revise
     * 
     * @param array $params
     * @return query
     */
    public function queryAllPtu($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(Revise::class, 'p')
                ;
        
        if (is_array($params)){
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('r.office = ?1')
                            ->setParameter('1', $office->getId());
                }
            }
            if (!empty($params['supplierId'])){
                $supplier = $entityManager->getRepository(Supplier::class)
                        ->findOneById($params['supplierId']);
                if ($supplier){
                    $orX = $queryBuilder->expr()->orX();
                    foreach ($supplier->getLegalContact()->getLegals() as $legal){
                        $orX->add($queryBuilder->expr()->eq('r.legal', $legal->getId()));
                    }    
                    $queryBuilder->andWhere($orX);
                }    
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(r.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }            
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(r.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
        }
        
        return $queryBuilder->getQuery();
    }    
    
    
    /**
     * Запрос по количеству записей
     * 
     * @param array $params
     * @return query
     */
    public function findAllReviseTotal($dateOper, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(r.id) as countRevise')
            ->from(Revise::class, 'r')
                ;
        
        if (is_array($params)){
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('r.office = ?1')
                            ->setParameter('1', $office->getId());
                }
            }
            if (!empty($params['supplierId'])){
                $supplier = $entityManager->getRepository(Supplier::class)
                        ->findOneById($params['supplierId']);
                if ($supplier){
                    $orX = $queryBuilder->expr()->orX();
                    foreach ($supplier->getLegalContact()->getLegals() as $legal){
                        $orX->add($queryBuilder->expr()->eq('r.legal', $legal->getId()));
                    }    
                    $queryBuilder->andWhere($orX);
                }    
            }            
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(r.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(r.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
        }
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['countRevise'];
    }    

}
