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
use Company\Entity\BankAccount;
use Company\Entity\Contract;
use Application\Entity\ContactCar;
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
use Application\Entity\Order;
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

    /**
     * Order manager
     * @var \Application\Service\OrderManager
     */
    private $orderManager;

    /**
     * ContactCar manager
     * @var \Application\Service\ContactCarManager
     */
    private $contactCarManager;

    /**
     * Legal manager
     * @var \Company\Service\LegalManager
     */
    private $legalManager;

    
    public function __construct($entityManager, $adminManager, $aplSevice,
            $aplDocService, $orderManager, $contactCarManager,
            $legalManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->aplService = $aplSevice;
        $this->aplDocService = $aplDocService;
        $this->orderManager = $orderManager;
        $this->contactCarManager = $contactCarManager;
        $this->legalManager = $legalManager;
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
     * Обновить клиента
     * @param array $data
     * @return boolean
     */
    protected function getClient($data)
    {
        if (empty($data['client'])){
            return;
        }
        $this->aplService->getClient([
            'id' => $data['client'],
            'email' => (empty($data['email'])) ? null:$data['email'],
            'phone' => (empty($data['phone'])) ? null:$data['phone'],
            'name' => (empty($data['name'])) ? null:$data['name'],
            'publish' => 1,
        ]);
        
        $client = $this->entityManager->getRepository(AplClient::class)
                ->find($data['client']);
        return $client;
    }

    private function orderStatus($data)
    {
        if (!empty($data['parent'])){
            switch ((int) $data['parent']){
                case -1: return Order::STATUS_CANCELED;
                case 50: return Order::STATUS_PROCESSED;
                case 100: return Order::STATUS_CONFIRMED;
                case 150: return Order::STATUS_DELIVERY;
                case 210: return Order::STATUS_SHIPPED;
                default : return Order::STATUS_UNKNOWN;    
            }
        }
        
        return Order::STATUS_NEW;
    }
    
    private function orderMode($data)
    {
        if (!empty($data['mode'])){
            switch ((int) $data['mode']){
                case 'vin': return Order::MODE_VIN;
                case 'order': return Order::MODE_ORDER;
                case 'inner': return Order::MODE_INNER;
                case 'fast': return Order::MODE_FAST;
                default : return Order::MODE_MAN;    
            }
        }
        
        return Order::MODE_MAN;
    }
        
    /**
     * Подставить банковский счет 
     * @param Legal $legal
     * @param array $data
     * @return BankAccount
     */
    private function addBankAccount($legal, $data)
    {
        $rs = (empty($data['firmAccount'])) ? null:$data['firmAccount']; 
        $bik =  (empty($data['bik'])) ? null:$data['bik'];        
        $bankAccount = null;
        
        if ($rs && $bik){
            $bankAccount = $this->entityManager->getRepository(\Company\Entity\BankAccount::class)
                    ->findBy(['rs' => $rs, 'bik' => $bik, 'legal' => $legal->getId()]);
            if (!$bankAccount){
                $this->legalManager->addBankAccount($legal, [
                    'bik' => $bik,
                    'rs' => $rs,
                    'name' => (empty($data['bank'])) ? null:$data['bank'],
                    'ks' => (empty($data['firmAccount1'])) ? null:$data['firmAccount1'],
                ]);
            }
        }
        return $bankAccount;
    }
    
    /**
     * Найти юрлицо грузополучателя
     * @param Contact $contact
     * @param array $data
     * @return Legal
     */
    private function findConsignee($contact, $data)
    {
        $inn = (empty($data['inn'])) ? null:$data['inn']; 
        $kpp = (empty($data['consigneeKpp'])) ? null:$data['consigneeKpp']; 
        
        if (!$inn || !$kpp){
            return;
        }
        
        $legal = $this->entityManager->getRepository(Legal::class)
                ->findOneByInnKpp($inn, $kpp);
        if (!$legal){
            $legal = $this->legalManager->addLegal($contact, [
                'inn' => $inn,
                'kpp' => $kpp,
                'name' => (empty($data['consignee'])) ? null:$data['consignee'],
                'ogrn' => (empty($data['ogrn'])) ? null:$data['ogrn'],
                'okpo' => (empty($data['okpo'])) ? null:$data['okpo'],
                'address' => (empty($data['consigneeAddress'])) ? null:$data['consigneeAddress'],
            ]);
        }
        
        return $legal;
    }
    
    /**
     * Найти юрлицо плательщика 
     * @param Contact $contact
     * @param array $data
     * @return Legal
     */
    private function findLegal($contact, $data)
    {
        $legal = null;
        $inn = (empty($data['inn'])) ? null:$data['inn']; 
        $kpp = (empty($data['kpp'])) ? null:$data['kpp']; 
        
        if (!$inn){
            return;
        }
        
        $legal = $this->entityManager->getRepository(Legal::class)
                ->findOneByInnKpp($inn, $kpp);
        if (!$legal){
            $legal = $this->legalManager->addLegal($contact, [
                'inn' => $inn,
                'kpp' => $kpp,
                'name' => (empty($data['firmName'])) ? null:$data['firmName'],
                'ogrn' => (empty($data['ogrn'])) ? null:$data['ogrn'],
                'okpo' => (empty($data['okpo'])) ? null:$data['okpo'],
                'address' => (empty($data['firmAddress'])) ? null:$data['firmAddress'],
            ]);
        }
        
        if ($legal){
            $this->addBankAccount($legal, $data);
        }
        
        return $legal;
    }
    
    /**
     * Обновить колонку цен клиента
     * @param AplClient $client
     * @param integer $pricecol
     */
    private function updateClientPricecol($client, $pricecol)
    {
        if (is_numeric($pricecol)){
            $this->entityManager->getConnection()
                    ->update('client', ['pricecol' => $pricecol], ['id' => $client->getId()]);
        }
        
        return;
    }
    
    /**
     * Загрузить заказ
     * 
     * @param array $data
     */
    public function getOrder($data)
    {
//        var_dump($data); exit;
        $client = $this->getClient($data);
        if (!$client){
            return false;
        }
        
        $contact = $this->getLegalContact();
        if (!$contact){
            return false;
        }
        
        if (!empty($data['pricecol'])){
            $this->updateClientPricecol($client, $data['pricecol']);
        }
        
        $office = $this->aplDocService->officeFromAplId($data['publish']);
        
        $orderData = [
            'status' => $this->orderStatus($data),
            'mode' => $this->orderMode($data),
            'dateMod' => $data['lastmod'],
            'info' => (empty($data['info21'])) ? null:$data['info21'],
        ];

        $dateValidator = new Date();
        $dateValidator->setFormat('Y-m-d H:i:s');
        $dateMod = $data['lastmod'];
        if (!$dateValidator->isValid($dateMod)){
            $dateMod = $data['created'];
        }
        
        $legal = $this->legalFromSupplierAplId($data['name'], $data['ds'], $data['supplier']);        
        $contract = $this->findDefaultContract($office, $legal, $data['ds'], $data['ns'], $this->getCashContract($data));
        
        $dataPtu['office'] = $office;
        $dataPtu['legal'] = $legal;
        $dataPtu['contract'] = $contract; 

        $order = $this->entityManager->getRepository(Order::class)
                ->findBy(['aplId' => $data['id']]);        
        
        if ($order){
            $this->orderManager->updateOrder($order, $orderData);
            $this->orderManager->removeOrderBids($order); 
        } else {        
            $order = $this->orderManager->addNewOrder($office, $contact, $orderData);
        }    
        
        if ($order && isset($data['tp'])){
            $rowNo = 1;
            foreach ($data['tp'] as $tp){
                if (isset($tp['good'])){
                    $good = $this->findGood($tp['good']);   
                }    
                if (empty($good)){
    //                throw new \Exception("Не удалось создать карточку товара для документа {$data['id']}");
                } else {

                    $this->ptuManager->addPtuGood($ptu->getId(), [
                        'status' => $ptu->getStatus(),
                        'statusDoc' => $ptu->getStatusDoc(),
                        'quantity' => $tp['sort'],                    
                        'amount' => $tp['bag_total'],
                        'good_id' => $good->getId(),
                        'comment' => '',
                        'info' => '',
                        'countryName' => (isset($tp['country'])) ? $tp['country']:'',
                        'countryCode' => (isset($tp['countrycode'])) ? $tp['countrycode']:'',
                        'unitName' => (isset($tp['pack'])) ? $tp['pack']:'',
                        'unitCode' => (isset($tp['packcode'])) ? $tp['packcode']:'',
                        'ntd' => (isset($tp['gtd'])) ? $tp['gtd']:'',
                    ], $rowNo);
                    $rowNo++;
                }    
            }
        }  
        
        if ($order){
            $this->orderManager->updateOrderTotal($order);
            return true;            
        }
                
        return false;
    }
    
    /**
     * Обновить статус загруженного заказа
     * @param integer $aplId
     * @return boolean
     */
    public function unloadedOrder($aplId)
    {
        $result = true;
        if (is_numeric($aplId)){
            $url = $this->aplApi().'aa-order?api='.$this->aplApiKey();

            $post = [
                'orderId' => $aplId,
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
    
    /**
     * Загрузить заказ из Апл
     * 
     * @return 
     */
    public function unloadOrder()
    {
        $url = $this->aplApi().'unload-order?api='.$this->aplApiKey();
        
        $post = [
        ];

        $client = new Client();
        $client->setUri($url);
        $client->setMethod('POST');
        $client->setParameterPost($post);

        $response = $client->send();
        $body = $response->getBody();

//        var_dump($body); exit;
        try{
            $result = json_decode($body, true);
        } catch (\Laminas\Json\Exception\RuntimeException $ex) {
            var_dump($ex->getMessage());
            var_dump($body);
            exit;
        }
        var_dump($result); exit;

        if (is_array($result)){
            if ($this->getOrder($result)){ 
                //$this->unloadedOrder($result['id']);
            }    
        } else {
            return false;
        }
        return true;
    }
    
    /**
     * Получить заказы
     */
    public function uploadOrders()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(900);
        $startTime = time();
        
        while (true){
            if ($this->unloadOrder()) {
                usleep(100);
                if (time() > $startTime + 840){
                    break;
                }
            } else {
                break;
            }    
        }    
        return;        
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
