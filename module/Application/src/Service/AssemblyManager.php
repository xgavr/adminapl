<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Application\Entity\Token;
use Application\Entity\Article;
use Application\Entity\Raw;
use Application\Entity\Rawprice;

use Phpml\FeatureExtraction\TokenCountVectorizer;
use Application\Filter\NameTokenizer;
use Application\Filter\Lemma;
use Application\Filter\Tokenizer;

/**
 * Description of AssemblyManager
 * Создание карточек товаров
 *
 * @author Daddy
 */
class AssemblyManager
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
     * Проверка строки прайса на возможность создания товара
     * @param Application\Entity\Rawprice $rawprice
     * @return bool
     */
    
    public function checkRawprice($rawprice)
    {
        if (!$rawprice->getCode()) {
            return false;
        }    
        
        if (strlen($rawprice->getCode()->getCode()) < 4) {
            return false;
        }    
        
        if (!$rawprice->getUnknownProducer()) {
            return false;
        }    
        
        if (!$rawprice->getTitle()) {
            return false;
        }    
        
        if (!$rawprice->getRealPrice()) {
            return false;
        }    
        
        if (!$rawprice->getReatRest()) {
            return false;
        }           
        
        return true;
    }
    
    /**
     * Привязка производителя
     * 
     * @param Application\Entity\Rawprice $rawprice
     * 
     * @return Application\Entity\Producer
     */
    public function producer($rawprice)
    {
    }
    
    /**
     * Добавление нового товара из прайса
     * 
     * @param Application\Entity\Rawprice $rawprice
     * @param bool $flush
     */
    public function addNewGoodFromRawprice($rawprice, $flush = true) 
    {
        if (!$this->checkRawprice($rawprice)){
            return;
        }
        
        if ($title){
            $lemmaFilter = new Lemma();
            $tokenFilter = new Tokenizer();
            
            $lemms = $lemmaFilter->filter($tokenFilter->filter($title));
            
            foreach ($lemms as $key => $words){
                $this->addLemms($rawprice, $words, $key, $flush);
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
     * Сьорка товаров по прайсу
     * @param Appllication\Entity\Raw $raw
     */
    public function assemplyGoodFromRaw($raw)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1200);
        $startTime = time();
        
        $rawprices = $this->entityManager->getRepository(Rawprice::class)
                ->findBy(['raw' => $raw->getId(), 'good' => null]);
        
        foreach ($rawprices as $rawprice){
            $this->addNewGoodFromRawprice($rawprice, false);
            if (time() > $startTime + 400){
                $this->entityManager->flush();
                return;
            }
        }
        
        $raw->setParseStage(Raw::STAGE_GOOD_ASSEMBLY);
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
}
