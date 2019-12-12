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
use Application\Filter\ProducerName;
use Application\Entity\Images;
use Application\Entity\AutoDbResponse;
use Application\Entity\UnknownProducer;
use Application\Entity\Producer;
use Application\Entity\Goods;
use Application\Entity\Make;

/**
 * Description of AutoitManager
 *
 * @author Daddy
 */
class AvtoitManager
{
    
    const HTTPS_ADAPTER = 'Zend\Http\Client\Adapter\Curl';  
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * AdminManager.
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;
    
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
    }
    
    /**
     * Обработка ошибок
     * @param \Zend\Http\Response $response
     */
    public function exception($response)
    {
        ini_set('memory_limit', '512M');
        
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
     * Получить сохраненный ответ запроса
     * 
     * @param string $uri
     * @return array
     */
    private function getAutoDbResponseData($uri)
    {
        $uriMd5 = md5(mb_strtoupper(trim($uri), 'UTF-8'));
        $this->entityManager->getRepository(AutoDbResponse::class)
                ->deleteOld($uriMd5);
        
        $autoDbResponse = $this->entityManager->getRepository(AutoDbResponse::class)
                ->findOneByUriMd5($uriMd5);
        
        if ($autoDbResponse == null){
            return false;
        }
        
        return $autoDbResponse->getResponseAsArray();
    }
    
    /**
     * Количество запросов за сегодня
     * @return integer
     */
    private function getAutoDbResponseTodayCount()
    {
        return $this->entityManager->getRepository(AutoDbResponse::class)
                ->getAutoDbResponseTodayCount();
    }
    
    /**
     * Добавить обновить запись в auto_db_response
     * 
     * @param string $uri
     * @param string $response
     * @return boolean
     */
    private function updateAutoDbResponse($uri, $response)
    {
        $autoDbResponse = $this->entityManager->getRepository(AutoDbResponse::class)
                ->findOneByUriMd5(md5(mb_strtoupper(trim($uri), 'UTF-8')));
        
        if ($autoDbResponse == null){
            $this->entityManager->getRepository(AutoDbResponse::class)
                    ->insertAutoDbResponse($uri, $response);
            return true;
        }
        
//        if ($autoDbResponse->getResponseMd5() != md5(mb_strtoupper(trim($response), 'UTF-8'))){
//            $this->entityManager->getRepository(AutoDbResponse::class)
//                    ->updateAutoDbResponse($uri, $response);
//            return true;            
//        }
        
        return false;
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
        ini_set('memory_limit', '512M');       
        
        if ($action){
            if (is_array($params)){
                $params = array_filter($params);
            } else {
                $params = [];
            }
            
            $settings = $this->adminManager->getAvtoitSettings();
            
            $uri = $settings['host'].'/'.$action.'?';

            $params['apiId'] = $settings['api_key'];
            $params['apiKey'] = $settings['md5_key'];
            foreach ($params as $key => $value){
                $uri .= "&$key=$value";
            }    
            
            $result = $this->getAutoDbResponseData($uri);
            if (is_array($result)){
                return $result;
            }
            
            if ($settings['max_query'] <= $this->getAutoDbResponseTodayCount()){
                throw new \Exception("Достигнут лимит запросов {$settings['max_query']}");
            }
    //        var_dump($uri); exit;
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
                try {
                    $body = $response->getBody();
                    $result = Decoder::decode($body, \Zend\Json\Json::TYPE_ARRAY);
                    //$result['change'] = $this->updateAutoDbResponse($uri, $body);
                    return $result;            
                } catch (\Zend\Json\Exception\RuntimeException $e){
                   // var_dump($response->getBody()); exit;
                }    
            }
        }        

        return; // $this->exception($response);
        
    }
    
    /**
     * Получить список доступных транспортных средств
     * 
     * @return array|Esception
     */
    public function getVehiclestypes()
    {
        return $this->getAction('wizzard/vehiclestypes/', []);
    }

    /**
     * Получить список доступных производителей транспортного средства
     * 
     * @param array $params
     * @return array|Esception
     */
    public function getManufacturers($params)
    {
        return $this->getAction('wizzard/brands/', ['vehiclesTypeId' => $params['vehiclesTypeId']]);
    }

    /**
     * Получить моделей производителя
     * @param array $params
     * @return array|Esception
     */
    public function getModels($params)
    {
        return $this->getAction('models', ['manufacturerId' => $params['manufacturerId'], 'carType' => null]);
    }

    /**
     * Получение списка модификаций
     * @param array $params
     * @return array|Esception
     */
    public function getModifications($params)
    {
        return $this->getAction('modifications', ['manufacturerId' => $params['manufacturerId'], 'modelId' => $params['modelId'], 'carType' => null]);
    }
    
    /**
     * Получение модификации по идентификатору
     * @param array $params
     * @return array|Esception
     */
    public function getModification($params)
    {
        return $this->getAction('modification', ['modelVariant' => $params['modificationId']]);
    }

    /**
     * Получение списка брендов
     * @param array $params
     * @return array|Esception
     */
    public function getBrands($params = null)
    {
        return $this->getAction('brands', $params);
    }
    
    /**
     * Поулчить бренд из неизвестного производителя
     * 
     * @param UnknownProducer $unknownProducer
     * @param array $brands
     * 
     * @return string
     */
    public function brandFromUnknownProducer($unknownProducer, $brands = null)
    {
        if ($brands == null){
            $brands = $this->getBrands();
        }
        
        $filter = new ProducerName();
        foreach ($brands as $row){
            if ($filter->filter($row['name']) == $filter->filter($unknownProducer->getName())){
                return $row['name'];
            }            
            if ($unknownProducer->getNameTd()){
                if ($filter->filter($row['name']) == $filter->filter($unknownProducer->getNameTd())){
                    return $row['name'];
                }                        
            }
        }
        
        return;
    }
    
    /**
     * Получить бренд из производителя
     * 
     * @param Producer $producer
     * @return string 
     */
    public function brandFromProducer($producer)
    {
        
        $brands = $this->getBrands();
        $unknownProducers = $this->entityManager->getRepository(UnknownProducer::class)
                ->findByProducer($producer->getId());
        
        foreach($unknownProducers as $unknownProducer){
            $brandName = $this->brandFromUnknownProducer($unknownProducer, $brands);
            if ($brandName){
                return $brandName;
            }
        }        
        
        return;
    }    
    
    /**
     * Получить список производителей использующих деталь
     * 
     * @param Goods $good
     * @param string $brandName
     * @param string $manufacturerName
     * @param string $modelName
     */
    protected function findBrandByPart($vehiclesTypeId, $partId)
    {
        return $this->getAction('find/brand-by-part/', [
            'vehiclesTypeId' => $brandName, 
            'partId' => $good->getCode(),
            ]);                
    }
    
    /**
     * Возвращает список моделей, применимых для выбранной детали и производителю.
     * 
     * @param Goods $good
     * @param string $brandName
     * @param string $manufacturerName
     * 
     * @return array
     */
    protected function adaptabilityModels($good, $brandName, $manufacturerName)
    {
        $models = $this->getAction('adaptabilityModels', [
            'brandName' => $brandName, 
            'number' => $good->getCode(),
            'manufacturerName' => $manufacturerName,
            ]);
        
        $result = [];
        if (is_array($models)){
            foreach ($models as $modelName) {
                $cars = $this->adaptabilityModifications($good, $brandName, $manufacturerName, $modelName);
                if (is_array($cars)){
                    $result = array_merge($result, $cars);
                }    
            }
        }    
        return $result;
    }

    /**
     * Получение списка применимости
     * 
     * @param Goods $good
     * @return array
     */
    public function adaptabilityManufacturers($good)
    {
        $brandName = $this->brandFromProducer($good->getProducer());
        if ($brandName){
            $manufacturers = $this->getAction('adaptabilityManufacturers', ['brandName' => $brandName, 'number' => $good->getCode()]);
            $result = [];
            if (is_array($manufacturers)){
                foreach ($manufacturers as $manufacturer){
                    $modelsCar = $this->adaptabilityModels($good, $brandName, $manufacturer['name']);
                    if (is_array($modelsCar)){
                        $result = array_merge($result, $modelsCar);
                    }    
                }
            }    
            return $result;
        }
        
        return;
    }

    /**
     * Получить группы запчастей
     * 
     * @return array
     */
    public function getGenericArticles()
    {
        $params = [
            'articleCountry' => 'RU',
            'lang' => 'RU',
            'searchTreeNodes' => true,
        ];
        
        return $this->getAction('getGenericArticles', $params);
    }
    
    /**
     * Получить articleId
     * 
     * @param Goods $good
     * @param string $oper
     * @return array|Esception
     */
    public function getArticleDirectSearchAllNumbersWithState($good, $oper = null)
    {
        $params = [
            'articleNumber' => $good->getCode(), 
            'articleCountry' => 'RU',            
            'numberType' => 0,
            'searchExact' => true,
        ];
        
        $result = $this->getAction('getArticleDirectSearchAllNumbersWithState', $params, $good->getId(), $oper);

        if (isset($result['data'])){
            if (isset($result['data']['array'])){
                return $result;
            }
        }
        
        $params['numberType'] = 10;
        $result = $this->getAction('getArticleDirectSearchAllNumbersWithState', $params, $good->getId(), $oper);            

        if (isset($result['data'])){
            if (isset($result['data']['array'])){
                return $result;
            }
        }
        
        return;
    }
    
    /**
     * Получить похожий по группе articleId
     * 
     * @param \Application\Entity\Goods $good
     * @param \Application\Entity\GenericGroup $genericGroup
     * @param string $oper
     * @return array|null|Exception
     */
    public function getArticleDirectSearchAllNumbersWithGeneric($good, $genericGroup = null, $oper = null)
    {
        if (!$genericGroup){
            $genericGroup = $good->getGenericGroup();
        }
        
        if ($genericGroup->getTdId()>0){
            $params = [
                'articleNumber' => $good->getCode(), 
                'articleCountry' => 'RU',            
                'genericArticleId' => $genericGroup->getTdId(),
                'numberType' => 0,
                'searchExact' => true,
            ];

            $result = $this->getAction('getArticleDirectSearchAllNumbersWithState', $params, $good->getId(), $oper);
            if (isset($result['data'])){
                if (isset($result['data']['array'])){
                    return $result;
                }
            }
            
            $oemsQuery = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                    ->findOems($good, ['limit' => 10, 'source' => \Application\Entity\Oem::SOURCE_SUP]);
            $oems = $oemsQuery->getResult();
            
            foreach ($oems as $oem){
                $params['articleNumber'] = $oem->getOe();
                $result = $this->getAction('getArticleDirectSearchAllNumbersWithState', $params, $good->getId(), $oper);
                if (isset($result['data'])){
                    if (isset($result['data']['array'])){
                        return $result;
                    }
                }
            }    
            
            $params['numberType'] = 10;
            $result = $this->getAction('getArticleDirectSearchAllNumbersWithState', $params, $good->getId(), $oper);            

            if (isset($result['data'])){
                if (isset($result['data']['array'])){
                    return $result;
                }
            }
        }
        
        return;
    }
    
    /**
     * Плучить наиболее подходящий к товару артикул
     * 
     * @param \Application\Entity\Goods $good
     * @param string $oper
     * @return array
     */
    public function getBestArticle($good, $oper = null)
    {
        $filter = new ProducerName();
        $articles = $this->getArticleDirectSearchAllNumbersWithState($good, $oper);
        if ($articles['data']){
            foreach ($articles['data']['array'] as $row){
                foreach($good->getProducer()->getUnknownProducer() as $unknownProducer){
                    if ($filter->filter($row['brandName']) == $filter->filter($unknownProducer->getName())){
                        return $row;
                    }
                    if ($unknownProducer->getNameTd()){
                        if ($filter->filter($row['brandName']) == $filter->filter($unknownProducer->getNameTd())){
                            return $row;
                        }                        
                    }
                }        
            }
        }
        
        return;
    }
    
    /**
     * Плучить похожий товару артикул
     * 
     * @param \Application\Entity\Goods $good
     * @param bool $newSearch
     * @param string $oper
     * 
     * @return array
     */
    public function getSimilarArticle($good, $newSearch = false, $oper = null)
    {
        $articles = [];
        if ($good->getGenericGroup()->getTdId() > 0 && !$newSearch){
            $articles = $this->getArticleDirectSearchAllNumbersWithGeneric($good, null, $oper);
        } else {
            if ($good->getTokenGroup()){
                $genericGroups = $this->entityManager->getRepository(\Application\Entity\GenericGroup::class)
                        ->genericTokenGroup($good->getTokenGroup(), $good);
                foreach ($genericGroups as $row){
                    $articles = $this->getArticleDirectSearchAllNumbersWithGeneric($good, $row[0], $oper);
                    if (is_array($articles)){
                        break;
                    }
                }
            }    
        }    
        
        if (isset($articles['data'])){
            if (isset($articles['data']['array'])){
                foreach ($articles['data']['array'] as $row){
                    return $row;
                }
            }    
        }
        unset($articles);
        
        return;
    }
    
    /**
     * Получить артикул текдока
     * 
     * @param \Application\Entity\Goods $good
     * @return integer|null
     */
    public function getBestArticleId($good)
    {
        $tdData = $this->getBestArticle($good);
        if (is_numeric($tdData['articleId'])){
            return $tdData['articleId'];
        }
        
        return;
    }

    /**
     * Получить похожий артикул текдока
     * 
     * @param \Application\Entity\Goods $good
     * @return integer|null
     */
    public function getSimilarArticleId($good)
    {
        $tdData = $this->getSimilarArticle($good);
        if (is_numeric($tdData['articleId'])){
            return $tdData['articleId'];
        }        
        return;
    }
    

    /**
     * Получить группу текдока
     * 
     * @param Application\Entity\Goods $good
     * @param bool $newSearch
     * 
     * @return integer|null
     */
    public function getGenericArticleId($good, $newSearch = false)
    {
        $tdData = $this->getBestArticle($good);
        if (is_numeric($tdData['genericArticleId'])){
            return $tdData['genericArticleId'];
        }
        
        $tdSimilarData = $this->getSimilarArticle($good, $newSearch);
        if (is_numeric($tdSimilarData['genericArticleId'])){
            return $tdSimilarData['genericArticleId'];
        }
        
        return;
    }

    /**
     * Получить детальную информацию об артикуле
     * 
     * @param array $articleIds
     * @param integer $goodId
     * @param string $oper
     * @return array|Esception
     */
    public function getDirectArticlesByIds6($articleIds, $params = null, $goodId = null, $oper = null)
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

        $result = $this->getAction('getDirectArticlesByIds6', $params, $goodId, $oper);

        return $result;
    }
    
    /**
     * Получить информацию по товару
     * 
     * @param Application\Entity\Goods $good
     * @param string $oper
     * @return array
     */
    public function getDirectInfo($good, $params = null, $oper = null)
    {
        $article = $this->getBestArticle($good, $oper);
        
        if (is_array($article)){
            return $this->getDirectArticlesByIds6([$article['articleId']], $params, $good->getId(), $oper);
        }
        
        return;
    }

    /**
     * Получить информацию по похожему товару
     * 
     * @param \Application\Entity\Goods $good
     * @param string $oper
     * @return array
     */
    public function getSimilarDirectInfo($good, $params = null, $oper = null)
    {
        $article = $this->getSimilarArticle($good, $oper);
        
        if (is_array($article)){
            return $this->getDirectArticlesByIds6([$article['articleId']], $params, $good->getId(), $oper);
        }
        
        return;
    }

    /**
     * Получить машины, связанные с артикулом
     * 
     * @param integer $articleId
     * @param integer $goodId
     * @param string $oper
     * 
     * @return array|Esception
     */
    public function getArticleLinkedAllLinkingTarget3($articleId, $goodId = null, $oper = null)
    {
        $params = [
            'articleCountry' => 'RU',            
            'articleId' => $articleId, 
            'linkingTargetType' => 'P',
         ];

        $result = $this->getAction('getArticleLinkedAllLinkingTarget3', $params, $goodId, $oper);
        
        if (!$result){
            $params['linkingTargetType'] = 'O';
            $result = $this->getAction('getArticleLinkedAllLinkingTarget3', $params, $goodId, $oper);
        }

        return $result;
    }
    
    /**
     * Описание машин
     * 
     * @param array $carIds
     * @return array
     */
    public function getVehicleByIds3($carIds)
    {
        $params = [
            'articleCountry' => 'RU',            
            'lang' => 'RU',
            'countriesCarSelection' => 'RU',
            'country' => 'RU',
            'carIds' => Encoder::encode(['array' => $carIds]),
         ];

        $result = $this->getAction('getVehicleByIds3', $params);

        return $result;        
    }
    
    /**
     * Получить машины, связанные с артикулом
     * 
     * @param integer $tdId
     * @param integer $goodId
     * @param string $oper
     * @return array|null
     */
    public function getLinked($tdId, $goodId = null, $oper = null)
    {
        $cars = $this->getArticleLinkedAllLinkingTarget3($tdId, $goodId, $oper);
        $carIds = [];
        $i = 0;
        if (isset($cars['data'])){
            if (isset($cars['data']['array'])){
                foreach ($cars['data']['array'] as $links){
                    if (isset($links['articleLinkages'])){
                        if (isset($links['articleLinkages']['array'])){
                            foreach ($links['articleLinkages']['array'] as $carLinks){
                                if (isset($carLinks['linkingTargetId'])){
                                    $carIds[$i][] = $carLinks['linkingTargetId'];
                                    if (count($carIds[$i]) > 20){
                                        $i++;
                                    }
                                }    
                            }
                        }    
                    }    
                }
            }    
        }    

        if (count($carIds)){
            $result = [];
            $result['change'] = $cars['change'];
            foreach($carIds as $key => $value){
                $result[$key] = $this->getVehicleByIds3($value);
            } 
            
            return $result;
        }
        
        return;
        
    }

    /**
     * Получить машины, связанные с товаром
     * 
     * @param \Application\Entity\Goods $good
     * @return array|null
     */
    public function getGoodLinked($good)
    {
        $article = $this->getBestArticleId($good);    
        if (!$article){
            $article = $this->getSimilarArticleId($good);
        }
        return $this->getLinked($article);        
    }
    
    /**
     * Получить машины, связанные с похожим товаром
     * 
     * @param \Application\Entity\Goods $good
     * @return array|null
     */
    public function getSimilarGoodLinked($good)
    {
        $article = $this->getSimilarArticleId($good);        
        return $this->getLinked($article);        
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
     * Скачать картинку товара
     * 
     * @param \Application\Entity\Goods $good
     * 
     */
    public function getImages($good)
    {
        $articleInfo = $this->getDirectInfo($good, ['documents' => true], 'img');
        $similar = Images::SIMILAR_MATCH;
        if (!is_array($articleInfo)){
            $articleInfo = $this->getSimilarDirectInfo($good, ['documents' => true], 'img');
            $similar = Images::SIMILAR_SIMILAR;   
            if (!is_array($articleInfo)){
                $this->entityManager->getRepository(Images::class)->removeGoodImages($good, Images::STATUS_TD);                
            }
        }
        
        if (is_array($articleInfo)){
            if ($articleInfo['change']){            
                $this->entityManager->getRepository(Images::class)->addImageFolder($good, Images::STATUS_TD);
                $this->entityManager->getRepository(Images::class)->removeGoodImages($good, Images::STATUS_TD);

                if ($similar == Images::SIMILAR_SIMILAR){
                    $similarImgCount = $this->entityManager->getRepository(Images::class)
                            ->count(['good' => $good->getId()]);
                    if ($similarImgCount > 0){
                        return;
                    }
                }

                foreach($articleInfo['data']['array'] as $articleDocuments){
                    if (isset($articleDocuments['articleDocuments'])){
                        if (isset($articleDocuments['articleDocuments']['array'])){
                            foreach($articleDocuments['articleDocuments']['array'] as $document){
                                if ($document['docId'] && isset($document['docFileName']) && isset($document['docFileTypeName'])){
                                    if ($document['docFileTypeName'] != 'URL'){
                                        $uri = $this->getDocImageUri($document['docId']);
                                        $this->entityManager->getRepository(Images::class)
                                                ->saveImageGood($good, $uri, $document['docFileName'], Images::STATUS_TD, $similar);
                                    } else {   
                                        if (isset($document['docUrl'])){
                                            $url = $document['docUrl'];
                                            $this->entityManager->getRepository(Images::class)
                                                    ->saveImageUrl($good, $url, $document['docFileName'], Images::STATUS_TD, $similar);
                                        }    
                                    }    
                                }
                            }
                        }    
                    }    
                }    
            }
        }
        
        return;
    }    
}
