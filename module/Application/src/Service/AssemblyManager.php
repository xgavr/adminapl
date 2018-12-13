<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Application\Entity\Token;
use Application\Entity\Producer;
use Application\Entity\UnknownProducer;
use Application\Entity\Article;
use Application\Entity\Raw;
use Application\Entity\Rawprice;
use Application\Entity\Goods;

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
    
    /**
     * Менеджер ml
     * 
     * @var Application\Service\MlManager 
     */
    private $mlManager;

    /**
     * Менеджер producer
     * 
     * @var Application\Service\ProducerManager 
     */
    private $producerManager;

    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $articleManager, $mlManager, $producerManager)
    {
        $this->entityManager = $entityManager;
        $this->articleManager = $articleManager;
        $this->mlManager = $mlManager;
        $this->producerManager = $producerManager;
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
        
        $good->setName('');
        $good->setAvailable(Goods::AVAILABLE_TRUE);
        $good->setDescription('');
        $good->setPrice(0);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($good);
        
        $this->entityManager->flush($good);
        
        return $good;
    }   
    
        
    /**
     * Проверка строки прайса на возможность создания товара
     * @param Application\Entity\Rawprice $rawprice
     * @return bool
     */
    
    public function checkRawprice($rawprice)
    {
        $result = true;
        
        if (!$rawprice->getCode()) {
            $result = false;
        }    
        if (strlen($rawprice->getCode()->getCode()) < 4 || strlen($rawprice->getCode()->getCode()) > 24) {
            $result = false;
        }    
        
        if (!$rawprice->getUnknownProducer()) {
            $result = false;
        }    
        
        if (!$rawprice->getTitle()) {
            $result = false;
        }    
        
        if (!$rawprice->getRealPrice()) {
            $result = false;
        }    
        
        if (!$rawprice->getRealRest()) {
            $result = false;
        }       
        
        if (!$result){
            $rawprice->setStatusGood(Rawprice::GOOD_MISSING_DATA);
            $this->entityManager->persist($rawprice);
            $this->entityManager->flush($rawprice);
        }    
        
        return $result;
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
    public function findArticles($rawprice)
    {
        $code = $rawprice->getCode()->getCode();
        
        return $this->entityManager->getRepository(Article::class)
                ->findByCode($code);
    }
    
    /**
     * Получить все возможные неизвестные производители по строке прайса 
     * 
     * @param Application\Entity\Rawprice $rawprice
     * @return array
     */
    public function findUnknownProducers($rawprice)
    {
        $result = [];
        $articles = $this->findArticles($rawprice);        
        foreach ($articles as $article){
            $result[] = $article->getUnknownProducer();
        }
        return $result;
    }
    
    /**
     * Выбрать лучшего неизвестно производителя из списка
     * 
     * @param array $unknownProducers
     * 
     * @return Application\Entity\UnknownProducer
     */
    public function findBestUnknownProducer($unknownProducers)
    {
        $result = $unknownProducers[0];
        foreach ($unknownProducers as $unknownProducer){
            if ($unknownProducer->getRawpriceCount() > $result->getRawpriceCount()){
                $result = $unknownProducer->getRawpriceCount();
            }
        }
        
        return $result;
    }
    
    /**
     * Найти артикулы с известным производителем
     * 
     * @param Applcation\Entity\rawprice $rawprice
     * @return array 
     */
    public function findArticlesProducers($rawprice)
    {
        $result = [];
        $articles = $this->findArticles($rawprice);
        
        foreach ($articles as $article){
            if ($article->getUnknownProducer()->getProducer()){
                $result[] = $article;
            }
        }
    }
    
    /**
     * Сравнение артикула и строки прайса
     * 
     * @param Application\Entity\Article $article
     * @param Application\Entity\Rawprice $rawprice
     */
    public function matchingArticle($article, $rawprice)
    {
        //сопоставление токенов
        $tokenIntersect = $this->articleManager->tokenIntersect($article, $rawprice);
        $oemIntersect = $this->articleManager->oemIntersect($article, $rawprice);
        $priceMatching = $this->articleManager->priceMatching($article, $rawprice);

        return $this->mlManager->matchingRawprice([(int) $tokenIntersect, (int) $priceMatching, (int) count($oemIntersect)>0]);
    }
    
    /**
     * Выбор лучшего артикула
     * 
     * @param Application\Entity\Rawprice $rawprice
     */
    public function findBestArticle($rawprice)
    {
        $articles = $this->findArticles($rawprice);

        if (count($articles) === 1){
            return $rawprice->getCode();
        }
        
        foreach ($articles as $article){
            if ($this->matchingArticle($article, $rawprice)){
                return $article; 
            }
        }
        
        $rawprice->setStatusGood(Rawprice::GOOD_NO_MATCH);
        $this->entityManager->persist($rawprice);
        $this->entityManager->flush($rawprice);

        return;
    }
    
    /**
     * Сравнение пересекающихся производителей
     * 
     * @param Application\Entity\UnknownProducer $intersectUnknownProducer
     * @param Application\Entity\Unknownproducer $unknownProducer
     * @param integer $intersectCountCode
     */
    public function matchingUnknownProducer($unknownProducer, $intersectUnknownProducer, $intersectCountCode)
    {
        if ($intersectCountCode <= 10){
            $codeRaws = $this->entityManager->getRepository(Producer::class)
                    ->intersectesCode($unknownProducer, $intersectUnknownProducer);
            if (!count($codeRaws)){
                return false;
            }
            
            foreach ($codeRaws as $code){
                
            }
        }
        return true;
    }
    
    /**
     * Получить производителя из пересечения неизвестных производителей
     * 
     * @param Application\Entity\UnknownProducer $unknownProducer
     * @return Application\Entity\Producer|null
     */
    public function intersectUnknownProducer($unknownProducer)
    {
        $intersects = $this->entityManager->getRepository(Producer::class)
                        ->unknownProducerIntersect($unknownProducer);

        if (count($intersects)){
            $intersectUnknownProducerId = $intersects[0]['unknown_producer_id'];
            $intersectUnknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                    ->findOneById($intersectUnknownProducerId);
            
            if ($intersectUnknownProducer){                
                if ($this->matchingUnknownProducer($unknownProducer, $intersectUnknownProducer, $intersectCountCode)){
                    $producer = $intersectUnknownProducer->getProducer();
                    if ($producer){
                        return $producer;
                    } else {
                        return $this->addProducerFromUnknownProducer($intersectUnknownProducer);
                    }
                }    
            }
        }   
        
        return;
    }
    
    /**
     * Создать производителя из неизвестного производителя с проверками
     * 
     * @param Application\Entity\UnknownProducer $unknownProducer
     * @return Application\Entity\Producer|null
     */
    public function addProducerFromUnknownProducer($unknownProducer)
    {
        $producer = null;
        
        $unknownProducer->setProducer($producer);
        $this->entityManager->persist($unknownProducer);
        $this->entityManager->flush($unknownProducer);
        
        if ($unknownProducer->getSupplierCount() && $unknownProducer->getRawpriceCount() && $unknownProducer->getName()){
            
            $producer = intersectUnknownProducer($unknownProducer);
            
            if (!$producer){
                $producer = $this->producerManager->addProducerFromUnknownProducer($unknownProducer);
            }
        }
        
        return $producer;
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
            return;
        }
        
        $article = $this->findBestArticle($rawprice);

        if ($article){
            
            $producer = $this->producerManager->addProducerFromArticle($article);

            $code = $rawprice->getCode()->getCode();
            $good = $this->entityManager->getRepository(Goods::class)
                    ->findOneBy(['code' => $code, 'producer' => $producer->getId()]);

            if (!$good){
                $good = $this->addNewGood($code, $producer);
            }

            if ($good){
                $rawprice->setGood($good);
                $rawprice->setStatusGood(Rawprice::GOOD_OK);
                $this->entityManager->persist($rawprice);
                $this->entityManager->flush();
            }
        }
        return;
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
        
//        $rawprices = $this->entityManager->getRepository(Rawprice::class)
//                ->findBy(['raw' => $raw->getId(), 'good' => null, 'statusGood' => Rawprice::GOOD_NEW]);
        $rawprices = $this->entityManager->getRepository(Article::class)
                ->findArticleForAssemblyByRaw($raw);
        
        exit;
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
    
}
