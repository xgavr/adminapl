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
use Search\Entity\SearchLog;
use Application\Entity\Goods;
use Application\Entity\Oem;

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
        
    /**
     * ApiSuppliersPricesResource manager.
     * @var \Api\V1\Rest\ApiSuppliersPrices\ApiSuppliersPricesResource
     */
    private $suppliersPricesManager;
        
    public function __construct($entityManager, $adminManager, $suppliersPrices)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->suppliersPricesManager = $suppliersPrices;
    }
    
    /**
     * Добавить строку лога
     * @param array $data
     */
    public function addSearchLog($data)
    {
        $searchLog = new SearchLog();
        $searchLog->setContent($data['content']);
        $searchLog->setDateCreated(date('Y-m-d H:i:s'));
        $searchLog->setDevice(empty($data['device']) ? null:$data['device']);
        $searchLog->setIpAddress(empty($data['ipAddress']) ? null:$data['ipAddress']);
        $searchLog->setFound($data['found']);
        $searchLog->setSearchTitle(null);
        
        $this->entityManager->persist($searchLog);
        $this->entityManager->flush();
        
        return $searchLog;
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

    /**
     * Поиск по строке
     * @param string $searchStr
     * @param array $params
     * 
     * @return array
     */
    public function searchFromStr($searchStr, $params = null)
    {
        $query = $this->entityManager->getRepository(SearchTitle::class)
                ->queryGoodsBySearchStr($searchStr, $params);

        $page = 1; $limit = 20; $maxLimit = 50;
        
        if (!empty($params['page'])){
            if (is_numeric($params['page'])){
                $page = $params['page']; 
            }
        }
        if (!empty($params['limit'])){
            if (is_numeric($params['limit'])){
                $limit = min($maxLimit, $params['limit']); 
            }
        }
        if ($page) {
            $query->setFirstResult(($page-1) * $limit);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }
        
        $params['total'] = 1;
        $totalQuery = $this->entityManager->getRepository(SearchTitle::class)
                ->queryGoodsBySearchStr($searchStr, $params);
        
        $totalSize = count($totalQuery->getResult(2));
        
        $this->addSearchLog([
            'content' => $searchStr,
            'device' => empty($params['device']) ? null:$params['device'],
            'ipAddress' => empty($params['ip_address']) ? null:$params['ip_address'],
            'found' => $totalSize,
        ]);
//        return $query->getResult(2);
        
        return [
            'data' => [
                'rows' => $query->getResult(2),
                'page' => $page,
                'page_size' => $limit,
                'total_size' => $totalSize,
            ]    
        ];
    }
    
    /**
     * Поиск по oe
     * @param string $oemStr
     * @param array $params
     * 
     * @return array
     */
    public function searchFromOem($oemStr, $params = null)
    {
        $searchParams = array_filter([
            'q' => $oemStr, 
            'accurate' => 4,            
        ]);
        
        $query = $this->entityManager->getRepository(Goods::class)
                ->findAllGoods($searchParams);
        
        $result = $query->getResult(2);
        foreach ($result as $key => $value){
            $result[$key]['opts'] = Goods::optPrices($value['price'], empty($value['meanPrice']) ? 0:$value['meanPrice']);
            $result[$key]['sups'] = $this->suppliersPricesManager->fetch($value['aplId']);
        }
        
        return $result;        
    }
    
    /**
     * Поиск по oe
     * @param string $oemStr
     * @param array $params
     * 
     * @return array
     */
    public function searchFromOe($oemStr, $params = null)
    {
        $searchParams = array_filter([
            'q' => $oemStr, 
            'accurate' => 4,            
        ]);
        
        $query = $this->entityManager->getRepository(Oem::class)
                ->querySearchOem($searchParams);
        
        $result = $query->getResult(2);
        foreach ($result as $key => $value){
            $result[$key]['good']['opts'] = Goods::optPrices($value['good']['price'], empty($value['good']['meanPrice']) ? 0:$value['good']['meanPrice']);
            $result[$key]['good']['sups'] = $this->suppliersPricesManager->fetch($value['good']['aplId']);
        }
        
        return $result;        
    }
}
