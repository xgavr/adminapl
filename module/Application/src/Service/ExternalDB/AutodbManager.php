<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service\ExternalDB;

use Zend\ServiceManager\ServiceManager;
use Zend\Http\Client;
use Zend\Json\Decoder;
use Zend\Json\Encoder;

/**
 * Description of AutodbManager
 *
 * @author Daddy
 */
class AutodbManager
{
    
    const URI_PRODUCTION = 'https://auto-db.pro/ws/tecdoc-api/';
    
    const HTTPS_ADAPTER = 'Zend\Http\Client\Adapter\Curl';  
    
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
     * Получить uri api
     * 
     * @return string 
     */
    public function getUri()
    {
        return $this::URI_PRODUCTION;
    }    
    
    /**
     * Обработка ошибок
     * @param \Zend\Http\Response $response
     */
    public function exception($response)
    {
        switch ($response->getStatusCode()) {
            case 400: //Invalid code
                throw new \Exception('Ошибка');
            case 401: //The access token is invalid or has expired
                throw new \Exception('Ошибка');
            case 403: //The access token is missing
                throw new \Exception('Доступ запрещен');
            default:
                $error = Decoder::decode($response->getContent(), \Zend\Json\Json::TYPE_ARRAY);
                $error_msg = $response->getStatusCode();
                if (isset($error['error'])){
                    $error_msg .= ' ('.$error['error'].')';
                }
                if (isset($error['error_description'])){
                    $error_msg .= ' '.$error['error_description'];
                }
                if (isset($error['message'])){
                    $error_msg .= ' '.$error['message'];
                }
                throw new \Exception($error_msg);
        }
        
        throw new \Exception('Неопознаная ошибка');
    }    
    
    
    /**
     * Базовый метод доступа к апи
     * 
     * @param string $action
     * @param array $params
     * @return array|Exception
     */    
    public function getAction($action, $params = null)
    {
        $uri = $this->getUri().'?action='.$action;
        if (is_array($params)){
            foreach ($params as $key => $value){
                $uri .= "&$key=$value";
            }    
        }        
//        var_dump($uri);
        $client = new Client();
        $client->setUri($uri);
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('GET');
        
        $headers = $client->getRequest()->getHeaders();
//        $headers->addHeaders([
//            'Content-Type: application/json',
//        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isOk()){
            return Decoder::decode($response->getBody(), \Zend\Json\Json::TYPE_ARRAY);            
        }

        return $this->exception($response);
        
    }
    
    /**
     * Получить версию апи
     * @return array|Esception
     */
    public function getPegasusVersionInfo()
    {
        return $this->getAction('getPegasusVersionInfo');
    }
    
    /**
     * Получить версию апи
     * @return array|Esception
     */
    public function getPegasusVersionInfo2()
    {
        return $this->getAction('getPegasusVersionInfo2');
    }

    /**
     * Получить критерии
     * @return array|Esception
     */
    public function getCriteria2()
    {
        return $this->getAction('getCriteria2');
    }

    /**
     * Получить производителей
     * @return array|Esception
     */
    public function getManufacturers()
    {
        return $this->getAction('getManufacturers', ['linkingTargetType' => 'P']);
    }

    /**
     * Получить модели серии
     * @return array|Esception
     */
    public function getModelSeries()
    {
        return $this->getAction('getModelSeries', ['linkingTargetType' => 'P']);
    }

    /**
     * Получить страны
     * @return array|Esception
     */
    public function getCountries()
    {
        return $this->getAction('getCountries');
    }

    /**
     * Получить articleId
     * 
     * @param Application\Entity\Goods $good
     * 
     * @return array|Esception
     */
    public function getArticleDirectSearchAllNumbersWithState($good)
    {
        $params = [
            'articleNumber' => $good->getCode(), 
            'articleCountry' => 'RU',            
            'numberType' => 0,
            'searchExact' => true,
        ];
        
        $result = $this->getAction('getArticleDirectSearchAllNumbersWithState', $params);
        
        if (!$result['data']){
            $params['numberType'] = 10;
            $result = $this->getAction('getArticleDirectSearchAllNumbersWithState', $params);            
        }
        return $result;
    }
    
    /**
     * Плучить наиболее подходящий к товару артикул
     * 
     * @param Application\Entity\Goods $good
     * 
     * @return array
     */
    public function getBestArticle($good)
    {
        $articles = $this->getArticleDirectSearchAllNumbersWithState($good);
        if ($articles['data']){
            foreach ($articles['data']['array'] as $row){
                foreach($good->getProducer()->getUnknownProducer() as $unknownProducer){
                    if (mb_strtoupper($row['brandName']) == mb_strtoupper($unknownProducer->getName())){
                        return $row;
                    }
                }        
            }
        }
        
        return;
    }

    /**
     * Получить детальную информацию об артикуле
     * 
     * @param array $articleIds
     * 
     * @return array|Esception
     */
    public function getDirectArticlesByIds6($articleIds)
    {
        $params = [
            'articleCountry' => 'RU',            
            'articleId' => Encoder::encode(['array' => $articleIds]), 
            'attributs' => true,
            'basicData' => true,
            'documents' => true,
            'eanNumbers' => true,
            'immediateAttributs' => true,
            'immediateInfo' => true,
            'info' => true,
            'lang' => 'RU',
            'mainArticles' => true,
            'normalAustauschPrice' => false,
            'oeNumbers' => true,
            'prices' => false,
            'replacedByNumbers' => true,
            'replacedNumbers' => true,
            'thumbnails' => true,
            'usageNumbers' => true,            
         ];

        $result = $this->getAction('getDirectArticlesByIds6', $params);

        return $result;
    }
    
    /**
     * Получить информацию по товару
     * 
     * @param Application\Entity\Goods $good
     * @return array
     */
    public function getDirectInfo($good)
    {
        $article = getBestArticle($good);
        
        if (is_array($article)){
            return $this->getDirectArticlesByIds6([$article['articleId']]);
        }
        
        return;
    }

    /**
     * Получить машины, связанные с артикулом
     * 
     * @param integer $articleId
     * 
     * @return array|Esception
     */
    public function getArticleLinkedAllLinkingTarget3($articleId)
    {
        $params = [
            'articleCountry' => 'RU',            
            'articleId' => $articleId, 
         ];

        $result = $this->getAction('getArticleLinkedAllLinkingTarget3', $params);

        return $result;
    }

}
