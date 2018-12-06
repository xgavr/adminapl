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
use Application\Entity\Goods;

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
  
    /**
     * Менеджер артикулов
     * 
     * @var Application\Service\ArticleManager 
     */
    private $articleManager;
    
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $articleManager)
    {
        $this->entityManager = $entityManager;
        $this->articleManager = $articleManager;
    }
    
    /**
     * Добавление новой карточки товара
     * 
     * @param string $code
     * @param Application\Entity\Producer $producer
     * @return Goods
     */
    public function addNewGood($code, $producer) 
    {
        // Создаем новую сущность Goods.
        $good = new Goods();
        $good->setCode($code);
        $good->setProducer($producer);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($good);
        
        $this->entityManager->flush($good);
        
        return $good;
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
    
    
    public function tokenIntersect($good)
    {
        
    }
    
    /**
     * Получить все возможные артикулы по строке прайса
     * 
     * @param Apprlication\Entity\Rawprice $rawprice
     * @return array
     */
    public function getArticles($rawprice)
    {
        $code = $rawprice->getCode->getCode();
        
        return $this->entityManager->getRepository(Article::class)
                ->findByCode($code);
    }
    
    /**
     * Получить все возможные неизвестные производители по строке прайса 
     * 
     * @param Application\Entity\Rawprice $rawprice
     * @return array
     */
    public function getUnknownProducers($rawprice)
    {
        $result = [];
        $articles = $this->getArticles($rawprice);
        foreach ($articles as $article){
            $result[] = $artcle->getUnknownProducer();
        }
        return $result;
    }
    
    /**
     * Поиск товара по артикулу
     * 
     * @param type $rawprice
     * @return Application\Entity\Goods|null
     */
    public function findGoodByCode($rawprice)
    {
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findByCode($rawprice->getCode()->getCode());
        
        foreach ($goods as $good){
            
        }
        
        return;
    }
    /**
     * Добавление нового товара из прайса
     * 
     * @param Application\Entity\Rawprice $rawprice
     * @param bool $flush
     */
    public function addNewGoodFromRawprice($rawprice) 
    {
        if (!$this->checkRawprice($rawprice)){
            $rawprise->setStatusGood(Rawprice::GOOD_MISSING_DATA);
            $this->entityManager->persist($rawprice);
            $this->entityManager->flush($rawprice);
            return;
        }
        
        $producer = $rawprice->getUnknownProducer()->getProducer();
        
        if (!$producer){
            
        }
        
        $code = $rawprice->getCode()->getCode();
        $good = $this->entityManager->getRpository(Goods::class)
                ->findOneBy(['code' => $code, 'producer' => $producer->getId()]);
        
        if ($good){
            $rawprice->setGood($good);
            $rawprice->setStatusGood(Rawprice::GOOD_OK);
            $this->entityManager->persist($rawprice);
            $this->entityManager->flush();
        }
        
        return;
    }
    
    /**
     * Данные для анализа и выбора производителя
     * 
     */
    public function trainData($rawprice)
    {
        
    }
            
    
    /**
     * Сборка товаров по прайсу
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
