<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Article;
use Application\Entity\OemRaw;
use Application\Entity\Rawprice;


/**
 * Description of OemRepository
 *
 * @author Daddy
 */
class OemRepository  extends EntityRepository{

    
    public function findOemForInsert($raw)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r.id, r.oem, r.vendor, identity(r.code) as articleId')
                ->from(Rawprice::class, 'r')
                ->where('r.raw = ?1')
                ->andWhere('r.statusOem = ?2')
                ->andWhere('r.status = ?3')
                ->setParameter('1', $raw->getId())
                ->setParameter('2', Rawprice::OEM_NEW)
                ->setParameter('3', Rawprice::STATUS_PARSED)
                ;
        
        return $queryBuilder->getQuery()->getResult();        
    }
    
    /**
     * Быстрая вставка номера
     * @param array $row 
     * @return integer
     */
    public function insertOemRaw($row)
    {
        $inserted = $this->getEntityManager()->getConnection()->insert('oem_raw', $row);
        return $inserted;
    }    
    
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
     * Выборка артикулов из прайса
     * 
     * @param Application\Entity\Raw $raw
     * @return object
     */
    public function findArticleFromRaw($raw)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r.code, r.unknownProducer')
                ->from(Rawprice::class, 'r')
                ->distinct()
                ->where('r.raw = ?1')
                ->andWhere('r.unknownProducer is not null')
                ->setParameter('1', $raw->getId())
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    
    /**
     * Количество записей в прайсах с этим номером
     * в разрезе поставщиков
     * 
     * @param Application\Entity\Article $oem
     * 
     * @return object
     */
    public function rawpriceCountBySupplier($oem)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('o.id as oemId')
            ->from(OemRaw::class, 'o')
            ->join('o.article', 'a')    
            ->addSelect('a.id as articleId')
            ->join('a.rawprice', 'r')    
            ->addSelect('count(r.id) as rawpriceCount')
            ->join('r.raw', 'w')    
            ->join('w.supplier', 's')
            ->addSelect('s.id as supplierId', 's.name as supplierName')    
            ->where('o.code = ?1')
            ->andWhere('r.status = ?2')
            ->groupBy('s.id')    
            ->addGroupBy('o.id')    
            ->setParameter('1', $oem->getCode())    
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
            ->where('r.code = ?1')
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
     * Найти номера для удаления
     * 
     * @return object
     */
    public function findOemRawForDelete()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('o')
            ->addSelect('count(r.id) as rawpriceCount')    
            ->from(OemRaw::class, 'o')
            ->leftJoin('o.rawprice', 'r')
            ->groupBy('o.id')
            ->having('rawpriceCount = 0')    
            //->setParameter('1', Rawprice::STATUS_PARSED)
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }

    /**
     * Запрос по кроссам по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllOemRaw($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('o, a, u')
            ->from(OemRaw::class, 'o')
            ->join('o.article', 'a') 
            ->join('a.unknownProducer', 'u')    
            ->orderBy('o.code')                
                ;
        
        if (!is_array($params)){
            $params['q'] = 'moreThan';
        } elseif (isset($params['q'])){ 
            if (strlen($params['q']) < 3){
                $params['q'] = 'moreThan';
            }
        }    
        
        if (is_array($params)){
            if (isset($params['q'])){
                $filter = new \Application\Filter\ArticleCode();
                $queryBuilder->where('o.code like :search')
                    ->setParameter('search', '%' . $filter->filter($params['q']) . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->where('o.code > ?1')
                    ->setParameter('1', $params['next1'])
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('o.code < ?2')
                    ->setParameter('2', $params['prev1'])
                    ->orderBy('o.code', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
        }

        return $queryBuilder->getQuery();
    }            
    
    /**
     * Найти артикулы производителей для удаления
     * 
     * @return object
     */
    public function findOemForDelete()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('u')
            ->addSelect('count(r.id) as rawpriceCount')    
            ->from(OemRaw::class, 'u')
            ->leftJoin('rawprice_oem_raw', 'r', 'WITH', 'r.code = u.id')
            ->groupBy('u.id')
            ->having('rawpriceCount = 0')    
                ;
        
        return $queryBuilder->getQuery()->getResult();            
    }

    
}
