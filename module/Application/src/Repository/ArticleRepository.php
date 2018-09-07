<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Article;
use Application\Entity\Rawprice;


/**
 * Description of ArticleRepository
 *
 * @author Daddy
 */
class ArticleRepository  extends EntityRepository{

    /**
     * Выборка не привязанных артикулов из прайса
     */
    public function findRawpriceArticle()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
            ->from(Rawprice::class, 'r')
            ->where('r.code is null')
            ->andWhere('r.status = ?1')
            ->setMaxResults(10000)    
            ->setParameter('1', Rawprice::STATUS_PARSED)    
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Выборка не связанных с прайсом артикулов
     */
    public function findEmptyArticle()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('u')
            ->from(Article::class, 'u')
            ->leftJoin(Rawprice::class, 'r')    
            ->where('r.code is null')
            ->andWhere('r.status = ?1')
            ->setParameter('1', Rawprice::STATUS_PARSED)    
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Количество записей в прайсах с этим артикулом
     * 
     * @param Application\Entity\Article $article
     */
    public function rawpriceCount($article)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('count(r.id) as rawpriceCount')
            ->from(Rawprice::class, 'r')                
            ->where('r.code = ?1')
            ->andWhere('r.status = ?2')
            ->groupBy('r.code')    
            ->setParameter('1', $article->getId())    
            ->setParameter('2', Rawprice::STATUS_PARSED)    
                ;
        
        return $queryBuilder->getQuery()->getResult();
        
    }

    /**
     * Количество записей в прайсах с этим артикулом
     * в разрезе поставщиков
     * 
     * @param Application\Entity\Article $article
     * 
     * @return object
     */
    public function rawpriceCountBySupplier($article)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('count(r.id) as rawpriceCount')
            ->from(Rawprice::class, 'r')
            ->join('r.raw', 'w')    
            ->join('w.supplier', 's')
            ->addSelect('s.id as supplierId', 's.name as supplierName')    
            ->where('r.code = ?1')
            ->andWhere('r.status = ?2')
            ->groupBy('r.code')    
            ->addGroupBy('s.id')    
            ->setParameter('1', $article->getId())    
            ->setParameter('2', Rawprice::STATUS_PARSED)    
                ;
        //var_dump($queryBuilder->getQuery()->getDQL());
        return $queryBuilder->getQuery()->getResult();    
    }
    
    /**
     * Случайная выборка из прайсов по id артикула и id поставщика 
     * @param array $params
     * @return object
     */
    public function randRawpriceBy($params)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
            ->from(Rawprice::class, 'r')
            ->join('r.raw', 'w')    
            ->where('r.article = ?1')
            ->andWhere('w.supplier = ?2')
            ->andWhere('r.status = ?3')
            ->setParameter('1', $params['article'])    
            ->setParameter('2', $params['supplier'])    
            ->setParameter('3', Rawprice::STATUS_PARSED)
            ->setMaxResults(5)
            //->orderBy('rand()')    
                ;
        return $queryBuilder->getQuery()->getResult();    
        
    }
    
    /**
     * Количество привязанных строк прайсов к артикулу и не привязанных
     * 
     * @return array
     */
    public function findBindNoBindRawprice()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('case when r.code is null then 0 else 1 end as bind, COUNT(r.id) as bindCount')
            ->from(Rawprice::class, 'r')
            ->where('r.status = ?1')
            ->groupBy('bind')    
            ->setParameter('1', Rawprice::STATUS_PARSED)
                ;
        return $queryBuilder->getQuery()->getResult();            
    }

    /**
     * Найти артикулы для удаления
     * 
     * @return object
     */
    public function findArticlesForDelete()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('u')
            ->addSelect('count(r.id) as rawpriceCount')    
            ->from(Article::class, 'u')
            ->leftJoin(Rawprice::class, 'r', 'WITH', 'r.code = u.id and r.status = ?1')
            ->groupBy('u.id')
            ->having('rawpriceCount = 0')    
            ->setParameter('1', Rawprice::STATUS_PARSED)
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }

    /**
     * Запрос по неизвестным производителям по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllArticle($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Article::class, 'c')
            ->orderBy('c.name')
                ;
        
        if (is_array($params)){
            if (isset($params['unattached'])){
                $queryBuilder->where('c.good is null');
            }
            if (isset($params['q'])){
                $queryBuilder->where('c.code like :search')
                    ->setParameter('search', '%' . $params['q'] . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->where('c.code > ?1')
                    ->setParameter('1', $params['next1'])
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('c.code < ?1')
                    ->setParameter('1', $params['prev1'])
                    ->orderBy('c.name', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
        }

        return $queryBuilder->getQuery();
    }            
}
