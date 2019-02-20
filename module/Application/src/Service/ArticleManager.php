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

/**
 * Description of RbService
 *
 * @author Daddy
 */
class ArticleManager
{
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     *
     * @var Application\Service\NameManager
     */
    private $nameManager;
  
    /**
     *
     * @var Application\Service\OemManager
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
     * Выборка артиклей из прайса и добавление их в артиклулы
     */
    public function grabArticleFromRaw($raw)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(900);
        
        $rawprices = $this->entityManager->getRepository(Rawprice::class)
                ->findBy(['raw' => $raw->getId(), 'code' => null, 'status' => Rawprice::STATUS_PARSED]);
        
        $filter = new \Application\Filter\ArticleCode();
        
        foreach ($rawprices as $rawprice){

            $filteredCode = $filter->filter($rawprice->getArticle());
        
            $article = $this->entityManager->getRepository(Article::class)
                    ->findOneBy(['code' => $filteredCode, 'unknownProducer' => $rawprice->getUnknownProducer()->getId()]);
            
            if (!$article){
                try{
                    $this->entityManager->getRepository(Article::class)
                            ->insertArticle([
                                'code' => $filteredCode,
                                'fullcode' => mb_substr($rawprice->getArticle(), 0, 36),
                                'unknown_producer_id' => $rawprice->getUnknownProducer()->getId(),
                            ]);
                } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e){ 
                    //дубликат;
                }    
                
                $article = $this->entityManager->getRepository(Article::class)
                        ->findOneBy(['code' => $filteredCode, 'unknownProducer' => $rawprice->getUnknownProducer()->getId()]);
            }    


            $this->entityManager->getRepository(Article::class)
                    ->updateRawpriceCode($rawprice, $article);
            
            $article->addRawprice($rawprice);
            
            $rawprice->getUnknownProducer()->addCode($article);
        }    
                
        $rawprices = $this->entityManager->getRepository(Rawprice::class)
                ->findBy(['raw' => $raw->getId(), 'code' => null, 'status' => Rawprice::STATUS_PARSED]);
        
        if (count($rawprices) === 0){
            
            $oldRaws = $this->entityManager->getRepository(Raw::class)
                    ->findPreRetiredRaw($raw);

            foreach ($oldRaws as $oldRaw){
                                
                $oldRaw->setStatus(Raw::STATUS_RETIRED);
                $this->entityManager->persist($oldRaw);

                $this->entityManager->getRepository(Raw::class)
                        ->updateAllRawpriceStatus($oldRaw, Rawprice::STATUS_RETIRED);
            }    
            
            $raw->setParseStage(Raw::STAGE_ARTICLE_PARSED);
            $this->entityManager->persist($raw);
            $this->entityManager->flush();
        }        
        
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
        
        $articlesForDelete = $this->entityManager->getRepository(Article::class)
                ->findArticlesForDelete();

        foreach ($articlesForDelete as $row){
            $this->removeArticle($row[0]);
        }
        
        //$this->entityManager->flush();
        
        return count($articlesForDelete);
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
     * Выборка артикулов из прайсов и добавление их в артикулы
     * привязка к строкам прайса
     * 
     * @param Application\Entity\Raw $raw
     */
    public function grabArticleFromRaw2($raw)
    {
        ini_set('memory_limit', '2048M');

        $articles = $this->entityManager->getRepository(Article::class)
                ->findArticleFromRaw($raw);

        $filter = new \Application\Filter\ArticleCode();

        foreach ($articles as $row){

            $filteredCode = $filter->filter($row['code']);        
            $unknownProducerId = $row['unknownProducer'];
            
            $data = [
                'code' => $filteredCode,
                'fullcode' => trim($row['code']),
                'unknown_producer_id' => $unknownProducerId,
            ];
            try{
                $this->entityManager->getRepository(UnknownProducer::class)
                        ->insertUnknownProducer($data);
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e){
                //дубликат
            }   
            
            $article = $this->entityManager->getRepository(Article::class)
                    ->findOneBy(['code' => $filteredCode, 'unknownProducer' => $unknownProducerId]);
            
            if ($article){
                
                $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                        ->findOneById($unknownProducerId);
                
                $article->setUnknownProducer($unknownProducer);
                $this->entityManager->persist($article);
                
                $rawprices = $this->entityManager->getRepository(Rawprice::class)
                        ->findBy(['raw' => $raw->getId(), 'unknownProducer' => $unknownProducerId, 'code' => $article->getId()]);
                
                foreach ($rawprices as $rawprice){
                    $rawprice->setCode($article);
                    $this->entityManager->persist($rawprice);
                }
            }            
        }
        
        $this->entityManager->flush();
        
        $rawprices = $this->entityManager->getRepository(Raw::class)
                ->findCodeRawprice($raw);
        
        if (count($rawprices) === 0){
            $raw->setParseStage(Raw::STAGE_ARTICLE_PARSED);
            $this->entityManager->persist($raw);
            $this->entityManager->flush($raw);
        }        
    }
    
    /**
     * Средняя цена по строкам прайса
     * 
     * @param array $rawprices
     * @return float
     */
    public function rawpricesMeanPrice($rawprices)
    {
        $result = [];
        $rest = 0;
        foreach($rawprices as $rawprice){
            if ($rawprice->getStatus() == Rawprice::STATUS_PARSED && $rawprice->getRealRest()){
                $result[] = $rawprice->getRealPrice() * $rawprice->getRealRest();
                $rest += $rawprice->getRealRest();
            }    
        }

        if ($rest){
            return array_sum($result)/$rest;
        }    
        
        return 0;
    }
    
    /**
     * Вычисление средней цены 
     * 
     * @param Application\Entity\Article
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
     * Разброс цены по строкам по набору строк прайса 
     * 
     * @param Doctrine\Common\Collections\ArrayCollection $rawprices
     * @return float|null
     */
    public function rawpricesDispersion($rawprices)
    {
        $mean = $this->rawpricesMeanPrice($rawprices);

        $result = [];
        $rest = 0;
        foreach($rawprices as $rawprice){
            if ($rawprice->getStatus() == Rawprice::STATUS_PARSED && $rawprice->getRealRest()){
                $result[] = pow(($rawprice->getRealPrice() - $mean), 2)*$rawprice->getRealRest();
                $rest += $rawprice->getRealRest();
            }    
        }

        if ($rest){
            return sqrt(array_sum($result)/$rest);
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
            return $this->rawpricesDispersion($article->getRawprice());
        }
        
        return;
    }
    
    /**
     * Проверка цены на попадание в диапазон цен
     * 
     * @param Application\Entity\Article $article
     * @param Application\Entity\Rawprice $rawprice
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
        $dispersion = $this->rawpricesDispersion($rawprices);
//        var_dump($meanPrice);
//        var_dump($dispersion);
        return $this->inSigma3($rawprice->getRealPrice(), $meanPrice, $dispersion);
    }
    
    /**
     * Сравнение цен артикулов
     * 
     * @param Application\Entity\Article $article
     * @param Application\Entity\Article $articleForMatching
     * 
     * @return bool Description
     */
    public function articlePriceMatching($article, $articleForMatching)
    {
//        var_dump($article->getId());
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
//        var_dump($result);
        
        return $result > 0;
    }
    
    /**
     * Получить токены списка строк прайса
     * 
     * @param Doctrine\Common\Collections\ArrayCollection $rawprices
     * @param integer $rawpriceDiff
     * @return array
     */
    public function getRawpricesTokens($rawprices, $rawpriceDiff = 0)
    {
        $result = [];
        foreach ($rawprices as $rawprice){
            if ($rawprice->getStatus() == $rawprice::STATUS_PARSED && $rawprice->getId() != $rawpriceDiff){
                if ($rawprice->getStatusToken() != $rawprice::TOKEN_PARSED){
                    $this->nameManager->addNewTokenFromRawprice($rawprice);
                    return $this->getRawpricesTokens($rawprices, $rawpriceDiff);
                }
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
     * @param Application\Entity\Article $article
     * @param Application\Entity\Rawprice $rawprice
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
     * @param Doctrine\Common\Collections\ArrayCollection $rawprices
     * @param integer $rawpriceDiff;
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
