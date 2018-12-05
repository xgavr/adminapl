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

            if (mb_strlen($code, 'utf-8') > 36){
               $result = 'moreThan36';
            }
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
        
        $rawprices = $this->entityManager->getRepository(Rawprice::class)
                ->findBy(['raw' => $raw->getId(), 'code' => null]);
        
        foreach ($rawprices as $rawprice){
            $this->addNewArticleFromRawprice($rawprice, false);
        }
        $this->entityManager->flush();
        
        $rawprices = $this->entityManager->getRepository(Rawprice::class)
                ->findBy(['raw' => $raw->getId(), 'code' => null]);
        
        if (count($rawprices) === 0){
            $raw->setParseStage(Raw::STAGE_ARTICLE_PARSED);
            $this->entityManager->persist($raw);
            $this->entityManager->flush($raw);
        }        
        
    }
    

    /**
     * Удаление артикула
     * 
     * @param Application\Entity\Article $article
     */
    public function removeArticle($article, $flush = true) 
    {   
        $oemRaws = $article->getOemRaw();
        foreach ($oemRaws as $oemRaw){
            $this->entityManager->remove($oemRaw);
        }
        
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
        
        $articlesForDelete = $this->entityManager->getRepository(Article::class)
                ->findArticlesForDelete();

        foreach ($articlesForDelete as $row){
            $this->removeArticle($row[0], false);
        }
        
        $this->entityManager->flush();
        
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
            $result = [];
            $rest = 0;
            foreach($article->getRawprice() as $rawprice){
                if ($rawprice->getStatus() == Rawprice::STATUS_PARSED && $rawprice->getRealRest()){
                    $result[] = $rawprice->getRealPrice() * $rawprice->getRealRest();
                    $rest += $rawprice->getRealRest();
                }    
            }

            if ($rest){
                return array_sum($result)/$rest;
            }    
        }    
        return 0;
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
            $mean = $this->meanPrice($article);

            $result = [];
            $rest = 0;
            foreach($article->getRawprice() as $rawprice){
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
        if ($meanPrice){
            if ($dispersion/$meanPrice < 0.01){
                return true;
            }
        }        
        
        $minPrice = $meanPrice - 3*$dispersion;
        $maxPrice = $meanPrice + 3*$dispersion;
        
        return $price >= $minPrice && $price <= $maxPrice;
    }
    
    /**
     * Получить токены артикула
     * 
     * @param Application\Entity\Article|integer $article
     * @param integer $rawpriceDiff Исключение
     * 
     * @return array
     */
    public function getTokens($article, $rawpriceDiff = 0)
    {
        if (is_numeric($article)){
            $article = $this->entityManager->getRepository(Article::class)
                    ->findOneById($article);
        }
        
        if ($article){
            $result = [];
            foreach ($article->getRawprice() as $rawprice){
                if ($rawprice->getStatus() == $rawprice::STATUS_PARSED && $rawprice->getId() != $rawpriceDiff){
                    if ($rawprice->getStatusToken() != $rawprice::TOKEN_PARSED){
                        $this->nameManager->addNewTokenFromRawprice($rawprice);
                        return $this->getTokens($article, $rawpriceDiff);
                    }
                    foreach ($rawprice->getTokens() as $token){
                        $result[$token->getId()] += 1;
                    }            
                }
            }

            return $result;
        }
        
        return;
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
       $articleTokens = $this->getTokens($article, $rawprice->getId());
       
       if (count($articleTokens)){
            
           if ($rawprice->getStatusToken() != $rawprice::TOKEN_PARSED){
                $this->nameManager->addNewTokenFromRawprice($rawprice);
                return $this->tokenIntersect($article, $rawprice);
            }

            $rawpriceTokens = [];
            foreach ($rawprice->getTokens() as $token){
                $rawpriceTokens[$token->getId()] += 1;
            }
            
            $inersect = array_intersect_key($articleTokens, $rawpriceTokens);
            //var_dump(count($inersect) > 0);
            return count($inersect) > 0;
       }
       
       return true;
    }
    
    /**
     * Получить номера артикула
     * 
     * @param Application\Entity\Article|integer $article
     * @param integer $rawpriceDiff Исключение
     * 
     * @return array
     */
    public function getOemRaw($article, $rawpriceDiff = 0)
    {
        if (is_numeric($article)){
            $article = $this->entityManager->getRepository(Article::class)
                    ->findOneById($article);
        }
        
        if ($article){
            $result = [];
            foreach ($article->getRawprice() as $rawprice){
                if ($rawprice->getStatus() == $rawprice::STATUS_PARSED && $rawprice->getId() != $rawpriceDiff){
                    if ($rawprice->getStatusOem() != $rawprice::OEM_PARSED){
                        $this->oemManager->addNewOemRawFromRawprice($rawprice);
                        return $this->getOemRaw($article, $rawpriceDiff);
                    }
                    foreach ($rawprice->getOemRaw() as $oem){
                        $result[$oem->getCode()] += 1;
                    }            
                }
            }

            return $result;
        }
        
        return;
        
    }

    /**
     * Сравнить номера артикула и строки прайса
     * 
     * @param Application\Entity\Article $article
     * @param Application\Entity\Rawprice $rawprice
     * 
     * @return bool|null
     */
    public function oemIntersect($article, $rawprice)
    {
       $articleOem = $this->getOemRaw($article, $rawprice->getId());
       
       if (count($articleOem)){
            
           if ($rawprice->getStatusOem() != $rawprice::OEM_PARSED){
                $this->nameManager->addNewOemRawFromRawprice($rawprice);
                return $this->oemIntersect($article, $rawprice);
            }

            $rawpriceOem = [];
            foreach ($rawprice->getOemRaw() as $oem){
                $rawpriceOem[] = $oem->getCode();
            }
            
            if (!count($rawpriceOem)){
                return;
            }
            
            $inersect = array_intersect_key($articleOem, $rawpriceOem);
            var_dump($inersect);
            return $inersect;
       }
       
       return;
    }
    
}
