<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Token;
use Application\Entity\FpTree;
use Application\Entity\ArticleTitle;
use Application\Entity\ArticleToken;
use Application\Entity\Article;

/**
 * Description of FpTreeRepository
 *
 * @author Daddy
 */
class FpTreeRepository  extends EntityRepository{

    /**
     * Добавить ветвь
     * 
     * @param Token|integer $token
     * @param Token|integer $rootToken
     * @param integer $rootTree
     * 
     * @return FpTree|null;
     */
    public function findBanch($token, $rootToken = 0, $rootTreeId = 0)
    {
        if (is_numeric($token)){
            $tokenId = $token;            
        } else {
            $tokenId = $token->getId();            
        }
        
        if (is_numeric($rootToken)){
            $rootTokenId = $rootToken;            
        } else {
            $rootTokenId = $rootToken->getId();            
        }
        
        return $this->getEntityManager()->getRepository(FpTree::class)
                ->findOneBy(['rootTree' => $rootTreeId, 'rootToken' => $rootTokenId, 'token' => $tokenId]);
    }    

    /**
     * Добавить ветвь
     * 
     * @param Token $token
     * @param Token|integer $rootToken
     * @param integer $rootTreeId
     * 
     * @return null;
     */
    public function addBanch($token, $rootToken = 0, $rootTreeId = 0)
    {
        if (is_numeric($token)){
            $tokenId = $token;            
        } else {
            $tokenId = $token->getId();            
        }

        if (is_numeric($rootToken)){
            $rootTokenId = $rootToken;            
        } else {
            $rootTokenId = $rootToken->getId();            
        }
        
        $fpTree = $this->findBanch($tokenId, $rootTokenId, $rootTreeId);
        
        if (!$fpTree){
            $this->getEntityManager()->getConnection()->insert('fp_tree', [
                'root_tree_id' => $rootTreeId,
                'root_token_id' => $rootTokenId,
                'token_id' => $tokenId,
            ]);           
            $fpTree = $this->findBanch($tokenId, $rootTokenId, $rootTreeId);
        }
                
        return $fpTree;
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
        
        $rootTokenId = $rootTreeId = 0;
        foreach ($rows as $row){
            $fpTree = $this->addBanch($row['tid'], $rootTokenId, $rootTreeId);
            $rootTokenId = $row['tid'];
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
     * Обработать артикул
     * 
     * @param Article $article
     */
    public function addFromArticle($article)
    {
        $titles = $this->getEntityManager()->getRepository(ArticleTitle::class)
                ->findBy(['article' => $article->getId()]);
        
        foreach ($titles as $title){
            $this->addFromArticleTitle($title);
        }
        
        return;
    }
    
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
     * Найти префиксные пути
     * 
     * @param integer $tokenId
     * @param integer $rootTreeId
     * @param array $ways
     */
    public function prefixWays($tokenId, $rootTreeId = null, $ways = null)
    {
        ini_set('memory_limit', '1024M');
//        set_time_limit(1800);        
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('ft')
            ->from(FpTree::class, 'ft')
            ->where('ft.token = ?1')    
            ->setParameter('1', $tokenId)
            ;  
        
        if ($rootTreeId){
            $queryBuilder->andWhere('ft.rootTree = ?2')
                    ->setParameter('2', $rootTreeId);
        }
        
        $data = $queryBuilder->getQuery()->getResult();
        
        if (!is_array($ways)){
            $ways = [];
        }    
        foreach ($data as $fpTree){
            $ways[$fpTree->getRootTree()][$tokenId] = $fpTree->getToken()->getLemma();
            if ($fpTree->getRootToken() > 0){
                $this->prefixWays($fpTree->getRootToken(), $fpTree->getRootTree(), $ways);
            }
        }
        
        return $ways;                
    }
}
