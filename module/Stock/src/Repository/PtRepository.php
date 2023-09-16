<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Repository;

use Doctrine\ORM\EntityRepository;
use Company\Entity\Office;
use Stock\Entity\Pt;
use Stock\Entity\PtGood;
use Company\Entity\Legal;
use Application\Filter\ArticleCode;
use Stock\Entity\Movement;

/**
 * Description of PtRepository
 *
 * @author Daddy
 */
class PtRepository extends EntityRepository{
    
    /**
     * Сумма Pt
     * 
     * @param Pt $pt 
     * @return float
     */
    public function ptAmountTotal($pt)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('sum(pg.amount) as total')
                ->from(PtGood::class, 'pg')
                ->where('pg.pt = ?1')
                ->setParameter('1', $pt->getId())
                ->setMaxResults(1)
                ;
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        
        if (!empty($result['total'])){
            return $result['total'];
        }
        
        return 0;
    }

    /**
     * Запрос по ПТ
     * 
     * @param array $params
     * @return query
     */
    public function findAllPt($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('p, oo, oo2, c, c2')
            ->from(Pt::class, 'p')
            ->join('p.office', 'oo')    
            ->join('p.company', 'c')    
            ->join('p.office2', 'oo2')    
            ->join('p.company2', 'c2')    
                ;
        
        if (is_array($params)){
            if (isset($params['sort'])){
                $queryBuilder->orderBy('p.'.$params['sort'], $params['order']);
            }            
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('p.office = ?2 or p.office2 = ?2')
                            ->setParameter('2', $office->getId());
                }
            }
            if (!empty($params['companyId'])){
                $company = $entityManager->getRepository(Legal::class)
                        ->findOneById($params['companyId']);
                if ($company){
                    $queryBuilder->andWhere('p.company = :company or p.company2 = :company')
                            ->setParameter('company', $company->getId());
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
            if (!empty($params['ptId'])){
                if (is_numeric($params['ptId'])){
                    $queryBuilder->andWhere('p.id = :ptId')
                            ->setParameter('ptId', $params['ptId']);
                }    
            }
            if (!empty($params['q'])){     
                $articleCodeFilter = new ArticleCode(); 
                $queryBuilder->distinct()
                        ->join('p.ptGoods', 'pg')
                        ->join('pg.good', 'g')
                        ->andWhere('g.code like :q')
                        ->setParameter('q', $articleCodeFilter->filter($params['q']).'%');
            }
        }
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }    
    
    
    /**
     * Запрос по все ПТ
     * 
     * @param array $params
     * @return query
     */
    public function queryAllPt($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('p')
            ->from(Pt::class, 'p')
                ;
        
        if (is_array($params)){
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('p.office = ?1 or p.office2 = ?1')
                            ->setParameter('1', $office->getId());
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
     * Запрос по  количество ПТ
     * 
     * @param array $params
     * @return query
     */
    public function findAllPtTotal($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(p.id) as countPt')
            ->from(Pt::class, 'p')
                ;
        
        if (is_array($params)){
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('p.office = ?2 or p.office2 = ?2')
                            ->setParameter('2', $office->getId());
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
                        ->join('p.ptGoods', 'pg')
                        ->join('pg.good', 'g')
                        ->andWhere('g.code like :q')
                        ->setParameter('q', $articleCodeFilter->filter($params['q']).'%');
            }
        }
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['countPt'];
    }    
    
    /**
     * Запрос товаров по ПТ
     * 
     * @param integer $ptId
     * @param array $params
     * @return query
     */
    public function findPtGoods($ptId, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('pg, g, p')
            ->from(PtGood::class, 'pg')
            ->join('pg.good', 'g')    
            ->join('g.producer', 'p')    
            ->where('pg.pt = ?1')
            ->setParameter('1', $ptId)    
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

        $queryBuilder->select('p')
            ->from(Pt::class, 'p')
            ->where('p.statusEx = ?1')
            ->setParameter('1', Pt::STATUS_EX_NEW)  
                
//            ->andWhere('p.aplId > 0') 
            ->setMaxResults(1)    
                
                ;
        
        $data = $queryBuilder->getQuery()->getResult();
        foreach ($data as $pt){
            $flag = true;
            $ptGoods = $entityManager->getRepository(PtGood::class)
                    ->findBy(['pt' => $pt->getId()]);
            foreach ($ptGoods as $ptGood){
               if (empty($ptGood->getGood()->getAplId())){
                   $flag = false;
                   break;
               }  
            }
            if ($flag){
                return $pt;
            }    
        }
        
        return;                
        
    }                
    
    /**
     * Количество списано
     * 
     * @param PtGood $ptGood 
     * @return integer
     */
    public function movementQuantityCount($ptGood)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('sum(m.quantity) as movementCount')
                ->from(Movement::class, 'm')
                ->where('m.docKey = :docKey')
                ->setParameter('docKey', $ptGood->getPt()->getLogKey())
                ->andWhere('m.good = :goodId')
                ->setParameter('goodId', $ptGood->getGood()->getId())
                ->andWhere('m.office = :officeId')
                ->setParameter('officeId', $ptGood->getPt()->getOffice()->getId())
                ->setMaxResults(1)
                ;
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        
        if (!empty($result['movementCount'])){
            return abs($result['movementCount']);
        }
        
        return 0;
    }
    
}