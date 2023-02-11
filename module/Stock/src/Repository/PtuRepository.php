<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Repository;

use Doctrine\ORM\EntityRepository;
use Stock\Entity\Ptu;
use Stock\Entity\PtuGood;
use Application\Entity\Supplier;
use Company\Entity\Office;
use Application\Filter\ArticleCode;
use Application\Entity\SupplierOrder;
use Application\Entity\Order;

/**
 * Description of PtuRepository
 *
 * @author Daddy
 */
class PtuRepository extends EntityRepository{
    
    /**
     * Сумма ПТУ
     * 
     * @param Ptu $ptu
     * @return float
     */
    public function ptuAmountTotal($ptu)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('sum(pg.amount) as total')
                ->from(PtuGood::class, 'pg')
                ->where('pg.ptu = ?1')
                ->setParameter('1', $ptu->getId())
                ->setMaxResults(1)
                ;
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        
        if (!empty($result['total'])){
            return $result['total'];
        }
        
        return 0;
    }

    /**
     * Запрос по пту
     * 
     * @param array $params
     * @return query
     */
    public function findAllPtu($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('p, l, o, c, s')
            ->from(Ptu::class, 'p')
            ->join('p.legal', 'l')
            ->join('p.office', 'o')    
            ->join('p.contract', 'c')    
            ->join('p.supplier', 's')    
                ;
        
        if (is_array($params)){
            if (isset($params['sort'])){
                $queryBuilder->orderBy('p.'.$params['sort'], $params['order']);
                $queryBuilder->addOrderBy('p.id', $params['order']);
            }            
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->find($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('p.office = ?1')
                            ->setParameter('1', $office->getId());
                }
            }
            if (!empty($params['supplierId'])){
                $supplier = $entityManager->getRepository(Supplier::class)
                        ->find($params['supplierId']);
                if ($supplier){
                    $queryBuilder->andWhere('p.supplier = :supplier')
                            ->setParameter('supplier', $supplier->getId());
                }
            }            
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(p.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(p.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
            if (!empty($params['q'])){     
                $articleCodeFilter = new ArticleCode(); 
                $queryBuilder->distinct()
                        ->join('p.ptuGoods', 'pg')
                        ->join('pg.good', 'g')
                        ->andWhere('g.code like :q')
                        ->setParameter('q', $articleCodeFilter->filter($params['q']).'%');
            }
        }

        return $queryBuilder->getQuery();
    }    
    
    
    /**
     * Запрос по все пту
     * 
     * @param array $params
     * @return query
     */
    public function queryAllPtu($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('p')
            ->from(Ptu::class, 'p')
                ;
        
        if (is_array($params)){
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('p.office = ?1')
                            ->setParameter('1', $office->getId());
                }
            }
            if (!empty($params['supplierId'])){
                $supplier = $entityManager->getRepository(Supplier::class)
                        ->find($params['supplierId']);
                if ($supplier){
                    $queryBuilder->andWhere('p.supplier = :supplier')
                            ->setParameter('supplier', $supplier->getId());
                }
            }            
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(p.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }            
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(p.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
        }
        
        return $queryBuilder->getQuery();
    }    
    
    /**
     * Запрос по  количество пту
     * 
     * @param array $params
     * @return query
     */
    public function findAllPtuTotal($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(p.id) as countPtu')
            ->from(Ptu::class, 'p')
                ;
        
        if (is_array($params)){
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('p.office = ?1')
                            ->setParameter('1', $office->getId());
                }
            }
            if (!empty($params['supplierId'])){
                $supplier = $entityManager->getRepository(Supplier::class)
                        ->findOneById($params['supplierId']);
                if ($supplier){
                    $orX = $queryBuilder->expr()->orX();
                    foreach ($supplier->getLegalContact()->getLegals() as $legal){
                        $orX->add($queryBuilder->expr()->eq('p.legal', $legal->getId()));
                    }    
                    $queryBuilder->andWhere($orX);
                }    
            }            
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(p.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(p.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
            if (!empty($params['q'])){     
                $articleCodeFilter = new ArticleCode(); 
                $queryBuilder->distinct()
                        ->join('p.ptuGoods', 'pg')
                        ->join('pg.good', 'g')
                        ->andWhere('g.code like :q')
                        ->setParameter('q', $articleCodeFilter->filter($params['q']).'%');
            }
        }
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['countPtu'];
    }    
    
    /**
     * Запрос товаров по пту
     * 
     * @param integer $ptuId
     * @param array $params
     * @return query
     */
    public function findPtuGoods($ptuId, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('pg, g, p, n, u, c')
            ->from(PtuGood::class, 'pg')
            ->join('pg.good', 'g')    
            ->join('g.producer', 'p')    
            ->join('pg.ntd', 'n')    
            ->join('pg.unit', 'u')    
            ->join('pg.country', 'c')    
            ->where('pg.ptu = ?1')
            ->setParameter('1', $ptuId)    
                ;
        
        if (is_array($params)){
            if (isset($params['sort'])){
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();
    }        
    
    /**
     * Запрос товаров для пту
     * 
     * @param integer $supplierId
     * @return query
     */
    public function fillPtu($supplierId)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('so, g, p, o')
            ->from(SupplierOrder::class, 'so')
            ->join('so.good', 'g')  
            ->join('so.order', 'o')    
            ->join('g.producer', 'p')    
            ->where('so.supplier = ?1')
            ->setParameter('1', $supplierId)    
            ->andWhere('so.status = ?2')
            ->setParameter('2', SupplierOrder::STATUS_NEW)    
            ->andWhere('so.statusOrder = ?3')
            ->setParameter('3', SupplierOrder::STATUS_ORDER_ORDERED) 
            ->andWhere('so.dateCreated > ?4')    
            ->setParameter('4', date('Y-m-d', strtotime('- 1 month'))) 
            ->orderBy('id', 'DESC')    
                ;
        
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();
    }        

    /**
     * Найти записи для отправки в АПЛ
     */
    public function findForUpdateApl()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('p')
            ->from(Ptu::class, 'p')
            ->where('p.statusEx = ?1')
            ->setParameter('1', Ptu::STATUS_EX_NEW)    
                ;
        
        $data = $queryBuilder->getQuery()->getResult();
        foreach ($data as $ptu){
            $flag = true;
            $ptuGoods = $entityManager->getRepository(PtuGood::class)
                    ->findBy(['ptu' => $ptu->getId()]);
            foreach ($ptuGoods as $ptuGood){
               if (empty($ptuGood->getGood()->getAplId())){
                   $flag = false;
                   break;
               }  
            }
            if ($flag){
                return $ptu;
            }    
        }
        
        return;                
        
    }    
}