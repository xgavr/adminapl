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

    public function findAllRaw()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c, count(r.id) as rowcount')
            ->from(Raw::class, 'c')
            ->join('c.rawprice', 'r')
            ->groupBy('c.id')     
            ->orderBy('c.id', 'DESC')
                ;
//var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }        

    /*
     * @var Apllication\Entity\Raw
     */
    public function findRawRawprice($raw)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Rawprice::class, 'c')
            ->where('c.raw = ?1')    
            ->orderBy('c.id')
            ->setParameter('1', $raw->getId())    
                ;

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
