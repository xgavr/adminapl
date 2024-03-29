<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service\ExternalDB;

use Laminas\Http\Client;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;
use Application\Filter\ProducerName;
use Application\Entity\Images;
use Application\Entity\AutoDbResponse;
use Application\Entity\Goods;
use Application\Entity\GenericGroup;
use Application\Entity\Oem;

/**
 * Description of ZetasoftManager
 *
 * @author Daddy
 */
class ZetasoftManager
{
    
    const HTTPS_ADAPTER = 'Laminas\Http\Client\Adapter\Curl';  
    
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
    
    /**
     * Container session.
     * @var \Laminas\Session\Container
     */
    private $sessionContainer;

    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $adminManager, $sessionContainer)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->sessionContainer = $sessionContainer;
        
//        $this->setAccess(TRUE);
    }
    
    /**
     * Получить доступ
     * 
     * @return bool
     */
    private function getAccess()
    {
        return $this->sessionContainer->access;
    }


    /**
     * Установить доступ
     * @param bool $access
     */
    private function setAccess($access)
    {
        $this->sessionContainer->access = $access;
    }
    
    /**
     * Обработка ошибок
     * @param \Laminas\Http\Response $response
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
                $error = Decoder::decode($response->getContent(), \Laminas\Json\Json::TYPE_ARRAY);
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
     * Добавить обновить запись в auto_db_response
     * 
     * @param string $uri
     * @param string $response
     * @return boolean
     */
    private function updateAutoDbResponse($uri, $response = null)
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
     * Получить сохраненный ответ запроса
     * 
     * @param string $uri
     * @return array
     */
    private function getResponseData($uri)
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
    private function getResponseTodayCount()
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
    private function updateResponse($uri, $response)
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
            
            $settings = $this->adminManager->getZetasoftSettings();
            
            $uri = $settings['host'].'/'.$action;
            
            $query = [];
            foreach ($params as $key => $value){
                $query[] = "$key=$value";
            }    
            if (count($query)){
                $uri .= '?'.implode('&', $query);
            }    
            
            $result = $this->getResponseData($uri);
            if (is_array($result)){
                if (isset($result['status'])){
                    if ($result['status'] === 404){
                        return;
                    }
                }
                $result['change'] = true;
                return $result;
            }
            
            if ($settings['max_query'] <= $this->getResponseTodayCount()){
                throw new \Exception("Достигнут лимит запросов {$settings['max_query']}");
            }
                        
            if ($settings['do_query'] == 2){
                return; //не подключаться к серверу
            }

//            if (!$this->getAccess()){
//                return;
//            }
//            var_dump($uri); exit;
            $client = new Client();
            $client->setUri($uri);
            $client->setAdapter($this::HTTPS_ADAPTER);
            $client->setMethod('GET');
            $client->setOptions(['timeout' => 60]);

            $headers = $client->getRequest()->getHeaders();
            $headers->addHeaders([
                'Content-Type: application/json',
                'Authorization: Bearer '.$settings['api_key'],
            ]);

            $client->setHeaders($headers);

            $response = $client->send();
//            var_dump($response->getContent());exit;
            if ($response->isOk() || $response->isNotFound()){
                try {
                    $body = $response->getBody();
                    $result = Decoder::decode($body, \Laminas\Json\Json::TYPE_ARRAY);
                    $result['change'] = $this->updateAutoDbResponse($uri, $body);
                    if (isset($result['status'])){
                        if ($result['status'] === 404){
                            return;
                        }
                    }
                    return $result;            
                } catch (\Laminas\Json\Exception\RuntimeException $e){
                   // var_dump($response->getBody()); exit;
                }    
            } elseif ($response->getStatusCode() === 402){   
//                $this->setAccess(FALSE);
                throw new \Exception("Оплаченный лимит исчерпан");
            } else {
//                $this->setAccess(FALSE);
                throw new \Exception($response->getStatusCode()." Неизвестная ошибка");
            }
        }        

        return; // $this->exception($response);
        
    }
    
    /**
     * Проверка связи c API
     * 
     * @return array
     */
    public function ping()
    {
        return $this->getAction('ping');
    }
    
    /**
     * Получить JWT-токен по логину и паролю
     * 
     * @return array
     */
    public function token()
    {
        $settings = $this->adminManager->getZetasoftSettings();
            
        $uri = $settings['host'].'/User/Token';

        $client = new Client();
        $client->setUri($uri);
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('POST');
        $client->setRawBody(Encoder::encode([
            'email' => $settings['login'],
            'password' => $settings['md5_key'],
        ]));
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
        ]);

        $client->setHeaders($headers);

        $response = $client->send();

        try {
            $body = $response->getBody();
            $result = Decoder::decode($body, \Laminas\Json\Json::TYPE_ARRAY);
            if ($response->isOk()){
                
            }
            return $result;            
        } catch (\Laminas\Json\Exception\RuntimeException $e){
           // var_dump($response->getBody()); exit;
        }    

        return; // $this->exception($response);
    }
    
    
    
    /**
     * Найти запчасти по коду (артикулу) (уточнение бренда)
     * 
     * @param Goods $good
     * @return array|Esception
     */
    public function getVendorCode($good)
    {
        return $this->getAction('ru-ru/Part/VendorCode', [
            'vendorCode' => urlencode($good->getCode()),
            'vendorCodeStartsWith' => 'false',
            'disableOem' => 'true',
            'NewVendorCodes' => 'false',
            'OldVendorCodes' => 'false',
            'Barcodes' => 'false',
            'TradeCodes' => 'false',
            'OemCodes' => 'true',
            'KitCodes' => 'true',
            'Properties' => 'true',
            'Documents' => 'false',
            'TechnicalInformation' => 'false',
            'Images' => 'true',
            'Links' => 'false',
            'AnaloguesCodes' => 'true',
            'Page' => 1,
            'PerPage' => 25,
        ]);
    }
    
    /**
     * Найти запчасти по коду (артикулу) (уточнение бренда)
     * 
     * @param string $code
     * @return array|Esception
     */
    public function getVendorCodeV2($code)
    {
        return $this->getAction('ru-ru/v2/Part/VendorCode', [
            'vendorCode' => urlencode($code),
            'vendorCodeStartsWith' => 'false',
            'disableOem' => 'true',
            'NewVendorCodes' => 'false',
            'OldVendorCodes' => 'false',
            'Barcodes' => 'false',
            'TradeCodes' => 'false',
            'OemCodes' => 'true',
            'KitCodes' => 'true',
            'Properties' => 'true',
            'Documents' => 'false',
            'TechnicalInformation' => 'false',
            'Images' => 'true',
            'Links' => 'false',
            'AnaloguesCodes' => 'true',
        ]);
    }
    
    /**
     * Найти запчасти по коду (артикулу) и идентификатору группы запчастей
     * 
     * @param string $vendorCode
     * @param integr $partGroupId
     * @return array|Exception
     */
    public function getVendorCodeAndPartGroup($vendorCode, $partGroupId)
    {
        return $this->getAction('ru-ru/Part/VendorCodeAndPartGroup', [
            'vendorCode' => urlencode($vendorCode),
            'partGroupId' => $partGroupId,
            'vendorCodeStartsWith' => 'false',
            'NewVendorCodes' => 'false',
            'OldVendorCodes' => 'false',
            'Barcodes' => 'false',
            'TradeCodes' => 'false',
            'OemCodes' => 'true',
            'KitCodes' => 'true',
            'Properties' => 'true',
            'Documents' => 'false',
            'TechnicalInformation' => 'false',
            'Images' => 'true',
            'Links' => 'false',
            'AnaloguesCodes' => 'true',
            'Page' => 1,
            'PerPage' => 25,
        ]);
    }
    
    /**
     * Найти запчасти по коду (артикулу) и идентификатору группы запчастей
     * 
     * @param string $vendorCode
     * @param integr $partGroupId
     * @return array|Exception
     */
    public function getVendorCodeAndPartGroupV2($vendorCode, $partGroupId)
    {
        return $this->getAction('ru-ru/v2/Part/VendorCodeAndPartGroup', [
            'vendorCode' => urlencode($vendorCode),
            'partGroupId' => $partGroupId,
            'vendorCodeStartsWith' => 'false',
            'disableOem' => 'true',
            'NewVendorCodes' => 'false',
            'OldVendorCodes' => 'false',
            'Barcodes' => 'false',
            'TradeCodes' => 'false',
            'OemCodes' => 'true',
            'KitCodes' => 'true',
            'Properties' => 'true',
            'Documents' => 'false',
            'TechnicalInformation' => 'false',
            'Images' => 'true',
            'Links' => 'false',
            'AnaloguesCodes' => 'true',
        ]);
    }
    
    /**
     * Получить полный список групп запчастей
     * 
     * @return array
     */
    public function getPartGroups()
    {
        return $this->getAction('ru-ru/PartGroup');        
    }
    
    /**
     * Получить похожий по группе articleId
     * 
     * @param integer $goodId
     * @params string $code
     * @param string $genericGroupTdId
     * 
     * @return array|null|Exception
     */
    public function getArticleDirectSearchAllNumbersWithGeneric($goodId, $code, $genericGroupTdId = 0)
    {
        if ($genericGroupTdId>0){
            $result = $this->getVendorCodeAndPartGroupV2($code, $genericGroupTdId);
            if (isset($result['data'])){                
                return $result;
            }
            
            $sources = [Oem::SOURCE_SUP, Oem::SOURCE_CROSS, Oem::SOURCE_MAN];
            foreach ($sources as $source){
                $oemsQuery = $this->entityManager->getRepository(Goods::class)
                        ->findOems($goodId, ['limit' => 10, 'source' => $source]);
                $oems = $oemsQuery->getResult();

                foreach ($oems as $oem){
                    $result = $this->getVendorCodeAndPartGroupV2($oem->getOe(), $genericGroupTdId);
                    if (isset($result['data'])){
                        return $result;
                    }
                }                                
            }            
        }
        
        return;
    }
    
    /**
     * Плучить похожий товару артикул
     * 
     * @param integer $goodId
     * @param string $code
     * @param integer $genericGroupTdId
     * @param integer $tokenGroupId
     * @param bool $newSearch
     * 
     * @return array
     */
    public function getSimilarArticle($goodId, $code, $genericGroupTdId, $tokenGroupId = null, $newSearch = false)
    {
        $articles = [];
        if ($genericGroupTdId > 0 && !$newSearch){
            $articles = $this->getArticleDirectSearchAllNumbersWithGeneric($goodId, $code, $genericGroupTdId);
        } else {
            if ($tokenGroupId){
                $genericGroups = $this->entityManager->getRepository(GenericGroup::class)
                        ->genericTokenGroup($tokenGroupId, $goodId);
                foreach ($genericGroups as $row){
                    $articles = $this->getArticleDirectSearchAllNumbersWithGeneric($goodId, $code, $row[0]->getTdId());
                    if (is_array($articles)){
                        break;
                    }
                }
            }    
        }    
        
        if (isset($articles['data'])){
            if (isset($articles['data'])){
                $change = $articles['change'];
                foreach ($articles['data'] as $row){
                    $row['change'] = $change;
                    return $row;
                }
            }    
        }
        unset($articles);
        
        return;
    }    

    /**
     * Плучить наиболее подходящий к товару артикул
     * 
     * @param integer $goodId
     * @param string $code
     * @return array
     */
    public function getBestArticle($goodId, $code)
    {
        $filter = new ProducerName();
        $articles = $this->getVendorCodeV2($code);
        if (is_array($articles)){
            $change = $articles['change'];
            if ($articles['data']){
                $upNames = $this->entityManager->getRepository(Goods::class)
                        ->findUnknownProducerNames($goodId);
                foreach($upNames as $upName){
                    foreach ($articles['data'] as $row){
                        if ($filter->filter($row['vendorName']) == $filter->filter($upName['name'])){
                            $row['change'] = $change;
                            return $row;
                        }
                        if (!empty($upName['nameTd'])){
                            if ($filter->filter($row['vendorName']) == $filter->filter($upName['nameTd'])){
                                $row['change'] = $change;
                                return $row;
                            }                        
                        }
                    }
                }    
            }
        }    
        
        return;
    }
    
    /**
     * Получить информацию по товару
     * 
     * @param integer $goodId
     * @param string $code
     * @return array
     */
    public function getDirectInfo($goodId, $code)
    {
        return $this->getBestArticle($goodId, $code);        
    }

    /**
     * Получить информацию по похожему товару
     * 
     * @param integer $goodId
     * @param string $code
     * @param integer $genericGroupTdId
     * @param integer $tokenGroupId
     * 
     * @return array
     */
    public function getSimilarDirectInfo($goodId, $code, $genericGroupTdId, $tokenGroupId = null)
    {
        return $this->getSimilarArticle($goodId, $code, $genericGroupTdId, $tokenGroupId);        
    }

    /**
     * Получить список доступных производителей транспортного средства
     * 
     * @param string $vendorCode
     * @param string $vendorName
     * @return array|Exception
     */
    public function getManufacturers($vendorCode, $vendorName)
    {
        return $this->getAction('ru-ru/Manufacturer/Part', [
            'VendorCode' => urlencode($vendorCode),
            'VendorName' => urlencode($vendorName),
            'types' => 'All',
            'popular' => 'true',
                ]);
    }

    /**
     * Получить модели производителя
     * @param string $vendorCode
     * @param string $vendorName
     * @param integer $manufacturerId
     * @return array|Exception
     */
    public function getModels($vendorCode, $vendorName, $manufacturerId)
    {
        return $this->getAction('ru-ru/Model/Part', [
            'VendorCode' => urlencode($vendorCode),
            'VendorName' => urlencode($vendorName),
            'manufacturerId' => $manufacturerId, 
            'types' => 'All',
            'popular' => 'true',
            ]);
    }

    /**
     * Получение списка модификаций
     * @param string $vendorCode
     * @param string $vendorName
     * @param integer $manufacturerId
     * @param integer $modelId
     * @return array|Esception
     */
    public function getModifications($vendorCode, $vendorName, $manufacturerId, $modelId)
    {
        return $this->getAction('ru-ru/Modification/Part', [
            'VendorCode' => urlencode($vendorCode),
            'VendorName' => urlencode($vendorName),
            'manufacturerId' => $manufacturerId, 
            'modelId' => $modelId,
            'types' => 'All',
            'EnginesCodes' => 'true',
            'Wheelbases' => 'true',
            'Axles' => 'true',
            'CabinsNames' => 'true',
            'PlatformCodes' => 'true',
            'Page' => 1,
            'PerPage' => 25,
            ]);
    }
    
    /**
     * Получить машины, связанные с товаром
     * 
     * @param Goods $good
     * @return array|null
     */
    public function getGoodLinked($good)
    {
        $article = $this->getBestArticle($good->getId(), $good->getCode());  
//        var_dump($article); exit;
        if (!$article){
            $article = $this->getSimilarArticle($good->getId(), $good->getCode(), $good->getGenericGroup()->getTdId(), $good->getTokenGroupId());
        }
        
        if (is_array($article)){
            $manufacturers = $this->getManufacturers($article['vendorCode'], $article['vendorName']);
            $models = [];
            if (is_array($manufacturers)){
                if (isset($manufacturers['data'])){
//                    var_dump(count($manufacturers['data'])); exit;
                    if (count($manufacturers['data']) < 10){
                        foreach ($manufacturers['data'] as $manufacturer){
                            $models[$manufacturer['id']] = $this->getModels($article['vendorCode'], $article['vendorName'], $manufacturer['id']);
                        }
                    }
                }
            }    
            $cars = [];
            if (count($models)){
                foreach ($models as $manufacturerId => $modelData){
                    if (isset($modelData['data'])){
                        foreach ($modelData['data'] as $model){
                            $cars[$manufacturerId][$model['id']] = $this->getModifications($article['vendorCode'], $article['vendorName'], $manufacturerId, $model['id']);
                        }
                    }                    
                }
            }

            return $cars;
        }    
    }
        
    /**
     * Получить машины, связанные с похожим товаром
     * 
     * @param Goods $good
     * @return array|null
     */
    public function getSimilarGoodLinked($good)
    {
        $article = $this->getSimilarArticle($good->getId(), $good->getCode(), $good->getGenericGroup()->getTdId(), $good->getTokenGroupId());        
        return $this->getGoodLinked($article);        
    }

    /**
     * Получить картинку по номеру документа
     * 
     * @param string $url
     */
    public function getDocImageUri($url)
    {
        $settings = $this->adminManager->getZetasoftSettings();            
        $result = parse_url($settings['host'], PHP_URL_SCHEME).'://'.parse_url($settings['host'], PHP_URL_HOST).$url;
        return $result;
    }

    
    /**
     * Скачать картинку товара
     * 
     * @param Goods $good
     * 
     */
    public function getImages($good)
    {
        $articleInfo = $this->getDirectInfo($good->getId(), $good->getCode());
        $similar = Images::SIMILAR_MATCH;
        if (!is_array($articleInfo)){
            $articleInfo = $this->getSimilarDirectInfo($good->getId(), $good->getCode(), $good->getGenericGroup()->getTdId(), $good->getTokenGroupId());
            $similar = Images::SIMILAR_SIMILAR;   
            if (!is_array($articleInfo)){
                $this->entityManager->getRepository(Images::class)->removeGoodImages($good, Images::STATUS_TD);                
            }
        }
        if (is_array($articleInfo)){
            $change = false;
            if (isset($articleInfo['change'])){
                $change = $articleInfo['change'];
            }    
            if (!$change){
                $imgCount = $this->entityManager->getRepository(Images::class)
                        ->count(['good' => $good->getId(), 'status' => Images::STATUS_TD]);
                $change = $imgCount === 0;
            }
            if ($change){
                $this->entityManager->getRepository(Images::class)->addImageFolder($good, Images::STATUS_TD);
                $this->entityManager->getRepository(Images::class)->removeGoodImages($good, Images::STATUS_TD);

                if ($similar == Images::SIMILAR_SIMILAR){
                    $similarImgCount = $this->entityManager->getRepository(Images::class)
                            ->count(['good' => $good->getId()]);
                    if ($similarImgCount > 0){
                        return;
                    }
                }

                if (isset($articleInfo['images'])){
                    foreach($articleInfo['images'] as $document){
//                        $uri = $this->getDocImageUri($document['url800']);
                        $uri = trim($document['url400']);
//                        var_dump($uri); exit;
                        $this->entityManager->getRepository(Images::class)
                                ->saveImageGood($good, $uri, $document['fileName'], Images::STATUS_TD, $similar);
                    }
                }    
            }
        }
        
        return;
    }    
    
}
