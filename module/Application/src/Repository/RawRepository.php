<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Raw;
use Application\Entity\Rawprice;
/**
 * Description of SupplierRepository
 *
 * @author Daddy
 */
class RawRepository extends EntityRepository{

    public function findAllRaw($status = null, $supplier = null, $exceptRaw = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c, count(r.id) as rowcount')
            ->from(Raw::class, 'c')
            ->leftJoin('c.rawprice', 'r')
            ->groupBy('c.id')     
            ->orderBy('c.id', 'DESC')
                ;
        
        if ($status){
            $queryBuilder->andWhere('c.status = ?2')
            ->setParameter('2', (int) $status)    
                ;                    
        }

        if ($supplier){
            $queryBuilder->andWhere('c.supplier = ?3')
            ->setParameter('3', $supplier->getId())    
                ;                    
        }

        if ($exceptRaw){
            $queryBuilder->andWhere('c.id != ?4')
            ->setParameter('4', $exceptRaw->getId())    
                ;                    
        }

        return $queryBuilder->getQuery();
    }        

    /*
     * @var Apllication\Entity\Raw
     * $var int status
     */
    public function findRawRawprice($raw, $status = null, $limit = 0)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Rawprice::class, 'c')
            ->where('c.raw = ?1')    
            //->orderBy('c.id')
            ->setParameter('1', $raw->getId())    
                ;
        
        if ($status){
            $queryBuilder->andWhere('c.status = ?2')
            ->setParameter('2', (int) $status)    
                ;                    
        }

        if ($limit){
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery();
    }
    
    /*
     * Выбрать уникальных производителей из прайса
     * @var Apllication\Entity\Raw
     */
    public function findProducerRawprice($raw)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c.producer')
            ->from(Rawprice::class, 'c')
            ->where('c.raw = ?1')    
            ->distinct()    
            ->setParameter('1', $raw->getId())    
                ;

        return $queryBuilder->getQuery()->getResult();
    }        
    
    /*
     * Выбрать уникальные товары из прайса
     * @var Apllication\Entity\Raw
     */
    public function findGoodRawprice($raw)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c.article, IDENTITY(c.unknownProducer) as unknownProducer, c.goodname')
            ->from(Rawprice::class, 'c')
            ->where('c.raw = ?1')    
            ->distinct()    
            ->setParameter('1', $raw->getId())    
                ;

        return $queryBuilder->getQuery()->getResult();
    }  
    
    /*
     * Получить статусы обработки прайса
     * @var Apllication\Entity\Raw
     * 
     */
    public function rawpriceStatuses($raw = null)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('r.status as status, count(r.id) as status_count')
                ->from(Rawprice::class, 'r')
                ->groupBy('r.status')
                ;
        if ($raw){
            $queryBuilder->where('r.raw = ?1')
                    ->setParameter('1', $raw->getId())
                    ;
        }
        return $queryBuilder->getQuery()->getResult();
    }
    
    /*
     * Получить статусы состояния прайсов
     * @var Apllication\Entity\Raw
     * 
     */
    public function rawStatuses()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('r.status as status, count(r.id) as status_count')
                ->from(Raw::class, 'r')
                ->groupBy('r.status')
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /*
     * Получить записи для разбора
     */
    public function findRawpriceForParse($raw)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('rp')
                ->from(Rawprice::class, 'rp')
                ->where('rp.raw = ?1')
                ->andWhere('rp.status = ?2')
                ->setParameter('1', $raw->getId())
                ->setParameter('2', Rawprice::STATUS_NEW)
                ;
        
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
                
        return $queryBuilder->getQuery()->getResult();
    }
    
    /*
     * Поиск старых прайсов по отношению к данному прайсу
     * @var Apllication\Entity\Raw
     * 
     */
    public function findOldRaw($raw)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('r')
                ->from(Raw::class, 'r')
                ->where('r.supplier = ?1')
                ->andWhere('r.id < ?2')
                ->andWhere('r.status = ?3')
                ->setParameter('1', $raw->getSupplier()->getId())
                ->setParameter('2', $raw->getId())
                ->setParameter('3', Raw::STATUS_PARSED)
                ;
        
        return $queryBuilder->getQuery()->getResult();
        
    }
    
    /*
     * Поиск прайсов для удаления
     */
    public function findRawForRemove()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('r')
                ->from(Raw::class, 'r')
                ->where('r.dateCreated <= ?1')
                ->setParameter('1', date('Y-m-d', strtotime('-1 week')))
                ;
        
        return $queryBuilder->getQuery()->getResult();        
    }


    /*
     * Удаление raw
     * @var Apllication\Entity\Raw
     * 
     */
    public function deleteRawprices($raw)
    {
        $rawId = $raw->getId();
        
        if ($rawId){
            $entityManager = $this->getEntityManager();
            $query = $entityManager->createQuery("delete from Application\Entity\Rawprice m where m.raw = $rawId");
            $numDeleted = $query->execute();
        }    
        return;
    }
        
}
