<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service\ExternalDB;

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
    
    const IMAGE_DIR = './public/img'; //папка для хранения картинок
    const GOOD_IMAGE_DIR = './public/img/goods'; //папка для хранения картинок товаров
    
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
    public function getDirectArticlesByIds6($articleIds, $params = null)
    {
        if (!$params){
            $params = [
                'attributs' => true,
                'basicData' => true,
                'documents' => true,
                'eanNumbers' => true,
                'immediateAttributs' => true,
                'immediateInfo' => true,
                'info' => true,
                'mainArticles' => true,
                'normalAustauschPrice' => false,
                'oeNumbers' => true,
                'prices' => false,
                'replacedByNumbers' => true,
                'replacedNumbers' => true,
                'thumbnails' => true,
                'usageNumbers' => true,            
             ];
        }
        
        $params['articleCountry'] = 'RU';
        $params['lang'] = 'RU';
        $params['articleId'] = Encoder::encode(['array' => $articleIds]);

        $result = $this->getAction('getDirectArticlesByIds6', $params);

        return $result;
    }
    
    /**
     * Получить информацию по товару
     * 
     * @param Application\Entity\Goods $good
     * @return array
     */
    public function getDirectInfo($good, $params = null)
    {
        $article = $this->getBestArticle($good);
        
        if (is_array($article)){
            return $this->getDirectArticlesByIds6([$article['articleId']], $params);
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
            'linkingTargetType' => 'P',
         ];

        $result = $this->getAction('getArticleLinkedAllLinkingTarget3', $params);

        return $result;
    }
    
    /**
     * Получить машины, связанные с товаром
     * 
     * @param Application\Entity\Goods $good
     * @return array|null
     */
    public function getLinked($good)
    {
        $article = $this->getBestArticle($good);
        
        if (is_array($article)){
            return $this->getArticleLinkedAllLinkingTarget3($article['articleId']);
        }
        
        return;
        
    }
    
    /**
     * Получить картинку по номеру документа
     * 
     * @param integer $docId
     */
    public function getDocImageUri($docId)
    {
        $result = $this::URI_PRODUCTION.'?file='.$docId;
        return $result;
    }

    
    /**
     * Получить путь к папке с картинками
     * 
     * @param Application\Entity\Goods $good
     * @return string
     */
    public function getImageFolder($good)
    {
        return self::GOOD_IMAGE_DIR.'/'.$good->getId().'/td';
    }

    /**
     * Создать папку с картинками
     * 
     * @param Application\Entity\Goods $good
     */
    public function addImageFolder($good)
    {
        $images_folder = self::IMAGE_DIR;
        if (!is_dir($images_folder)){
            mkdir($images_folder);
        }
        
        $image_folder = self::GOOD_IMAGE_DIR;
        if (!is_dir($image_folder)){
            mkdir($image_folder);
        }
        
        $good_image_folder = self::GOOD_IMAGE_DIR.'/'.$good->getId();
        if (!is_dir($good_image_folder)){
            mkdir($good_image_folder);
        }

        $td_image_folder = $this->getImageFolder($good);
        if (!is_dir($td_image_folder)){
            mkdir($td_image_folder);
        }
        return;
    }        
    
    
    /*
     * Очистить содержимое папки c картинками товара
     * 
     * @var Application\Entity\Goods $folderName
     * 
     */
    public function clearImageGoodFolder($good)
    {
        $folderName = $this->getImageFolder($good);
                
        if (is_dir($folderName)){
            foreach (new \DirectoryIterator($folderName) as $fileInfo) {
                if ($fileInfo->isDot()) continue;
                if ($fileInfo->isFile()){
                    unlink($fileInfo->getFilename());                            
                }
            }
        }
    }
    
    /**
     * Сохранить картинку товара по ссылке
     * 
     * @param string $uri
     */
    public function saveImageGood($good, $uri, $docFileName)
    {
        $headers = get_headers($uri);
        if(preg_match("|200|", $headers[0])) {
            
            $image = file_get_contents($uri);
            file_put_contents($this->getImageFolder($good)."/".$docFileName, $image);
        } 
        
        return;
            
    }
    
    /**
     * Скачать картинку товара
     * 
     * @param Application\Entity\Goods $good
     * 
     */
    public function getImages($good)
    {
        $this->addImageFolder($good);
        $this->clearImageGoodFolder($good);
        
        $articleInfo = $this->getDirectInfo($good, ['documents' => true]);
        
        if (is_array($articleInfo)){
            foreach($articleInfo['data']['array'] as $articleDocuments){
                foreach($articleDocuments['articleDocuments']['array'] as $document){
                    if ($document['docId'] && $document['docFileName']){
                        $uri = $this->getDocImageUri($document['docId']);
                        $this->saveImageGood($good, $uri, $document['docFileName']);
                    }
                }
            }
        }
        
        return;
    }
}
