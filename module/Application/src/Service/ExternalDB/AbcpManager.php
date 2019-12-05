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

/**
 * Description of AutodbManager
 *
 * @author Daddy
 */
class AbcpManager
{
    
    const HTTPS_ADAPTER = 'Zend\Http\Client\Adapter\Curl';  
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
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
     */
    private function getAutoDbResponse($uri)
    {
        $autoDbResponse = $this->entityManager->getRepository(AutoDbResponse::class)
                ->findOneByUriMd5(md5(mb_strtoupper(trim($uri), 'UTF-8')));
        
        if ($autoDbResponse == null){
            return false;
        }
        
        return $autoDbResponse->getResponseAsArray();
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
        
        if ($autoDbResponse->getResponseMd5() != md5(mb_strtoupper(trim($response), 'UTF-8'))){
            $this->entityManager->getRepository(AutoDbResponse::class)
                    ->updateAutoDbResponse($uri, $response);
            return true;            
        }
        
        return false;
    }
    
    /**
     * Базовый метод доступа к апи
     * 
     * @param string $action
     * @param array $params
     * @param integer $goodId
     * @param string $oper
     * @return array|Exception
     */    
    public function getAction($action, $params = null, $goodId = null, $oper = null)
    {
        ini_set('memory_limit', '512M');       
        
        if ($action){
            $settings = $this->adminManager->getAbcpSettings();
            
            $uri = $settings['host'].'/'.$action.'?';

            $params['userlogin'] = $settings['login'];
            $params['userpsw'] = $settings['md5_key'];
            $params['userkey'] = $settings['api_key'];
            foreach ($params as $key => $value){
                $uri .= "&$key=$value";
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
                    $result = Decoder::decode($response->getBody(), \Zend\Json\Json::TYPE_ARRAY);
                    $result['change'] = $this->updateAutoDbResponse($uri, $body);
                    return $result;            
                } catch (\Zend\Json\Exception\RuntimeException $e){
                   // var_dump($response->getBody()); exit;
                }    
            }
        }        

        return; // $this->exception($response);
        
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
     * @param \Application\Entity\Goods $good
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
