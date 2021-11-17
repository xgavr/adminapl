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
use Application\Entity\GenericGroup;
use Application\Entity\Oem;
use Application\Entity\GoodSupplier;
use Application\Entity\Supplier;

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
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    /**
     * Менеджер артикулов
     * 
     * @var \Application\Service\ArticleManager 
     */
    private $articleManager;
    
    /**
     * Менеджер ml
     * 
     * @var \Application\Service\MlManager 
     */
    private $mlManager;

    /**
     * Менеджер producer
     * 
     * @var \Application\Service\ProducerManager 
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
     * @param \Application\Entity\Producer $producer
     * @param \Application\Entity\GenericGroup $zeroGroup
     * @return Goods
     */
    public function addNewGoodFlush($code, $producer, $zeroGroup = null) 
    {
        // Создаем новую сущность Goods.
        $good = new Goods();
        $good->setCode($code);
        $good->setProducer($producer);
        
        $good->setName('');
        $good->setAvailable(Goods::AVAILABLE_TRUE);
        $good->setDescription('');
        $good->setPrice(0);
        $good->setStatusCar(Goods::CAR_FOR_UPDATE);
        $good->setStatusDescription(Goods::DESCRIPTION_FOR_UPDATE);
        $good->setStatusGroup(Goods::GROUP_FOR_UPDATE);
        $good->setStatusImage(Goods::IMAGE_FOR_UPDATE);
        $good->setStatusOem(Goods::OEM_FOR_UPDATE);
        $good->setStatusRawpriceEx(Goods::RAWPRICE_EX_NEW);
        $good->setDateEx(date('Y-m-d H:i:s'));
        $good->setCarCount(0);
        
        if (!$zeroGroup){
            $zeroGroup = $this->entityManager->getRepository(GenericGroup::class)
                    ->findOneByTdId(0);
        }
        
        if ($zeroGroup){
            $good->setGenericGroup($zeroGroup);
        }
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($good);
        
        $this->entityManager->flush($good);
        
        $this->entityManager->getRepository(Oem::class)
                ->addMyCodeAsOe($good);
        
        return $good;
    }   
    
    /**
     * Добавление новой карточки товара
     * 
     * @param string $code
     * @param \Application\Entity\Producer $producer
     * @param \Application\Entity\GenericGroup $zeroGroup
     * @param integer $aplId
     * @param string $name
     * @return Goods
     */
    public function addNewGood($code, $producer, $zeroGroup = null, $aplId = 0, $name = '') 
    {
        // Создаем новую сущность Goods.
        $data = [
            'code' => $code,
            'producer_id' => $producer->getId(),
            'name' => $name,
            'available' => Goods::AVAILABLE_TRUE,
            'description' => $name,
            'price' => 0,
            'status_car' => Goods::CAR_FOR_UPDATE,
            'status_description' => Goods::DESCRIPTION_FOR_UPDATE,
            'status_group' => Goods::GROUP_FOR_UPDATE,
            'status_image' => Goods::IMAGE_FOR_UPDATE,
            'status_oem' => Goods::OEM_FOR_UPDATE,
            'status_rawprice_ex' => Goods::RAWPRICE_EX_NEW,
            'date_ex' => date('Y-m-d H:i:s'),
            'date_price' => date('Y-m-d H:i:s'),
            'car_count' => 0,
            'apl_id' => $aplId,
            'status_oem_ex' => Goods::OEM_EX_NEW,
            'status_attr_ex' => Goods::ATTR_EX_NEW,
            'status_car_ex' => Goods::CAR_EX_NEW,
            'status_img_ex' => Goods::IMG_EX_NEW,
            'status_price_ex' => Goods::PRICE_EX_NEW,
            'status_group_ex' => Goods::GROUP_EX_NEW,
            'min_price' => 0.0,
            'mean_price' => 0.0,
            'fix_price' => 0.0,
            'markup' => 0.0,
            'group_apl' => Goods::DEFAULT_GROUP_APL_ID,
            'token_group_id' => NULL,
            'upd_week' => date('W'),
        ];

        if (!$zeroGroup){
            $zeroGroup = $this->entityManager->getRepository(GenericGroup::class)
                    ->findOneByTdId(0);
        }                
        if ($zeroGroup){
            $data['generic_group_id'] = $zeroGroup->getId();
        }
        
        $this->entityManager->getConnection()->insert('goods', $data);
        $good = $this->entityManager->getRepository(Goods::class)
                ->findOneBy(['code' => $code, 'producer' => $producer->getId()]);
        
        if ($good){
            $this->entityManager->getRepository(Oem::class)
                    ->addMyCodeAsOe($good);
        }    
        
        return $good;
    }   

    /**
     * Добавление новой карточки товара из артикула
     * 
     * @param \Application\Entity\Article $article
     * @param \Application\Entity\Producer $producer
     * @param \Application\Entity\GenericGroup $zeroGroup
     * @return Goods
     */
    public function addNewGoodFromArticle($article, $producer, $zeroGroup = null) 
    {
        // Создаем новую сущность Goods.
        $good = $this->addNewGood($article->getCode(), $producer, $zeroGroup);
        
        $this->entityManager->getRepository(Article::class)
                ->updateArticle($article->getId(), ['good_id' => $good->getId()]);
        
        return $good;
    }   
    
        
    /**
     * Проверка строки прайса на возможность создания товара
     * @param Rawprice $rawprice
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
        
        if ($code == Article::LONG_CODE_NAME) {
            return false;
        }    
        
        
        if (strlen($code) < 3 || strlen($code) > 24) {
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
     * @param Rawprice $rawprice
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
     * @param \Application\Entity\UnknownProducer $unknownProducer
     * @param \Application\Entity\UnknownProducer $intersectUnknownProducer
     * @param integer $intersectCountCode
     */
    public function matchingUnknownProducer($unknownProducer, $intersectUnknownProducer, $intersectCountCode)
    {
        $maxCheck = max(UnknownProducer::CHECK_MAX_ROW, $unknownProducer->getSupplierCount() * UnknownProducer::CHECK_COUNT);
        
        $codeRaws = $this->entityManager->getRepository(Producer::class)
                ->intersectesCode($unknownProducer, $intersectUnknownProducer);

        if (!count($codeRaws)){
            return false;
        }
        
        shuffle($codeRaws);
        
        $nameValidator = new \Application\Validator\NameValidator();
        if ($nameValidator->isValid($unknownProducer->getName(), $intersectUnknownProducer->getName())){
            return true;
        }
        
        $codeRawsCount = count($codeRaws);
        $iPrice = $iIntersect = $i = 0;
        foreach ($codeRaws as $code){
//            var_dump($code);
            $articleForMatching = $this->findArticleByCodeUnknownProducer($code['code'], $unknownProducer);
            $article = $this->findArticleByCodeUnknownProducer($code['code'], $intersectUnknownProducer);
            
            if ($article && $articleForMatching){
                $intersectResult = $this->entityManager->getRepository(Token::class)
                        ->intersectArticleTokenByStatus($article, $articleForMatching);
                
                if ($intersectResult){
                    $iIntersect++;
                }    
                
                $priceMatching = false;

                $meanPrice = $this->articleManager->meanPrice($article);
                $meanPriceForMatching = $this->articleManager->meanPrice($articleForMatching);
                $priceMatching = $this->articleManager->articleMeanPriceMatching($meanPrice, 0, $meanPriceForMatching, 0);
                
//                    var_dump($meanPrice);
//                    var_dump($meanPriceForMatching);
//                    var_dump($priceMatching);

                if ($priceMatching){
                    $iPrice++;
                }
                
                $i++;
            }    

            if ($i > $maxCheck){
                break;
            }
        }

//            var_dump($i);
//            var_dump($iIntersect);
//            var_dump($iPrice);
        $result = ($iIntersect*100/$i) > 50 && ($iPrice*100/$i) > 40;
        return $result;

    }
    
    /**
     * Получить производителя из пересечения неизвестных производителей
     * 
     * @param \Application\Entity\UnknownProducer $unknownProducer
     * @return \Application\Entity\Producer|null
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
//                return;
            }    
        }   
        
        return;
    }
    
    /**
     * Проверка записей в прайсах неизвестного производителя
     * 
     * @param \Application\Entity\UnknownProducer $unknownProducer
     * @return boolean
     */
    public function checkUnknownProducer($unknownProducer)
    {
        if ($unknownProducer->getRawpriceCount() > max(UnknownProducer::CHECK_MAX_ROW, $unknownProducer->getSupplierCount() * UnknownProducer::CHECK_COUNT)){
            return true;
        }
        
        if (date('Y-m-d', strtotime($unknownProducer->getDateCreated().' +5 day')) > date('Y-m-d')){
            return false;
        }

        $result = 0;
        
        $rawprices = $this->entityManager->getRepository(Producer::class)
                ->getRawprices($unknownProducer);
        
        foreach ($rawprices as $rawprice){
            if ($this->checkRawprice($rawprice)){
                $result += 1;
            } else {
//                var_dump($rawprice->getId());
                $result -= 1;
            }  
        }
        
        
        return $result > 0;
    }
    
    /**
     * Создать производителя из неизвестного производителя с проверками
     * 
     * @param \Application\Entity\UnknownProducer $unknownProducer
     * @return \Application\Entity\Producer|null
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
                ->updateRawpriceAssemblyProducerStatus($rawprice);
                
        return;
    }

    
    /**
     * УСТАРЕЛО
     * Собрать производителей из прайса
     * 
     * @param Application\Entity\Raw $raw
     */
    public function assemblyProducerFromRaw_old($raw)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(900);
        $startTime = time();        
        
        $unknownProducers = $this->entityManager->getRepository(UnknownProducer::class)
                ->findUnknownProducerForAssemblyFromRaw($raw);
        
        foreach ($unknownProducers as $unknownProducer){
            
            if (!$unknownProducer->getProducer() || ($unknownProducer->getIntersectUpdateFlag() != UnknownProducer::INTERSECT_UPDATE_FLAG)){            
                $this->addProducerFromUnknownProducer($unknownProducer);
            }    
            
            $rawprices = $this->entityManager->getRepository(Rawprice::class)
                    ->findBy(['raw' => $raw->getId(), 'unknownProducer' => $unknownProducer->getId(), 'status' => Rawprice::STATUS_PARSED]);

            foreach ($rawprices as $rawprice){
                $this->entityManager->getRepository(Rawprice::class)
                    ->updateRawpriceAssemblyProducerStatus($rawprice);                    
                
                if (time() > $startTime + 840){
                    return;
                }            
            }    
        }
        
        $raw->setParseStage(Raw::STAGE_PRODUCER_ASSEMBLY);
        $this->entityManager->persist($raw);
        $this->entityManager->flush($raw);
        
    }
       
    /**
     *
     * Собрать производителей из прайса
     * 
     * @param Application\Entity\Raw $raw
     */
    public function assemblyProducerFromRaw($raw)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(900);
        $startTime = time();        
        
        $rawpricesQuery = $this->entityManager->getRepository(Rawprice::class)
                ->findAllRawprice(['rawId' => $raw->getId(), 'status' => Rawprice::STATUS_PARSED, 'statusProducer' => Rawprice::PRODUCER_NEW]);
        $iterable = $rawpricesQuery->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $rawprice){
                $unknownProducer = $rawprice->getUnknownProducer();
                $article = $rawprice->getCode();
                
                if ($unknownProducer && $article){                
                    if (!$unknownProducer->getProducer() || ($unknownProducer->getIntersectUpdateFlag() != UnknownProducer::INTERSECT_UPDATE_FLAG)){            
                        $this->addProducerFromUnknownProducer($unknownProducer);
                    }    
                }    

                $this->entityManager->getRepository(Rawprice::class)
                    ->updateRawpriceAssemblyProducerStatus($rawprice);                    

                if (time() > $startTime + 840){
                    return;
                }          
                
                $this->entityManager->detach($rawprice);
            }    
        }
        
        $raw->setParseStage(Raw::STAGE_PRODUCER_ASSEMBLY);
        $this->entityManager->persist($raw);
        $this->entityManager->flush($raw);
        
    }

    /**
     * Обработка неполных данных
     * @param \Application\Entity\Rawprice $rawprice
     */
    public function missingData($rawprice)
    {
        $rawprice->setStatusGood(Rawprice::GOOD_MISSING_DATA);
        $rawprice->setCode(null);
        
        $this->entityManager->persist($rawprice);
        $this->entityManager->flush($rawprice);
        
        return;        
    }
    
    /**
     * Добавить зависимость товар-поставщик
     * @param Goods $good
     * @param Rawprice $rawprice
     * @param Supplier $supplier 
     * @param date $priceDate
     */
    private function insertGoodSupplier($good, $rawprice, $supplier, $priceDate)
    {
        $rest = $rawprice->getRealRest();
        if ($rest > 0){
            $goodSupplier = $this->entityManager->getRepository(GoodSupplier::class)
                    ->findOneBy(['good' => $good->getId(), 'supplier' => $supplier->getId()]);
            if ($goodSupplier){
                $this->entityManager->getConnection()
                        ->update('good_supplier', ['rest' => $rest, 'up_date' => $priceDate], ['id' => $goodSupplier->getId()]);
            } else {
                $this->entityManager->getConnection()
                        ->insert('good_supplier', ['good_id' => $good->getId(), 'supplier_id' => $supplier->getId(), 'rest' => $rest, 'up_date' => $priceDate]);
            }        
        }    
        return;
    }
    
    /**
     * Добавление нового товара из прайса
     * 
     * @param \Application\Entity\Rawprice $rawprice
     * @param \Application\Entity\GenericGroup $zeroGroup
     * @param Supplier $supplier
     * @param date $priceDate
     * 
     */
    public function addNewGoodFromRawprice($rawprice, $zeroGroup = null, $supplier = null, $priceDate = null) 
    {
        if (!$this->checkRawprice($rawprice)){
            return $this->missingData($rawprice);
        }
        
        $producer = $rawprice->getUnknownProducer()->getProducer();
        
        if (!$producer){
            return $this->missingData($rawprice);
        }
        
        if ($producer->getStatus() == Producer::STATUS_RETIRED){
            return $this->missingData($rawprice);
        }
        
        if (!$this->checkRawprice($rawprice)){
            return $this->missingData($rawprice);            
        }
        
        if ($producer){

            $article = $rawprice->getCode();
            
            if (!$article){
                return $this->missingData($rawprice);
            }

            $code = $article->getCode();
            $good = $this->entityManager->getRepository(Goods::class)
                    ->findOneBy(['code' => $code, 'producer' => $producer->getId()]);

            if (!$good){
                $good = $this->addNewGoodFromArticle($article, $producer, $zeroGroup);
            } else {
                $this->entityManager->getRepository(Oem::class)
                        ->addMyCodeAsOe($good);               
                $this->entityManager->getRepository(Article::class)
                        ->updateArticle($article->getId(), ['good_id' => $good->getId()]);
                
                $this->entityManager->getRepository(Goods::class)
                        ->updateGood($good, ['statusRawpriceEx' => Goods::RAWPRICE_EX_NEW]);                
            }
            
            if (!$supplier){
                $supplier = $rawprice->getRaw()->getSupplier();
            }
            if (!$priceDate){
                $priceDate = $rawprice->getRaw()->getDateCreated();
            }
            $this->insertGoodSupplier($good, $rawprice, $supplier, $priceDate);
            
            $this->entityManager->getRepository(Rawprice::class)
                    ->updateRawpriceField($rawprice->getId(), ['good_id' => $good->getId(), 'status_good' => Rawprice::GOOD_OK]);
            
            $this->entityManager->detach($good);
            $this->entityManager->detach($article);
        }
        return;
    }
    
    /**
     * Сборка товаров по прайсу
     * @param Raw $raw
     */
    public function assemblyGoodFromRaw($raw)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(900);
        $startTime = time();
        $finishTime = $startTime + 840;
        
        $rawpricesQuery = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForAccembly($raw);

        $zeroGroup = $this->entityManager->getRepository(GenericGroup::class)
                ->findOneByTdId(0);
        $iterable = $rawpricesQuery->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $rawprice){
                $this->addNewGoodFromRawprice($rawprice, $zeroGroup, $raw->getSupplier(), $raw->getDateCreated());
                $this->entityManager->detach($rawprice);
            }    
            if (time() >= $finishTime){
                return;
            }
        }
        
        $this->entityManager->getRepository(Raw::class)
                ->updateRawParseStage($raw, Raw::STAGE_GOOD_ASSEMBLY); 

        return;
    }
    
}
