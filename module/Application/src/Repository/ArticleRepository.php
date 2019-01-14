<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Article;
use Application\Entity\ArticleToken;
use Application\Entity\Token;
use Application\Entity\Rawprice;
use Application\Entity\Raw;


/**
 * Description of ArticleRepository
 *
 * @author Daddy
 */
class ArticleRepository  extends EntityRepository
{

    public function findArticleForInsert($raw)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
                ->from(Rawprice::class, 'r')
                ->where('r.raw = ?1')
                ->andWhere('r.code is null')
                ->setParameter('1', $raw->getId())
//                ->setParameter('2', Rawprice::STATUS_PARSED)
                ;
        
        return $queryBuilder->getQuery()->getResult();        
    }
    
    /**
     * Быстрая вставка артикула
     * @param array $row 
     * @return integer
     */
    public function insertArticle($row)
    {
        $inserted = $this->getEntityManager()->getConnection()->insert('article', $row);
        return $inserted;
    }    

    /**
     * Быстрая обновление строки прайса кодом артикула
     * @param Application\Entity\Rawprice $rawprice
     * @param Application\Entity\Article $code 
     * @return integer
     */
    public function updateRawpriceCode($rawprice, $code)
    {
        $updated = $this->getEntityManager()->getConnection()->update('rawprice', ['article_id' => $code->getId()], ['id' => $rawprice->getId()]);
        return $updated;
    }    

    /**
     * Быстрое удаление номеров, свзанных с артикулом
     * @param Application\Entity\Article $article 
     * @return integer
     */
    public function deleteOemRaw($article)
    {
        $deleted = $this->getEntityManager()->getConnection()->delete('oem_raw', ['article_id' => $article->getId()]);
        return $deleted;
    }    

    /**
     * Быстрое удаление токенов, свзанных с артикулом
     * @param Application\Entity\Article $article 
     * @return integer
     */
    public function deleteArticleToken($article)
    {
        $deleted = $this->getEntityManager()->getConnection()->delete('article_token', ['article_id' => $article->getId()]);
        return $deleted;
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
    
    
    public function findArticleTokens($article)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('t')
                ->from(ArticleToken::class, 'at')
                ->join(Token::class, 't', 'WITH', 't.lemma = at.lemma')
                ->distinct()
                ->where('at.article = ?1')
                ->orderBy('t.status')
                ->setParameter('1', $article->getId())
                ;
        
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
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
        $queryBuilder->select('a.id as articleId')
            ->from(Article::class, 'a')
            ->join('a.rawprice', 'r')    
            ->addSelect('count(r.id) as rawpriceCount')
            ->join('r.raw', 'w')    
            ->join('w.supplier', 's')
            ->addSelect('s.id as supplierId', 's.name as supplierName')    
            ->where('a.code = ?1')
            ->andWhere('r.status = ?2')
            ->groupBy('a.id')    
            ->addGroupBy('s.id')    
            ->setParameter('1', $article->getCode())    
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
     * Количество строк в стадии разборки артикулов
     * @param integer $maxStage Description
     * @return object
     */
    public function findParseStageRawpriceCount($maxStage)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('case when r.parseStage < ?2 then 0 else 1 end as stage, sum(r.rows) as rowCount')
            ->from(Raw::class, 'r')
            ->where('r.status = ?1')
            ->groupBy('stage')    
            ->setParameter('1', Raw::STATUS_PARSED)
            ->setParameter('2', $maxStage)
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
            ->leftJoin(Rawprice::class, 'r', 'WITH', 'r.code = u.id')
            ->groupBy('u.id')
            ->having('rawpriceCount = 0')    
            //->setParameter('1', Rawprice::STATUS_PARSED)
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

        $queryBuilder->select('c, u')
            ->from(Article::class, 'c')
            ->join('c.unknownProducer', 'u')    
            ->orderBy('c.code')                
                ;
        
        if (!is_array($params)){
            $params['q'] = 'moreThan';
        } elseif (isset($params['q'])){ 
            if (strlen($params['q']) < 3){
                $params['q'] = 'moreThan';
            }
        }    
        
        if (is_array($params)){
            if (isset($params['unattached'])){
                $queryBuilder->where('c.good is null');
            }
            if (isset($params['q'])){
                $filter = new \Application\Filter\ArticleCode();
                $queryBuilder->where('c.code like :search')
                    ->setParameter('search', '%' . $filter->filter($params['q']) . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->where('c.code > ?1')
                    ->setParameter('1', $params['next1'])
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('c.code < ?2')
                    ->setParameter('2', $params['prev1'])
                    ->orderBy('c.code', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
        }

        return $queryBuilder->getQuery();
    }
    
        
    public function findArticleForAssemblyByRaw($raw)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('a.code, count(a.unknownProducer) as unknownProducerCount')
                ->from(Article::class, 'a')
                ->groupBy('a.code')
                ->having('unknownProducerCount > 1')
                ;
        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();                    
    }
    
    /**
     * Найти артикулы производителей для удаления
     * 
     * @return object
     */
    public function findArticleForDelete()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('u')
            ->addSelect('count(r.id) as rawpriceCount')    
            ->from(Article::class, 'u')
            ->leftJoin(Rawprice::class, 'r', 'WITH', 'r.code = u.id')
            ->groupBy('u.id')
            ->having('rawpriceCount = 0')    
                ;
        
        return $queryBuilder->getQuery()->getResult();            
    }

    
}
