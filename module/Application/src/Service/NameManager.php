<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Application\Entity\Token;
use Application\Entity\Raw;
use Application\Entity\Rawprice;

use Phpml\FeatureExtraction\TokenCountVectorizer;
use Application\Filter\NameTokenizer;
use Application\Filter\Lemma;
use Application\Filter\Tokenizer;

/**
 * Description of RbService
 *
 * @author Daddy
 */
class NameManager
{
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Разбивает наименование товара на токены
     * 
     * @param Application\Entity\Article $article
     * @return array
     */
    public function tokenArticle($article)
    {
        $titles = [];
        $rawprices = $article->getRawprice();
        foreach ($rawprices as $rawprice){
            if ($rawprice->getStatus() == $rawprice::STATUS_PARSED){
                $titles[] = $rawprice->getTitle();
            }    
        }
        
        if (count($titles)){
            $vectorizer = new TokenCountVectorizer(new NameTokenizer());
            $vectorizer->fit($titles);
            $vacabulary = $vectorizer->getVocabulary();

            $vectorizer->transform($titles);
            //\Zend\Debug\Debug::dump($titles);
            return ['NameTokenizer' => $vacabulary];
        }
        
        return;
    }
    
    /**
     * Добавить новый токен
     * 
     * @param string $word
     * @param bool $flushnow
     */
    public function addToken($data, $flushnow = true)
    {
        
        $word = mb_strcut(trim($data['word']), 0, 64, 'UTF-8');
        
        $token = $this->entityManager->getRepository(Token::class)
                    ->findOneBy(['lemma' => $word]);

        if ($token == null){

            $token = new Token();
            $token->setLemma($word);            
            $token->setStatus($data['status']);            

            // Добавляем сущность в менеджер сущностей.
            $this->entityManager->persist($token);

            // Применяем изменения к базе данных.
            $this->entityManager->flush($token);
        }
        
        return $token;        
    }  
    
    /**
     * Обновить флаг токена
     * 
     * @param Application\Entity\Token $token
     * @param integer $flag
     */
    public function updateTokenFlag($token, $flag)
    {
        $token->setFlag($flag);
        $this->entityManager->persist($token);
        $this->entityManager->flush($token);
    }
    
    /**
     * Добавление нового слова со статусом
     * 
     */
    public function addLemms($rawprice, $lemms, $status, $flush)
    {
        
        if (is_array($lemms)){
            foreach ($lemms as $lemma){
                $token = $this->addToken(['word' => $lemma, 'status' => $status], $flush);
                if ($token){
                    $rawprice->addToken($token);
                }   
            }
        }    
    }
    
    /**
     * Добавление нового слова из прайса
     * 
     * @param Application\Entity\Rawprice $rawprice
     * @param bool $flush
     */
    public function addNewTokenFromRawprice($rawprice, $flush = true) 
    {
        $rawprice->getTokens()->clear();

        $title = $rawprice->getTitle();
        
        if ($title){
            $lemmaFilter = new Lemma();
            $tokenFilter = new Tokenizer();
            
            $lemms = $lemmaFilter->filter($tokenFilter->filter($title));
            
            foreach ($lemms as $key => $words){
                if ($key == Token::IS_RU){
                    foreach ($words as $word){
                        $predictWords = $this->entityManager->getRepository(Token::class)
                               ->findNearToken($word);
                        if (count($predictWords)){
                            foreach($predictWords as $predictWord){
//                                var_dump($predictWord['lemma']); exit;
                                $this->addLemms($rawprice, [$predictWord['lemma']], Token::IS_DICT, $flush);
                            }    
                        } else {
                            $this->addLemms($rawprice, [$word], $key, $flush);
                        }
                    }    
                } else {
                    $this->addLemms($rawprice, $words, $key, $flush);
                }    
            }    
        }  
        
        $rawprice->setStatusToken(Rawprice::TOKEN_PARSED);
        $this->entityManager->persist($rawprice);
        if ($flush){
            $this->entityManager->flush();
        }    
        return;
    }  
    
    /**
     * Выборка токенов из прайса и добавление их в таблицу токенов
     * @param Appllication\Entity\Raw $raw
     */
    public function grabTokenFromRaw($raw)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1200);
        $startTime = time();
        
        $rawprices = $this->entityManager->getRepository(Rawprice::class)
                ->findBy(['raw' => $raw->getId(), 'statusToken' => Rawprice::TOKEN_NEW]);
        
        foreach ($rawprices as $rawprice){
            if ($rawprice->getStatusToken() != $rawprice::TOKEN_PARSED){
                $this->addNewTokenFromRawprice($rawprice, false);
                if (time() > $startTime + 400){
                    $this->entityManager->flush();
                    return;
                }
            }    
        }
        
        $raw->setParseStage(Raw::STAGE_TOKEN_PARSED);
        $this->entityManager->persist($raw);
        
        $this->entityManager->flush();
    }
    
    /**
     * Удаление токена
     * 
     * @param Application\Entity\Token $token
     */
    public function removeToken($token) 
    {   
        $this->entityManager->remove($token);
        
        $this->entityManager->flush($token);
    }    
    
    /**
     * Поиск и удаление токенов не привязаных к строкам прайсов
     */
    public function removeEmptyToken()
    {
        ini_set('memory_limit', '2048M');
        
        $tokenForDelete = $this->entityManager->getRepository(Token::class)
                ->findTokenForDelete();

        foreach ($tokenForDelete as $row){
            $this->removeToken($row[0], false);
        }
        
        $this->entityManager->flush();
        
        return count($tokenForDelete);
    }

    /**
     * Поиск лучшего наименования для товара
     * 
     * @param Application\Entity\Goods $good
     * @return string
     */
    public function findBestName($good)
    {
        $result = '';
        $dict = 0;
        foreach ($good->getRawprice() as $rawprice){
            $dictRu = $rawprice->getDictRuTokens()->count();
            $dictEn = $rawprice->getDictEnTokens()->count();
            if ($dict < (2*$dictRu + $dictEn)){
                $dict = 2*$dictRu + $dictEn;
                $result = $rawprice->getTitle();
            }
        }
        
        return $result;
    }
}
