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
use GuayaquilLib\ServiceAm;

/**
 * Description of LaximoManager
 *
 * @author Daddy
 */
class LaximoManager
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
     * Проверка связи c API
     * 
     * @return array
     */
    public function ping()
    {
        $settings = $this->adminManager->getLaximoSettings();
//        var_dump($settings['login'], $settings['api_key']); exit;
        $am = new ServiceAm($settings['login'], $settings['api_key']);
        print_r($am->findOem('c110'));
        print_r($am->findOem('c110', 'vic'));
        print_r($am->findOem('c110', 'vic', [Am::optionsCrosses]));
        print_r($am->findOem('90471-PX4-000', 'HONDA', [Am::optionsCrosses]));
        print_r($am->findOem('AN723K', 'AKEBONO', [Am::optionsImages]));
        print_r($am->findOem('44010-S04-961', 'honda', [Am::optionsCrosses], [Am::replacementTypePartOfTheWhole]));
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
