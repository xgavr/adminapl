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
use GuayaquilLib\Am;
use Laximo\Search\Config;
use Laximo\Search\SearchService;
use GuayaquilLib\ServiceOem;
use GuayaquilLib\objects\am\PartObject;
use GuayaquilLib\objects\am\PartCrossObject;

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
        
        $dimensions = [
            'D1'=> $laximoPart->getDimensions()->getD1(),
            'D2'=> $laximoPart->getDimensions()->getD2(),
            'D3'=> $laximoPart->getDimensions()->getD3(),
        ];
        
        $properties = [];
        foreach ($laximoPart->getProperties() as $prop){
           $properties[$prop->getCode()] = [
               'code' => $prop->getCode(),
               'name' => $prop->getPropertyName(),
               'rate' => $prop->getRate(),
               'value' => $prop->getValue(),
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
    private function getCroosPart($partCrossObject)
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
                $result[$partObject->getPartId()]['oems'] = $this->getCroosPart($partObject); 
      
            }
            
            var_dump($parts); exit;
        }
        
        return $result; 
    }
}
