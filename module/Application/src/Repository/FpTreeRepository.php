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
     * @param Token $token
     * @param Token|integer $rootToken
     * @param integer $rootTree
     * 
     * @return FpTree|null;
     */
    public function findBanch($token, $rootToken = 0, $rootTreeId = 0)
    {
        if (is_numeric($rootToken)){
            $rootTokenId = $rootToken;            
        } else {
            $rootTokenId = $rootToken->getId();            
        }
        
        return $this->getEntityManager()->getRepository(FpTree::class)
                ->findOneBy(['rootTree' => $rootTreeId, 'rootToken' => $rootTokenId, 'token' => $token->getId()]);
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
        if (is_numeric($rootToken)){
            $rootTokenId = $rootToken;            
        } else {
            $rootTokenId = $rootToken->getId();            
        }
        
        $fpTree = $this->findBanch($token, $rootTokenId, $rootTreeId);
        
        if (!$fpTree){
            $this->getEntityManager()->getConnection()->insert('fp_tree', [
                'root_tree_id' => $rootTreeId,
                'root_token_id' => $rootTokenId,
                'token_id' => $token->getId(),
            ]);           
            $fpTree = $this->findBanch($token, $rootTokenId, $rootTreeId);
            if (!$fpTree->getRootTree()){
                $this->getEntityManager()->getConnection()->update('fp_tree', [
                    'root_tree_id' => $fpTree->getId(),
                ], ['id' => $fpTree->getId()]);                           
            }
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
        $queryBuilder->select('t')
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
        $tokens = $queryBuilder->getQuery()->getResult();
        
        $rootTokenId = $rootTreeId = 0;
        foreach ($tokens as $token){
            $fpTree = $this->addBanch($token, $rootTokenId, $rootTreeId);
            $rootTokenId = $token->getId();
            if (!$rootTreeId){
                $rootTreeId = $fpTree->getRootTree();
            }    
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
}
