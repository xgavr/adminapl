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
use Laminas\Filter\Digits;
use Stock\Entity\PtuCost;

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
        $result = 0;
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $qbCost = $entityManager->createQueryBuilder();

        $queryBuilder->select('sum(pg.amount) as total')
                ->from(PtuGood::class, 'pg')
                ->where('pg.ptu = ?1')
                ->setParameter('1', $ptu->getId())
                ->setMaxResults(1)
                ;
        
        $goodTotal = $queryBuilder->getQuery()->getOneOrNullResult();

        if (!empty($goodTotal['total'])){
            $result += $goodTotal['total'];
        }
        
        $qbCost->select('sum(pc.amount) as total')
                ->from(PtuCost::class, 'pc')
                ->where('pc.ptu = ?1')
                ->setParameter('1', $ptu->getId())
                ->setMaxResults(1)
                ;
        
        $costTotal = $qbCost->getQuery()->getOneOrNullResult();
        
        if (!empty($costTotal['total'])){
            $result += $costTotal['total'];
        }
        
        return $result;
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

        $queryBuilder->select('p, l, o, c, s, i')
            ->from(Ptu::class, 'p')
            ->join('p.legal', 'l')
            ->join('p.office', 'o')    
            ->join('p.contract', 'c')    
            ->join('p.supplier', 's')    
            ->leftJoin('p.idoc', 'i')    
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
            if (!empty($params['ptuId'])){
                if (is_numeric($params['ptuId'])){
                    $queryBuilder->andWhere('p.id = :ptuId')
                            ->setParameter('ptuId', $params['ptuId']);
                }    
            }
            if (!empty($params['q'])){     
                $orX = $queryBuilder->expr()->orX();

                $articleCodeFilter = new ArticleCode(); 
                $queryBuilder->distinct()
                        ->join('p.ptuGoods', 'pg')
                        ->join('pg.good', 'g')
                        ;
                
                $orX->add($queryBuilder->expr()->like('g.code', $queryBuilder->expr()->literal($articleCodeFilter->filter($params['q']).'%')));
                                
                if (is_numeric($params['q'])){
                    $orX->add($queryBuilder->expr()->eq('p.id', $params['q']));                    
                    $orX->add($queryBuilder->expr()->eq('p.aplId', $params['q']));                    
                }
                
                $queryBuilder->andWhere($orX);
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
                $orX = $queryBuilder->expr()->orX();

                $articleCodeFilter = new ArticleCode(); 
                $queryBuilder->distinct()
                        ->join('p.ptuGoods', 'pg')
                        ->join('pg.good', 'g')
                        ;
                
                $orX->add($queryBuilder->expr()->like('g.code', $queryBuilder->expr()->literal($articleCodeFilter->filter($params['q']).'%')));
                                
                if (is_numeric($params['q'])){
                    $orX->add($queryBuilder->expr()->eq('p.id', $params['q']));                    
                    $orX->add($queryBuilder->expr()->eq('p.aplId', $params['q']));                    
                }
                
                $queryBuilder->andWhere($orX);
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
     * Запрос услуг по пту
     * 
     * @param integer $ptuId
     * @param array $params
     * @return query
     */
    public function findPtuCosts($ptuId, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('pc, c')
            ->from(PtuCost::class, 'pc')
            ->join('pc.cost', 'c')    
            ->where('pc.ptu = ?1')
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
            ->orderBy('so.id', 'DESC')    
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
            ->join('p.supplier', 's')    
            ->where('p.statusEx = ?1')
            ->setParameter('1', Ptu::STATUS_EX_NEW)
            ->andWhere('s.aplId > 0')    
            ->setMaxResults(1)    
                
//            ->andWhere('p.aplId > 0')   
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