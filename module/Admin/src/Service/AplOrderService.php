<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Bank\Entity\Statement;
use Laminas\Http\Client;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;
use Company\Entity\Office;
use Application\Entity\Supplier;
use Company\Entity\Legal;
use Company\Entity\Contract;
use Stock\Entity\Ptu;
use Stock\Entity\Vtp;
use Stock\Entity\Ot;
use Application\Entity\Contact;
use Application\Entity\Client as AplClient;
use Stock\Entity\St;
use Stock\Entity\Pt;
use User\Entity\User;
use Company\Entity\Cost;
use Application\Entity\Producer;
use Application\Filter\ProducerName;
use Application\Entity\UnknownProducer;
use Application\Entity\Goods;
use Application\Filter\ArticleCode;
use Laminas\Validator\Date;


/**
 * Description of AplOrderService
 *
 * @author Daddy
 */
class AplOrderService {

    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Admin manager
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;

    /**
     * Apl service manager
     * @var \Admin\Service\AplService
     */
    private $aplService;

    /**
     * Apl doc service manager
     * @var \Admin\Service\AplDocService
     */
    private $aplDocService;

    
    public function __construct($entityManager, $adminManager, $aplSevice,
            $aplDocService)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->aplService = $aplSevice;
        $this->aplDocService = $aplDocService;
    }
    
    private function aplApi() 
    {
        return $this->aplService->aplApi();
    }
    
    private function aplApiKey()
    {
        return $this->aplService->aplApiKey();
    }

    /**
     * Обновить статус загруженной машины клиента
     * @param integer $userId
     * @return boolean
     */
    public function unloadedUserModel($userId)
    {
        $result = true;
        if (is_numeric($userId)){
            $url = $this->aplApi().'aa-user-model?api='.$this->aplApiKey();

            $post = [
                'userId' => $userId,
            ];
            
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setParameterPost($post);

            $result = $ok = FALSE;
            try{
                $response = $client->send();
//                var_dump($response->getBody()); exit;
                if ($response->isOk()) {
                    $result = $ok = TRUE;
                }
            } catch (\Laminas\Http\Client\Adapter\Exception\RuntimeException $e){
                $ok = true;
            } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                $ok = true;
            }    
            
            if ($ok){
            }

            unset($post);
        }    
        return $result;        
    }

    /*
     * Получить машину клиентf
     * @param array $row;
     */
    public function getUserModel($row)
    {
        $client = $this->entityManager->getRepository(AplClient::class)
                ->findOneBy(['aplId' => $row['id']]);
        
        if (!$client){
            return false;
        }
        
        $contact = $client->getLegalContact();
        
        if (!$contact){
            return false;
        }
        
        $data = [
            'name' => $row['name'],
            'status' => ($row['publish'] == 1 ? AplClient::STATUS_ACTIVE:AplClient::STATUS_RETIRED),
            'aplId' => $row['id'],
        ];    

        if ($client){                    
            $this->clientManager->updateClient($client, $client_data);                    
        } else {                            
            $client = $this->clientManager->addNewClient($client_data);                        
        }

        return true;
    }
    
    /**
     * Загрузить машины пользователей
     * @return 
     */
    public function uploadUserModels()
    {
        set_time_limit(1800);
        $startTime = time();
        $url = $this->aplApi().'get-user-models?api='.$this->aplApiKey();
        
        $data = file_get_contents($url);
        if ($data){
            $data = (array) Json::decode($data);
        } else {
            $data = [];
        }
        
        $items = $data['items'];
        if (count($items)){
            foreach ($items as $item){
                $row = (array) $item;
                if (!empty($row['desc'])){
                    $data = $row + Json::decode($row['desc'], Json::TYPE_ARRAY);
                } else {
                    $data = $row;
                }    
                unset($data['desc']);
//                var_dump($data); exit;
                if ($this->getUserModel($data)){
                    $this->unloadedUserModel($data['id']);
                }    
                usleep(100);
                if (time() > $startTime + 1740){
                    return;
                }
            }    
        }
        
        return;
    }

}
