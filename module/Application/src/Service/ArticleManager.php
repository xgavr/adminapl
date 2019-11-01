<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Application\Entity\Article;
use Application\Entity\UnknownProducer;
use Application\Entity\Raw;
use Application\Entity\Rawprice;
use Application\Validator\Sigma3;
use Phpml\Math\Statistic\Mean;
use Phpml\Math\Statistic\StandardDeviation;

/**
 * Description of RbService
 *
 * @author Daddy
 */
class ArticleManager
{
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     *
     * @var \Application\Service\NameManager
     */
    private $nameManager;
  
    /**
     *
     * @var \Application\Service\OemManager
     */
    private $oemManager;
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $nameManager, $oemManager)
    {
        $this->entityManager = $entityManager;
        $this->nameManager = $nameManager;
        $this->oemManager = $oemManager;
    }
    
    /**
     * Добавить новый артикул
     * 
     * @param string $code
     * @param Application\Entity\UnknownProducer $unknownProducer
     * @param bool $flushnow
     */
    public function addArticle($code, $unknownProducer, $flushnow = true)
    {
        $filter = new \Application\Filter\ArticleCode();
        $filteredCode = $filter->filter($code);
        
        $article = $this->entityManager->getRepository(Article::class)
                    ->findOneBy(['code' => $filteredCode, 'unknownProducer' => $unknownProducer->getId()]);

        if ($article == null){
                    
            // Создаем новую сущность UnknownProducer.
            $article = new Article();
            $article->setCode($filteredCode);            
            $article->setFullCode($code);
            $article->setUnknownProducer($unknownProducer);

            // Добавляем сущность в менеджер сущностей.
            $this->entityManager->persist($article);

            // Применяем изменения к базе данных.
            $this->entityManager->flush($article);
        } else {
            if (mb_strlen($article->getFullCode()) < mb_strlen(trim($code))){
                $article->setFullCode(trim($code));                
                $this->entityManager->persist($article);
                if ($flushnow){
                    $this->entityManager->flush($article);
                }    
            }
        }  
        
        $this->updatePriceRest($article);
        
        return $article;        
    }        
    
    /**
     * Добавление нового артикула из прайса
     * 
     * @param Application\Entity\Article $rawprice
     * @param bool $flush
     */
    public function addNewArticleFromRawprice($rawprice, $flush = true) 
    {
        if ($rawprice->getUnknownProducer()){
            $article = $this->addArticle($rawprice->getArticle(), $rawprice->getUnknownProducer(), $flush);

            if ($article){

                $rawprice->setCode($article);
                $this->entityManager->persist($rawprice);

                if ($flush){
                    $this->entityManager->flush();
                }    
            }   
        }    
        
        return;
    }  
    
    /**
     * Обновление средней ценыи остатка артикула
     * @param \Application\Entity\Article $article
     * @return null
     */
    public function updatePriceRest($article)
    {        

        $priceSum = 0;
        $totalRest = 0.0;
        $prices = [];
        
        $rawprices = $this->entityManager->getRepository(Rawprice::class)
                ->findByCode($article->getId());
        
        foreach($rawprices as $rawprice){
            if ($rawprice->getStatus() == Rawprice::STATUS_PARSED && $rawprice->getRealRest() > 0 && $rawprice->getRealPrice() > 0){
                $rest = min(1000, $rawprice->getRealRest());
                $prices = array_merge($prices, array_fill(0, $rest, $rawprice->getRealPrice()));
                $totalRest += $rest;
            }    
        }
        $meanPrice = 0.0;
        $standartDeviation = 0.0;
        
        if (count($prices)){
            $meanPrice = Mean::arithmetic($prices);
            $standartDeviation = StandardDeviation::population($prices, count($prices) > 1);
        }
        
        $this->entityManager->getRepository(Article::class)
                ->updateArticle($article->getId(), ['mean_price' => $meanPrice, 'standart_deviation' => $standartDeviation, 'total_rest' => $totalRest]);
        
        return;
    }


    /**
     * Выборка артиклей из прайса и добавление их в артиклулы
     * @param Raw $raw
     */
    public function grabArticleFromRaw($raw)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(900);
        $startTime = time();
        $finishTime = $startTime + 840;
        
        $filter = new \Application\Filter\ArticleCode();
        
        $rawpricesQuery = $this->entityManager->getRepository(Rawprice::class)
                ->findCodeRawprice($raw);
        $iterable = $rawpricesQuery->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $rawprice){
                $filteredCode = $filter->filter($rawprice->getArticle());
        
                $article = $this->entityManager->getRepository(Article::class)
                        ->findOneBy(['code' => $filteredCode, 'unknownProducer' => $rawprice->getUnknownProducer()->getId()]);

                if (!$article){
                    $this->entityManager->getRepository(Article::class)
                            ->insertArticle([
                                'code' => $filteredCode,
                                'fullcode' => mb_substr($rawprice->getArticle(), 0, 36),
                                'unknown_producer_id' => $rawprice->getUnknownProducer()->getId(),
                            ]);
                    
                    $article = $this->entityManager->getRepository(Article::class)
                            ->findOneBy(['code' => $filteredCode, 'unknownProducer' => $rawprice->getUnknownProducer()->getId()]);
                }    
                $this->entityManager->getRepository(Article::class)
                        ->updateRawpriceCode($rawprice, $article);
                
                $this->entityManager->detach($rawprice);
            }    
            if (time() >= $finishTime){
                return;
            }
        }    
                
        $oldRaws = $this->entityManager->getRepository(Raw::class)
                ->findPreRetiredRaw($raw);

        foreach ($oldRaws as $oldRaw){

            $oldRaw->setStatus(Raw::STATUS_RETIRED);
            $oldRaw->setStatusEx(Raw::EX_TO_DELETE);
            $this->entityManager->persist($oldRaw);

            $oldRawpriceQuery = $this->entityManager->getRepository(Raw::class)
                    ->findAllRawprice(['rawId' => $oldRaw->getId(), 'status' => Rawprice::STATUS_PARSED]);
            $oldIterable = $oldRawpriceQuery->iterate();
            foreach ($oldIterable as $row){
                foreach ($row as $oldRawprice){
                    $this->entityManager->getRepository(Rawprice::class)
                            ->updateRawpriceField($oldRawprice->getId(), ['status' => Rawprice::STATUS_RETIRED]);
                    $this->entityManager->detach($oldRawprice);
                }
                if (time() >= $finishTime){
                    return;
                }
            }
        }    

        $raw->setParseStage(Raw::STAGE_ARTICLE_PARSED);
        $this->entityManager->persist($raw);
        $this->entityManager->flush();        
    }
    

    /**
     * Удаление артикула
     * 
     * @param Application\Entity\Article $article
     */
    public function removeArticle($article, $flush = true) 
    {   
        
        $this->entityManager->getRepository(Article::class)
                ->deleteOemRaw($article);
        
        $this->entityManager->getRepository(Article::class)
                ->deleteArticleToken($article);
        
        $this->entityManager->getRepository(Article::class)
                ->deleteArticleBigram($article);

        $this->entityManager->getRepository(Article::class)
                ->deleteArticleTitle($article);
        
        $this->entityManager->getRepository(Article::class)
                ->deleteArticleCross($article);
        
        $this->entityManager->remove($article);
        
        if ($flush){
            $this->entityManager->flush();
        }    
    }    
    
    /**
     * Поиск и удаление артикулов не привязаных к строкам прайсов
     */
    public function removeEmptyArticles()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(900);        
        $startTime = time();
        $finishTime = $startTime + 840;
        
        $articlesForDelete = $this->entityManager->getRepository(Article::class)
                ->findArticlesForDelete();

        foreach ($articlesForDelete as $row){
            $this->removeArticle($row[0]);
            if (time() >= $finishTime){
                return;
            }
        }
        
        return;
    }    
    
    /**
     * Строки прайсов артикула
     * @param Article $article
     * @param integer $limit
     * 
     * @return array
     */
    public function articleRawprices($article, $limit = null)
    {
        return $this->entityManager->getRepository(Rawprice::class)
                ->findBy(['code' => $article->getId(), 'status' => Rawprice::STATUS_PARSED], null, $limit);
    }
    
    /**
     * Случайная выборка из прайсов по id артикля и id поставщика 
     * @param array $params
     * @return object      
     */
    public function randRawpriceBy($params)
    {
        return $this->entityManager->getRepository(Article::class)
                ->randRawpriceBy($params);
    }
        
    /**
     * Массив цен из строк прайсов
     * 
     * @param array $rawprices
     * @return array
     */
    public function rawpricesPrices($rawprices)
    {
        $result = [];
        foreach($rawprices as $rawprice){
            if ($rawprice->getStatus() == Rawprice::STATUS_PARSED && $rawprice->getRealRest() > 0 && $rawprice->getRealPrice() > 0){
                $rest = min(1000, $rawprice->getRealRest());
                $result = array_merge($result, array_fill(0, $rest, $rawprice->getRealPrice()));
            }    
        }
        
//        var_dump($result);
        return $result;
    }
    
    /**
     * Массив цен из прайсов артикулов
     * 
     * @param array $articles
     * @return array
     */
    public function articlesPrices($articles)
    {
        $result = [];
        foreach ($articles as $article){
            if (is_numeric($article)){
                $article = $this->entityManager->getRepository(Article::class)
                        ->findOneById($article);
            }
                
            $result = array_merge($result, $this->rawpricesPrices($article->getRawprice()));
        }
        return $result;
    }
    
    /**
     * Средняя цена по строкам прайса
     * 
     * @param array $rawprices
     * @return float
     */
    public function rawpricesMeanPrice($rawprices)
    {
        $prices = $this->rawpricesPrices($rawprices);
        if (count($prices)){
            return Mean::arithmetic($prices);
        }
        
        return 0;    
    }
    
    /**
     * Средняя цена по артикулам
     * 
     * @param array $articles
     * @return float
     */
    public function articlesMeanPrice($articles)
    {
        $prices = $this->articlesPrices($articles);
        if (count($prices)){
            return Mean::arithmetic($prices);
        }        
        return 0;
    }
    
    /**
     * Вычисление средней цены 
     * 
     * @param integer|\Application\Entity\Article $article
     * @return float 
     */
    public function meanPrice($article)
    {
        if (is_numeric($article)){
            $article = $this->entityManager->getRepository(Article::class)
                    ->findOneById($article);
        }
        
        if ($article){
            return $this->rawpricesMeanPrice($article->getRawprice());
        }    
        return 0;
    }
    
    /**
     * Отклонение прайсов
     * 
     * @param array $prices
     */
    public function pricesDeviation($prices)
    {
        if (count($prices)){
            return StandardDeviation::population($prices, count($prices) > 1);
        }
        
        return 0;         
    }
    
    /**
     * Вычисление стандартного отклонения цены 
     * 
     * @param array $rawprices
     * @return float 
     */
    public function rawpricesDeviation($rawprices)
    {
        return $this->pricesDeviation($this->rawpricesPrices($rawprices));
    }
    
    /**
     * Вычисление стандартного отклонения цены 
     * 
     * @param array $articles
     * @return float 
     */
    public function articlesDeviation($articles)
    {
        return $this->pricesDeviation($this->articlesPrices($articles));
    }

    /**
     * УСТАРЕЛО
     * Разброс цены по строкам по набору строк прайса 
     * 
     * @param array $rawprices
     * @return float|null
     */
    public function rawpricesDispersion($rawprices)
    {
        $mean = $this->rawpricesMeanPrice($rawprices);

        $result = [];
        $totalRest = 0;
        foreach($rawprices as $rawprice){
            if ($rawprice->getStatus() == Rawprice::STATUS_PARSED && $rawprice->getRealRest() && $rawprice->getRealPrice()){
                $rest = min(1000, $rawprice->getRealRest());
                $result[] = pow(($rawprice->getRealPrice() - $mean), 2)*$rest;
                $totalRest += $rest;
            }    
        }

        if ($rest){
            return sqrt(array_sum($result)/$totalRest);
        } else {
            return 0;
        } 
        
        return;
    }

    /**
     * Разброс цен строки прайса в артикуле
     * 
     * @param Application\Entity\Article $article
     * @return float
     */
    public function dispersionPrice($article)
    {
        if (is_numeric($article)){
            $article = $this->entityManager->getRepository(Article::class)
                    ->findOneById($article);
        }
        
        if ($article){
//            return $this->rawpricesDispersion($article->getRawprice());
            return $this->rawpricesDeviation($article->getRawprice());
        }
        
        return;
    }
    
    /**
     * Сравнение средних цен артикулов
     * 
     * @param type $articleMeanPrice
     * @param type $articleRest
     * @param type $articleForMatchingMeanPrice
     * @param type $articleForMatchingRest
     * @return type
     */
    public function articleMeanPriceMatching($articleMeanPrice, $articleRest, $articleForMatchingMeanPrice, $articleForMatchingRest)
    {
        if ($articleMeanPrice && $articleForMatchingMeanPrice){
            //$prices = array_merge(array_fill(0, $articleRest, $articleMeanPrice), array_fill(0, $articleForMatchingRest, $articleForMatchingMeanPrice));
            $prices = [$articleMeanPrice, $articleForMatchingMeanPrice];
            if (count($prices)){
                $meanPrice = Mean::arithmetic($prices);
                $dispersion = StandardDeviation::population($prices, count($prices) > 1);
//            var_dump($dispersion);
//            var_dump($meanPrice);

                $validator = new Sigma3();
                return $validator->isValid($articleForMatchingMeanPrice, $meanPrice, $dispersion);
            }
        }    
     
        return false;
    }
    
    /**
     * Проверка цены на попадание в диапазон цен
     * 
     * @param float $price
     * @param float $meanPrice
     * @param float $dispersion
     * 
     * @return bool
     */
    public function inSigma3($price, $meanPrice, $dispersion)
    {
        $validator = new Sigma3();
        
        return $validator->isValid($price, $meanPrice, $dispersion);
    }
    
    
    public function getArticleGood($article)
    {
        foreach ($article->getRawprice() as $rawprice){
            if ($rawprice->getGood()){
                return $rawprice->getGood();
            }
        }
        
        return;
    }
    /**
     * Сравнение цены строки прайса с артикулом
     * 
     * @param Application\Entity\Article $article
     * @param Application\entity\Rawprice $rawprice
     * 
     * @return bool
     */
    public function priceMatching($article, $rawprice)
    {
        $rawprices = [
            $rawprice->getId() => $rawprice,
        ];
        
        foreach ($article->getRawprice() as $articleRawprice){
            $rawprices[$articleRawprice->getId()] = $articleRawprice;
        }
        
        $meanPrice = $this->rawpricesMeanPrice($rawprices);
        $dispersion = $this->rawpricesDeviation($rawprices);
//        var_dump($meanPrice);
//        var_dump($dispersion);
        return $this->inSigma3($rawprice->getRealPrice(), $meanPrice, $dispersion);
    }
    
    /**
     * Сравнение цен артикулов
     * 
     * @param \Application\Entity\Article $article
     * @param \Application\Entity\Article $articleForMatching
     * 
     * @return bool Description
     */
    public function articlePriceMatching($article, $articleForMatching)
    {
        $result = 0;        
        foreach ($articleForMatching->getRawprice() as $rawpriceForMatching){
            if ($rawpriceForMatching->getStatus() == $rawpriceForMatching::STATUS_PARSED){
                if ($this->priceMatching($article, $rawpriceForMatching)){
                    $result += 1;
                } else {
                    $result -= 1;
                }
            }    
        }
        
        return $result > 0;
    }
    
    /**
     * Отстаток по артикулу
     * 
     * @param \Application\Entity\Article|float $article
     * @return float
     */
    public function articleRest($article)
    {
        $result = 0.0;
        if (is_numeric($article)){
            $article = $this->entityManager->getRepository(Article::class)
                    ->findOneById($article);
        }
        
        if ($article){
            foreach($article->getRawprice() as $rawprice){
                if ($rawprice->getStatus() == Rawprice::STATUS_PARSED && $rawprice->getRealRest()>0 && $rawprice->getRealPrice()>0){
                    $rest = min(1000, $rawprice->getRealRest());
                    $result += $rest;
                }    
            }
        }
        
        return $result;
    }
    
    /**
     * Пересечение токенов артикулов
     * 
     * @param \Application\Entity\Article $article
     * @param \Application\Entity\Article $articleForMatching
     * 
     * @return array
     */
    public function articleTokenIntersect($article, $articleForMatching)
    {
        return $this->entityManager->getRepository(\Application\Entity\Token::class)
                ->articleTokenIntersect($article, $articleForMatching);
    }
    
    /**
     * Получить токены списка строк прайса
     * 
     * @param array $rawprices
     * @param integer $rawpriceDiff
     * @return array
     */
    public function getRawpricesTokens($rawprices, $rawpriceDiff = 0)
    {
        $result = [];
        foreach ($rawprices as $rawprice){
            if ($rawprice->getStatus() == $rawprice::STATUS_PARSED && $rawprice->getId() != $rawpriceDiff){
//                if ($rawprice->getStatusToken() != $rawprice::TOKEN_PARSED){
//                    $this->nameManager->addNewTokenFromRawprice($rawprice);
//                    return $this->getRawpricesTokens($rawprices, $rawpriceDiff);
//                }
                foreach ($rawprice->getTokens() as $token){
                    if ($token->isIntersectLemma()){
                        if (array_key_exists($token->getId(), $result)){
                            $result[$token->getId()] += 1;                        
                        } else {
                            $result[$token->getId()] = 1;                        
                        }
                    }    
                }            
            }
        }

        return $result;        
    }
    
    /**
     * Получить токены артикула
     * 
     * @param Application\Entity\Article|integer $article
     * @param integer $rawpriceDiff Исключение
     * 
     * @return array|null
     */
    public function getTokens($article, $rawpriceDiff = 0)
    {
        if (is_numeric($article)){
            $article = $this->entityManager->getRepository(Article::class)
                    ->findOneById($article);
        }
        
        if ($article){
            return $this->getRawpricesTokens($article->getRawprice(), $rawpriceDiff);
        }
        
        return;
    }
    
    /**
     * Сравнить токены списка строк прайсов и строки прайса
     * 
     * @param Doctrine\Common\Collections\ArrayCollection $rawprices
     * @param Application\Entity\Rawprice $rawprice
     * 
     * @return bool
     */
    public function tokenRawpricesIntersect($rawprices, $rawprice)
    {
       $rawpricesTokens = $this->getRawpricesTokens($rawprices, $rawprice->getId());
       
       if (count($rawpricesTokens)){
            
           if ($rawprice->getStatusToken() != $rawprice::TOKEN_PARSED){
                $this->nameManager->addNewTokenFromRawprice($rawprice);
                return $this->tokenRawpricesIntersect($rawprices, $rawprice);
            }
            
            $exclusions = [
                mb_strtoupper($rawprice->getProducer(), 'utf-8'),
                mb_strtoupper($rawprice->getArticle(), 'utf-8'),
                mb_strtoupper($rawprice->getCode()->getCode(), 'utf-8'),
            ];
            

            $rawpriceTokens = [];
            foreach ($rawprice->getTokens() as $token){
                if ($token->isIntersectLemma() && !in_array($token->getLemma(), $exclusions)){
                    if (array_key_exists($token->getId(), $rawpriceTokens)){
                        $rawpriceTokens[$token->getId()] += 1;                        
                    } else {
                        $rawpriceTokens[$token->getId()] = 1;                        
                    }
                }    
            }
            
            $inersect = array_intersect_key($rawpricesTokens, $rawpriceTokens);
//            if (count($inersect)){
//                var_dump($inersect);
//            }    
            return count($inersect) > 0;
       }    
        
       return true;
    }
    
    /**
     * Сравнить токены артикула и строки прайса
     * 
     * @param \Application\Entity\Article $article
     * @param \Application\Entity\Rawprice $rawprice
     * 
     * @return bool|null
     */
    public function tokenIntersect($article, $rawprice)
    {
        if (is_numeric($article)){
            $article = $this->entityManager->getRepository(Article::class)
                    ->findOneById($article);
        }

        if ($article){
            return $this->tokenRawpricesIntersect($article->getRawprice(), $rawprice);
        }
        
        return false;
    }
    
    /**
     * Получить номера из списка строка прайса
     * @param array $rawprices
     * @param integer $rawpriceDiff
     * 
     * @return array
     */
    public function getOemRawRawprices($rawprices, $rawpriceDiff = 0)
    {
        $result = [];
        foreach ($rawprices as $rawprice){
            if ($rawprice->getStatus() == $rawprice::STATUS_PARSED && $rawprice->getId() != $rawpriceDiff){
                if ($rawprice->getStatusOem() != $rawprice::OEM_PARSED){
                    $this->oemManager->addNewOemRawFromRawprice($rawprice);
                    return $this->getOemRawRawprices($rawprices, $rawpriceDiff);
                }
                foreach ($rawprice->getOemRaw() as $oem){
                    $result[] = $oem->getCode();
                }            
            }
        }

        return $result;        
    }
    
    /**
     * Получить номера артикула
     * 
     * @param Application\Entity\Article|integer $article
     * @param integer $rawpriceDiff Исключение
     * 
     * @return array|null
     */
    public function getOemRaw($article, $rawpriceDiff = 0)
    {
        if (is_numeric($article)){
            $article = $this->entityManager->getRepository(Article::class)
                    ->findOneById($article);
        }
        
        if ($article){
            return $this->getOemRawRawprices($article->getRawprice(), $rawpriceDiff);
        }
        
        return;        
    }

    /**
     * 
     * @param Doctrine\Common\Collections\ArrayCollection $rawprices
     * @param Application\Entity\Rawprice $rawprice
     * @return array|null
     */
    public function oemRawpricesIntersect($rawprices, $rawprice)
    {
       $rawpricesOem = $this->getOemRawRawprices($rawprices, $rawprice->getId());
       
       if ($rawpricesOem){
            
           if ($rawprice->getStatusOem() != $rawprice::OEM_PARSED){
                $this->oemManager->addNewOemRawFromRawprice($rawprice);
                return $this->oemRawpricesIntersect($rawprices, $rawprice);
            }

            $rawpriceOem = [];
            foreach ($rawprice->getOemRaw() as $oem){
                $rawpriceOem[] = $oem->getCode();
            }
            
            if (!count($rawpriceOem)){
                return [];
            }
            
            $inersect = array_intersect($rawpricesOem, $rawpriceOem);
            return $inersect;
       }
       
       return [];
        
    }
    
    /**
     * Сравнить номера артикула и строки прайса
     * 
     * @param Application\Entity\Article $article
     * @param Application\Entity\Rawprice $rawprice
     * 
     * @return array|null
     */
    public function oemIntersect($article, $rawprice)
    {
        if (is_numeric($article)){
            $article = $this->entityManager->getRepository(Article::class)
                    ->findOneById($article);
        }

        return $this->oemRawpricesIntersect($article->getRawprice(), $rawprice);
    }
    
}
