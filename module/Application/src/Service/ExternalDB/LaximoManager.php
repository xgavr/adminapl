<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service\ExternalDB;


use Laminas\Json\Decoder;
use Laminas\Json\Encoder;
use Application\Entity\Images;
use Application\Entity\Goods;
use Application\Entity\Oem;
use GuayaquilLib\ServiceAm;
use GuayaquilLib\Am;
use Laximo\Search\Config;
use Laximo\Search\SearchService;
use GuayaquilLib\ServiceOem;
use GuayaquilLib\objects\am\PartObject;
use GuayaquilLib\objects\am\PartCrossObject;
use Application\Entity\GoodAttributeValue;

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
    
    /**
     * 
     * @var ServiceAm
     */
    private $am;

    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $adminManager, $sessionContainer)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->sessionContainer = $sessionContainer;
        
//        $this->setAccess(TRUE);
        $settings = $this->adminManager->getLaximoSettings();
        
        $this->am = new ServiceAm($settings['login'], $settings['api_key']);
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
//        
//        Laximo.DOC
//        $am = new ServiceAm($settings['login'], $settings['api_key']);
//        
        $oem = new ServiceOem($settings['login'], $settings['api_key']);
//        print_r($oem->listCatalogs());
//        print_r($oem->getCatalogInfo('CFIAT84'));
//        

//        print_r($oem->findVehicle('XZU423-0001026'));
//        print_r($oem->findVehicleByVin('VR3UDYHZSMJ631263'));
//        print_r($oem->findVehicleByFrameNo('XZU423-0001026'));
//        print_r($oem->execCustomOperation('DAF', 'findByChassisNumber', ['chassis' => 'EB100567']));
//        print_r($oem->getVehicleInfo('TOYOTA00', '$*KwFEcGEOQQ9FN0UYBAtiQhwIKC8xR0RDQFFXVBI3C1MfA1JIDWdvNzo1U1pVGQENGx8uLSVFRERAQh8QDURBUl1UFBNQFQMKQUZBQkZVXFBCQh9MVSgrI0NCQQJ1bDA6J1NaVRYbSwMHREBIDAAAAACTKWcw$', '0'));
        print_r($oem->getVehicleInfo('PSA_P202311', '$*KwFkWWR1AGUzJS4XK3BsIyd0ARMeZmVhZS0tPyV1anMbdXxweSMndAECYgMTCRpVUlAcYWNgZGFjcHJ_QhZiGxUfJxUIQ1NrYnYuDgkXaGI9NSVgZ2FjKAggIXtBSx8ZLg4mJCYSWVMuAxMJGQwEHRg5MixkYWMAAAAAyBeVuQ==$', '0'));
//        
//        print_r($am->findOem('c110'));
//        print_r($am->findOem('c110', 'vic'));
//        print_r($am->findOem('c110', 'vic', [Am::optionsCrosses]));
//        print_r($am->findOem('90471-PX4-000', 'HONDA', [Am::optionsCrosses]));
//        print_r($am->findOem('AN723K', 'AKEBONO', [Am::optionsImages]));
//        print_r($am->findOem('44010-S04-961', 'honda', [Am::optionsCrosses], [Am::replacementTypePartOfTheWhole]));
        
        ///Laximo.Search SDK                
//        $service = new SearchService(new Config(['login' => $settings['login'], 'password' => $settings['api_key']]));
//        
//        print_r($service->user());
//        print_r($service->search('фильтр маслянный XW8ZZZ7PZHG003807'));        
    }
    
    /**
     * 
     * @param int $manufacturerId
     * @return array
     */
    private function getManufacturerInfo($manufacturerId)
    {

        $manufacturerObject = $this->am->getManufacturerInfo($manufacturerId);
        
        if (!$manufacturerObject){
            return;
        }
        
        $result = [
            'name' => $manufacturerObject->getName(),
            'isOriginal' => $manufacturerObject->isOriginal(),
        ];
                
        return $result;        
    }
    
    /**
     * @param PartObject $laximoPart
     * @return array
     */
    private function partToArray($laximoPart)
    {
        $images = [];
        foreach($laximoPart->getImages() as $image){
            $images[] = [
                'filename' =>  $image->getFilename(),
                'hight' =>  $image->getHeight(),
                'width' =>  $image->getWidth(),
            ];                   
        }
        
        $dimensions = [];
        if (!empty($laximoPart->getDimensions())){
            $dimensions = [
                [
                    'propertyId' => 10001,
                    'propertyShortName' => 'd1 [мм]',
                    'propertyName' => 'd1 [мм]',
                    'propertyType' => 'N',
                    'propertyUnitName' => 'мм',
                    'value' => $laximoPart->getDimensions()->getD1(),
                    'id' => md5($laximoPart->getDimensions()->getD1()),                    
                    'valueId' => md5($laximoPart->getDimensions()->getD1()),                    
                ],
                [
                    'propertyId' => 10002,
                    'propertyShortName' => 'd2 [мм]',
                    'propertyName' => 'd2 [мм]',
                    'propertyType' => 'N',
                    'propertyUnitName' => 'мм',
                    'value' => $laximoPart->getDimensions()->getD2(),
                    'id' => md5($laximoPart->getDimensions()->getD2()),                    
                    'valueId' => md5($laximoPart->getDimensions()->getD2()),                    
                ],
                [
                    'propertyId' => 10003,
                    'propertyShortName' => 'd3 [мм]',
                    'propertyName' => 'd3 [мм]',
                    'propertyType' => 'N',
                    'propertyUnitName' => 'мм',
                    'value' => $laximoPart->getDimensions()->getD3(),
                    'id' => md5($laximoPart->getDimensions()->getD3()),                    
                    'valueId' => md5($laximoPart->getDimensions()->getD3()),                    
                ],
                [
                    'propertyId' => 10005,
                    'propertyShortName' => 'Вес [кг]',
                    'propertyName' => 'Вес [кг]',
                    'propertyType' => 'N',
                    'propertyUnitName' => 'кг',                    
                    'value' => $laximoPart->getWeight(),
                    'id' => md5($laximoPart->getWeight()),                    
                    'valueId' => md5($laximoPart->getWeight()),                    
                ],
                [
                    'propertyId' => 10004,
                    'propertyShortName' => 'Объем [м³]',
                    'propertyName' => 'Объем [м³]',
                    'propertyType' => 'N',
                    'propertyUnitName' => 'куб. м.',
                    'value' => $laximoPart->getVolume(),
                    'id' => md5($laximoPart->getVolume()),                    
                    'valueId' => md5($laximoPart->getVolume()),                    
                ],
            ];
        }    
        
        $properties = [];
        foreach ($laximoPart->getProperties() as $prop){
           $properties[$prop->getCode()] = [
               'code' => $prop->getCode(),
               'propertyId' => $prop->getCode() + 5000,
               'id' => $prop->getCode() + 5000,
               'name' => $prop->getPropertyName(),
               'propertyShortName' => $prop->getPropertyName(),
               'propertyName' => $prop->getPropertyName(),
               'rate' => $prop->getRate(),
               'propertyUnitName' => $prop->getRate(),
               'propertyType' => 'K',
               'value' => $prop->getValue(),
               'valueId' => md5($prop->getValue()),
            ]; 
        }        
        
        $result = [
            'partId' => $laximoPart->getPartId(),
            'dimensions' => $dimensions,
            'formattedOem' => $laximoPart->getFormattedOem(),
            'images' => $images,
            'manufacturer' => $this->getManufacturerInfo($laximoPart->getManufacturerId()),
            'manufacturerId' => $laximoPart->getManufacturerId(),
            'name' => $laximoPart->getName(),
            'oem' => $laximoPart->getOem(),
            'properties' => $properties,
            'volume' => $laximoPart->getVolume(),
            'weight' => $laximoPart->getWeight(),
        ];
        
        return $result;
    }
    
    /**
     * @param PartCrossObject $partCrossObject
     * @retunr array
     */
    private function crossPartToArray($partCrossObject)
    {
        $result = [
            'rate' => $partCrossObject->getRate(),
            'type' => $partCrossObject->getType(),
            'part' => $this->partToArray($partCrossObject->getPart()),
            'way' => $partCrossObject->getWay(),
        ];
        
        return $result;
    }
    
    /**
     * 
     * @param array $params
     * @return array
     */
    public function findOem($params)
    {
        if (!is_array($params)){
            return;
        }
        
        $result = [];
        
        $code = null;
        if (!empty($params['code'])){
            $code = $params['code'];
        }
        
        $brand = null;
        if (!empty($params['brand'])){
            $brand = $params['brand'];
        }
//        var_dump($code, $brand); exit;
        if ($code){
            $parts = $this->am->findOem($code, $brand, [Am::optionsCrosses, Am::optionsImages, Am::optionsNames, Am::optionsProperties]);
            
            if (!$parts){
                return;
            }
            
            foreach ($parts->getOems() as $partObject){

                $result[$partObject->getPartId()] = $this->partToArray($partObject); 
                foreach ($partObject->getReplacements() as $crossPart){
                    $result[$partObject->getPartId()]['oems'][] = $this->crossPartToArray($crossPart); 
                }    
                
//                var_dump($partObject);
//                var_dump($partObject->getProperties());
            }
            
//            var_dump($parts); exit;
        }
        
        return $result; 
    }
    
    /**
     * Сохранить картинку
     * @param Goods $good
     * @param array $part
     */
    private function saveImage($good, $part)
    {
        if (!empty($part['images'])){
            
//            var_dump($part['images']);
            
            if (count($part['images']) > 0){
                foreach ($good->getImages() as $oldImage){
                    if ($oldImage->getSimilar() !== Images::SIMILAR_MATCH){
                        $this->entityManager->getRepository(Images::class)
                                ->removeImage($oldImage);
                    }
                }

                // если картинка есть, не добавляем
                if ($good->getImages()->count() !== 0){
                    return;
                }

                $this->entityManager->getRepository(Images::class)
                        ->addImageFolder($good, Images::STATUS_TD);                

                foreach ($part['images'] as $image){

                    $this->entityManager->getRepository(Images::class)
                        ->saveImageGood($good, $image['filename'], basename($image['filename']), Images::STATUS_TD, Images::SIMILAR_MATCH);

                }
            }    
        }
        
        return;
    }
    
    /**
     * Сохранить номера
     * @param Goods $good
     * @param array $part
     */
    private function saveOem($good, $part)
    {
        if (!empty($part['oems'])){                
        
            foreach ($part['oems'] as $value){
                $oem = [
                    'oeNumber' => $value['part']['formattedOem'],
                    'brandName' => $value['part']['manufacturer']['name'],
                ];
                
                $source = Oem::SOURCE_CROSS;
                if ($value['part']['manufacturer']['isOriginal'] == true){
                    $source = Oem::SOURCE_TD;
                }
                
                $this->entityManager->getRepository(Oem::class)
                    ->addOemToGood($good->getId(), $oem, $source);                
            }
        }
        
        return;        
    }
    
    /**
     * Сохранить характеристики
     * 
     * @param Goods $good
     * @param array $part
     */
    private function saveAttributes($good, $part)
    {
        $attrCount = $this->entityManager->getRepository(GoodAttributeValue::class)
                    ->count(['good' => $good->getId()]);
        
//        var_dump($attrCount, $part['properties'], $part['dimensions']); exit;
        
        if ($attrCount > 0){
            return;
        }
        
        if (!empty($part['properties'])){                
        
            foreach ($part['properties'] as $attr){
                $this->entityManager->getRepository(GoodAttributeValue::class)
                        ->addGoodAttributeValue($good, $attr);
            }                
        }
        
        if (!empty($part['dimensions'])){                
        
            foreach ($part['dimensions'] as $attr){
//                var_dump($attr);
                if (!empty($attr['value'])){
                    $this->entityManager->getRepository(GoodAttributeValue::class)
                            ->addGoodAttributeValue($good, $attr);
                }    
            }                
        }
        
        return;        
        
    }
    
    /**
     * 
     * @param Goods $good
     */
    public function updateGoodFromLaximo($good)
    {
        $laximoData = $this->findOem([
            'code' => $good->getCode(),
            'brand' => $good->getProducer()->getName(),
        ]);
        
        if(is_array($laximoData)){
            foreach ($laximoData as $part){
                
                //1. Картирка
                $this->saveImage($good, $part);
                
                //2. Номера
                $this->saveOem($good, $part);
                
                //3. Атрибуты
                $this->saveAttributes($good, $part);
            }
        }
        
        return $laximoData;
    }
}
