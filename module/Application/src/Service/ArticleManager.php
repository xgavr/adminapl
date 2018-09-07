<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Application\Entity\Article;
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
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Добавить новый артикул
     * 
     * @param string $code
     * @param bool $flushnow
     */
    public function addArticle($code, $flushnow = true)
    {
        $filter = new \Application\Filter\ArticleCode();
        $filteredCode = $filter->filter($code);
        
        $article = $this->entityManager->getRepository(Article::class)
                    ->findOneByCode($filteredCode);

        if ($article == null){

            // Создаем новую сущность UnknownProducer.
            $article = new Article();
            $article->setCode($filteredCode);            
            $article->setFullCode($code);

            // Добавляем сущность в менеджер сущностей.
            $this->entityManager->persist($article);

            // Применяем изменения к базе данных.
            if ($flushnow){
                $this->entityManager->flush($article);
            }    
        } else {
            if (mb_strlen($article)->getFullCode() < mb_strlen(trim($code))){
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
        $article = $this->addArticle($rawprice->getArticle(), $flush);
        
        if ($article){
            
            $rawprice->setCode($article);
            $this->entityManager->persist($rawprice);

            $this->entityManager->flush();
        }   
        
        return;
    }  
    
    /**
     * Выборка артиклей из прайсов и добавление их в артиклулы
     */
    public function grabArticleFromRawprice()
    {
        $rawprices = $this->entityManager->getRepository(Producer::class)
                ->findRawpriceUnknownProducer();
        
        foreach ($rawprices as $rawprice){
            $this->addNewArticleFromRawprice($rawprice, false);
        }
        $this->entityManager->flush();
    }
    

    /**
     * Удаление артикула
     * 
     * @param Application\Entity\Article $article
     */
    public function removeArticle($article) 
    {   
        $this->entityManager->remove($article);
        
        $this->entityManager->flush($article);
    }    
    
    /**
     * Поиск и удаление артикулов не привязаных к строкам прайсов
     */
    public function removeEmptyArticles()
    {
        $articlesForDelete = $this->entityManager->getRepository(Article::class)
                ->findArticlesForDelete();

        foreach ($articlesForDelete as $row){
            $this->removeArticle($row[0]);
        }
        
        return count($articlesForDelete);
    }    
}
