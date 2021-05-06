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
use Application\Entity\ArticleTitle;
use Application\Entity\Token;
use Application\Entity\Bigram;
use Application\Entity\ArticleBigram;
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
     * Быстрая вставка артикула наименования
     * @param array $row 
     * @return integer
     */
    public function insertArticleTitle($row)
    {
        $inserted = $this->getEntityManager()->getConnection()->insert('article_title', $row);
        return $inserted;
    }    

    
    
    /**
     * Быстрая обновление артикула
     * 
     * @param integer $articleId
     * @param array $data 
     * @return integer
     */
    public function updateArticle($articleId, $data)
    {
        if (!count($data)){
            return;
        }
        
        $updated = $this->getEntityManager()->getConnection()->update('article', $data, ['id' => $articleId]);
        return $updated;
    }    
    
    /**
     * Быстрое обновление флагов токенов артикула по лемме
     * 
     * @param string $lemma
     * @param int $flag
     * @return integer
     */
    public function updateTokenUpdateFlag($lemma, $flag = 10)
    {
        ini_set('memory_limit', '2048M');
        
        if ($flag == Article::getUpdateFlag()){
            return;
        }
        
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('a')
                ->distinct()
                ->from(ArticleToken::class, 'at')
                ->join(Article::class, 'a', 'WITH', 'a.id = at.article')
                ->where('at.lemma = ?1')
                ->andWhere('a.tokenUpdateFlag = ?2')
                ->setParameter('1', $lemma)
                ->setParameter('2', Article::getUpdateFlag())
                ;
        
        $articles = $queryBuilder->getQuery()->getResult();

        foreach ($articles as $article){
            $this->getEntityManager()->getConnection()->update('article', ['token_update_flag' => $flag], ['id' => $article->getId()]);            
        }
    }
    
    /**
     * Быстрое обновление флагов биграм артикула по билемме
     * 
     * @param Bigram $bigram
     * @param int $flag
     * @return integer
     */
    public function updateBigramUpdateFlag($bigram, $flag = 10)
    {
        ini_set('memory_limit', '2048M');
        
        if ($flag == Article::getUpdateFlag()){
            return;
        }
        
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('a')
                ->distinct()
                ->from(ArticleBigram::class, 'ab')
                ->join(Article::class, 'a', 'WITH', 'a.id = ab.article')
                ->where('ab.bigram = ?1')
                ->andWhere('a.tokenUpdateFlag = ?2')
                ->setParameter('1', $bigram->getId())
                ->setParameter('2', Article::getUpdateFlag())
                ;
        
        $articles = $queryBuilder->getQuery()->getResult();

        foreach ($articles as $article){
            $this->getEntityManager()->getConnection()->update('article', ['token_update_flag' => $flag], ['id' => $article->getId()]);            
        }
    }
    

    /**
     * Быстрое обновление строки прайса кодом артикула
     * @param Rawprice $rawprice
     * @param Article $code 
     * @return integer
     */
    public function updateRawpriceCode($rawprice, $code)
    {
        $updated = $this->getEntityManager()->getConnection()->update('rawprice', ['article_id' => $code->getId()], ['id' => $rawprice->getId()]);
        return $updated;
    }    

    /**
     * Быстрое удаление номеров, свзанных с артикулом
     * @param rticle $article 
     * @return integer
     */
    public function deleteOemRaw($article)
    {
        $deleted = $this->getEntityManager()->getConnection()->delete('oem_raw', ['article_id' => $article->getId()]);
        return $deleted;
    }    

    /**
     * Быстрое удаление токенов, свзанных с артикулом
     * @param Article|integer $article
     * @param ArticleTitle $articleTitle 
     * @return integer
     */
    public function deleteArticleToken($article, $articleTitle = null)
    {
        if (is_numeric($article)){
            $articleId = $article;
        } else {
            $articleId = $article->getId();
        }

        if ($articleTitle){
            $deleted = $this->getEntityManager()->getConnection()->delete('article_token', 
                    [
                        'article_id' => $articleId,
                        'title_id' => $articleTitle->getId(),
                    ]);            
        } else {
            $deleted = $this->getEntityManager()->getConnection()->delete('article_token', ['article_id' => $articleId]);
        }    
        return $deleted;
    }    

    /**
     * Быстрое удаление биграм, свзанных с артикулом
     * @param Article|integer $article 
     * @param ArticleTitle $articleTitle 
     * @return integer
     */
    public function deleteArticleBigram($article, $articleTitle = null)
    {
        if (is_numeric($article)){
            $articleId = $article;
        } else {
            $articleId = $article->getId();
        }

        if ($articleTitle){
            $deleted = $this->getEntityManager()->getConnection()->delete('article_bigram', 
                    [
                        'article_id' => $articleId,
                        'title_id' => $articleTitle->getId(),
                    ]);            
        } else {
            $deleted = $this->getEntityManager()->getConnection()->delete('article_bigram', ['article_id' => $articleId]);
        }    
        return $deleted;
    }    

    /**
     * Быстрое удаление наименований, свзанных с артикулом
     * @param Article|integer $article 
     * @return integer
     */
    public function deleteArticleTitle($article)
    {
        if (is_numeric($article)){
            $articleId = $article;
        } else {
            $articleId = $article->getId();
        }

        $deleted = $this->getEntityManager()->getConnection()->delete('article_title', ['article_id' => $articleId]);
        return $deleted;
    }    

    /**
     * Быстрое удаление кроссов, свзанных с артикулом
     * @param Article|integer $article 
     * @return integer
     */
    public function deleteArticleCross($article)
    {
        if (is_numeric($article)){
            $articleId = $article;
        } else {
            $articleId = $article->getId();
        }

        $deleted = $this->getEntityManager()->getConnection()->delete('cross_list', ['article_id' => $articleId]);
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
     * @param Article $article
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
     * @param Raw $raw
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
    
    public function findArticleBigrams($article)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('b')
                ->from(ArticleBigram::class, 'ab')
                ->join(Bigram::class, 'b', 'WITH', 'b.id = ab.bigram')
                ->distinct()
                ->where('ab.article = ?1')
                ->orderBy('b.status')
                ->setParameter('1', $article->getId())
                ;
        
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();        
    }
    
    /**
     * Количество записей в прайсах с этим артикулом
     * в разрезе поставщиков
     * 
     * @param Article $article
     * @param array $params
     * @return object
     */
    public function rawpriceCountBySupplier($article, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('a.id as articleId, identity(a.unknownProducer) as unknownProducerId')
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
            if (is_array($params)){
                $orX = $queryBuilder->expr()->orX();
                if (isset($params['unknownProducer'])){
                    $orX->add($queryBuilder->expr()->eq('a.unknownProducer', $params['unknownProducer']));
                }
                if (isset($params['unknownProducerIntersect'])){
                    $orX->add($queryBuilder->expr()->eq('a.unknownProducer', $params['unknownProducerIntersect']));
                }
                
                if ($orX->count()){
                    $queryBuilder->andWhere($orX);
                }
            }    

//            var_dump($queryBuilder->getQuery()->getSql());
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
            //->setMaxResults(5)
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
        $queryBuilder->select('a')
            ->from(Article::class, 'a')
            ->where('a.updWeek < ?1')
            ->setParameter('1', date('YW'))    
                ;
        return $queryBuilder->getQuery();            
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
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
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
        $queryBuilder->select('a')
            ->addSelect('count(r.id) as rawpriceCount')    
            ->from(Article::class, 'a')
            ->leftJoin('a.rawprice')
            ->groupBy('a.id')
            ->having('rawpriceCount = 0')    
                ;
        
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }

    
}
