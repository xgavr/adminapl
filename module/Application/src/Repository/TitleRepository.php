<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Token;
use Application\Entity\ArticleTitle;
use Application\Entity\ArticleToken;
use Application\Entity\ArticleBigram;
use Application\Entity\Article;
use Application\Entity\TokenGroup;
use Application\Entity\TokenGroupToken;
use Application\Entity\Goods;
use Application\Entity\Bigram;
use Application\Entity\TokenGroupBigram;
use Application\Entity\TitleToken;
use Application\Entity\TitleBigram;

/**
 * Description of TitleRepository
 *
 * @author Daddy
 */
class TitleRepository  extends EntityRepository{

    /**
     * Выбрать токены группы 
     * 
     * @param TokenGroup $tokenGroup
     * @return array;
     */
    public function selectTokenGroupToken($tokenGroup)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('t')
                ->from(ArticleToken::class, 'at')
                ->join(Token::class, 't', 'WITH', 'at.lemma = at.lemma')
                ->where('at.tokenGroup = ?1')
                ->setParameter('1', $tokenGroup->getId())
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }    

    /**
     * Обновить токены группы
     * 
     * @param TokenGroup $tokenGroup
     * 
     * @return null;
     */
    public function updateTokenGroupToken($tokenGroup)
    {
        $tokens = $this->selectTokenGroupToken($tokenGroup);
        if ($tokens){
            $entityManager = $this->getEntityManager();
            foreach ($tokens as $token){
                
                $tokenGroupToken = $entityManager->getRepository(TokenGroupToken::class)
                        ->findOneBy(['tokenGroup' => $tokenGroup->getId(), 'token' => $token->getId()]);
                
                if (!$tokenGroupToken){
                    $entityManager->getConnection()->insert('token_group_token', [
                       'token_group_id' => $tokenGroup->getId(),
                        'token_id' => $token->getId(),
                        'frequency' => 0,
                    ]);
                }    
            }
        }
        
        return;
    }
    
    /**
     * Выбрать биграммы группы 
     * 
     * @param TokenGroup $tokenGroup
     * @return array;
     */
    public function selectTokenGroupBigram($tokenGroup)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('identity(ab.bigram) as bigramId')
                ->from(ArticleBigram::class, 'ab')
                ->where('ab.tokenGroup = ?1')
                ->setParameter('1', $tokenGroup->getId())
                ;
        
        return $queryBuilder->getQuery();
    }    

    /**
     * Обновить биграмы группы
     * 
     * @param TokenGroup $tokenGroup
     * 
     * @return null;
     */
    public function updateTokenGroupBigram($tokenGroup)
    {
        $entityManager = $this->getEntityManager();

        $bigramsQuery = $this->selectTokenGroupBigram($tokenGroup);
        $iterable = $bigramsQuery->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $bigram){        
                $tokenGroupBigram = $entityManager->getRepository(TokenGroupBigram::class)
                        ->findOneBy(['tokenGroup' => $tokenGroup->getId(), 'bigram' => $bigram['bigramId']]);

                if (!$tokenGroupBigram){
                    $entityManager->getConnection()->insert('token_group_bigram', [
                       'token_group_id' => $tokenGroup->getId(),
                        'bigram_id' => $bigram['bigramId'],
                        'frequency' => 0,
                    ]);
                }    
            }    
        }
        
        return;
    }

    /**
     * Обновить токены по всем группам
     * 
     */
    public function fillTokenGroupToken()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1800);        
        $startTime = time();
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('identity(at.tokenGroup) as tokenGroupId, at.lemma as lemma')
            ->distinct()    
            ->from(ArticleToken::class, 'at')
            ;    
        
        $query = $queryBuilder->getQuery();
        $iterable = $query->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $tokenGroupLemma){ 
                if ($tokenGroupLemma['tokenGroupId']){
                    $token = $entityManager->getRepository(Token::class)
                            ->findOneByLemma($tokenGroupLemma['lemma']);

                    if ($token){
                        $tokenGroupToken = $entityManager->getRepository(TokenGroupToken::class)
                                ->findOneBy(['tokenGroup' => $tokenGroupLemma['tokenGroupId'], 'token' => $token->getId()]);

                        if (!$tokenGroupToken){
                            $entityManager->getConnection()->insert('token_group_token', [
                               'token_group_id' => $tokenGroupLemma['tokenGroupId'],
                                'token_id' => $token->getId(),
                                'frequency' => 0,
                            ]);
                        }    

                        $entityManager->detach($token);
                    }    
                }    
            }
            if (time() > $startTime + 1740){
                return;
            }            
        }
        
        return;
    }

    /**
     * Обновить биграмы по всем группам
     * 
     */
    public function fillTokenGroupBigram()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1800);        
        $startTime = time();
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('identity(ab.tokenGroup) as tokenGroupId, identity(ab.bigram) as bigramId')
            ->distinct()    
            ->from(ArticleBigram::class, 'ab')
            ;    
        
        $query = $queryBuilder->getQuery();
        $iterable = $query->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $tokenGroupBigramId){ 
                if ($tokenGroupBigramId['tokenGroupId']){
                    $tokenGroupBigram = $entityManager->getRepository(TokenGroupBigram::class)
                            ->findOneBy(['tokenGroup' => $tokenGroupBigramId['tokenGroupId'], 'bigram' => $tokenGroupBigramId['bigramId']]);

                    if (!$tokenGroupBigram){
                        $entityManager->getConnection()->insert('token_group_bigram', [
                           'token_group_id' => $tokenGroupBigramId['tokenGroupId'],
                            'bigram_id' => $tokenGroupBigramId['bigramId'],
                            'frequency' => 0,
                        ]);
                    }    
                }    
            }
            if (time() > $startTime + 1740){
                return;
            }            
        }
        
        return;
    }
    
    /**
     * Поддержка токена в группе наименований
     * 
     * @param TokenGroup $tokenGroup
     * @param Token $token
     */
    public function supportTokenGroupToken($tokenGroup, $token)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('count(at.id) as lemmaCount')
                ->from(ArticleToken::class, 'at')
                ->where('at.tokenGroup = ?1')
                ->andWhere('at.lemma = ?2')
                ->setParameter('1', $tokenGroup->getId())
                ->setParameter('2', $token->getLemma())
                ->groupBy('at.tokenGroup')
                ->setMaxResults(1)
                ;

        $row = $queryBuilder->getQuery()->getResult();
        $result = $row['lemmaCount'];
        
        if ($result === 0){
            $entityManager->getConnection()->delete('token_group_token', [
                    'token_group_id' => $tokenGroup->getId(),
                    'token_id' => $token->getId(),
                ]);            
        } else {    
            $entityManager->getConnection()->update('token_group_token', [
                    'frequency' => $result,
                ], [
                    'token_group_id' => $tokenGroup->getId(),
                    'token_id' => $token->getId(),
                ]);
        }    
        
        return;
    }
    
    /**
     * Обновить поддержку по всем записям
     * 
     */
    public function supportTokenGroupTokens()
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(1800);        
        $startTime = time();
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('tgt')
            ->from(TokenGroupToken::class, 'tgt')
            ;    
        
        $query = $queryBuilder->getQuery();
        
        $iterable = $query->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $tokenGroupToken){        
                $this->supportTokenGroupToken($tokenGroupToken->getTokenGroup(), $tokenGroupToken->getToken());
                $this->getEntityManager()->detach($tokenGroupToken);
                if (time() > $startTime + 1740){
                    return;
                }            
            }
        }
        
        return;
    }    

    /**
     * Поддержка биграма в группе наименований
     * 
     * @param TokenGroup $tokenGroup
     * @param Bigram $bigram
     */
    public function supportTokenGroupBigram($tokenGroup, $bigram)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('count(ab.id) as bigramCount')
                ->from(ArticleBigram::class, 'ab')
                ->where('ab.tokenGroup = ?1')
                ->andWhere('ab.bigram = ?2')
                ->setParameter('1', $tokenGroup->getId())
                ->setParameter('2', $bigram->getId())
                ->groupBy('ab.tokenGroup')
                ->setMaxResults(1)
                ;
        
        $row = $queryBuilder->getQuery()->getOneOrNullResult();
        $result = $row['bigramCount'];
        
        if ($result === 0){
            $entityManager->getConnection()->delete('token_group_bigram', [
                    'token_group_id' => $tokenGroup->getId(),
                    'bigram_id' => $bigram->getId(),
                ]);            
        } else {
            $entityManager->getConnection()->update('token_group_bigram', [
                    'frequency' => $result,
                ], [
                    'token_group_id' => $tokenGroup->getId(),
                    'bigram_id' => $bigram->getId(),
                ]);
        }    
        
        return;
    }

    /**
     * Обновить поддержку по всем записям
     * 
     */
    public function supportTokenGroupBigrams()
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(1800);        
        $startTime = time();
        
        $entityManager = $this->getEntityManager();
        
        $entityManager->getConnection()->update('token_group_bigram', ['frequency' => 0], [1=>1]);

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('identity(ab.tokenGroup) as tokenGroupId, '
                . 'identity(ab.bigram) as bigramId,'
                . 'count(ab.id) as abCount')
            ->from(ArticleBigram::class, 'ab')
            ->groupBy('ab.tokenGroup')
            ->addGroupBy('ab.bigram')    
            ;    
        
        $rows = $queryBuilder->getQuery()->getResult();
        
        foreach ($rows as $row){        
            $entityManager->getConnection()->update('token_group_bigram', 
                    ['frequency' => $row['abCount']], 
                    ['token_group_id' => $row['tokenGroupId'], 'bigram_id' => $row['bigramId']]);
            if (time() > $startTime + 1740){
                return;
            }            
        }
        
        return;
    }    
    
    /**
     * Выбрать токены группы
     * 
     * @param TokenGroup $tokenGroup
     * @param array $params
     */
    public function findTokenGroupToken($tokenGroup, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('at.lemma, count(at.id) as tokenCount')
                ->from(ArticleToken::class, 'at')
                ->where('at.tokenGroup = ?1')
                ->setParameter('1', $tokenGroup->getId())
                ->groupBy('at.lemma')
                ->having('tokenCount > ?2')
                ->andHaving('tokenCount < ?3')
                ->setParameter('2', Token::MIN_DF)
                ->setParameter('3', $tokenGroup->getGoodCount())
                ->orderBy('tokenCount', 'DESC')
                ;
        
        if (is_array($params)){
            if (isset($params['status'])){
                $queryBuilder->andWhere('at.status = ?4')
                        ->setParameter('4', $params['status']);
            }
        }
        
        return $queryBuilder->getQuery();
    }

    /**
     * Выбрать биграмы группы
     * 
     * @param TokenGroup $tokenGroup
     * @param array $params
     */
    public function findTokenGroupBigram($tokenGroup, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('b.bilemma, b.id as bigramId, count(ab.id) as bigramCount')
                ->from(ArticleBigram::class, 'ab')
                ->join('ab.bigram', 'b')
                ->where('ab.tokenGroup = ?1')
                ->setParameter('1', $tokenGroup->getId())
                ->groupBy('ab.bigram')
                ->having('bigramCount > ?2')
                ->andHaving('bigramCount < ?3')
                ->setParameter('2', Bigram::MIN_FREQUENCY)
                ->setParameter('3', $tokenGroup->getGoodCount())
                ->orderBy('bigramCount', 'DESC')
                ;
        if (is_array($params)){
            if (isset($params['status'])){
                $queryBuilder->andWhere('b.status = ?4')
                        ->setParameter('4', $params['status']);
            }
        }
        
        return $queryBuilder->getQuery();
    }

    /**
     * Обработать наименование артикула
     * 
     * @param ArticleTitle $articleTitle
     */
    public function addFromArticleTitle($articleTitle)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('t.id as tid, at.id as atid')
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
        
        $rootTokenId = $rootTreeId = $parentTreeId = 0;
        foreach ($rows as $row){
            $fpTree = $this->addBanch($row['tid'], $rootTokenId, $rootTreeId, $parentTreeId);
            $rootTokenId = $row['tid'];
            $parentTreeId = $fpTree->getId();
            if (!$rootTreeId){
                $rootTreeId = $fpTree->getId();
            }    
            $this->getEntityManager()->getConnection()->update('article_token', [
                'fp_tree_id' => $fpTree->getId(),
            ], ['id' => $row['atid']]);                       
        }
        
        if ($rootTreeId > 0){
            $this->getEntityManager()->getConnection()->update('article_title', [
                'fp_tree_id' => $rootTreeId,
            ], ['id' => $articleTitle->getId()]);                       
        }
        
        return;
    }
    
    
    /**
     * Обновить выводимое наименование токена
     * 
     * @param TokenGroup $tokenGroup
     * @param Token $token
     * @param string $titleMd5
     * @param string $displayLemma
     * @return null
     */
    public function updateTitleToken($tokenGroup, $token, $titleMd5, $displayLemma = null)
    {
        $entityManager = $this->getEntityManager();
        $titleToken = $entityManager->getRepository(TitleToken::class)
                ->findOneBy(['tokenGroup' => $tokenGroup->getId(), 'token' => $token->getId(), 'titleMd5' => $titleMd5]);
        
        if ($titleToken && $displayLemma){
            $entityManager->getConnection()->update('title_token', ['display_lemma' => $displayLemma], ['id' => $titleToken->getId()]);           
        }
        if ($titleToken && !$displayLemma){
            $entityManager->getConnection()->delete('title_token', ['id' => $titleToken->getId()]);           
        }
        
        if (!$titleToken && $displayLemma){
            $entityManager->getConnection()->insert('title_token', 
                    [
                        'group_id' => $tokenGroup->getId(),
                        'token_id' => $token->getId(),
                        'title_md5' => $titleMd5,
                        'display_lemma' => $displayLemma,
                    ]);
        }
        
        return;
    }
    
    /**
     * Обновить выводимое наименование токенов
     * 
     * @param TokenGroup $tokenGroup
     * @param Token $token
     * @param string $displayLemma
     * @return null
     */
    public function updateTitleTokens($tokenGroup, $token, $displayLemma = null)
    {
        $entityManager = $this->getEntityManager();
        $articleTokens = $entityManager->getRepository(ArticleToken::class)
                ->findBy(['tokenGroup' => $tokenGroup->getId(), 'lemma' => $token->getLemma()]);
        
        foreach($articleTokens as $articleToken){
            $this->updateTitleToken($tokenGroup, $token, $articleToken->getArticleTitle()->getTokenGroupTitleMd5(), $displayLemma);
        }
    }   

    /**
     * Обновить выводимое наименование биграм
     * 
     * @param TokenGroup $tokenGroup
     * @param Bigram $bigram
     * @param string $titleMd5
     * @param string $displayBilemma
     * @return null
     */
    public function updateTitleBigram($tokenGroup, $bigram, $titleMd5, $displayBilemma = null)
    {
        $entityManager = $this->getEntityManager();
        $titleBigram = $entityManager->getRepository(TitleBigram::class)
                ->findOneBy(['tokenGroup' => $tokenGroup->getId(), 'bigram' => $bigram->getId(), 'titleMd5' => $titleMd5]);
        
        if ($titleBigram && $displayBilemma){
            $entityManager->getConnection()->update('title_bigram', ['display_bilemma' => $displayBilemma], ['id' => $titleBigram->getId()]);           
        }
        if ($titleBigram && !$displayBilemma){
            $entityManager->getConnection()->delete('title_bigram', ['id' => $titleBigram->getId()]);           
        }
        
        if (!$titleBigram && $displayBilemma){
            $entityManager->getConnection()->insert('title_bigram', 
                    [
                        'group_id' => $tokenGroup->getId(),
                        'bigram_id' => $bigram->getId(),
                        'title_md5' => $titleMd5,
                        'display_bilemma' => $displayBilemma,
                    ]);
        }
        
        return;
    }
    
    /**
     * Обновить выводимое наименование биграм
     * 
     * @param TokenGroup $tokenGroup
     * @param Bigram $bigram
     * @param string $displayBilemma
     * @return null
     */
    public function updateTitleBigrams($tokenGroup, $bigram, $displayBilemma = null)
    {
        $entityManager = $this->getEntityManager();
        $articleBigrams = $entityManager->getRepository(ArticleBigram::class)
                ->findBy(['tokenGroup' => $tokenGroup->getId(), 'bigram' => $bigram->getId()]);
        
        foreach($articleBigrams as $articleBigram){
            $this->updateTitleBigram($tokenGroup, $bigram, $articleBigram->getArticleTitle()->getTokenGroupTitleMd5(), $displayBilemma);
        }
    }   

    /**
     * Поддержка токенов наименования
     * 
     * @param TitleToken $titleToken
     */
    public function supportTitleToken($titleToken)
    {
        $entityManager = $this->getEntityManager();
        
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(at.lemma) as tokenCount')
            ->from(ArticleTitle::class, 'ati')                
            ->where('ati.tokenGroupTitleMd5 = ?1')
            ->setParameter('1', $titleToken->getTitleMd5())
            ->andWhere('ati.tokenGroup = ?2')    
            ->setParameter('2', $titleToken->getTokenGroup()->getId())
            ->join('ati.articleTokens', 'at')    
            ->andWhere('at.lemma = ?3')    
            ->setParameter('3', $titleToken->getToken()->getLemma())    
            ->groupBy('ati.tokenGroupTitleMd5')    
            ;    
        
        $articleTokens = $queryBuilder->getQuery()->getOneOrNullResult(); 
        
        $articleTokenCount = 0;
        if ($articleTokens){
            $articleTokenCount = $articleTokens['tokenCount'];
        }
                
        $entityManager->getConnection()->update('title_token', ['frequency' => $articleTokenCount], ['id' => $titleToken->getId()]);
    }
    
    /**
     * Обновить поддержку всех токенов наименований
     */
    public function supporTitleTokens()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(1800);        
        $startTime = time();

        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('tt')
            ->from(TitleToken::class, 'tt')
            ;    
        
        $query = $queryBuilder->getQuery();
        
        $iterable = $query->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $titleToken){                
                $this->supportTitleToken($titleToken);
                if (time() > $startTime + 1740){
                    return;
                }            
                $entityManager->detach($titleToken);
            }    
        }
        
        return;
    }   

    /**
     * Поддержка биграм наименования
     * 
     * @param TitleBigram $titleBigram
     */
    public function supportTitleBigram($titleBigram)
    {
        $entityManager = $this->getEntityManager();
        
        $articleBigramCount = $entityManager->getRepository(ArticleBigram::class)
                ->count([
                    'tokenGroup' => $titleBigram->getTokenGroup()->getId(),
                    'bigram' => $titleBigram->getBigram()->getId(),
                    'titleMd5' => $titleBigram->getTitleMd5(),
                ]);
        $entityManager->getConnection()->update('title_bigram', ['frequency' => $articleBigramCount], ['id' => $titleBigram->getId()]);
    }
    
    /**
     * Обновить поддержку всех бтграм наименований
     */
    public function supporTitleBigrams()
    {
        $entityManager = $this->getEntityManager();
        $titleBigrams = $entityManager->getRepository(TitleBigram::class)
                ->findBy([]);
        
        foreach($titleBigrams as $titleBigram){
            $this->supportTitleBigram($titleBigram);
        }
        
        return;
    }   

    /**
     * Токены товара
     * 
     * @param Goods $good
     */
    public function goodTitleToken($good)
    {
        if ($good->getTokenGroup()){
            $entityManager = $this->getEntityManager();
            $queryBuilder = $entityManager->createQueryBuilder();

            $queryBuilder->select('tt')
                ->from(TitleToken::class, 'tt')
                ->where('tt.tokenGroup = ?1')
                ->setParameter('1', $good->getTokenGroup()->getId())    
                ->join(ArticleTitle::class, 'at', 'WITH', 'at.tokenGroupTitleMd5 = tt.titleMd5')
                ->join('at.article', 'a')
                ->andWhere('a.good = ?2')
                ->setParameter('2', $good->getId())                
                ;    

            return $queryBuilder->getQuery()->getResult();       
        }
        
        return;
    }
    
    
    ////////////////////////////////////////////////////////////////////////////
    /**
     * Заполнить по всем наименованиям артикулов
     * 
     */
    public function fillFromArticles()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(1800);        
        $startTime = time();
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('at')
            ->from(ArticleTitle::class, 'at')
            ->where('at.fpTree = 0')    
            ;    
        
        $query = $queryBuilder->getQuery();
        
        $iterable = $query->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $articleTitle){        
                $this->addFromArticleTitle($articleTitle);
                $this->getEntityManager()->detach($articleTitle);
                if (time() > $startTime + 1740){
                    return;
                }            
            }
        }
        
        return;
    }
    
    /**
     * Количество поддержек ветви
     * 
     * @param FpTree $fpTree
     */
    public function suppportCount($fpTree)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('at.id')
            ->from(ArticleToken::class, 'at')
            ->where('at.fpTree = ?1')    
            ->setParameter('1', $fpTree->getId())
            ;    
        
        $this->getEntityManager()->getConnection()->update('fp_tree', [
            'frequency' => count($queryBuilder->getQuery()->getResult()),
            ], ['id' => $fpTree->getId()]);
        
        return;        
    }
    
    public function updateSupportCount()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(1800);        
        $startTime = time();
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('f')
            ->from(FpTree::class, 'f')
            ;    
        
        $query = $queryBuilder->getQuery();
        
        $iterable = $query->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $fpTree){        
                $this->suppportCount($fpTree);
                $this->getEntityManager()->detach($fpTree);
                if (time() > $startTime + 1740){
                    return;
                }            
            }
        }
        
        return;        
    }
    
    /**
     * Удалить узел и потомки
     * 
     * @param FpTree $fpTree
     */
    public function recursiveDelete($fpTree)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('f')
            ->from(FpTree::class, 'f')
            ->where('f.parentTree = ?1')
            ->setParameter('1', $fpTree->getId())    
            ;    
        
        $data = $queryBuilder->getQuery()->getResult();
        
        foreach ($data as $row){
            $this->recursiveDelete($row);
        }

        $this->getEntityManager()->getConnection()->delete('fp_tree', ['id' => $fpTree->getId()]);                               
    }
    
    /**
     * 
     * Удаление пустых узлов
     */
    public function deleteEmpty()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(1800);        
        $startTime = time();

        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('f')
            ->from(FpTree::class, 'f')
            ->where('f.frequency = 0')
            ;    
        
        $query = $queryBuilder->getQuery();
        
        $iterable = $query->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $fpTree){        
                $this->recursiveDelete($fpTree);
                $this->getEntityManager()->detach($fpTree);
                if (time() > $startTime + 1740){
                    return;
                }            
            }
        }        
    }
    
    /**
     * Найти префиксные пути
     * 
     * @param integer $tokenId
     * @param integer $rootTreeId
     * @param array $ways
     */
    public function prefixWays($tokenId)
    {
//        ini_set('memory_limit', '1024M');
//        set_time_limit(1800);        
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('ft')
            ->from(FpTree::class, 'ft')
            ->where('ft.token = ?1')    
            ->setParameter('1', $tokenId)
            ;  
        
        $data = $queryBuilder->getQuery()->getResult();
        
        $result = [];
        foreach ($data as $row){
            $parentTreeId = $row->getParentTree();
            
//            $way = [$tokenId => $row->getToken()->getLemma()];
            $way = [];
            
            while (true){
                if ($parentTreeId){
                    $fpTree = $this->getEntityManager()->getRepository(FpTree::class)
                            ->findOneBy(['id' => $parentTreeId]);
                    
                    if ($fpTree){
                        $way = [$fpTree->getToken()->getId() => $fpTree->getToken()->getLemma()] + $way;
                        $parentTreeId = $fpTree->getParentTree();
                    } else {
                        break;
                    }                       
                } else {
                    break;
                }                
            }
            
            $result[$row->getId()] = $way;            
        }
        
        return $result;                
    }
    
    /**
     * Условное дерево наименований
     * 
     * @param Token $token
     */
    public function nominalFpTree($token)
    {
        $ways = $this->prefixWays($token->getId());
        $result = [];
        $counter = [];
        
        foreach ($ways as $way){
            foreach ($way as $key => $lemma){
                if (isset($counter[$key])){
                    $counter[$key]['count'] += 1;
                } else {
                    $counter[$key] = ['count' => 1];
                }
            }    
        }
        foreach ($ways as $way){
            $newWay = [];
            foreach ($way as $key => $lemma){
                if ($counter[$key]['count'] > FpTree::MIN_FREQUENCY){
                    $newWay[$key]['count'] = $counter[$key]['count'];
                    $newWay[$key]['lemma'] = $lemma;
                }
            }       
            
            if (count($newWay)){
                usort($newWay, function($a, $b){
                    if ($a['count'] == $b['count']) {
                        return 0;
                    }
                    return ($a['count'] > $b['count']) ? -1 : 1;            
                }); 
                
                $lemms = [];
                foreach ($newWay as $newRow){
                    $lemms[] = $newRow['lemma'];
                }
                
                $newWayStr = implode('_', $lemms);
                if (!isset($result[$newWayStr])){
                    $result[$newWayStr]['name'] = $newWayStr.'_'.$token->getLemma();
                    $result[$newWayStr]['count'] = 1;
                } else {
                    $result[$newWayStr]['count'] += 1;                    
                }                
            }    
        }
        
        usort($result, function($a, $b){
            if ($a['count'] == $b['count']) {
                return 0;
            }
            return ($a['count'] > $b['count']) ? -1 : 1;            
        }); 
        
        return $result;
    }
    
    /**
     * Обновить популярные наборы
     * 
     * @param Token $token
     */
    public function updateFpGroup($token)
    {
        $sets = $this->nominalFpTree($token);  
        
        if (count($sets)){
            foreach ($sets as $set){
                $fpGroup = $this->getEntityManager()->getRepository(FpGroup::class)
                        ->findOneByName($set['name']);
                if ($fpGroup){
                    if ($fpGroup->getFrequency() != $set['count']){
                        $this->getEntityManager()->getConnection()->update('fp_group', [
                            'frequency' => $set['count']], ['id' => $fpGroup->getId()]);                                           
                    }    
                } else {
                    $this->getEntityManager()->getConnection()->insert('fp_group', 
                            [
                                'name' => $set['name'],
                                'frequency' => $set['count'],
                                'token_id' => $token->getId(),
                            ]);                                                               
                }
            }            
        }
        
        return;
    }
    
    /**
     * 
     * Сформировать популярные группы
     */
    public function updateFpGroups()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(1800);        
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('identity(ft.token) as tokenId, sum(ft.frequency) as frequencySum')
            ->from(FpTree::class, 'ft')
            ->groupBy('ft.token')
            ->having('frequencySum > ?1')
            ->orderBy('frequencySum')
            ->setParameter('1', FpTree::MIN_FREQUENCY)    
            ;  
        
        $tokens = $queryBuilder->getQuery()->getResult();

        foreach ($tokens as $row){
            $token = $this->getEntityManager()->getRepository(Token::class)
                    ->findOneById($row['tokenId']);
            if ($token){
                $this->updateFpGroup($token);
            }
        }
        
        return;
    }
    
}
