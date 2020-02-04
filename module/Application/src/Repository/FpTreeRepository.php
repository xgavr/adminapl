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
     * @param integer $parentTreeId
     * 
     * @return FpTree|null;
     */
    public function findBanch($token, $parentTreeId = 0)
    {
        if (is_numeric($token)){
            $tokenId = $token;            
        } else {
            $tokenId = $token->getId();            
        }
                
        return $this->getEntityManager()->getRepository(FpTree::class)
                ->findOneBy(['token' => $tokenId, 'parentTree' => $parentTreeId]);
    }    

    /**
     * Добавить ветвь
     * 
     * @param Token $token
     * @param Token|integer $rootToken
     * @param integer $rootTreeId
     * @param integer $parentTreeId
     * 
     * @return null;
     */
    public function addBanch($token, $rootToken = 0, $rootTreeId = 0, $parentTreeId = 0)
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
        
        $fpTree = $this->findBanch($tokenId, $parentTreeId);
        
        if (!$fpTree){
            $this->getEntityManager()->getConnection()->insert('fp_tree', [
                'root_tree_id' => $rootTreeId,
                'root_token_id' => $rootTokenId,
                'token_id' => $tokenId,
                'parent_tree_id' => $parentTreeId,
            ]);           
            $fpTree = $this->findBanch($tokenId, $parentTreeId);
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
     * Очистить деревья и связи
     */
    public function resetFpTree()
    {
        set_time_limit(0);        
        
        $this->getEntityManager()->getConnection()->update('article_token', [
            'fp_tree_id' => 0], ['1' => 1]);                       
        
        $this->getEntityManager()->getConnection()->update('article_title', [
            'fp_tree_id' => 0], ['1' => 1]);                       

        $this->getEntityManager()->getConnection()->delete('fp_tree', ['1' => 1]);                       
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
            $newWay = $way;
            foreach ($way as $key => $lemma){
                if ($counter[$key]['count'] < FpTree::MIN_FREQUENCY){
                    unset($newWay[$key]);
                }
            }       
            if (count($newWay)){
                $newWayStr = implode('_', $newWay);
                if (!isset($result[$newWayStr])){
                    $result[$newWayStr] = $newWayStr.'_'.$token->getLemma();
                }
            }    
        }
        
        return $result;
    }
}
