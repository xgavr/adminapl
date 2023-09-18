<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Laminas\Http\Client;
use Company\Entity\Office;
use Company\Entity\Legal;
use Company\Entity\BankAccount;
use Application\Entity\Shipping;
use Application\Entity\ContactCar;
use Application\Entity\Contact;
use Application\Entity\Courier;
use Application\Entity\Client as AplClient;
use Application\Entity\Make;
use Application\Entity\Model;
use Application\Entity\Car;
use Application\Entity\Comment;
use User\Entity\User;
use Application\Entity\Order;
use Laminas\Validator\Date;
use Laminas\Json\Decoder;
use Application\Entity\Bid;
use Laminas\Json\Encoder;


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

    /**
     * Comment manager
     * @var \Application\Service\CommentManager
     */
    private $commentManager;

    
    public function __construct($entityManager, $adminManager, $aplSevice,
            $aplDocService, $orderManager, $contactCarManager,
            $legalManager, $commentManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->aplService = $aplSevice;
        $this->aplDocService = $aplDocService;
        $this->orderManager = $orderManager;
        $this->contactCarManager = $contactCarManager;
        $this->legalManager = $legalManager;
        $this->commentManager = $commentManager;
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
     * @return AplClient
     */
    protected function getClient($data)
    {
        if (empty($data['client'])){
            return;
        }
        $client = $this->aplService->getClient([
            'id' => $data['client'],
            'email' => (empty($data['email'])) ? null:$data['email'],
            'phone' => (empty($data['phone'])) ? null:$data['phone'],
            'name' => (empty($data['name'])) ? null:$data['name'],
            'publish' => 1,
        ]);
        
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
            switch ($data['mode']){
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
                    ->findOneBy(['rs' => $rs, 'bik' => $bik, 'legal' => $legal->getId()]);
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
        if (!$legal && !empty($data['consignee'])){
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
        $inn = (empty($data['inn'])) ? null:$data['inn']; 
        $kpp = (empty($data['kpp'])) ? null:$data['kpp']; 
        
        if (!$inn){
            return;
        }
        
        $dateOper = NULL;
        if (!empty($data['type'])){
            $dateValidator = new Date();
            $dateValidator->setFormat('Y-m-d');
            $dateOper = $data['type'];
            if (!$dateValidator->isValid($dateOper)){
                $dateOper = $data['type'];
            }
        } 
        
        $legal = $this->entityManager->getRepository(Legal::class)
                ->findOneByInnKpp($inn, $kpp);
        if (!$legal && !empty($data['firmName'])){
            $legal = $this->legalManager->addLegal($contact, [
                'inn' => $inn,
                'kpp' => $kpp,
                'name' => (empty($data['firmName'])) ? null:$data['firmName'],
                'ogrn' => (empty($data['ogrn'])) ? null:$data['ogrn'],
                'okpo' => (empty($data['okpo'])) ? null:$data['okpo'],
                'address' => (empty($data['firmAddress'])) ? null:$data['firmAddress'],
                'dateStart' => $dateOper,
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
     * 
     * @param Office $office
     * @param array $data
     * @return Shipping
     */
    private function findDelivery($office, $data)
    {
        $shipping = null;
        if (!empty($data['delivery'])){
            $shipping = $this->entityManager->getRepository(Shipping::class)
                    ->findOneBy(['office' => $office->getId(), 'aplId' => $data['delivery']]);
        }
        
        if (!$shipping){
            $shipping = $this->entityManager->getRepository(Shipping::class)
                    ->findDefaultShipping($office);
        }
        
        return $shipping->getId();
    }
    
    /**
     * Конвертация apl ac в ас
     * @param integer $ac
     */
    private function aplAc($ac)
    {
        switch ($ac){
            case '3': return ContactCar::AC_YES;
            case '5': return ContactCar::AC_NO;
            default : return ContactCar::AC_UNKNOWN;    
        }
        
        return;
    }

    /**
     * Конвертация apl trans в tm
     * @param integer $tm
     */
    private function aplTm($tm)
    {
        switch ($tm){
            case '3': return ContactCar::TM_MECH;
            case '4': return ContactCar::TM_AUTO;
            default : return ContactCar::TM_UNKNOWN;    
        }
        
        return;
    }

    /**
     * Конвертация apl wheel в wheel
     * @param integer $wheel
     */
    private function aplWheel($wheel)
    {
        switch ($wheel){
            case '1': return ContactCar::WHEEL_LEFT;
            case '2': return ContactCar::WHEEL_RIGHT;
            default : return ContactCar::WHEEL_LEFT;    
        }
        
        return;
    }

    /**
     * Добавить/изменить машину в заказе
     * @param Contact $contact
     * @param array $data
     * @return ContactCar
     */
    private function addOrderCar($contact, $data)
    {
        $vin = $make = $model = $car = NULL;
        if (!empty($data['vin'])){
            $vin = trim($data['vin']);
        }
        if (!empty($data['brand'])){
            $make = $this->entityManager->getRepository(Make::class)
                    ->findOneBy(['aplId' => $data['brand']]);
        }
        if (!empty($data['serie'])){
            $model = $this->entityManager->getRepository(Model::class)
                    ->findOneBy(['aplId' => $data['serie']]);
        }
        if (!empty($data['model'])){
            $car = $this->entityManager->getRepository(Car::class)
                    ->findOneBy(['aplId' => $data['model']]);
        }
        $contactCar = $this->entityManager->getRepository(ContactCar::class)
                ->findContactCar($contact, [
                    'vin' => $vin,
                    'make' => ($make) ? $make->getId():null,
                    'model' => ($model) ? $model->getId():null,
                    'car' => ($car) ? $car->getId():null,
                ]);
        if ($contactCar){
            if (!$make && $contactCar->getMake()){
                $make = $contactCar->getMake();
            }
            if (!$model && $contactCar->getModel()){
                $model = $contactCar->getModel();
            }
            if (!$car && $contactCar->getCar()){
                $car = $contactCar->getCar();
            }
            $this->contactCarManager->upd($contactCar, [
                'vin' => ($vin) ? $vin:$contactCar->getVin(),
                'comment' => (!empty($data['info'])) ? $data['info']:$contactCar->getComment(),
                'yocm' => (!empty($data['year'])) ? $data['year']:$contactCar->getYocm(),
                'wheel' => (!empty($data['wheel'])) ? $this->aplWheel($data['wheel']):$contactCar->getWheel(),
                'tm' => (!empty($data['trans'])) ? $this->aplTm($data['trans']):$contactCar->getTm(),
                'ac' => (!empty($data['ac'])) ? $this->aplAc($data['ac']):$contactCar->getAc(),
                'md' => (!empty($data['motor3'])) ? $data['motor3']:$contactCar->getMd(),
                'ed' => (!empty($data['motor1'])) ? $data['motor1']:$contactCar->getEd(),
                'ep' => (!empty($data['motor2'])) ? $data['motor2']:$contactCar->getEp(),
                'make' => ($make) ? $make->getId():null,
                'model' => ($model) ? $model->getId():null,
                'car' => ($car) ? $car->getId():null,                
            ]);
        }
        if (!$contactCar && ($vin || $make)){
            $contactCar = $this->contactCarManager->ins($contact, [
                'vin' => ($vin) ? $vin:null,
                'comment' => (!empty($data['info'])) ? $data['info']:null,
                'yocm' => (!empty($data['year'])) ? $data['year']:null,
                'wheel' => (!empty($data['wheel'])) ? $this->aplWheel($data['wheel']):null,
                'tm' => (!empty($data['trans'])) ? $this->aplTm($data['trans']):null,
                'ac' => (!empty($data['ac'])) ? $this->aplAc($data['ac']):null,
                'md' => (!empty($data['motor3'])) ? $data['motor3']:null,
                'ed' => (!empty($data['motor1'])) ? $data['motor1']:null,
                'ep' => (!empty($data['motor2'])) ? $data['motor2']:null,
                'make' => ($make) ? $make->getId():null,
                'model' => ($model) ? $model->getId():null,
                'car' => ($car) ? $car->getId():null,
            ]);
        }
        
        return $contactCar;
    }
    
    /**
     * Загрузить заказ
     * 
     * @param array $data
     * @para bool $debug
     */
    public function getOrder($data, $debug = false)
    {
        $client = $this->getClient($data);
        if (!$client){
            return true; // позже загрузим
        }
//        var_dump($data); exit;
        
        $contact = $client->getLegalContact();
        if (!$contact){
            if ($debug){
                echo 'no client!';
                var_dump($data);
            }
            return false;
        }
//        var_dump($data); exit;
        
        if (!empty($data['pricecol'])){
            $this->updateClientPricecol($client, $data['pricecol']);
        }
        
        $office = $this->aplDocService->officeFromAplId($data['publish']);
        
        $dateValidator = new Date();
        $dateValidator->setFormat('Y-m-d H:i:s');
        $dateMod = $data['lastmod'];
        if (!$dateValidator->isValid($dateMod)){
            $dateMod = $data['created'];
        }
        
        $dateOper = NULL;
        if (!empty($data['type'])){
            $dateValidator->setFormat('Y-m-d');
            $dateOper = $data['type'];
            if (!$dateValidator->isValid($dateOper)){
                $dateOper = $data['created'];
            }
        }    
        
        $contactCarId = null;
        $contactCar = $this->addOrderCar($contact, $data);
        if ($contactCar){
            $contactCarId = $contactCar->getId();
        }
        
        $courierId = NULL;
        if (!empty($data['carrier'])){
            $courier = $this->entityManager->getRepository(Courier::class)
                    ->findOneByAplId($data['carrier']);
            if ($courier){
                $courierId = $courier->getId();
            }    
        }    
        
        $legalId = null;
        $legal = $this->findLegal($contact, $data);
        if ($legal){
            $legalId = $legal->getId();
        }
        
        $recipientId = null;
        $recipient = $this->findConsignee($contact, $data);
        if ($recipient){
            $recipientId = $recipient->getId();
        }
        
        $skiperId = NULL;
        if (!empty($data['skiper'])){
            $skiper = $this->entityManager->getRepository(User::class)
                    ->findOneByAplId($data['skiper']);
            if ($skiper){
                $skiperId = $skiper->getId();
            }    
        }    

        $userId = NULL;
        if (!empty($data['user'])){
            $user = $this->entityManager->getRepository(User::class)
                    ->findOneByAplId($data['user']);
            if ($user){
                $userId = $user->getId();
            }    
        }    
        $info = '';
        if (!empty($data['info21'])){
            $info .= $data['info21'].' ';
        }
        if (!empty($data['info2'])){
            $info .= $data['info2'];
        }
        $orderData = [
            'address' => (empty($data['address'])) ? null:$data['address'],
            'aplId' => $data['id'],
            'dateMod' => $dateMod,
            'dateOper' => $dateOper,
            'dateShipment' => $dateOper,
            'geo' => (empty($data['geo'])) ? null:$data['geo'],
            'info' => trim($info),
            'mode' => $this->orderMode($data),
            'shipmentDistance' => (empty($data['delivery_distance'])) ? null:$data['delivery_distance'],
            'shipmentRate' => (empty($data['delivery_rate'])) ? null:$data['delivery_rate'],
            'shipmentAddRate' => (empty($data['delivery_rate_adv'])) ? null:$data['delivery_rate_adv'],
            'shipmentTotal' => (empty($data['delivery_sum'])) ? null:$data['delivery_sum'],
            'status' => $this->orderStatus($data),
            'trackNumber' => (empty($data['tracker'])) ? null:$data['tracker'],
            'contactCar' =>$contactCarId,
            'courier' => $courierId,
            'legal' => $legalId,
            'recipient' => $recipientId,
            'shipping' => $this->findDelivery($office, $data),
            'skiper' => $skiperId,
            'user' => $userId,
            'office' => $office->getId(),
            'statusEx' => Order::STATUS_EX_OK,
        ];
        
        $order = $this->entityManager->getRepository(Order::class)
                ->findOneBy(['aplId' => $data['id']]);        
        
        if ($order){
            $this->orderManager->updOrder($order, $orderData);
        } else {        
            $order = $this->orderManager->insOrder($office, $contact, $orderData);
        }    
        
        if ($order){

            $this->entityManager->getConnection()
                    ->delete('bid', ['order_id' => $order->getId()]);
            $this->entityManager->getConnection()
                    ->delete('selection', ['order_id' => $order->getId()]);

            if (isset($data['tp'])){
                $rowNo = 1;
                foreach ($data['tp'] as $tp){
                    if (!empty($tp['good'])){                        
                        $good = $this->aplDocService->findGood($tp['good']);   
                    }    
                    if (empty($good)){
//            var_dump($tp); exit;
                        throw new \Exception("Не удалось создать карточку товара для документа ".\Laminas\Json\Encoder::encode($tp['good']));
//                        return false;
                    } else {

                        $this->orderManager->insBid($order, [
                            'rowNo' => $rowNo,
                            'num' => $tp['sort'],
                            'price' => $tp['comment'],
                            'good' => $good,
                            'displayName' => (empty($tp['dispname'])) ? null:$tp['dispname'],
                            'oem' => (empty($tp['selection'])) ? null:mb_substr($tp['selection'], 3),
                            'opts' => $good->getOptsJson(),
                        ]);
                        $rowNo++;
                    } 
                }
            }  
//            var_dump(222); exit;
            if (isset($data['selections'])){
                if (is_array($data['selections'])){
                    foreach ($data['selections'] as $selection){
                        if (!empty($selection['q'])){
                            $this->orderManager->insSelection($order, [
                               'oem'  => $selection['q'],
                               'comment'  => $selection['qc'],
                            ]);
                        }
                    }    
                }    
            }


            $this->orderManager->updOrderTotal($order);
            return true;
//            if (round($order->getTotal()) == round($data['sort'])){
//                return true;            
//            } else {
//                if ($debug){
//                    echo 'no sum match!';
//                    var_dump($data);
//                }                
//            }   
        }    
                
        if ($debug){
            echo 'no order!';
            var_dump($data);
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
            $client->setOptions(['timeout' => 30]);
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
     * @param integer $start
     * @param integer $aplId
     * @param bool $debug
     * @return 
     */
    public function unloadOrder($start = 0, $aplId = null, $orderTotal = null, $debug = false)
    {
        $url = $this->aplApi().'unload-order?api='.$this->aplApiKey();
        
        $post = [
            'start' => $start,
            'id' => $aplId,
        ];

        $client = new Client();
        $client->setUri($url);
        $client->setMethod('POST');
        $client->setParameterPost($post);
        $client->setOptions(['timeout' => 120]);
        $response = $client->send();
        $body = $response->getBody();

//            var_dump($body); exit;
            
        if ($debug){
            var_dump($response->getStatusCode(), $body);
        }    
        try{
            $result = json_decode($body, true);
        } catch (\Laminas\Json\Exception\RuntimeException $ex) {
            if ($debug){
                var_dump($ex->getMessage());
            }    
            return false;
        } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
            return true;
        }    

        if (is_array($result)){
            if ($debug){
                var_dump($result);
            }    
            if (is_numeric($orderTotal) && $aplId){
                if ($orderTotal == $result['sort']){
                    return true;
                }
//                var_dump($aplId);
//                var_dump($orderTotal);
//                var_dump($result); exit;
            }

            if ($this->getOrder($result, $debug)){ 
                $this->unloadedOrder($result['id']);
            } else {
                return false;
            }    
        } else {
            return false;
        }
        return true;
    }
    
    /**
     * Получить заказы
     * @param bool $debug
     */
    public function uploadOrders($debug = false)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(900);
        $startTime = time();
        $start = 0;
        
        while (true){
            if ($this->unloadOrder($start, null, null, $debug)) {
                usleep(100);
            } else {
                break;
            }
            if (time() > $startTime + 870){
                break;
            }
            $start++;
        }    
        
        while (true){
            if ($order = $this->sendOrders($debug)) {
                usleep(100);
            } else {
                break;
            }
            if (time() > $startTime + 770){
                break;
            }
            $start++;
        }    

        return $order;        
    }
    
    /**
     * Проверить заказы
     */
    public function checkOrders()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(900);
        $startTime = time();
        $start = 0;
                        
        while (true){
            $order = $this->entityManager->getRepository(Order::class)
                    ->findOneByStatusEx(Order::STATUS_EX_NO);
            if (!$order){
                $order = $this->entityManager->getRepository(Order::class)
                        ->findOneByStatusEx(Order::STATUS_EX_TOTAL_NO_MATH);
            }
            if (!$order){
                break;
            }
            if ($order->getAplId()){
                $result = $this->entityManager->getRepository(Bid::class)
                    ->getOrderNum($order);
                $total = $order->getShipmentTotal();
                if (count($result)){
                    $total += $result[0]['total'];
                }
                
//                var_dump($order->getAplId());
//                var_dump($total);
                if ($this->unloadOrder($start, $order->getAplId(), $total)) {
                    $statusEx = Order::STATUS_EX_OK;
                    usleep(100);
                } else {
                    $statusEx = Order::STATUS_EX_TOTAL_NO_MATH;
                }
            } else {
               $statusEx = Order::STATUS_EX_NEW; 
            }    
            
            $this->entityManager->getConnection()
                    ->update('orders', ['status_ex' => $statusEx], ['id' => $order->getId()]);
            $this->entityManager->refresh($order);
                
            if (time() > $startTime + 870){
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

    /*
     * Получить comment
     * 
     */
    public function getComment()
    {
        $url = $this->aplApi().'unload-comment?api='.$this->aplApiKey();

        $post = [
            'start' => 0,
        ];
        
        $client = new Client();
        $client->setUri($url);
        $client->setMethod('POST');
        $client->setParameterPost($post);
        $client->setOptions(['timeout' => 120]);
        $response = $client->send();
        $body = $response->getBody();

//        var_dump($body); exit;
        try{
            $result = json_decode($body, true);
        } catch (\Laminas\Json\Exception\RuntimeException $ex) {
//            var_dump($ex->getMessage());
//            var_dump($body);
            return false;
        } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
            return true;
        }    
        
//        var_dump($result); exit;
        if (is_array($result)){
            $comment = $this->entityManager->getRepository(Comment::class)
                    ->findOneBy(['aplId' => $result['id']]);
            $order = $client = null;
            if ($result['type'] == 'Orders'){
                $order = $this->entityManager->getRepository(Order::class)
                        ->findOneByAplId($result['parent']);
            }    
            if ($result['type'] == 'Users'){
                $client = $this->entityManager->getRepository(AplClient::class)
                        ->findOneByAplId($result['parent']);
            }    
            $user = $this->entityManager->getRepository(User::class)
                    ->findOneByAplId($result['user']);
            $data = [
                'aplId' => $result['id'],
                'comment' => $result['comment'],
                'dateCreated' => $result['created'],
                'user' => $user,
                'statusEx' => Comment::STATUS_EX_RECD,
            ];    

            if ($comment){                    
                $this->commentManager->updateComment($comment, $data);                    
            } else {                            
                if ($order){
                    $comment = $this->commentManager->addOrderComment($order, $data);
                } elseif ($client) {
                    $comment = $this->commentManager->addClientComment($client, $data);                            
                } else {
                    //return false;
                }   
            }

            return $result['id'];
        }
        return true;
    }
    
        /**
     * Обновить статус comment
     * @param integer $commentId
     * @return boolean
     */
    public function unloadedComment($commentId)
    {
        $result = true;
        if (is_numeric($commentId)){
            $url = $this->aplApi().'aa-comment?api='.$this->aplApiKey();

            $post = [
                'commentId' => $commentId,
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
     * Загрузить comments
     * @return 
     */
    public function unloadComment()
    {
        $commentId = $this->getComment();
        if (is_numeric($commentId)){
            $this->unloadedComment($commentId);
            return true;
        }    
        return false;
    }    
    
    /**
     * Получить комментарии
     */
    public function uploadComments()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(600);
        $startTime = time();
        $start = 0;
        
        while (true){
            if ($this->unloadComment()) {
                usleep(100);
                if (time() > $startTime + 560){
                    break;
                }
            } else {
                break;
            }
            $start++;
        }    
        return;        
    }    
    
    /*
     * Отправить comment в апл
     * @param Comment $comment
     * @param array $post
     */
    public function sendComment($comment = null, $post = null)
    {
        $url = $this->aplApi().'update-comment?api='.$this->aplApiKey();

        $result = false;
        if ($comment){
            $post = [
                'parent' => $comment->getOrder()->getAplId(),
                'type' => 'Orders',
                'comment' => $comment->getComment(),
                'sf' => 1,
            ];

            if ($comment->getAplId()){
                $post['id'] = $comment->getAplId();
            }
        }    
        
        if (is_array($post)){
//            var_dump($post); exit;
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setOptions(['timeout' => 60]);
            $client->setParameterPost($post);            

            $ok = $result = false;
            $aplId = 0;
            try{
                $response = $client->send();
//                var_dump($response->getStatusCode()); exit;
                if ($response->isOk()) {                    
                    $aplId = (int) $response->getBody();
                    if ($aplId){
                        $ok = $result = true;
                    }
                }
            } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                $ok = true;
            }    

            if ($ok) {      
                if ($comment){
                    $comment->setStatusEx(Comment::STATUS_EX_APL);
                    if ($aplId > 0){
                        $comment->setAplId($aplId);
                    }    
                    $this->entityManager->persist($comment);
                    $this->entityManager->flush();
                    $this->entityManager->refresh($comment);
                }    
            }
        }
        
        return $result;        
    }
    
    /**
     * Отправить comments в АПЛ
     */
    public function sendComments($limit = null)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);
        $startTime = time();
        
        $comments = $this->entityManager->getRepository(Comment::class)
                ->findBy(['statusEx' => Comment::STATUS_EX_NEW], null, $limit);
        
        $result = 0;
        foreach ($comments as $comment){
            if ($this->sendComment($comment)) {
                $result = $comment->getId();
                usleep(100);
                if (time() > $startTime + 280){
                    break;
                }
            } else {
                break;
            }
        }    
        return $result;        
    }
    
    /**
     * Отправить заказ в апл
     * @param Order $order
     * @param bool $debug
     */
    public function sendOrder($order, $debug = false)
    {
        $url = $this->aplApi().'update-order?api='.$this->aplApiKey();

        $result = false;
        $post = [];
        if ($order){
            $value = [
                'publish' => $order->getOffice()->getAplId(),
                'user' => $order->getUserApl(),
                'name' => $order->getContact()->getName(),
                'email' => $order->getContact()->getEmailAsString(),
                'firmName' => ($order->getLegal()) ? $order->getLegal()->getName():'',
                'inn' => ($order->getLegal()) ? $order->getLegal()->getInn():'',
                'kpp' => ($order->getLegal()) ? $order->getLegal()->getKpp():'',
                'ogrn' => ($order->getLegal()) ? $order->getLegal()->getOgrn():'',
                'okpo' => ($order->getLegal()) ? $order->getLegal()->getOkpo():'',
                'firmAddress' => ($order->getLegal()) ? $order->getLegal()->getAddress():'',
                'firmAccount' => ($order->getBankAccount()) ? $order->getBankAccount()->getRs():'',
                'bank' => ($order->getBankAccount()) ? $order->getBankAccount()->getName():'',
                'bik' => ($order->getBankAccount()) ? $order->getBankAccount()->getBik():'',
                'firmAccount1' => ($order->getBankAccount()) ? $order->getBankAccount()->getKs():'',
                'consignee' => ($order->getRecipient()) ? $order->getRecipient()->getName():'',
                'consigneeKpp' => ($order->getRecipient()) ? $order->getRecipient()->getKpp():'',
                'consigneeAddress' => ($order->getRecipient()) ? $order->getRecipient()->getAddress():'',
                'vin' => ($order->getContactCar()) ? $order->getContactCar()->getVin():null,
                'auto' => $order->getContactCarMakeName(),
                'info' => ($order->getContactCar()) ? $order->getContactCar()->getComment():null,
                'carrier' => ($order->getCourier()) ? $order->getCourier()->getAplId():null,
                'tracker' => $order->getTrackNumber(),
                'delivery_rate_options' => $order->getShipping()->getAplRateAsString(),
                'delivery_rate' => $order->getShipmentRate(),
                'delivery_distance' => $order->getShipmentDistance(),
                'delivery_rate_adv' =>$order->getShipmentAddRate(),
                'delivery' => $order->getShipping()->getAplId(),
                'type' => date('Y-m-d', strtotime($order->getDateOper())),
                'delivery_sum' => $order->getShipmentTotal(),
                'address' => $order->getAddress(),
                'brand' => $order->getContactCarMakeAplId(),
                'shipping' => $order->getShipping()->getAplShipping(),
                'selections' => $order->getSelectionsAsAplArray(),
                'phone' => $order->getContact()->getPhonesAsString(),
                'parent' => $order->getAplStatusAsString(),
                'paystatus' => 0,
                'mode' => $order->getAplModeAsString(),
                'info2' => $order->getInfo(),
                'skiper' => $order->getSkiperAplId(),
            ];

            if ($order->getAplId()){
                $value['id'] = $order->getAplId();
            }
            
            $post['value'] = Encoder::encode($value);
            
            $so = [];
            $bids = $this->entityManager->getRepository(Bid::class)
                    ->findBy(['order' => $order->getId()]);
            foreach ($bids as $bid){
                $tp = [
                    'parent' => $bid->getGood()->getAplId(),
                    'makerAplId' => $bid->getGood()->getProducer()->getAplId(), 
                    'makerId' => $bid->getGood()->getProducer()->getId(), 
                    'makerName' => $bid->getGood()->getProducer()->getName(), 
                    'article' => $bid->getGood()->getCode(), 
                    'sort' => $bid->getNum(),
                    'publish' => $order->getAplId(),
                    'comment' => $bid->getPrice(),
                    'opts' => $bid->getOpts(),
                    'selection' => $bid->getOe(),                    
                    'dispname' => $bid->getDisplayName(),
                ];                
                $so[] = $tp;
            }
            $post['tp'] = $so;
            $post['ds'] = $order->getDocDate();
            
            
//            var_dump($post); exit;
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setOptions(['timeout' => 60]);
            $client->setParameterPost($post);            

            $ok = $result = false;
            $aplId = 0;
            try{
                $response = $client->send();
//                var_dump($response->getBody()); exit;
                if ($response->isOk()) {                    
                    $aplId = (int) $response->getBody();
                    if ($aplId){
                        $ok = $result = true;
                    }
                } else {
//                    var_dump($response->getBody()); exit;                    
                }
            } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                $ok = true;
            }    

            if ($ok) {            
                $order->setStatusEx(Order::STATUS_EX_OK);
                if ($aplId > 0 && empty($order->getAplId())){
                    $order->setAplId($aplId);
                }    
                $this->entityManager->persist($order);
                $this->entityManager->flush($order);
            }

            if (!$result && $debug){
                var_dump($order->getId(), $response->getBody());                
            }

            $this->entityManager->detach($order);
        }
        
        return $result;        
    }
    
    /**
     * Обновить заказ в апл
     * @param bool $debug
     */
    public function sendNexOrder($debug = false)
    {
        $order = $this->entityManager->getRepository(Order::class)
                ->findForUpdateApl();
        if ($order){
            if ($this->sendOrder($order, $debug)){
                return $order->getId();
            }
        }
        
        if ($debug && $order){
            var_dump($order->getId());
        }
        return false;
    }
    
    /**
     * Отправить заказы в АПЛ
     * @param bool $debug
     */
    public function sendOrders($debug = false)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(900);
        $startTime = time();
        $order = null;
        
        while (true){
            if ($order = $this->sendNexOrder($debug)) {
                usleep(100);
                if (time() > $startTime + 870){
                    break;
                }
            } else {
                break;
            }
        }    
        return $order;        
    }
    
    /**
     * Отменить старые заказы со статусом новый и обработан
     */
    public function cancelOld()
    {
        return $this->orderManager->cancelOld();
    }        
}
