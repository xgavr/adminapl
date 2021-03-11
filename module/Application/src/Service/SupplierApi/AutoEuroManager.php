<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service\SupplierApi;

use Laminas\Http\Client;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;
use Application\Entity\SupplierApiSetting;

/**
 * Description of AutoEuroManager
 *
 * @author Daddy
 */
class AutoEuroManager
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
    
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
    }
    
    /**
     * Получить настройки
     * 
     * @return SupplierApiSetting
     */
    private function apiSetting()
    {
        $apiSetting = $this->entityManager->getRepository(SupplierApiSetting::class)
                ->findOneBy(['status' => SupplierApiSetting::STATUS_ACTIVE, 'supplier' => 20]); 
        if ($apiSetting){
            return $apiSetting;
        }        
        return;
    }
    
    /**
     * Получить userId из настроек апи
     * @return string
     */
    private function apiUserId()
    {
        $apiSetting = $this->apiSetting();
        if ($apiSetting){
            return $apiSetting->getUserId();
        }
        return;
    }
    
    /**
     * Получить apiUrl из настроек апи
     * @return string
     */
    private function apiUrl()
    {
        $apiSetting = $this->apiSetting();
        if ($apiSetting){
            return $apiSetting->getBaseUri();
        }
        return;
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
            
            $apiSetting = $this->apiSetting();
            if ($apiSetting){
                $userId = $apiSetting->getUserId();
                $apiUrl = $apiSetting->getBaseUri();

                $uri = $apiUrl.'/api/current/shop/'.$action.'/'.$userId.'/?';

                foreach ($params as $key => $value){
                    $uri .= "$key=$value&";
                }    

    //            var_dump($uri); exit;
                $client = new Client();
                $client->setUri(trim($uri, '&'));
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
                        $result = Decoder::decode($body, \Laminas\Json\Json::TYPE_ARRAY);
                        //$result['change'] = $this->updateAutoDbResponse($uri, $body);
                        return $result;            
                    } catch (\Laminas\Json\Exception\RuntimeException $e){
                       // var_dump($response->getBody()); exit;
                    }    
                }
            }    
        }        

        return; // $this->exception($response);
        
    }
    
    /**
     * Поиск товаров
     * 
     * @param string $art
     * @param string $brand
     * @param integer $with_crosses
     * @return array
     */
    public function stockItems($art, $brand = null, $with_crosses = 1)
    {
        $result = $this->getAction('stock_items', ['code' => $art, 'brand' => $brand, 'with_crosses' => $with_crosses]);
        return $result;                
    }
}
