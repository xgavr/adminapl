<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Token;
use Application\Entity\ArticleToken;
use Application\Entity\Rawprice;
use Application\Entity\TokenGroup;
use Application\Entity\Goods;


/**
 * Description of TokenRepository
 *
 * @author Daddy
 */
class TokenRepository  extends EntityRepository
{
    /**
     * Найти артикулы из прайса
     * 
     * @param Application\Entity\Raw $raw
     */
    public function findRawpriceTitle($raw)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r.id, identity(r.code) as articleId, r.goodname, r.statusToken, a.tokenUpdateFlag')
            ->distinct()    
            ->from(Rawprice::class, 'r')
            ->join('r.code', 'a')    
            ->where('r.raw = ?1')
            ->andWhere('r.statusToken = ?2')
            ->andWhere('r.code is not null')
            ->andWhere('r.status = ?3')    
            ->setParameter('1', $raw->getId())    
            ->setParameter('2', Rawprice::TOKEN_NEW)    
            ->setParameter('3', Rawprice::STATUS_PARSED) 
            ->setMaxResults(100000)    
            ;    

//            var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();
        
    }
    

    /**
     * 
     * @param integer $articleId
     * @return type
     */
    public function findArticleTitle($articleId)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r.id, r.goodname, r.statusToken')
            ->from(Rawprice::class, 'r')
            ->where('r.code = ?1')
            ->andWhere('r.status = ?2')    
            ->setParameter('1', $articleId)    
            ->setParameter('2', Rawprice::STATUS_PARSED)    
            ;    

        return $queryBuilder->getQuery()->getResult();
        
    }

    /**
     * Быстрая вставка токена
     * @param array $row 
     * @return integer
     */
    public function insertToken($row)
    {
        $inserted = $this->getEntityManager()->getConnection()->insert('token', $row);
        return $inserted;
    }    

    /**
     * Быстрая вставка артикула токена
     * @param array $row 
     * @return integer
     */
    public function insertArticleToken($row)
    {
        $inserted = $this->getEntityManager()->getConnection()->insert('article_token', $row);
        return $inserted;
    }    

    
    
    
    /**
     * Быстрое обновление токенов артикула по лемме
     * 
     * @param string $lemma
     * @param array $data
     * @return integer
     */
    public function updateArticleToken($lemma, $data)
    {
        unset($data['flag']);
        unset($data['frequency']);
        
        if (!count($data)){
            return;
        }
        
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->update(ArticleToken::class, 'at')
                ->where('at.lemma = ?1')
                ->setParameter('1', $lemma)
                ;
        foreach ($data as $key => $value){
            $queryBuilder->set('at.'.$key, $value);
        }
        
        return $queryBuilder->getQuery()->getResult();        
    }


    /**
     * Быстрое обновление токена по лемме
     * 
     * @param string $lemma
     * @param array $data
     * @return integer
     */
    public function updateToken($lemma, $data)
    {
        if (!count($data)){
            return;
        }
        
        $this->updateArticleToken($lemma, $data);
        
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->update(Token::class, 't')
                ->where('t.lemma = ?1')
                ->setParameter('1', $lemma)
                ;
        foreach ($data as $key => $value){
            $queryBuilder->set('t.'.$key, $value);
        }
        
        return $queryBuilder->getQuery()->getResult();        
    }
    
    /**
     * Выборка количества артикулов в токене
     * 
     * @return array
     */
    public function tokenFrequencies()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('t.lemma, count(at.id) as articleCount')
                ->from(Token::class, 't')
                ->leftJoin(ArticleToken::class, 'at', 'WITH', 'at.lemma = t.lemma')
                ->groupBy('t.id')
                ;
        
        
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();        
        
    }
    /**
     * Быстрое удаление article токенов, свзанных с token
     * @param Application\Entity\Token $token 
     * @return integer
     */
    public function deleteArticleToken($token)
    {
        $deleted = $this->getEntityManager()->getConnection()->delete('article_token', ['lemma' => $token->getLemma()]);
        return $deleted;
    }    
    
    /**
     * Запрос по токенам по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllToken($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('t')
            ->from(Token::class, 't')
            ->addOrderBy('t.lemma')                
                ;
        
        if (is_array($params)){
            if (isset($params['q'])){
                $queryBuilder->where('t.lemma like :search')
                    ->setParameter('search', '%' . $params['q'] . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->where('t.lemma > ?1')
                    ->setParameter('1', $params['next1'])
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('t.lemma < ?2')
                    ->setParameter('2', $params['prev1'])
                    ->orderBy('t.lemma', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('t.'.$params['sort'], $params['order']);                
            }            
            if (isset($params['status'])){
                $queryBuilder->andWhere('t.status = ?3')
                    ->setParameter('3', $params['status'])
                        ;                
            }            
        }

//            var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }            
    
    /**
     * Количество токенов по статусу
     * 
     * @return array
     */
    public function statusTokenCount()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('t.status, count(t.id) as tokenCount')
                ->from(Token::class, 't')
                ->groupBy('t.status')
            ;
        
        return $queryBuilder->getQuery()->getResult();        
    }
    
    /**
     * Запрос по группам наименований по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllTokenGroup($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('t')
            ->from(TokenGroup::class, 't')
            ->addOrderBy('t.name')                
                ;
        
        if (is_array($params)){
            if (isset($params['q'])){
                $queryBuilder->where('t.lemms like :search')
                    ->setParameter('search', '%' . $params['q'] . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->where('t.ids > ?1')
                    ->setParameter('1', $params['next1'])
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('t.ids < ?2')
                    ->setParameter('2', $params['prev1'])
                    ->orderBy('t.ids', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('t.'.$params['sort'], $params['order']);                
            }            
        }

        return $queryBuilder->getQuery();
    }            
    
    /**
     * Найти строки прайсов токена
     * 
     * @param Application\Entity\Token $token
     * @return object
     */
    public function findTokenRawprice($token)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
            ->from(Rawprice::class, 'r')
            ->join('r.code', 'a')
            ->join('a.articleTokens', 'at')
            ->where('at.lemma = ?1')    
            ->andWhere('r.status = ?2')    
            ->setParameter('1', $token->getLemma())
            ->setParameter('2', Rawprice::STATUS_PARSED)
            ;
        
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Поиск токенов артикула
     * 
     * @param Application\Entity\Article|integer $article
     * @param integer $status
     */
    public function findArticleTokenByStatus($article, $status = Token::IS_DICT)
    {
        var_dump($article); exit;
        if (is_numeric($article)){
            $articleId = $article;
        } else {
            $articleId = $article->getId();
        }
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('at.lemma')
                ->from(ArticleToken::class, 'at')
                ->where('at.article = ?1')
                ->andWhere('at.status = ?2')
                ->setParameter('1', $articleId)
                ->setParameter('2', $status)
                ;
        
//            var_dump($queryBuilder->getQuery()->getSQL()); exit;
        $result = $queryBuilder->getQuery()->getResult(2);
        
        return array_column($result, 'lemma');                    
    }
    
    /**
     * Пересечение токенов артикулов
     * 
     * @param \Application\Entity\Article|integer $article
     * @param \Application\Entity\Article|integer $articleForMatching
     * @param integer $status
     * @return bool
     */
    public function articleTokenIntersect($article, $articleForMatching, $status = Token::IS_DICT)
    {
        $result = [];
        
        $articleTokens = $this->findArticleTokenByStatus($article, $status);
        $articleTokensForMatching = $this->findArticleTokenByStatus($articleForMatching, $status);
        
        if (count($articleTokens) && count($articleTokensForMatching)){        
            $result = array_intersect($articleTokens, $articleTokensForMatching);
        }
        
        return $result;
    }
    
    /**
     * Совпадение токенов артикулов по статусу
     * 
     * @param \Application\Entity\Article|integer $article
     * @param \Application\Entity\Article|integer $articleForMatching
     * @param integer $status
     * @return bool
     */
    public function intersectArticleTokenByStatus($article, $articleForMatching, $status = Token::IS_DICT)
    {
        $result = $this->articleTokenIntersect($article, $articleForMatching, $status);
        if (count($result)){
            return count($result) > 0;
        }    
        return;
    }

    /**
     * Найти токены товара по типу
     * 
     * @param \Application\Entity\Goods $good
     * @param integer $tokenType Description
     * @return object
     */
    public function findTokenGoodsByStatus($good, $tokenType = Token::IS_DICT)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('t.id, t.lemma, t.status')
            ->distinct()
            ->from(\Application\Entity\Article::class, 'a')    
            ->join('a.articleTokens', 'at')
            ->join(Token::class, 't', 'WITH', 't.lemma = at.lemma')    
            ->where('a.good = ?1')   
            ->andWhere('(at.status = ?2 or at.status = ?5)')
            ->andWhere('t.flag = ?4')    
//            ->andWhere('t.frequency > ?3')    
            ->setParameter('1', $good->getId())
            ->setParameter('2', $tokenType)
//            ->setParameter('3', TokenGroup::FREQUENCY_MIN)
            ->setParameter('4', Token::WHITE_LIST)
            ->setParameter('5', Token::IS_EN_ABBR)
            ;
//            var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }

    /**
     * Выборка строк прайса для создания групп наименований
     * 
     * @param Application\Entity\Rawprice $raw
     * @return array
     */
    public function findTokenGroupsForAccembly($raw)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
                ->from(Rawprice::class, 'r')
                ->where('r.raw = ?1')
                ->andWhere('r.statusGood = ?2')
                ->andWhere('r.statusToken = ?3')
                ->andWhere('r.status = ?4')
                ->setParameter('1', $raw->getId())
                ->setParameter('2', Rawprice::GOOD_OK)
                ->setParameter('3', Rawprice::TOKEN_PARSED)
                ->setParameter('4', Rawprice::STATUS_PARSED)
                ->setMaxResults(100000)
                ;

//var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();        
        
    }
    
    /**
     * Быстрая вставка группы наименований
     * @param array $row 
     * @return integer
     */
    public function insertTokenGroup($row)
    {
        $inserted = $this->getEntityManager()->getConnection()->insert('token_group', $row);
        return $inserted;
    }    

    /**
     * Быстрая вставка связи группы наименований и токена
     * @param array $row 
     * @return integer
     */
    public function insertTokenGroupToken($row)
    {
        $inserted = $this->getEntityManager()->getConnection()->insert('token_group_token', $row);
        return $inserted;
    }    
    
    /**
     * Быстрое обновление полей группы наименований
     * 
     * @param integer $tokenGroupId
     * @param array $data
     * @return integer
     */
    public function updateTokenGroup($tokenGroupId, $data)
    {
        if (!count($data)){
            return;
        }
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->update(TokenGroup::class, 'tg')
                ->where('tg.id = ?1')
                ->setParameter('1', $tokenGroupId)
                ;
        foreach ($data as $key => $value){
            $queryBuilder->set('tg.'.$key, $value);
        }
        
        return $queryBuilder->getQuery()->getResult();        
    }
    

    /**
     * Выборка количества товара в группах наименований
     * 
     * @return array
     */
    public function goodCountAllTokenGroup()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('tg.id, count(g.id) as goodCount')
                ->from(TokenGroup::class, 'tg')
                ->leftJoin('tg.goods', 'g')
                ->groupBy('tg.id')
                ;
        
        
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();        
    }
    

    /**
     * Быстрое удаление всех групп наименований
     * @return integer
     */
    public function deleteAllTokenGroup()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('tg.id')
                ->from(TokenGroup::class, 'tg')
                ->setMaxResults(1)
                ;
        $row = $queryBuilder->getQuery()->getResult();
//        var_dump($row[0]['id']); exit;
        
        $update = $this->getEntityManager()->getConnection()->update('goods', ['token_group_id' => $row[0]['id']], ['1' => 1]);

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->delete(TokenGroup::class, 'tg')
                ->where('tg.id != ?1')
                ->setParameter('1', $row[0]['id'])
                ;
        $queryBuilder->getQuery()->getResult();
        
        return;
    }    
    
    
    
    /**
     * Найти товары группы наименований
     * 
     * @param Application\Entity\TokenGroup $tokenGroup
     * @param array $params
     * @return object
     */
    public function findTokenGroupGoods($tokenGroup, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->where('g.tokenGroup = ?1')    
            ->setParameter('1', $tokenGroup->getId())
            ;
        
        if (is_array($params)){
            if (isset($params['tdGroup'])){
                if ($params['tdGroup']){
                    $queryBuilder->andWhere('g.genericGroup = ?2')
                            ->setParameter('2', $params['tdGroup'])
                            ;
                }    
            }
        }
        
        return $queryBuilder->getQuery();            
    }

    /**
     * Найти токены для удаления
     * 
     * @return object
     */
    public function findTokenForDelete()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('t')
            ->addSelect('count(at.id) as articleCount')    
            ->from(Token::class, 't')
            ->leftJoin(ArticleToken::class, 'at', 'WITH', 'at.lemma = t.lemma')
            ->groupBy('t.id')
            ->having('articleCount = 0')    
                ;
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }

    /**
     * Найти близкий токен из словаря
     * 
     * @param Application\Entity\Token $token
     * @param integer $dict
     */
    public function findNearToken($token, $dict = Token::IS_DICT)
    {
        if (mb_strlen($token) < 3){
            return [];
        }
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('t')
                ->from(Token::class, 't')
                ->where('t.status = ?1')
                ->andWhere('t.lemma like ?2')
                ->orderBy('t.lemma')
                ->setParameter('1', $dict)
                ->setParameter('2', $token.'%')
                ->setMaxResults(1)
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);            
    }
    
    
    public function nameCoverage()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('sum(tg.goodCount) as goodCount')
                ->from(TokenGroup::class, 'tg')
                ->where("tg.name != ''")
//                ->setParameter('1', '')
                ;
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        $result = $queryBuilder->getQuery()->getResult();
//        var_dump($result[0]);
        return $result[0]['goodCount'];            
    }

    public function goodCoverage()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('sum(tg.goodCount) as goodCount')
                ->from(TokenGroup::class, 'tg')
                //->where("tg.name != ''")
//                ->setParameter('1', '')
                ;
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        $result = $queryBuilder->getQuery()->getResult();
//        var_dump($result[0]);
        return $result[0]['goodCount'];            
    }

    /**
     * Запрос на обучающую выборку наименований
     * 
     * @return object
     */
    public function findMlTitles()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
//        $queryBuilder->select('r')
//                ->from(Rawprice::class, 'r')
//                ->join(\Application\Entity\MlTitle::class, 'mt', 'WITH', 'r.id = mt.rawprice')
//                ->orderBy('r.good')
                ;
        $queryBuilder->select('g')
                ->distinct()
                ->from(Goods::class, 'g')
                ->join(Rawprice::class, 'r', 'WITH', 'r.good = g.id')
                ->join(\Application\Entity\MlTitle::class, 'mt', 'WITH', 'r.id = mt.rawprice')
                ;
                

//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
        
    }
}
