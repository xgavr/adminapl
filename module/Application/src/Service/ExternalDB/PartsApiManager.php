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
use Application\Entity\UnknownProducer;
use Application\Entity\Producer;
use Application\Entity\Goods;
use Application\Entity\Make;

/**
 * Description of AutodbManager
 *
 * @author Daddy
 */
class PartsApiManager
{
        
    const HTTPS_ADAPTER = 'Laminas\Http\Client\Adapter\Curl';  
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
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
            
            $settings = $this->adminManager->getPartsApiSettings();
            
            $uri = $settings['host'].'?act='.$action;

//            $params['userlogin'] = $settings['login'];
//            $params['userpsw'] = $settings['md5_key'];
            $params['key'] = $settings['api_key'];
            foreach ($params as $key => $value){
                $uri .= "&$key=$value";
            }    
            
            $result = $this->getAutoDbResponseData($uri);
            if (is_array($result)){
                return $result;
            }
            
//            var_dump($uri); exit;
            $client = new Client();
            $client->setUri($uri);
            $client->setAdapter($this::HTTPS_ADAPTER);
            $client->setMethod('GET');
            $client->setOptions(['timeout' => 30]);

            $headers = $client->getRequest()->getHeaders();
    //        $headers->addHeaders([
    //            'Content-Type: application/json',
    //        ]);

            $client->setHeaders($headers);

            $response = $client->send();

            if ($response->isOk()){
                try {
                    $body = $response->getBody();
                    $result = Decoder::decode($body, \Laminas\Json\Json::TYPE_ARRAY);
                    $result['change'] = $this->updateAutoDbResponse($uri, $body);
                    return $result;            
                } catch (\Laminas\Json\Exception\RuntimeException $ex){
                   // var_dump($response->getBody()); exit;
                } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $ex){
                    
                }    
            }
        }        

        return; // $this->exception($response);
        
    }
    
    /**
     * КАТАЛОГ: 
     * Список производителей автомобилей выбранного класса: 
     * passenger - легковые, 
     * commercial - грузовые и коммерческие, 
     * moto - мототехника
     * 
     * @param string $group
     * @return array|Esception
     */
    public function getMakes($group)
    {
        return $this->getAction('getMakes', ['group' => $group]);
    }

    /**
     * КАТАЛОГ: 
     * Список моделей выбранного производителя: 
     * passenger - легковые, 
     * commercial - грузовые и коммерческие, 
     * moto - мототехника
     * 
     * @param integer $makeId
     * @param string $group
     * @return array|Esception
     */
    public function getModels($makeId, $group)
    {
        return $this->getAction('getModels', ['make' => $makeId, 'group' => $group]);
    }

    /**
     * КАТАЛОГ: 
     * Список моделей выбранного производителя: 
     * passenger - легковые, 
     * commercial - грузовые и коммерческие, 
     * moto - мототехника
     * 
     * @param integer $makeId
     * @param integer $modelId
     * @param string $group
     * @return array|Esception
     */
    public function getCars($makeId, $modelId, $group)
    {
        return $this->getAction('getCars', ['make' => $makeId, 'model' => $modelId, 'group' => $group]);
    }

    /**
     * КАТАЛОГ: 
     * АВТОНОРМЫ
     * 
     * @param integer $carId
     * @return array|Esception
     */
    public function getFillVolumes($carId)
    {
        return $this->getAction('GetFillVolumes', ['carid' => $carId]);
    }

}
