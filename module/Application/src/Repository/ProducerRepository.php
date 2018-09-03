<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Country;
use Application\Entity\Producer;
use Application\Entity\UnknownProducer;
use Application\Entity\Rawprice;
use Application\Entity\Raw;

/**
 * Description of RbRepository
 *
 * @author Daddy
 */
class ProducerRepository  extends EntityRepository{

    /**
     * Выборка не привязанных производителей из прайса
     */
    public function findRawpriceUnknownProducer()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
            ->from(Rawprice::class, 'r')
            ->where('r.unknownProducer is null')
            ->andWhere('r.status = ?1')
            ->setMaxResults(3000)    
            ->setParameter('1', Rawprice::STATUS_PARSED)    
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    public function findAllUnknownProducer($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(UnknownProducer::class, 'c')
            ->orderBy('c.name')
                ;
        
        if (is_array($params)){
            if ($params['unattached']){
                $queryBuilder->where('c.producer is null');
            }
            if ($params['q']){
                $queryBuilder->where('c.name like :search')
                    ->setParameter('search', '%' . $params['q'] . '%')
                        ;
            }
        }

        return $queryBuilder->getQuery();
    }    
        
    public function findAllCountry()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Country::class, 'c')
            ->orderBy('c.name')
                ;

        return $queryBuilder->getQuery();
    }    
    
    public function findAllProducer()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Producer::class, 'c')
            ->orderBy('c.name')
                ;

        return $queryBuilder->getQuery();
    }    
    
    public function searchByName($search){

        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('g')
            ->from(Producer::class, 'g')
            ->where('g.name like :search')    
            ->orderBy('g.name')
            ->setParameter('search', '%' . $search . '%')
                ;
        return $queryBuilder->getQuery();
    }
        
    public function searchNameForSearchAssistant($search)
    {        
        return $this->searchByName($search)->getResult();
    }      
}
