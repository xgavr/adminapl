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
use Stock\Entity\Ptu;
use Stock\Entity\PtuGood;
use Application\Filter\ToFloat;

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

        $queryBuilder->select('v, p, s, o, c')
            ->from(Vtp::class, 'v')
            ->join('v.ptu', 'p')
            ->join('p.supplier', 's')
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
                $sort = 'v.'.$params['sort'];
                if ($params['sort'] == 'ptu.docDate'){
                    $sort = 'p.docDate';
                }    
                if ($params['sort'] == 'ptu.supplier.name'){
                    $sort = 's.name';
                }    
                $queryBuilder->orderBy($sort, $params['order'])
                        ->addOrderBy('v.id', 'DESC')
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
            if (!empty($params['status'])){
                if (is_numeric($params['status'])){
                    $queryBuilder->andWhere('v.status = :status')
                            ->setParameter('status', $params['status']);
                }    
            }
            if (!empty($params['vtpType'])){
                if (is_numeric($params['vtpType'])){
                    $queryBuilder->andWhere('v.vtpType = :vtpType')
                            ->setParameter('vtpType', $params['vtpType']);
                }    
            }
            if (!empty($params['vtpId'])){
                if (is_numeric($params['vtpId'])){
                    $queryBuilder->andWhere('v.id = :vtpId')
                            ->setParameter('vtpId', $params['vtpId']);
                }    
            }
            if (!empty($params['q'])){     
                $queryBuilder->distinct()
                        ->join('v.vtpGoods', 'vg')
                        ->join('vg.good', 'g')
                        ;

                $articleCodeFilter = new ArticleCode(); 
                
                $or = $queryBuilder->expr()->orX();
                $q = $articleCodeFilter->filter($params['q']);
                $toFloat = new ToFloat();
                $qf = $toFloat->filter($params['q']);
//                var_dump($qf);
                if ($qf){
                    $or->add($queryBuilder->expr()->eq('FLOOR(v.amount)', floor(abs($qf))));
                }    

                $or->add($queryBuilder->expr()->like('g.code', '\''.$q.'%\''));

                if (is_numeric($params['q'])){
                    $or->add($queryBuilder->expr()->eq('v.id', $params['q']));                    
                    $or->add($queryBuilder->expr()->eq('v.aplId', $params['q']));                    
                }
                
                $queryBuilder->andWhere($or);        
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
            if (!empty($params['status'])){
                if (is_numeric($params['status'])){
                    $queryBuilder->andWhere('v.status = :status')
                            ->setParameter('status', $params['status']);
                }    
            }
            if (!empty($params['vtpType'])){
                if (is_numeric($params['vtpType'])){
                    $queryBuilder->andWhere('v.vtpType = :vtpType')
                            ->setParameter('vtpType', $params['vtpType']);
                }    
            }
            if (!empty($params['q'])){     
                $queryBuilder->distinct()
                        ->join('v.vtpGoods', 'vg')
                        ->join('vg.good', 'g')
                        ;

                $articleCodeFilter = new ArticleCode(); 
                
                $or = $queryBuilder->expr()->orX();
                $q = $articleCodeFilter->filter($params['q']);
                $toFloat = new ToFloat();
                $qf = $toFloat->filter($params['q']);
//                var_dump($qf);
                if ($qf){
                    $or->add($queryBuilder->expr()->eq('FLOOR(v.amount)', floor(abs($qf))));
                }    

                $or->add($queryBuilder->expr()->like('g.code', '\''.$q.'%\''));
                
                if (is_numeric($params['q'])){
                    $or->add($queryBuilder->expr()->eq('v.id', $params['q']));                    
                    $or->add($queryBuilder->expr()->eq('v.aplId', $params['q']));                    
                }

                $queryBuilder->andWhere($or);        
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

        $queryBuilder->select('vg as vtpg, g, p, vg.id as vtpId, vg.amount/vg.quantity as price, vg.rowNo, vg.quantity, vg.amount')
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
                
//            ->andWhere('v.aplId > 0')    
            ->setMaxResults(1)    
                ;
        
        $data = $queryBuilder->getQuery()->getResult();
        foreach ($data as $vtp){
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
    
    /**
     * Доступные пту
     * @param Vtp $vtp
     */
    public function availableBase($vtp)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('p')
            ->from(PtuGood::class, 'pg')
            ->distinct()
            ->join(Ptu::class, 'p', 'WITH', 'p.id=pg.ptu')    
            ->where('p.contract = ?1')
            ->setParameter('1', $vtp->getPtu()->getContract()->getId())    
            ->andWhere('p.legal = ?2')
            ->setParameter('2', $vtp->getPtu()->getLegal()->getId())    
            ->andWhere('p.office = ?3')
            ->setParameter('3', $vtp->getPtu()->getOffice()->getId())    
            ->andWhere('p.status = ?4')
            ->setParameter('4', Ptu::STATUS_ACTIVE)    
            ->andWhere('p.docDate <= ?5')
            ->setParameter('5', $vtp->getDocDate())
            ->orderBy('p.docDate', 'desc')   
            ->setMaxResults(10)    
                ;        
        $orX = $queryBuilder->expr()->orX();
        foreach ($vtp->getVtpGoods() as $vtpGood){
            $orX->add($queryBuilder->expr()->eq('pg.good', $vtpGood->getGood()->getId()));            
        }
        $queryBuilder->andWhere($orX);
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Поставщики с приходами
     * @return array
     */
    public function activeSuppliers()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('s')
            ->from(Ptu::class, 'p')
            ->distinct()
            ->join(Supplier::class, 's', 'WITH', 's.id=p.supplier')    
            ->orderBy('s.name', 'ASC')
            ->andWhere('p.docDate > ?1')
            ->setParameter('1', date('Y-m-d', strtotime('-1 year')))                    
                ;        
        
        return $queryBuilder->getQuery()->getResult();
        
        
    }
}