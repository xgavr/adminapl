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
 * Description of ExternalManager
 * Создание карточек товаров
 *
 * @author Daddy
 */
class ExternalManager
{
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    /**
     * Менеджер auto-db
     * 
     * @var Application\Service\ExternalDB\AutodbManager 
     */
    private $autoDbManager;
    
    /**
     * Менеджер partsApi
     * 
     * @var Application\Service\ExternalDB\PartsApiManager 
     */
    private $partsApiManager;
    
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $autoDbManager, $partsApiManager)
    {
        $this->entityManager = $entityManager;
        $this->autoDbManager = $autoDbManager;
        $this->partsApiManager = $partsApiManager;
    }
    
    /**
     * Подключение к auto-db api
     * 
     * @param string $action
     * @param array $params
     * @return array|null;
     */
    public function autoDb($action, $params = null)
    {
        switch($action){
            case 'version': $result = $this->autoDbManager->getPegasusVersionInfo2(); break;
            case 'countries': $result = $this->autoDbManager->getCountries(); break;
            case 'сriteria': $result = $this->autoDbManager->getCriteria2(); break;
            case 'getArticle': $result = $this->autoDbManager->getArticleDirectSearchAllNumbersWithState($params['good']); break;
            case 'getBestArticle': $result = $this->autoDbManager->getBestArticle($params['good']); break;
            case 'getInfo': $result = $this->autoDbManager->getDirectInfo($params['good']); break;
            case 'getLinked': $result = $this->autoDbManager->getLinked($params['good']); break;
            case 'getImages': $result = $this->autoDbManager->getImages($params['good']); break;
            default: break;
        }
        
//        var_dump($result);
        return $result;
    }
    
    /**
     * Подключение к parts api
     * 
     * @param string $action
     * @param array $params
     * @return array|null;
     */
    public function partsApi($action, $params = null)
    {
        switch($action){
            case 'makes': $result = $this->partsApiManager->getMakes(); break;
            default: break;
        }
        
//        var_dump($result);
        return $result;
    }
    
}
