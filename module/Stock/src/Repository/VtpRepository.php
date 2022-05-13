<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Repository;

use Doctrine\ORM\EntityRepository;
use Stock\Entity\Vtp;
use Stock\Entity\VtpGood;
use Application\Entity\Supplier;
use Company\Entity\Office;
use Application\Filter\ArticleCode;

/**
 * Description of VtpRepository
 *
 * @author Daddy
 */
class VtpRepository extends EntityRepository{
    
    /**
     * Сумма ВТП
     * 
     * @param Vtp $vtp
     * @return float
     */
    public function vtpAmountTotal($vtp)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('sum(vg.amount) as total')
                ->from(VtpGood::class, 'vg')
                ->where('vg.vtp = ?1')
                ->setParameter('1', $vtp->getId())
                ->setMaxResults(1)
                ;
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        
        if (!empty($result['total'])){
            return $result['total'];
        }
        
        return 0;
    }

    /**
     * Запрос по ВТП
     * 
     * @param array $params
     * @return query
     */
    public function findAllVtp($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('v, p, l, o, c')
            ->from(Vtp::class, 'v')
            ->join('v.ptu', 'p')
            ->join('p.legal', 'l')
            ->join('p.office', 'o')    
            ->join('p.contract', 'c')    
                ;
        
        if (is_array($params)){
            if (isset($params['ptu'])){
                $queryBuilder->andWhere('v.ptu = ?1')
                    ->setParameter('1', $params['ptu'])
                        ;
            }            
            if (isset($params['sort'])){
                $queryBuilder->orderBy('v.'.$params['sort'], $params['order']);
            }            
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('p.office = ?2')
                            ->setParameter('2', $office->getId());
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
                    $queryBuilder->andWhere('YEAR(v.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(v.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
            if (!empty($params['statusDoc'])){
                if (is_numeric($params['statusDoc'])){
                    $queryBuilder->andWhere('v.statusDoc = :statusDoc')
                            ->setParameter('statusDoc', $params['statusDoc']);
                }    
            }
            if (!empty($params['q'])){     
                $articleCodeFilter = new ArticleCode(); 
                $queryBuilder->distinct()
                        ->join('v.vtpGoods', 'vg')
                        ->join('vg.good', 'g')
                        ->andWhere('g.code like :q')
                        ->setParameter('q', $articleCodeFilter->filter($params['q']).'%');
            }
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();
    }    
    
    
    /**
     * Запрос по все ВТП
     * 
     * @param array $params
     * @return query
     */
    public function queryAllVtp($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('v')
            ->from(Vtp::class, 'v')
            ->join('v.ptu', 'p')
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
                    $queryBuilder->andWhere('YEAR(v.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }            
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(v.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
        }
        
        return $queryBuilder->getQuery();
    }    
    
    /**
     * Запрос по  количество ВТП
     * 
     * @param array $params
     * @return query
     */
    public function findAllVtpTotal($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(v.id) as countVtp')
            ->from(Vtp::class, 'v')
            ->join('v.ptu', 'p')
                ;
        
        if (is_array($params)){
            if (isset($params['ptu'])){
                $queryBuilder->andWhere('v.ptu = ?1')
                    ->setParameter('1', $params['ptu']);
                        ;
            }            
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('p.office = ?2')
                            ->setParameter('2', $office->getId());
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
                    $queryBuilder->andWhere('YEAR(v.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(v.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
            if (!empty($params['statusDoc'])){
                if (is_numeric($params['statusDoc'])){
                    $queryBuilder->andWhere('v.statusDoc = :statusDoc')
                            ->setParameter('statusDoc', $params['statusDoc']);
                }    
            }
            if (!empty($params['q'])){                
                $queryBuilder->distinct()
                        ->join('v.vtpGoods', 'vg')
                        ->join('vg.good', 'g')
                        ->andWhere('g.code like :q')
                        ->setParameter('q', $params['q'].'%');
            }
        }
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['countVtp'];
    }    
    
    /**
     * Запрос товаров по ВТП
     * 
     * @param integer $vtpId
     * @param array $params
     * @return query
     */
    public function findVtpGoods($vtpId, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('vg, g, p')
            ->from(VtpGood::class, 'vg')
            ->join('vg.good', 'g')    
            ->join('g.producer', 'p')    
            ->where('vg.vtp = ?1')
            ->setParameter('1', $vtpId)    
                ;
        
        if (is_array($params)){
            if (isset($params['sort'])){
            }            
        }
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

        $queryBuilder->select('v')
            ->from(Vtp::class, 'v')
            ->where('v.statusEx = ?1')
            ->setParameter('1', Vtp::STATUS_EX_NEW)    
                ;
        
        $data = $queryBuilder->getQuery()->getResult();
        foreach ($data as $ptu){
            $flag = true;
            $vtpGoods = $entityManager->getRepository(VtpGood::class)
                    ->findBy(['vtp' => $vtp->getId()]);
            foreach ($vtpGoods as $vtpGood){
               if (empty($vtpGood->getGood()->getAplId())){
                   $flag = false;
                   break;
               }  
            }
            if ($flag){
                return $vtp;
            }    
        }
        
        return;                
        
    }        
}