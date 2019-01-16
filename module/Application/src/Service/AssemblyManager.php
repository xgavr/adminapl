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
        
        if (!$rawprice->getUnknownProducer()) {
            return false;
        }    
        
        if (!$rawprice->getCode()) {
            return false;
        }    

        try{
            $code = $rawprice->getCode()->getCode();
        } catch (\Doctrine\ORM\EntityNotFoundException $ex) {
            return false;
        }
        
        if (strlen($code) < 4 || strlen($code) > 24) {
            return false;
        }    
        
        if (!$rawprice->getTitle()) {
            return false;
        }    
        
        if (!$rawprice->getRealPrice()) {
            return false;
        }    
        
        if (!$rawprice->getRealRest()) {
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
     * 
     * @return integer 
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
     * Сравнение токенов артикула и строки прайса
     * 
     * @param Application\Entity\Article $article
     * @param Application\Entity\Rawprice $rawprice
     * 
     * @return bool
     */
    public function matchingArticleTokens($article, $rawprice)
    {
        //сопоставление токенов
        $tokenIntersect = $this->articleManager->tokenIntersect($article, $rawprice);

        return $tokenIntersect;
    }
    
    
    /**
     * Полное сравнение артикулов
     * 
     * @param Application\Entity\Article $article
     * @param Application\Entity\Article $articleForMatching
     * @return bool
     */
    public function matchingArticles($article, $articleForMatching)
    {
        $result = 0;
        foreach ($articleForMatching->getRawprice() as $rawprice){
            if ($rawprice->getCode()){
                if ($this->matchingArticle($article, $rawprice)){
                    $result += 1;
                } else {
                    $result -= 1;
                }
            }    
        }
        
        return $result >= 0;
    }
    
    /**
     * Сравнение токенов артикулов
     * 
     * @param Application\Entity\Article $article
     * @param Application\Entity\Article $articleForMatching
     * @return bool
     */
    public function matchingArticlesTokens($article, $articleForMatching)
    {
        $result = 0;
        foreach ($articleForMatching->getRawprice() as $rawprice){
            if ($rawprice->getCode()){
                if ($this->matchingArticleTokens($article, $rawprice)){
                    $result += 1;
                } else {
                    $result -= 1;
                }
            }    
        }
        return $result > 0;        
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
    
    public function findArticleByCodeUnknownProducer($code, $unknownProducer)
    {
        return $this->entityManager->getRepository(Article::class)
                ->findOneBy(['code' => $code, 'unknownProducer' => $unknownProducer]);
    }
    
    /**
     * Сравнение пересекающихся производителей
     * 
     * @param Application\Entity\UnknownProducer $unknownProducer
     * @param Application\Entity\UnknownProducer $intersectUnknownProducer
     * @param integer $intersectCountCode
     */
    public function matchingUnknownProducer($unknownProducer, $intersectUnknownProducer, $intersectCountCode)
    {
        $maxCheck = max(UnknownProducer::CHECK_MAX_ROW, $unknownProducer->getSupplierCount() * 2);
        
        $codeRaws = $this->entityManager->getRepository(Producer::class)
                ->intersectesCode($unknownProducer, $intersectUnknownProducer);

        if (!count($codeRaws)){
            return false;
        }

        $result = $i = 0;
        foreach ($codeRaws as $code){
            
            $articleForMatching = $this->findArticleByCodeUnknownProducer($code, $unknownProducer);
            $article = $this->findArticleByCodeUnknownProducer($code, $intersectUnknownProducer);
            
            if ($article && $articleForMatching){
                $intersectResult = $this->entityManager->getRepository(Token::class)
                        ->intersectArticleTokenByStatus($article, $articleForMatching);

                $priceMatching = $this->articleManager->articlePriceMatching($article, $articleForMatching);

                if ($intersectResult && $priceMatching){
                    $result += 1;
                } else {
                    $result -= 1;
                }
            }    

            if ($i > $maxCheck){
                break;
            }
            $i++;
        }

        return $result >= 0;

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
            foreach ($intersects as $intersect){
                $intersectUnknownProducerId = $intersect['unknown_producer_id'];
                $intersectUnknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                        ->findOneById($intersectUnknownProducerId);
                if ($intersectUnknownProducer){                
                    if ($this->matchingUnknownProducer($unknownProducer, $intersectUnknownProducer, $intersect['countCode'])){
                        $producer = $intersectUnknownProducer->getProducer();
                        if ($producer){
                            return $producer;
                        } else {
                            return $this->producerManager->addProducerFromUnknownProducer($intersectUnknownProducer);
                        }
                    }    
                }
                return;
            }    
        }   
        
        return;
    }
    
    /**
     * Проверка записей в прайсах неизвестного производителя
     * 
     * @param Application\Entity\UnknownProducer $unknownProducer
     * @return boolean
     */
    public function checkUnknownProducer($unknownProducer)
    {
        if ($unknownProducer->getRawpriceCount() > max(UnknownProducer::CHECK_MAX_ROW, $unknownProducer->getSupplierCount() * 2)){
            return true;
        }
        
        $result = 0;
        
        foreach ($unknownProducer->getRawprice() as $rawprice){
            if ($this->checkRawprice($rawprice)){
                $result += 1;
            } else {
                $result -= 1;
            }  
        }
        
        return $result > 0;
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
        
        if ($unknownProducer->getSupplierCount() && $unknownProducer->getRawpriceCount() && $unknownProducer->getName()){

            if ($this->checkUnknownProducer($unknownProducer)){
            
                $producer = $this->intersectUnknownProducer($unknownProducer);

                if (!$producer){
                    $producer = $this->producerManager->addProducerFromUnknownProducer($unknownProducer);
                }
            }    
        }

        $this->producerManager->bindUnknownProducer($unknownProducer, $producer);
        
        return $producer;
    }
    
    /**
     * Собрать производителей из строки прайса
     * 
     * @param Application\Entity\Rawprice $rawprice
     */
    public function assemblyProducerFromRawprice($rawprice)
    {
                            
        $this->addProducerFromUnknownProducer($rawprice->getUnknownProducer());
            
        $this->entityManager->getRepository(Rawprice::class)
                ->updateRawpriceAssemblyProducerStatus($rawprice->getRaw(), $rawprice->getUnknownProducer());
                
        return;
    }

    
    /**
     * Собрать производителей из прайса
     * 
     * @param Application\Entity\Raw $raw
     */
    public function assemblyProducerFromRaw($raw)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(900);
                
        $unknownProducers = $this->entityManager->getRepository(UnknownProducer::class)
                ->findUnknownProducerForAssemblyFromRaw($raw);
        
        foreach ($unknownProducers as $unknownProducer){
            
            $this->addProducerFromUnknownProducer($unknownProducer);
            
            $this->entityManager->getRepository(Rawprice::class)
                    ->updateRawpriceAssemblyProducerStatus($raw, $unknownProducer);
        }
        
        $raw->setParseStage(Raw::STAGE_PRODUCER_ASSEMBLY);
        $this->entityManager->persist($raw);
        $this->entityManager->flush($raw);
        
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
            $rawprice->setStatusGood(Rawprice::GOOD_MISSING_DATA);
            $this->entityManager->persist($rawprice);
            $this->entityManager->flush($rawprice);
            return;
        }
        
        $producer = $rawprice->getUnknownProducer()->getProducer();
        
        if ($producer){

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
                $this->entityManager->flush($rawprice);
            }
        }
        return;
    }
    
    /**
     * Сборка товаров по прайсу
     * @param Appllication\Entity\Raw $raw
     */
    public function assemblyGoodFromRaw($raw)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1200);
        $startTime = time();
        
        $rawprices = $this->entityManager->getRepository(Rawprice::class)
                ->findBy(['raw' => $raw->getId(), 'good' => null, 'statusGood' => Rawprice::GOOD_NEW]);
        
        foreach ($rawprices as $rawprice){
            $this->addNewGoodFromRawprice($rawprice);
            if (time() > $startTime + 400){
                return;
            }
        }
        
        $raw->setParseStage(Raw::STAGE_GOOD_ASSEMBLY);
        $this->entityManager->persist($raw);
        
        $this->entityManager->flush();
    }
    
}
