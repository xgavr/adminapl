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
use Application\Entity\Article;
use Application\Entity\TokenGroup;
use Application\Entity\TokenGroupToken;
use Application\Entity\Goods;
use Application\Entity\Bigram;
use Application\Entity\TokenGroupBigram;

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
                ->from(Goods::class, 'g')
                ->join('g.articles', 'a')
                ->join('a.articleTokens', 'at')
                ->join(Token::class, 't', 'WITH', 't.lemma = at.lemma')
                ->where('g.tokenGroup = ?1')
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
                ->from(Goods::class, 'g')
                ->join('g.articles', 'a')
                ->join('a.articleBigrams', 'ab')
                ->where('g.tokenGroup = ?1')
                ->setParameter('1', $tokenGroup->getId())
                ;
        
        return $queryBuilder->getQuery()->getResult();
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
        $bigrams = $this->selectTokenGroupBigram($tokenGroup);
        if ($bigrams){
            $entityManager = $this->getEntityManager();
            foreach ($bigrams as $row){
                
                $tokenGroupBigram = $entityManager->getRepository(TokenGroupBigram::class)
                        ->findOneBy(['tokenGroup' => $tokenGroup->getId(), 'bigram' => $row['bigramId']]);
                
                if (!$tokenGroupBigram){
                    $entityManager->getConnection()->insert('token_group_bigram', [
                       'token_group_id' => $tokenGroup->getId(),
                        'bigram_id' => $row['bigramId'],
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
        ini_set('memory_limit', '4096M');
        set_time_limit(1800);        
        $startTime = time();
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('tg')
            ->from(TokenGroup::class, 'tg')
            ;    
        
        $query = $queryBuilder->getQuery();
        
        $iterable = $query->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $tokenGroup){        
                $this->updateTokenGroupToken($tokenGroup);
                $this->getEntityManager()->detach($tokenGroup);
                if (time() > $startTime + 1740){
                    return;
                }            
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
        ini_set('memory_limit', '4096M');
        set_time_limit(1800);        
        $startTime = time();
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('tg')
            ->from(TokenGroup::class, 'tg')
            ;    
        
        $query = $queryBuilder->getQuery();
        
        $iterable = $query->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $tokenGroup){        
                $this->updateTokenGroupBigram($tokenGroup);
                $this->getEntityManager()->detach($tokenGroup);
                if (time() > $startTime + 1740){
                    return;
                }            
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
        $queryBuilder->select('count(at.lemma) as tokenCount')
                ->from(Goods::class, 'g')
                ->join('g.articles', 'a')
                ->join('a.articleTokens', 'at')
                ->where('g.tokenGroup = ?1')
                ->andWhere('at.lemma = ?2')
                ->setParameter('1', $tokenGroup->getId())
                ->setParameter('2', $token->getLemma())
                ->groupBy('g.tokenGroup')
                ->addGroupBy('at.lemma')
                ->setMaxResults(1)
                ;
        $row = $queryBuilder->getQuery()->getOneOrNullResult();
        $result = 0;
        if (is_array($row)){
            $result = $row['tokenCount'];
        }
        
        if ($result){
            $entityManager->getConnection()->update('token_group_token', [
                    'frequency' => $result,
                ], [
                    'token_group_id' => $tokenGroup->getId(),
                    'token_id' => $token->getId(),
                ]);
        } else {
            $entityManager->getConnection()->delete('token_group_token', [
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
        $queryBuilder->select('count(at.lemma) as bigramCount')
                ->from(Goods::class, 'g')
                ->join('g.articles', 'a')
                ->join('a.articleBigrams', 'ab')
                ->where('g.tokenGroup = ?1')
                ->andWhere('ab.bigram = ?2')
                ->setParameter('1', $tokenGroup->getId())
                ->setParameter('2', $bigram->getId())
                ->groupBy('g.tokenGroup')
                ->addGroupBy('ab.bigram')
                ->setMaxResults(1)
                ;
        $row = $queryBuilder->getQuery()->getOneOrNullResult();
        $result = 0;
        if (is_array($row)){
            $result = $row['bigramCount'];
        }
        
        if ($result){
            $entityManager->getConnection()->update('token_group_bigram', [
                    'frequency' => $result,
                ], [
                    'token_group_id' => $tokenGroup->getId(),
                    'bigram_id' => $bigram->getId(),
                ]);
        } else {
            $entityManager->getConnection()->delete('token_group_bigram', [
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

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('tgb')
            ->from(TokenGroupBigram::class, 'tgb')
            ;    
        
        $query = $queryBuilder->getQuery();
        
        $iterable = $query->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $tokenGroupBigram){        
                $this->supportTokenGroupBigram($tokenGroupBigram->getTokenGroup(), $tokenGroupBigram->getBigram());
                $this->getEntityManager()->detach($tokenGroupBigram);
                if (time() > $startTime + 1740){
                    return;
                }            
            }
        }
        
        return;
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
