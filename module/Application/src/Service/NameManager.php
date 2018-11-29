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
    public function addToken($word, $flushnow = true)
    {
        
        $token = $this->entityManager->getRepository(Token::class)
                    ->findOneBy(['stem' => $word]);

        if ($token == null){

            $token = new Token();
            $token->setLemms($word);            

            // Добавляем сущность в менеджер сущностей.
            $this->entityManager->persist($token);

            // Применяем изменения к базе данных.
            $this->entityManager->flush($token);
        }
        
        return $token;        
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

        if ($rawprice->getArticle()){
            $title = $rawprice->getTitle();
            if (is_array($oems)){
                foreach ($oems as $oemCode){
                    $oem = $this->addOemRaw($oemCode, $rawprice->getCode(), $flush);
                    if ($oem){
                        $rawprice->addOemRaw($oem);
                    }   
                }    
            }    
        }    
        $rawprice->setStatusOem(Rawprice::OEM_PARSED);
        $this->entityManager->persist($rawprice);
        if ($flush){
            $this->entityManager->flush();
        }    
        return;
    }  
    
    /**
     * Выборка оригинальных номеров из прайса и добавление их в таблицу оригинальных номеров
     */
    public function grabOemFromRaw($raw)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(1200);
        $startTime = time();
        
        $rawprices = $this->entityManager->getRepository(Rawprice::class)
                ->findBy(['raw' => $raw->getId(), 'statusOem' => Rawprice::OEM_NEW]);
        
        foreach ($rawprices as $rawprice){
            $this->addNewOemRawFromRawprice($rawprice, false);
            if (time() > $startTime + 400){
                $this->entityManager->flush();
                return;
            }
        }
        
        $raw->setParseStage(Raw::STAGE_OEM_PARSED);
        $this->entityManager->persist($raw);
        
        $this->entityManager->flush();
    }
    
    /**
     * Удаление кода
     * 
     * @param Application\Entity\OemRaw $oemRaw
     */
    public function removeOemRaw($oemRaw) 
    {   
        $this->entityManager->remove($oemRaw);
        
        $this->entityManager->flush($oemRaw);
    }    
    
    /**
     * Поиск и удаление номеров не привязаных к строкам прайсов
     */
    public function removeEmpty()
    {
        ini_set('memory_limit', '2048M');
        
        $oemForDelete = $this->entityManager->getRepository(OemRaw::class)
                ->findOemRawForDelete();

        foreach ($oemForDelete as $row){
            $this->removeOemRaw($row[0], false);
        }
        
        $this->entityManager->flush();
        
        return count($oemForDelete);
    }    
    

    /**
     * Выборка из прайсов по id артикля и id поставщика 
     * @param array $params
     * @return object      
     */
    public function randRawpriceBy($params)
    {
        return $this->entityManager->getRepository(OemRaw::class)
                ->randRawpriceBy($params);
    }   
}
