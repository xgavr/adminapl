<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Search\Service;

use Search\Entity\SearchToken;
use Search\Entity\SearchTitle;
use Application\Filter\Lemma;
use Application\Filter\Tokenizer;
use Application\Entity\Bigram;
use Application\Entity\Token;

/**
 * Description of SearchManager
 * 
 * @author Daddy
 */
class SearchManager {
    
    /**
     * Adapter
     */
    const HTTPS_ADAPTER = 'Laminas\Http\Client\Adapter\Curl';  
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Admin manager.
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;
        
    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
    }
    
    /**
     * Разбить поисковый запрос на леммы
     * 
     * @param string $searchStr
     * @return array;
     */
    public function lemmsFromSearchStr($searchStr)
    {        
        $lemmaFilter = new Lemma($this->entityManager);
        $tokenFilter = new Tokenizer();

        $lemms = $lemmaFilter->filter($tokenFilter->filter($searchStr));
        
        return $lemms;
    }

    /**
     * Добавить токены из строки поиска
     * @param SearchTitle $searchTitle
     */
    public function addSearchTokens($searchTitle)
    {
        $lemms = $this->lemmsFromSearchStr($searchTitle->getTitle());

        $preWord = $preToken = $token = null;
        $k = 0;
        foreach ($lemms as $k => $words){
            foreach ($words as $key => $word){
                if (mb_strlen($word) < 64){
                    $token = $this->entityManager->getRepository(Token::class)
                            ->findOneByLemma($word);
                    if (!$token){
                        $this->entityManager->getRepository(Token::class)
                                ->insertToken([
                                    'lemma' => $word,
                                    'status' => $key,
                                    ]);
                        $token = $this->entityManager->getRepository(Token::class)
                                ->findOneBy(['lemma' => $word]);
                    }    

                    if ($token){
                        $searchToken = $this->entityManager->getRepository(SearchToken::class)
                                ->findOneBy([
                                    'searchTitle' => $searchTitle->getId(),
                                    'lemma' => $token->getLemma(),
                                        ]);

                        if (!$searchToken){
                            $searchToken = new SearchToken();
                            $searchToken->setLemma($token->getLemma());
                            $searchToken->setSearchTitle($searchTitle);

                            $this->entityManager->persist($searchToken);                            
                        }   

                        if ($k > 0){
                            $bigram = $this->entityManager->getRepository(Bigram::class)
                                            ->insertBigram($preWord, $token->getLemma());
                        }
                        $preWord = $token->getLemma();
                        $preToken = $token;
                    }    
                }    
            }    
        }    
        if ($k == 0 && $token){
            $bigram = $this->entityManager->getRepository(Bigram::class)
                            ->insertBigram($token->getLemma(), null, $token->getFlag());
        }
        
        $this->entityManager->flush();
        
        return;
    }            
            
    /**
     * Добавить строку запроса
     * @param string $searchStr
     */
    public function addSearchStr($searchStr)
    {
        $searchTitle = $this->entityManager->getRepository(SearchTitle::class)
                ->findOneBy(['titleMd5' => SearchTitle::titleStrMd5($searchStr)]);
        
        if (!$searchTitle){
            $searchTitle = new SearchTitle();
            $searchTitle->setTitle($searchStr);
            $searchTitle->setDateCreated(date('Y-m-d H:i:s'));
            
            $this->entityManager->persist($searchTitle);
                        
            $this->entityManager->flush();
        }
        
        return $searchTitle;
    }    
}
