<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Repository;

use Doctrine\ORM\EntityRepository;
use Stock\Entity\Vt;
use Stock\Entity\VtGood;
use Application\Filter\ArticleCode;

/**
 * Description of VtRepository
 *
 * @author Daddy
 */
class VtRepository extends EntityRepository{
    
    /**
     * Сумма возврата
     * 
     * @param Vt $vt
     * @return float
     */
    public function vtAmountTotal($vt)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('sum(vg.amount) as total')
                ->from(VtGood::class, 'vg')
                ->where('vg.vt = ?1')
                ->setParameter('1', $vt->getId())
                ->setMaxResults(1)
                ;
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        
        if (!empty($result['total'])){
            return $result['total'];
        }
        
        return 0;
    }

    /**
     * Запрос по возвратам
     * 
     * @param array $params
     * @return query
     */
    public function findAllVt($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('v, p, o')
            ->from(Vt::class, 'v')
            ->join('v.order', 'p')
            ->join('v.office', 'o')    
                ;
        
        if (is_array($params)){
            if (isset($params['orderId'])){
                $queryBuilder->andWhere('v.order = ?1')
                    ->setParameter('1', $params['orderId'])
                        ;
            }            
            if (isset($params['sort'])){
                $queryBuilder->orderBy('v.'.$params['sort'], $params['order']);
            }            
            if (is_numeric($params['officeId'])){
                $queryBuilder->andWhere('v.office = ?2')
                        ->setParameter('2', $params['officeId']);
            }
            if (is_numeric($params['clientId'])){
                $queryBuilder
                        ->leftJoin('p.contact', 'c')
                        ->andWhere('c.client = :client')
                        ->setParameter('client', $params['clientId']);
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
            if (!empty($params['q'])){     
                $articleCodeFilter = new ArticleCode(); 
                $queryBuilder->distinct()
                        ->join('v.vtGoods', 'vg')
                        ->join('vg.good', 'g')
                        ->andWhere('g.code like :q')
                        ->setParameter('q', $articleCodeFilter->filter($params['q']).'%');
            }
        }

        return $queryBuilder->getQuery();
    }    
    
    
    /**
     * Запрос по всем возвратам
     * 
     * @param array $params
     * @return query
     */
    public function queryAllVt($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('v')
            ->from(Vt::class, 'v')
                ;
        
        if (is_array($params)){
            if (is_numeric($params['officeId'])){
                $queryBuilder->andWhere('v.office = ?1')
                        ->setParameter('1', $params['officeId']);
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
     * Запрос по  количество возвратов
     * 
     * @param array $params
     * @return query
     */
    public function findAllVtTotal($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(v.id) as countVt')
            ->from(Vt::class, 'v')
                ;
        
        if (is_array($params)){
            if (isset($params['orderId'])){
                $queryBuilder->andWhere('v.order = ?1')
                    ->setParameter('1', $params['orderId']);
                        ;
            }            
            if (is_numeric($params['officeId'])){
                $queryBuilder->andWhere('v.office = ?2')
                        ->setParameter('2', $params['officeId']);
            }
            if (is_numeric($params['clientId'])){
                $queryBuilder
                        ->join('v.order', 'p')
                        ->leftJoin('p.contact', 'c')
                        ->andWhere('c.client = :client')
                        ->setParameter('client', $params['clientId']);
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
            if (!empty($params['q'])){     
                $articleCodeFilter = new ArticleCode(); 
                $queryBuilder->distinct()
                        ->join('v.vtGoods', 'vg')
                        ->join('vg.good', 'g')
                        ->andWhere('g.code like :q')
                        ->setParameter('q', $articleCodeFilter->filter($params['q']).'%');
            }
        }
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['countVt'];
    }    
    
    /**
     * Запрос товаров по возврату
     * 
     * @param integer $vtId
     * @param array $params
     * @return query
     */
    public function findVtGoods($vtId, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('vt, g, p')
            ->from(VtGood::class, 'vt')
            ->join('vt.good', 'g')    
            ->join('g.producer', 'p')    
            ->where('vt.vt = ?1')
            ->setParameter('1', $vtId)    
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
     * @param integer $vtId 
     */
    public function findForUpdateApl($vtId = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('v')
            ->from(Vt::class, 'v')
            ->where('v.statusEx = ?1')
            ->setParameter('1', Vt::STATUS_EX_NEW)    
                
//            ->andWhere('v.aplId > 0')  
            ->setMaxResults(1)                    
                ;
        
        if ($vtId){
            $queryBuilder->resetDQLPart('where')
                    ->andWhere('v.id = ?1')
                    ->setParameter('1', $vtId)
                    ;
        }
        
        $data = $queryBuilder->getQuery()->getResult();
        foreach ($data as $vt){
            $flag = true;
            $vtGoods = $entityManager->getRepository(VtGood::class)
                    ->findBy(['vt' => $vt->getId()]);
            foreach ($vtGoods as $vtGood){
               if (empty($vtGood->getGood()->getAplId())){
                   $flag = false;
                   break;
               }  
            }
            if ($flag){
                return $vt;
            }    
        }
        
        return;                
        
    }           
    
    /**
     * Выборка для исправления движения товаров
     */
    public function findForFixMovement()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('v')
            ->distinct()    
            ->from(Vt::class, 'v')
            ->leftJoin(\Stock\Entity\Movement::class, 'm', 'WITH', 'm.docId = v.id and m.docType = 4 and m.status != 2')    
            ->where('v.status != :status')
            ->setParameter('status', Vt::STATUS_RETIRED)  
            ->andWhere('m.docStamp is null')    
            //->andWhere('v.aplId > 0')    
            ->setMaxResults(10000)
               ;
        
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery()->getResult();
        
    }
    
}