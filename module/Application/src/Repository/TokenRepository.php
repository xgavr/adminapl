<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Token;
use Application\Entity\Article;
use Application\Entity\ArticleToken;
use Application\Entity\ArticleTitle;
use Application\Entity\GoodToken;
use Application\Entity\Rawprice;
use Application\Entity\TokenGroup;
use Application\Entity\Goods;
use Application\Entity\GenericGroup;


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

        $queryBuilder->select('r')
            ->from(Rawprice::class, 'r')
            ->where('r.raw = ?1')
            ->andWhere('r.statusToken = ?2')
            ->andWhere('r.status = ?3')    
            ->setParameter('1', $raw->getId())    
            ->setParameter('2', Rawprice::TOKEN_NEW)    
            ->setParameter('3', Rawprice::STATUS_PARSED)    
            ;    

//            var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
        
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
        unset($data['idf']);
        
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
        
        if (isset($data['status'])){
            $this->updateArticleToken($lemma, $data);
        }    
        
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
     * @param \Application\Entity\Token $token 
     * @return integer
     */
    public function deleteArticleToken($token)
    {
        $deleted = $this->getEntityManager()->getConnection()->delete('article_token', ['lemma' => $token->getLemma()]);
        return $deleted;
    }    
    
    /**
     * Быстрое удаление good токенов, свзанных с token
     * @param \Application\Entity\Token $token 
     * @return integer
     */
    public function deleteGoodToken($token)
    {
        $deleted = $this->getEntityManager()->getConnection()->delete('good_token', ['lemma' => $token->getLemma()]);
        return $deleted;
    }    
    
    /**
     * Быстрое удаление good токенов, свзанных с token
     * @param \Application\Entity\Goods $good 
     * @return integer
     */
    public function deleteTokenGood($good)
    {
        $deleted = $this->getEntityManager()->getConnection()->delete('good_token', ['good_id' => $good->getId()]);
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
                $orX = $queryBuilder->expr()->orX();
                $orX->add($queryBuilder->expr()->like('t.lemma', ':search'));
                $orX->add($queryBuilder->expr()->like('t.correct', ':search'));
                $queryBuilder->andWhere($orX)
                    ->setParameter('search', '%' . trim($params['q']) . '%')
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
            if (isset($params['flag'])){
                $queryBuilder->andWhere('t.flag = ?4')
                    ->setParameter('4', $params['flag'])
                        ;                
            }            
            if (isset($params['isCorrect'])){
                if ($params['isCorrect'] == 1){
                    $queryBuilder->andWhere('t.correct is not null');                
                }    
                if ($params['isCorrect'] == 0){
                    $queryBuilder->andWhere('t.correct is null');                
                }    
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

        $queryBuilder->select('tg')
            ->from(TokenGroup::class, 'tg')
            ->addOrderBy('tg.name')                
                ;
        
        if (is_array($params)){
            if (isset($params['q'])){
                $queryBuilder->andWhere('tg.lemms like :search')
                    ->setParameter('search', '%' . $params['q'] . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->andWhere('tg.ids > ?1')
                    ->setParameter('1', $params['next1'])
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->andWhere('tg.ids < ?2')
                    ->setParameter('2', $params['prev1'])
                    ->orderBy('tg.ids', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['goodCountLevel'])){
                $levels = explode('_', $params['goodCountLevel']);
                if (count($levels)){
                    $queryBuilder->andWhere('tg.goodCount >= ?3')
                        ->setParameter('3', $levels[0]);
                    if (isset($levels[1])){
                        if ($levels[1] >= $levels[0]){
                            $queryBuilder->andWhere('tg.goodCount <= ?4')
                                ->setParameter('4', $levels[1]);
                        }
                    }
                }                                
            }
            if (isset($params['withoutName'])){
                if ($params['withoutName'] == 1){
                    $queryBuilder->andWhere('tg.name = ?5')
                        ->setParameter('5', '');
                }                                
                if ($params['withoutName'] == 2){
                    $queryBuilder->andWhere('tg.name != ?5')
                        ->setParameter('5', '');
                }                                
            }
            if (isset($params['withGenericGroup'])){
                if ($params['withGenericGroup'] == 1){
                    
                    $zeroGroup = $this->getEntityManager()->getRepository(GenericGroup::class)
                        ->findOneByTdId(0);
                    
                    $queryBuilder->join('tg.goods', 'g')
                            ->distinct()
                            ->andWhere('g.genericGroup != ?6')
                            ->setParameter('6', $zeroGroup)
                            ;
                }                                
                if ($params['withGenericGroup'] == 2){
                    $zeroGroup = $this->getEntityManager()->getRepository(GenericGroup::class)
                        ->findOneByTdId(0);

                    $queryBuilder->join('tg.goods', 'g')
                            ->distinct()
                            ->andWhere('g.genericGroup = ?7')
                            ->setParameter('7', $zeroGroup)
                            ;
                }                                
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('tg.'.$params['sort'], $params['order']);                
            }            
        }

//            var_dump($queryBuilder->getQuery()->getSQL()); exit;
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
     * Найти артикулы токена
     * 
     * @param Token $token
     * @return object
     */
    public function findTokenArticles($token)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('a')
            ->from(Article::class, 'a')
            ->join('a.articleTokens', 'at')
            ->where('at.lemma = ?1')    
            ->setParameter('1', $token->getLemma())
            ;
        
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Поиск токенов артикула
     * 
     * @param Article|integer $article
     * @param integer $status
     */
    public function findArticleTokenByStatus($article, $status = Token::IS_DICT)
    {
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
     * @param Article|integer $article
     * @param Article|integer $articleForMatching
     * @param integer $status
     * @return array
     */
    public function articleTokenIntersect($article, $articleForMatching, $status = Token::IS_DICT)
    {
        $result = [];
        
        if ($article && $articleForMatching){
            $articleTokens = $this->findArticleTokenByStatus($article, $status);
            $articleTokensForMatching = $this->findArticleTokenByStatus($articleForMatching, $status);

            if (count($articleTokens) && count($articleTokensForMatching)){        
                $result = array_intersect($articleTokens, $articleTokensForMatching);
            }
        }
        
        return $result;
    }
    
    /**
     * Совпадение токенов артикулов по статусу
     * 
     * @param Article|integer $article
     * @param Article|integer $articleForMatching
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
     * Найти токены товара
     * 
     * @param \Application\Entity\Goods $good
     * @return object
     */
    public function findGoodsToken($good)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('t')
            ->distinct()
            ->from(Article::class, 'a')    
            ->join('a.articleTokens', 'at')
            ->join(Token::class, 't', 'WITH', 't.lemma = at.lemma')    
            ->where('a.good = ?1')   
            ->andWhere('t.flag = ?2')
            ->andWhere('t.frequency > ?3')    
            ->setParameter('1', $good->getId())
            ->setParameter('2', Token::WHITE_LIST)
            ->setParameter('3', Token::MIN_DF)    
                ;
        
//            var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }

    /**
     * Найти токены товара по типу
     * 
     * @param \Application\Entity\Goods $good
     * @return object
     */
    public function findTokenGoodsByStatus($good)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('t.id, t.lemma, t.status, t.frequency')
            ->distinct()
            ->from(ArticleToken::class, 'at')    
            ->join(Token::class, 't', 'WITH', 't.lemma = at.lemma')
            ->join('at.article', 'a')    
            ->where('a.good = ?1')   
            ->andWhere('(t.status = ?2 or t.status = ?3 or t.status = ?4)')
            ->andWhere('t.flag = ?6')
            ->andWhere('t.frequency > ?7')    
            ->setParameter('1', $good->getId())
            ->setParameter('2', Token::IS_DICT)
            ->setParameter('3', Token::IS_EN_ABBR)
            ->setParameter('4', Token::IS_RU_ABBR)
            ->setParameter('6', Token::WHITE_LIST)
            ->setParameter('7', Token::MIN_DF)
            ->orderBy('t.frequency', 'DESC')    
            ->setMaxResults(Token::MAX_TOKENS_FOR_GROUP)    
            ;
//            var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }
    
    /**
     * Проверить вхождение в группу наименований токенов
     * 
     * @param integer $groupId
     * @param array $tokens
     * 
     * @return TokenGroup|null
     */
    protected function checkTokenGroupByGroupTokens($groupId, $tokens)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('tg')
            ->from(TokenGroup::class, 'tg')    
            ->join('tg.tokens', 't') 
            ->where('tg.id = ?1')    
            ->setParameter('1', $groupId)    
            ;
        
        $orX = $queryBuilder->expr()->orX();
        foreach ($tokens as $token){
            $orX->add($queryBuilder->expr()->eq('t.id', $token['id']));
        }
        $queryBuilder->andWhere($orX);

        return $queryBuilder->getQuery()->getResult();            
        
    }
    
    /**
     * Найди группу наименований по токенам
     * 
     * @param array $tokens
     * 
     * @return TokenGroup|null
     */
    public function findTokenGroupByTokens($tokens)
    {
        if (count($tokens)){
            $entityManager = $this->getEntityManager();

            $queryBuilder = $entityManager->createQueryBuilder();
            $queryBuilder->select('tg.id, count(t.id) as tokenCount')
                ->from(TokenGroup::class, 'tg')    
                ->join('tg.tokens', 't') 
                ->groupBy('tg.id')    
                ->orderBy('tokenCount', 'DESC')
                ;
            
            $orX = $queryBuilder->expr()->orX();
            foreach ($tokens as $token){
                $orX->add($queryBuilder->expr()->eq('t.id', $token['id']));
            }
            $queryBuilder->andWhere($orX);
            
            $data = $queryBuilder->getQuery()->getResult();
            foreach ($data as $row){
                $tokenGroup = $this->checkTokenGroupByGroupTokens($row['id'], $tokens);
                if ($tokenGroup){
                    return $tokenGroup[0];
                }
            }            
        }
        
        return;
    }

    /**
     * Выборка строк прайса для создания групп наименований
     * 
     * @param \Application\Entity\Raw $raw
     * @return array
     */
    public function findGoodTokenForParse($raw)
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
//                ->setMaxResults(100000)
                ;

//var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();        
        
    }
    
    /**
     * Выборка строк прайса для создания групп наименований
     * 
     * @param \Application\Entity\Raw $raw
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
                ->andWhere('r.statusToken != ?3')
                ->andWhere('r.status = ?4')
                ->setParameter('1', $raw->getId())
                ->setParameter('2', Rawprice::GOOD_OK)
                ->setParameter('3', Rawprice::TOKEN_GROUP_PARSED)
                ->setParameter('4', Rawprice::STATUS_PARSED)
//                ->setMaxResults(100000)
                ;

//var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();        
        
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
     * Быстрая вставка наименований товара
     * @param array $row 
     * @return integer
     */
    public function insertGoodToken($row)
    {
        $inserted = $this->getEntityManager()->getConnection()->insert('good_token', $row);
        return $inserted;
    }    

    /**
     * Быстрая обновление наименований товара
     * 
     * @param GoodToken $goodToken
     * @param array $data 
     * @return integer
     */
    public function updateGoodToken($goodToken, $data)
    {
        $inserted = $this->getEntityManager()->getConnection()->update('good_token', $data, ['id' => $goodToken->getId()]);
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
     * Быстрая вставка связи группы наименований и bigram
     * @param array $row 
     * @return integer
     */
    public function insertTokenGroupBigram($row)
    {
        $inserted = $this->getEntityManager()->getConnection()->insert('token_group_bigram', $row);
        return $inserted;
    }    
    
    /**
     * Получить токены группы наименований
     * 
     * @param TokenGroup $tokenGroup
     */
    public function findTokenGroupTokens($tokenGroup)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('t')
                ->from(Token::class, 't')
                ->join('t.tokenGroups', 'tg')
                ->where('tg.id = ?1')
                ->setParameter('1', $tokenGroup->getId())
                ;

        return $queryBuilder->getQuery()->getResult();        
                
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
     * @param TokenGroup $tokenGroup
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
     * 
     * @param TokenGroup $tokenGroup
     */
    public function findTokenGroupGoodName($tokenGroup)
    {

        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g.id as goodId, g.code as goodCode, p.id as producerId, p.name as producerName, '
                . 'group_concat(distinct r.goodname separator \'; \') as goodNames')
                ->from(Goods::class, 'g')
                ->join('g.producer', 'p')
                ->join('g.articles', 'a')
                ->join('a.rawprice', 'r')
                ->where('g.tokenGroup = ?1')
                ->setParameter('1', $tokenGroup->getId())
                ->groupBy('g.id')
                ;
        
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
            ->from(Token::class, 't')
            ->where('t.correct is null')
            ->andWhere('t.frequency <= ?1')
            ->andWhere('t.flag = ?2')    
            ->setParameter('1', 0)    
            ->setParameter('2', Token::WHITE_LIST)    
                ;
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
    }

    /**
     * Найти близкий токен из словаря
     * 
     * @param Application\Entity\Token $token
     * @param integer $dict
     * @param integer $flag 
     */
    public function findNearToken($token, $dict = Token::IS_DICT, $flag = Token::WHITE_LIST)
    {
        if (mb_strlen($token) < 3){
            return [];
        }
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('t')
                ->from(Token::class, 't')
                ->where('t.status = ?1')
                ->andWhere('t.flag = ?2')
                ->andWhere('t.lemma like ?3')
                ->andWhere('t.correct is null')
                ->orderBy('t.lemma')
                ->setParameter('1', $dict)
                ->setParameter('2', $flag)
                ->setParameter('3', $token.'%')
                ->setMaxResults(1)
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
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
        $queryBuilder->select('m')
                ->from(\Application\Entity\MlTitle::class, 'm')
                ;
        return $queryBuilder->getQuery();            
        
    }
    
    /**
     * Получить группы апл соответствующую групе токенов
     * 
     * @param TokenGroup $tokenGroup
     */
    public function getGroupApl($tokenGroup)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('g.groupApl, count(g.id) as goodCount')
                ->from(Goods::class, 'g')
                ->where('g.tokenGroup = ?1')
                ->andWhere('g.groupApl != ?2')
                ->andWhere('g.groupApl != 0')
                ->setParameter('1', $tokenGroup->getId())
                ->setParameter('2', Goods::DEFAULT_GROUP_APL_ID)
                ->groupBy('g.groupApl')
                ->orderBy('goodCount', 'DESC')
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Средняя частота токенов группы
     * 
     * @param TokenGroup $tokenGroup
     * @return int
     */
    public function meanFrequency($tokenGroup)
    {
        $result = [ 'sum' => 0, 'mean' => 0, 'median' => 0, 'mode' => 0, 'sd' => 0];
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('t.frequency')
                ->from(TokenGroup::class, 'g')
                ->join('g.tokens', 't')
                ->where('g.id = ?1')
                ->setParameter('1', $tokenGroup->getId())
                ;
        
        $data = $queryBuilder->getQuery()->getResult(2);
        $frequencies = [];
        foreach ($data as $row){
            $frequencies[] = $row['frequency'];
        }
        $result['sum'] = array_sum($frequencies);
        if (count($frequencies)){
            $result['mean'] = round(\Phpml\Math\Statistic\Mean::arithmetic($frequencies));
            $result['median'] = \Phpml\Math\Statistic\Mean::median($frequencies);
            $result['mode'] = \Phpml\Math\Statistic\Mean::mode($frequencies);
            if (count($frequencies) > 1){
                $result['sd'] = round(\Phpml\Math\Statistic\StandardDeviation::population($frequencies));
            }    
        }    
        
        return $result;
    }
    
    /**
     * Количество товаров с этим токеном
     * @param string $lemma
     */
    public function tokenGoodCount($lemma)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('identity(a.good)')
                ->distinct()
                ->from(ArticleToken::class, 'at')
                ->join('at.article', 'a')
                ->where('at.lemma = ?1')
                ->setParameter('1', $lemma)
                ;
        
        return count($queryBuilder->getQuery()->getResult());
    }
    
    /**
     * Количество групп наименований с этим токеном
     * @param string $lemma
     */
    public function tokenGroupCount($lemma)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('identity(g.tokenGroup)')
                ->distinct()
                ->from(ArticleToken::class, 'at')
                ->join('at.article', 'a')
                ->join('a.good', 'g')
                ->where('at.lemma = ?1')
                ->setParameter('1', $lemma)
                ;
        
        return count($queryBuilder->getQuery()->getResult());
    }
    
    /**
     * Количество токенов в группе токенов
     * @param TokenGroup $tokenGroup
     * 
     * @return integer
     */
    public function tokenGroupTokenCount($tokenGroup)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('at.id)')
                ->from(ArticleToken::class, 'at')
                ->join('at.article', 'a')
                ->join('a.good', 'g')
                ->where('g.tokenGroup = ?1')
                ->setParameter('1', $tokenGroup->getId())
                ;
        
        return count($queryBuilder->getQuery()->getResult());
    }
    
    /**
     * Число вхождений токена в группу токенов
     * @param string $lemma
     * @param TokenGroup $tokenGroup
     * 
     * @return integer
     */
    public function tokenInTokenGroupCount($lemma, $tokenGroup)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('at.id)')
                ->from(ArticleToken::class, 'at')
                ->join('at.article', 'a')
                ->join('a.good', 'g')
                ->where('g.tokenGroup = ?2')
                ->andWhere('at.lemma = ?1')
                ->setParameter('1', $lemma)
                ->setParameter('2', $tokenGroup->getId())
                ;
        
        return count($queryBuilder->getQuery()->getResult());
    }
    
    /**
     * Наименования товара
     * 
     * @param Goods $good
     */
    public function goodTitles($good)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r.goodname')
                ->distinct()
                ->from(Goods::class, 'g')
                ->join('g.articles', 'a')
                ->join('a.rawprice', 'r')
                ->where('g.id = ?1')
                ->andWhere('r.status = ?2')
                ->setParameter('1', $good->getId())
                ->setParameter('2', Rawprice::STATUS_PARSED)
                ;
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Токен наименование группы
     * 
     * @param ArticleTitle $articleTitle
     */
    public function tokenGroupArticleTitle($articleTitle)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('at.id', 'at.lemma')
                ->from(ArticleToken::class, 'at')
                ->join(Token::class, 't', 'WITH', 't.lemma = at.lemma')
                ->where('at.articleTitle = ?1')
                ->andWhere('t.frequency > ?2')
                ->andWhere('t.status in (?3, ?4, ?5)')
                ->andWhere('t.flag = ?6')
                ->setParameter('1', $articleTitle->getId())
                ->setParameter('2', Token::MIN_DF)
                ->setParameter('3', Token::IS_DICT)
                ->setParameter('4', Token::IS_RU_1)
                ->setParameter('5', Token::IS_RU)
                ->setParameter('6', Token::WHITE_LIST)
                ->orderBy('t.frequency', 'DESC')
                ->setMaxResults(Token::MAX_TOKENS_FOR_GROUP)
                ;
        $rows = $queryBuilder->getQuery()->getResult();         
        $result = [];
        foreach ($rows as $row){
            $result[] = $row['lemma'];
        }
        
        return implode('_', $result);
    }   
    
    /**
     * Выбрать наименование для группы токенов
     * 
     * @param Goods $good
     * @return type
     */
    public function choiceGroupTitle($good)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('max(at.tokenGroupTitle) as tokenGroupTitle, at.tokenGroupTitleMd5, count(at.id) as titleCount')
            ->from(Goods::class, 'g')
            ->join('g.articles', 'a')
            ->join(ArticleTitle::class, 'at', 'WITH', 'at.article = a.id')
            ->groupBy('at.tokenGroupTitleMd5')
//            ->having('titleCount > 1')  
            ->orderBy('titleCount', 'DESC')    
            ->where('g.id = ?1')
            ->setParameter('1', $good->getId())
            ->andWhere('at.tokenGroupTitleMd5 != ?2')    
            ->setParameter('2', md5(''))
            //->setMaxResults(1)    
                ;
        
        return $queryBuilder->getQuery()->getResult();    
        
    }
    
    /**
     * Выбор групы наименований по наименованию
     * 
     * @param array $articleTitlesMd5
     */
    public function selectTokenGroupByTitle($articleTitlesMd5)
    {        
        $entityManager = $this->getEntityManager();
        
        if (count($articleTitlesMd5)){

            $queryBuilder = $entityManager->createQueryBuilder();
            
            $orX = $queryBuilder->expr()->orX();
            foreach ($articleTitlesMd5 as $key => $articleTitleMd5){
                $orX->add($queryBuilder->expr()->eq('at.tokenGroupTitleMd5', "?$key"));                
                $queryBuilder->setParameter($key, $articleTitleMd5);
            }

            $queryBuilder->select('identity(at.tokenGroup) as tokenGroupId, count(at.id) as titleCount, tg.goodCount as goodCount')
                    ->from(ArticleTitle::class, 'at')
                    ->where($orX)
                    ->join('at.tokenGroup', 'tg')
                    ->groupBy('at.tokenGroup')
                    ->having('goodCount > :minGoodCount')
                    ->setParameter('minGoodCount', TokenGroup::MIN_GOODCOUNT)
                    ->orderBy('titleCount', 'DESC')
                    ->addOrderBy('goodCount', 'DESC')
                    ->setMaxResults(1)
                    ;

            $row = $queryBuilder->getQuery()->getOneOrNullResult();

            if ($row){
                return $this->getEntityManager()->getRepository(TokenGroup::class)
                        ->findOneById($row['tokenGroupId']);                
            }
        }
        
        return;
    }
    
    /**
     * Родительские группы наименований
     * 
     * @param TokenGroup $tokenGroup
     */
    public function inTokenGroup($tokenGroup)
    {
        $articleTitleCount = $this->getEntityManager()->getRepository(ArticleTitle::class)
                ->count(['tokenGroupTitleMd5' => $tokenGroup->getIds()]);
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('identity(at.tokenGroup) as tokenGroupId, '
                . 'max(tg.lemms)as tokenGroupTitle, '
                . 'count(at.tokenGroupTitleMd5) as inTokenCount, '
                . 'tg.goodCount as inGoodCount')
                ->from(ArticleTitle::class, 'at')
                ->where('at.tokenGroupTitleMd5 = ?1')
                ->setParameter('1', $tokenGroup->getIds())
                ->andWhere('at.tokenGroup != ?2')
                ->andWhere('at.tokenGroup > 0')
                ->setParameter('2', $tokenGroup->getId())
                ->join('at.tokenGroup', 'tg')
                ->groupBy('at.tokenGroup')
                ->orderBy('inGoodCount', 'DESC')
                ->having('inGoodCount > ?3')
                ->setParameter('3', $tokenGroup->getGoodCount())
                ->andHaving('inTokenCount > ?4')
                ->setParameter('4', $articleTitleCount)
                ;
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery()->getResult();                    
    }

    /**
     * Зависимые группы наименований
     * 
     * @param TokenGroup $tokenGroup
     */
    public function outTokenGroup($tokenGroup)
    {
        $articleTitleCount = $this->getEntityManager()->getRepository(ArticleTitle::class)
                ->count(['tokenGroupTitleMd5' => $tokenGroup->getIds()]);

        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('tg.id as tokenGroupId, '
                . 'max(at.tokenGroupTitle) as tokenGroupTitle, '
                . 'count(at.tokenGroupTitleMd5) as outTokenCount'
                . 'tg.goodCount as outGoodCount')
                ->from(ArticleTitle::class, 'at')
                ->where('at.tokenGroup = ?1')
                ->setParameter('1', $tokenGroup->getId())
                ->andWhere('at.tokenGroupTitleMd5 != ?2')
                ->setParameter('2', $tokenGroup->getIds())
                ->join(TokenGroup::class, 'tg', 'WITH', 'tg.ids = at.tokenGroupTitleMd5')
                ->groupBy('at.tokenGroupTitleMd5')
                ->orderBy('outGoodCount', 'DESC')
                ->having('outGoodCount > ?3')
                ->setParameter('3', $tokenGroup->getGoodCount())
                ->andHaving('inTokenCount > ?4')
                ->setParameter('4', $articleTitleCount)
                ;
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery()->getResult();                    
    }
}
