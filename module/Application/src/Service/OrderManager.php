<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Laminas\ServiceManager\ServiceManager;
use Application\Entity\Bid;
use Application\Entity\Order;
use Application\Entity\Goods;
use User\Entity\User;
use Application\Entity\Client;
use Application\Entity\Contact;
use Application\Entity\ContactCar;
use Application\Entity\Courier;
use Company\Entity\Office;
use Company\Entity\Legal;
use Application\Entity\Shipping;
use Application\Entity\Oem;
use Application\Entity\Selection;
use Application\Filter\ArticleCode;
use Stock\Entity\Mutual;
use Stock\Entity\Movement;
use Stock\Entity\Retail;
use Admin\Entity\Log;
use Company\Entity\Contract;
use Laminas\Validator\Date;
use Application\Entity\Email;
use Application\Entity\Phone;
use Company\Entity\BankAccount;
use Application\Entity\Make;
use Application\Entity\SupplierOrder;
use Laminas\Json\Decoder;
use Stock\Entity\Comiss;
use Stock\Entity\Register;
use Stock\Entity\Reserve;
use Laminas\Json\Encoder;
use Stock\Entity\ComissBalance;
use Stock\Entity\ComitentBalance;
use Stock\Entity\Comitent;
use Application\Entity\Comment;

/**
 * Description of OrderService
 *
 * @author Daddy
 */
class OrderManager
{
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    /**
     *
     * @var \Laminas\Authentication\AuthenticationService
     */
    private $authService;
        
    /**
     * Log manager
     * @var \Admin\Service\LogManager
     */
    private $logManager;
    
    /**
     * Apl manager
     * @var \Admin\Service\AplService
     */
    private $aplManager;
    
    /**
     * Legal manager
     * @var \Company\Service\LegalManager
     */
    private $legalManager;
    
    /**
     * Admin manager
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;
    
    /**
     * Contact manager
     * @var \Application\Service\ContactManager
     */
    private $contactManager;

    /**
     * Client manager
     * @var \Application\Service\ClientManager
     */
    private $clientManager;

    /**
     * Дата запрета
     * @var string
     */
    private $allowDate;
    
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $authService, $logManager,
            $legalManager, $adminManager, $contactManager, $clientManager, $aplManager)
    {
        $this->entityManager = $entityManager;
        $this->authService = $authService;
        $this->logManager = $logManager;
        $this->legalManager = $legalManager;
        $this->adminManager = $adminManager;
        $this->contactManager= $contactManager;
        $this->clientManager = $clientManager;
        $this->aplManager = $aplManager;

        $setting = $this->adminManager->getSettings();
        $this->allowDate = $setting['allow_date'];
    }
    
    /**
     * Получить дату запрета
     * @return date
     */
    public function getAllowDate()
    {
        return $this->allowDate; 
    }
    
    /**
     * Текущий пользователь
     * @return User
     */
    public function currentUser()
    {
        return $this->logManager->currentUser();
    }
    
    /**
     * Обновить взаиморасчеты розничного заказа
     * 
     * @param Order $order
     * @param float $docStamp
     */
    public function updateOrderRetails($order, $docStamp)
    {
        $legalId = $contractId = null;
        if ($order->getLegal()){
            $contract = $order->getContract();
            if (!$contract){
                $contract = $this->findDefaultContract($order->getOffice(), $order->getLegal(), $order->getDocDate(), $order->getAplId()); 
                $this->entityManager->getConnection()->update('orders', ['contract_id' => $contract->getId()], ['id' => $order->getId()]);
                $this->entityManager->refresh($order);
            }    
            $legalId = $order->getLegal()->getId();
            $contractId = $contract->getId();
            
            if ($contract->getKind() == Contract::KIND_COMITENT){
                return;
            }
        }
        $data = [
            'doc_key' => $order->getLogKey(),
            'doc_type' => Movement::DOC_ORDER,
            'doc_id' => $order->getId(),
            'date_oper' => $order->getDateOper(),
            'status' => Retail::getStatusFromOrder($order),
            'revise' => Retail::REVISE_NOT,
            'amount' => $order->getTotal(),
            'contact_id' => $order->getContact()->getId(),
            'office_id' => $order->getOffice()->getId(),
            'company_id' => $order->getCompany()->getId(),
            'legal_id' => $legalId,
            'contract_id' => $contractId,
            'doc_stamp' => $docStamp,
        ];

        $this->entityManager->getRepository(Retail::class)
                ->insertRetail($data);
        
        return;
    }    
    
    /**
     * Получить контракт по умолчанию
     * 
     * @param Office $office
     * @param Legal $legal
     * @param date $dateStart
     * @param string $act
     * @param integer $pay
     * 
     * @return Contract
     */
    public function findDefaultContract($office, $legal, $dateStart, $act, $pay = Contract::PAY_CASHLESS)
    {
        $dateValidator = new Date();
        $dateValidator->setFormat('Y-m-d H:i:s');
        if (!$dateValidator->isValid($dateStart)){
            $dateStart = '2012-05-15';
        }
        
        $contract = $this->entityManager->getRepository(Office::class)
                ->findDefaultContract($office, $legal, $dateStart, $pay);
        
        if (!$contract){
            $contract = $this->legalManager->addContract($legal, 
                    [
                        'office' => $office->getId(),
                        'name' => ($pay == Contract::PAY_CASH) ? 'Поставка Н':'Поставка БН',
                        'act' => trim($act),
                        'dateStart' => $dateStart,
                        'status' => Contract::STATUS_ACTIVE,
                        'kind' => Contract::KIND_CUSTOMER,
                        'pay' => $pay,
                        'nds' => Contract::NDS_NO,
                    ]);
        }
        
        return $contract;
    }
    
    /**
     * Обновить взаиморасчеты заказа
     * 
     * @param Order $order
     * @param float $docStamp
     */
    public function updateOrderMutuals($order, $docStamp)
    {
        $contract = $order->getContract();
        if (!$contract){
            $contract = $this->findDefaultContract($order->getOffice(), $order->getLegal(), $order->getDocDate(), $order->getAplId());
            $this->entityManager->getConnection()->update('orders', ['contract_id' => $contract->getId()], ['id' => $order->getId()]);
            $this->entityManager->refresh($order);
        }    
        
        if ($contract->getKind() == Contract::KIND_COMITENT){
            return;
        }
        
        $data = [
            'doc_key' => $order->getLogKey(),
            'doc_type' => Movement::DOC_ORDER,
            'doc_id' => $order->getId(),
            'date_oper' => $order->getDateOper(),
            'status' => Mutual::getStatusFromOrder($order),
            'revise' => Mutual::REVISE_NOT,
            'amount' => $order->getTotal(),
            'legal_id' => $order->getLegal()->getId(),
            'contract_id' => $contract->getId(),
            'office_id' => $order->getOffice()->getId(),
            'company_id' => $order->getCompany()->getId(),
            'doc_stamp' => $docStamp,
        ];

        $this->entityManager->getRepository(Mutual::class)
                ->insertMutual($data);
        
        return;
    }    
        
    /**
     * Добавить движения
     * @param Order $order
     * @param float $docStamp
     * @param Bid $bid
     * @return integer
     */    
    private function insertMovement($order, $docStamp, $bid)
    {
        $takeNoCount = 0;
        if ($order->getStatus() == Order::STATUS_SHIPPED){
            
            $bases = $this->entityManager->getRepository(Movement::class)
                    ->findBases($bid->getGood()->getId(), $docStamp, $order->getOffice()->getId(), $bid->getBaseKey());

            $write = $bid->getNum();

            $take = Bid::TAKE_NO;

            foreach ($bases as $base){
                $movement = $this->entityManager->getRepository(Movement::class)
                        ->findOneByDocKey($base['baseKey']);
//                var_dump($base);
                if ($movement){
                    $quantity = min($base['rest'], $write);
                    $amount = $quantity*$bid->getPrice();

                    $data = [
                        'doc_key' => $order->getLogKey(),
                        'doc_type' => Movement::DOC_ORDER,
                        'doc_id' => $order->getId(),
                        'base_type' => $movement->getBaseType(),
                        'base_key' => $movement->getBaseKey(),
                        'base_id' => $movement->getBaseId(),
                        'doc_row_key' => $bid->getRowKey(),
                        'doc_row_no' => $bid->getRowNo(),
                        'date_oper' => date('Y-m-d 22:00:00', strtotime($order->getDocDate())),
                        'status' => Movement::getStatusFromOrder($order),
                        'quantity' => -$quantity,
                        'amount' => -$amount,
                        'base_amount' => -$base['price']*$quantity,
                        'good_id' => $bid->getGood()->getId(),
                        'office_id' => $order->getOffice()->getId(),
                        'company_id' => $order->getCompany()->getId(),
                        'doc_stamp' => $docStamp,
                    ];

                    $this->entityManager->getRepository(Movement::class)
                            ->insertMovement($data);     
                    
                    $this->entityManager->getRepository(Comitent::class)
                            ->insertOrderComitent($order, $data);     

                    if ($movement->getStatus() == Movement::STATUS_COMMISSION){
                        $comiss = $this->entityManager->getRepository(Comiss::class)
                                ->findOneByDocKey($base['baseKey']);
                        $data = [
                            'doc_key' => $order->getLogKey(),
                            'doc_type' => Movement::DOC_ORDER,
                            'doc_id' => $order->getId(),
                            'doc_row_key' => $bid->getRowKey(),
                            'doc_row_no' => $bid->getRowNo(),
                            'date_oper' => $order->getDateOper(),
                            'status' => Movement::getStatusFromOrder($order),
                            'quantity' => -$quantity,
                            'amount' => -$base['price']*$quantity,
                            'good_id' => $bid->getGood()->getId(),
                            'office_id' => $order->getOffice()->getId(),
                            'company_id' => $order->getCompany()->getId(),
                            'contact_id' => $comiss->getContact()->getId(),
                            'doc_stamp' => $docStamp,
                        ];
                        $this->entityManager->getRepository(Comiss::class)
                                ->insertComiss($data);

                        $legalId = $contractId = null;
                        
                        $retailData = [
                            'doc_key' => $order->getLogKey(),
                            'doc_type' => Movement::DOC_ORDER,
                            'doc_id' => $order->getId(),
                            'date_oper' => $order->getDateOper(),
                            'status' => Retail::getStatusFromOrder($order),
                            'revise' => Retail::REVISE_NOT,
                            'amount' => -$base['price']*$quantity,
                            'contact_id' => $comiss->getContact()->getId(),
                            'office_id' => $order->getOffice()->getId(),
                            'company_id' => $order->getCompany()->getId(),
                            'doc_stamp' => $docStamp,
                            'legal_id' => $legalId,
                            'contract_id' => $contractId,
                            'doc_stamp' => $docStamp,
                        ];

                        $this->entityManager->getRepository(Retail::class)
                                ->insertRetail($retailData);                                
                    }  
                    $write -= $quantity;
                    if ($write <= 0){
                        break;
                    }                    
                }
                
            }

            $takeNoCount += $write;
            if ($write == 0){
                $take = Bid::TAKE_OK;
            } 
            $this->entityManager->getConnection()
                    ->update('bid', ['take' => $take], ['id' => $bid->getId()]);
        }    
        
        //обновить количество продаж товара
        $rCount = $this->entityManager->getRepository(Movement::class)
                ->goodMovementRetail($bid->getGood()->getId());
        $this->entityManager->getConnection()
                ->update('goods', ['retail_count' => -$rCount], ['id' => $bid->getGood()->getId()]);        
        
        return $takeNoCount;
    }
    
    /**
     * Обновить движения заказа
     * 
     * @param Order $order
     * @param float $docStamp
     */
    public function updateOrderMovement($order, $docStamp)
    {
                        
        $bids = $this->entityManager->getRepository(Bid::class)
                ->findByOrder($order->getId());
        
        $orderTake = $order->getStatusAccount();
        if ($order->getStatusAccount() == Order::STATUS_TAKE_NO){
            $orderTake = Order::STATUS_ACCOUNT_NO;
        }
        
        foreach ($bids as $bid){
            if ($this->insertMovement($order, $docStamp, $bid) > 0){
                $orderTake = Order::STATUS_TAKE_NO; //не проведено
            }
            $this->entityManager->getRepository(Movement::class)
                    ->updateGoodBalance($bid->getGood()->getId());
            
            $this->entityManager->getRepository(ComitentBalance::class)
                    ->updateComitentBalance($bid->getGood()->getId()); 

            $this->entityManager->getRepository(ComissBalance::class)
                    ->updateComissBalance($bid->getGood()->getId());
        }
        
        $this->entityManager->getConnection()
                ->update('orders', ['status_account' => $orderTake], ['id' => $order->getId()]);        
        
        return;
    }    
    
    /**
     * Найти контакт по данным из формы
     * @param array $data
     */
    public function findContactByOrderData($data)
    {
        $contact = $client = null;
        if (!empty($data['contact'])){
            $contact = $this->entityManager->getRepository(Contact::class)
                    ->find($data['contact']);
//            var_dump(1);
        }
        if (!$contact && !empty($data['phone'])){
            $phone = $this->entityManager->getRepository(Phone::class)
                    ->findOneByName($data['phone']);
            if ($phone){
                $contact = $phone->getContact();
//                var_dump(2);
            }
        }      
        if (!$contact && !empty($data['phone2'])){
            $phone2 = $this->entityManager->getRepository(Phone::class)
                    ->findOneByName($data['phone2']);
            if ($phone2){
                $contact = $phone->getContact();
//                var_dump(3);
            }
        }      
        if (!$contact && !empty($data['email'])){
            $email = $this->entityManager->getRepository(Email::class)
                    ->findOneByName($data['email']);
            if ($email){
                $contact = $email->getContact();
//                var_dump(4);
            }
        }      
        if (!$contact){
            $client = $this->clientManager->addNewClient(['name' => $data['name'], 'status' => Client::STATUS_ACTIVE]);
            $data['status'] = Contact::STATUS_LEGAL;
            $contact = $this->clientManager->addContactToClient($client, $data);
//            var_dump(5);
        }    
        
        if ($contact){
            if (!empty($data['phone'])){
                $this->contactManager->addPhone($contact, ['phone' => $data['phone']], true);
            }    
            if (!empty($data['phone2'])){
                $this->contactManager->addPhone($contact, ['phone' => $data['phone2']], true);
            }    
            if (!empty($data['email'])){
                $this->contactManager->addEmail($contact, $data['email'], true);
            }    
            if (!empty($data['name'])){
                if ($data['name'] != $contact->getName()){
                    $contact->setName($data['name']);
                    $this->entityManager->persist($contact);
                    $this->entityManager->flush($contact);
                }
            }    
            $client = $contact->getClient();
            if (!$client){
                $client = $this->clientManager->addNewClient(['name' => $data['name'], 'status' => Client::STATUS_ACTIVE]);
                $contact->setClient($client);
                $this->entityManager->persist($contact);
                $this->entityManager->flush($contact);
            }
        }

        return $contact;
    }
    
    /**
     * Найти машину в заказе
     * @param Contact $contact
     * @param array $data
     * @retrun ContactCar
     */
    public function findContactCarByOrderData($contact, $data)
    {
        $contactCar = null;
        if (!empty($data['contactCar'])){
            $contactCar = $this->entityManager->getRepository(ContactCar::class)
                    ->find($data['contactCar']);
        }
        if (!$contactCar && !empty($data['vin'])){
            $contactCar = $this->entityManager->getRepository(ContactCar::class)
                    ->findOneByVin($data['vin']);                
        }
        if (!$contactCar && !empty($data['vin2'])){
            $contactCar = $this->entityManager->getRepository(ContactCar::class)
                    ->findOneByVin($data['vin2']);                
        }
        if (!$contactCar && !empty($data['vin'])){
            $contactCar = new ContactCar();
            $contactCar->setVin($data['vin']);
            $contactCar->setDateCreated(date('Y-m-d H:i:s'));
            $contactCar->setStatus(ContactCar::STATUS_ACTIVE);
            $contactCar->setAc(ContactCar::AC_UNKNOWN);
            $contactCar->setTm(ContactCar::TM_UNKNOWN);
            $contactCar->setWheel(ContactCar::WHEEL_LEFT);
        }    
        
        if ($contactCar){
            if (!empty($data['vin'])){
                $contactCar->setVin($data['vin']);
            }    
            //$contactCar->setVin2((empty($data['vin2'])) ? null:$data['vin2']);
            $contactCar->setComment((empty($data['makeComment'])) ? null:$data['makeComment']);
            if (!empty($data['make'])){
                $make = $this->entityManager->getRepository(Make::class)
                        ->findOneByName($data['make']);
                $contactCar->setMake($make);
            }
            $contactCar->setContact($contact);
            $this->entityManager->persist($contactCar);
            $this->entityManager->flush($contactCar);
        }    

        return $contactCar;
    }
    
    /**
     * Добавить строку заказа
     * @param Order $order
     * @param array $data
     * @param bool $flushnow
     */
    public function addNewBid($order, $data, $flushnow=true)
    {
        $bid = new Bid();
        $bid->setNum($data['num']);
        $bid->setRowNo($data['rowNo']);
        $bid->setPrice($data['price']);
        $bid->setDisplayName((empty($data['displayName'])) ? null:$data['displayName']);
        $currentDate = date('Y-m-d H:i:s');        
        $bid->setDateCreated($currentDate);
        $bid->setBaseKey((empty($data['baseKey'])) ? null:$data['baseKey']);
        $bid->setTake(Bid::TAKE_NO);
        
        if ($data['good'] instanceof Goods){
            $bid->setGood($data['good']);            
            $bid->setOpts($data['good']->getOptsJson());
        } else {
            $good = $this->entityManager->getRepository(Goods::class)
                        ->findOneById($data['good']);        
            $bid->setGood($good);
            $bid->setOpts($good->getOptsJson());
        }    
        
        $bid->setOe(null);
        
        if (!empty($data['oem'])){
            $filter = new ArticleCode();
            $oe = $filter->filter($data['oem']);
            if ($oe){
                $bid->setOe($oe);
            }    
        }
        
        $bid->setUser($this->currentUser());  
        
        $bid->setOrder($order);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($bid);
        
        // Применяем изменения к базе данных.
        if ($flushnow){
            $this->entityManager->flush(); 
        }    
    }
    
    /**
     * Добавить строку заказа
     * @param Order $order
     * @param array $row
     * @param User $currentUser
     */
    public function insBid($order, $row, $currentUser = null)
    {
        $upd = [
            'row_no' => $row['rowNo'],
            'num' => $row['num'],
            'price' => $row['price'],
            'display_name' => (empty($row['displayName'])) ? null:$row['displayName'],
            'date_created' => date('Y-m-d H:i:s'),
            'oe' => null,
            'order_id' => $order->getId(),
            'take' => Bid::TAKE_NO,
            'base_key' => (empty($row['baseKey'])) ? null:$row['baseKey'],
        ];

        if ($row['good'] instanceof Goods){
            $upd['good_id'] = $row['good']->getId();
            $upd['opts'] = $row['good']->getOptsJson();
        } else {
            $upd['good_id'] = $row['good'];
            $upd['opts'] = (empty($row['opts'])) ? null:$row['opts'];
        }    
        
        if (!empty($row['oem'])){
            $filter = new ArticleCode();
            $oe = $filter->filter($row['oem']);
            if ($oe){
                $upd['oe'] = $oe;
            }    
        }
        
        if (!$currentUser){
            $currentUser = $this->currentUser();
        }    
        if ($currentUser){
            $upd['user_id'] = $currentUser->getId();
        }    
        
        $this->entityManager->getConnection()
                ->insert('bid', $upd);        
        return;
    }
    
    
    /**
     * Update bid.
     * @param Bid $bid
     * @param array $data
     * @return null
     */
    public function updateBid($bid, $data)            
    {
        
        $connection = $this->entityManager->getConnection(); 
        $connection->update('bid', $data, ['id' => $bid->getId()]);
        $this->updateOrderTotal($bid->getOrder());
        return;
    }
    
    /**
     * Добавить строки заказа
     * @param Order $order
     * @param array $data
     */
    public function updateBids($order, $data)
    {        
        $this->removeOrderBids($order);
        if (is_array($data)){
            $rowNo = 1;
            foreach ($data as $key => $row){
                $row['rowNo'] = $rowNo;
                $this->insBid($order, $row);
                $rowNo++;
            }
        }    
        $this->updOrderTotal($order);
        return;
    }

    /**
     * Добавить выборы
     * @param Order $order
     * @param array $data
     * @return type
     */
    public function addNewSelection($order, $data)
    {
        
        if (!empty($data['oem'])){
            $filter = new ArticleCode();
            $oe = $filter->filter($data['oem']);
            if ($oe){
                $selection = new Selection();
                $selection->setComment((empty($data['comment'])) ? null:$data['comment']);
                $selection->setOe($oe);

                $selection->setOrder($order);

                // Добавляем сущность в менеджер сущностей.
                $this->entityManager->persist($selection);

                // Применяем изменения к базе данных.
                $this->entityManager->flush($selection); 
            }    
        }
        return;
    }

    /**
     * Добавить выборы
     * @param Order $order
     * @param array $data
     * @return type
     */
    public function insSelection($order, $data)
    {
        
        if (!empty($data['oem'])){
            $filter = new ArticleCode();
            $oe = $filter->filter($data['oem']);
            if ($oe){
                $upd = [
                    'comment' => (empty($data['comment'])) ? null:$data['comment'],
                    'oe' => $oe,
                    'order_id' => $order->getId(),
                ];

                $this->entityManager->getConnection()
                        ->insert('selection', $upd);
            }    
        }
        return;
    }

    /**
     * Дата и время отгрузки из данных формы
     * @param array $data
     * @retrun string;
     */
    private function _shipmentDateTime($data)
    {
        $result = null;
        if (!empty($data['dateShipment'])){
            $result = date('Y-m-d', strtotime($data['dateShipment']));
            if (!empty($data['timeShipment'])){                
                $result = date('Y-m-d H:i:s', strtotime($data['dateShipment'].' '.$data['timeShipment'].':00:00'));
            }
        }
        
        return $result;
    }
    
    /**
     * Новый заказ
     * @param Office $office
     * @param Contact $contact
     * @param array $data
     * @return Order
     */
    public function addNewOrder($office, $contact, $data) 
    {
        $dateMod = !empty($data['dateMod']) ? $data['dateMod'] : date('Y-m-d H:i:s');
        $dateShipment = $this->_shipmentDateTime($data);
        $dateOper = !empty($dateShipment) ? $dateShipment : $dateMod;
        
        if ($dateOper > $this->allowDate){
            // Создаем новую сущность.
            $order = new Order();
            $order->setAddress(!empty($data['address']) ? $data['address'] : null);
            $order->setAplId(!empty($data['aplId']) ? $data['aplId'] : null);
            $order->setDateMod($dateMod);
            $order->setDateOper($dateOper);
            $order->setDateShipment($dateShipment);
            $order->setGeo(!empty($data['geo']) ? $data['geo'] : null);
            $order->setInfo(!empty($data['info']) ? $data['info'] : null);
            $order->setInvoiceInfo(!empty($data['invoiceInfo']) ? $data['invoiceInfo'] : null);
            $order->setMode(!empty($data['mode']) ? $data['mode'] : Order::MODE_MAN);
            $order->setShipmentDistance(!empty($data['shipmentDistance']) ? $data['shipmentDistance'] : 0);
            $order->setShipmentRate(!empty($data['shipmentRate']) ? $data['shipmentRate'] : 0);
            $order->setShipmetAddRate(!empty($data['shipmentAddRate']) ? $data['shipmentAddRate'] : 0);
            $order->setShipmetTotal(!empty($data['shipmentTotal']) ? $data['shipmentTotal'] : 0);
            $order->setStatus(!empty($data['status']) ? $data['status'] : Order::STATUS_NEW);
            $order->setTotal(!empty($data['total']) ? $data['total'] : 0);
            $order->setTrackNumber(!empty($data['trackNumber']) ? $data['trackNumber'] : null);
            $order->setInfoShipping(!empty($data['infoShipping']) ? $data['infoShipping'] : null);
            $order->setStatusAccount(Order::STATUS_ACCOUNT_NO);
            $order->setStatusEx(empty($data['statusEx']) ? Order::STATUS_EX_NO:$data['statusEx']);

            $order->setOffice($office);
            if (empty($data['company'])){
                $company = $this->entityManager->getRepository(Office::class)
                        ->findDefaultCompany($office, !empty($data['dateOper']) ? $data['dateOper'] : null);
            } else {
                $company = $this->entityManager->getRepository(Legal::class)
                        ->find($data['company']);
            }
            $order->setCompany($company);

            $order->setContact($contact);

            $order->setContactCar($this->findContactCarByOrderData($contact, $data));

            $order->setCourier(null);
            if (!empty($data['courier'])){
                $courier = $this->entityManager->getRepository(Courier::class)
                        ->find($data['courier']);
                $order->setCourier($courier);
            }

            $legal = null;
            $order->setLegal($legal);
            if (!empty($data['legal'])){
                $legal = $this->entityManager->getRepository(Legal::class)
                        ->find($data['legal']);
                $order->setLegal($legal);
            }
            if (empty($data['legal']) && !empty($data['legalInn']) && !empty($data['legalName'])){
                $legal = $this->legalManager->addLegal($contact, [
                    'inn' => $data['legalInn'],
                    'name' => $data['legalName'],
                    'kpp' => empty($data['legalKpp']) ? null:$data['legalKpp'],
                    'ogrn' => empty($data['legalOgrn']) ? null:$data['legalOgrn'],
                    'okpo' => empty($data['legalOkpo']) ? null:$data['legalOkpo'],
                    'head' => empty($data['legalHead']) ? null:$data['legalHead'],
                    'chiefAccount' => empty($data['legalChiefAccount']) ? null:$data['legalChiefAccount'],
                    'info' => empty($data['legalInfo']) ? null:$data['legalInfo'],
                    'address' => empty($data['legalAddress']) ? null:$data['legalAddress'],
                ]);
                $order->setLegal($legal);
            }

            $order->setBankAccount(null);
            if (!empty($data['bankAccount'])){
                $bankAccount = $this->entityManager->getRepository(BankAccount::class)
                        ->find($data['bankAccount']);
                $order->setBankAccount($bankAccount);
            }
            if ($legal && empty($data['bankAccount']) && !empty($data['rs']) && !empty($data['bik']) && !empty($data['bankName'])){
                $bankAccount = $this->legalManager->addBankAccount($legal, [
                    'rs' => $data['rs'],
                    'bik' => $data['bik'],
                    'name' => $data['bankName'],
                    'city' => empty($data['bankCity']) ? null:$data['bankCity'],
                    'ks' => empty($data['ks']) ? null:$data['ks'],
                ]);
                $order->setBankAccount($bankAccount);
            }

            $order->setRecipient(null);
            if (!empty($data['recipient'])){
                $recipient = $this->entityManager->getRepository(Legal::class)
                        ->find($data['recipient']);
                $order->setRecipient($recipient);
            }
            if (empty($data['recipient']) && !empty($data['recipientInn']) && !empty($data['recipientName'])){
                $recipient = $this->legalManager->addLegal($contact, [
                    'inn' => $data['recipientInn'],
                    'name' => $data['recipientName'],
                    'kpp' => empty($data['recipientKpp']) ? null:$data['recipientKpp'],
                    'ogrn' => empty($data['recipientOgrn']) ? null:$data['recipientOgrn'],
                    'okpo' => empty($data['recipientOkpo']) ? null:$data['recipientOkpo'],
                    'head' => empty($data['recipientHead']) ? null:$data['recipientHead'],
                    'chiefAccount' => empty($data['recipientChiefAccount']) ? null:$data['recipientChiefAccount'],
                    'info' => empty($data['recipientInfo']) ? null:$data['recipientInfo'],
                    'address' => empty($data['recipientAddress']) ? null:$data['recipientAddress'],
                ]);
                $order->setRecipient($recipient);
            }

            if (!empty($data['shipping'])){
                $shipping = $this->entityManager->getRepository(Shipping::class)
                        ->find($data['shipping']);
                $order->setShipping($shipping);
            } else {
                $shipping = $this->entityManager->getRepository(Shipping::class)
                        ->findOneBy(['office' => $office->getId(), 'status' => Shipping::STATUS_ACTIVE]);
            }
            $order->setShipping($shipping);

            $order->setSkiper(null);
            if (!empty($data['skiper'])){
                $skiper = $this->entityManager->getRepository(User::class)
                        ->find($data['skiper']);
                $order->setSkiper($skiper);
            }

            $order->setUser(null);
            if (!empty($data['user'])){
                $user = $this->entityManager->getRepository(User::class)
                        ->find($data['user']);
                $order->setUser($user);
            } else {
                $order->setUser($this->currentUser());
            }

            $currentDate = date('Y-m-d H:i:s');        
            $order->setDateCreated($currentDate);

            // Добавляем сущность в менеджер сущностей.
            $this->entityManager->persist($order);

            // Применяем изменения к базе данных.
            $this->entityManager->flush();
            
//            $this->checkoutOrder($order);

            return $order;
        }
        
        return;
    }   
    
    /**
     * Новый заказ
     * @param Office $office
     * @param Contact $contact
     * @param array $data
     * @return Order
     */
    public function insOrder($office, $contact, $data) 
    {
        $dateMod = !empty($data['dateMod']) ? $data['dateMod'] : date('Y-m-d H:i:s');
        $dateShipment = $this->_shipmentDateTime($data);
        $dateOper = !empty($dateShipment) ? $dateShipment : $dateMod;
        
        if ($dateOper > $this->allowDate){
            $upd = [
                'address' =>  (!empty($data['address'])) ? $data['address'] : null,
                'apl_id' =>  (!empty($data['aplId'])) ? $data['aplId'] : null,
                'date_mod' =>  $dateMod,
                'date_oper' =>  $dateOper,
                'date_shipment' => $dateShipment,
                'geo' =>  (!empty($data['geo'])) ? $data['geo'] : null,
                'info' =>  (!empty($data['info'])) ? $data['info'] : null,
                'invoice_info' =>  (!empty($data['invoiceInfo'])) ? $data['invoiceInfo'] : null,
                'mode' =>  (!empty($data['mode'])) ? $data['mode'] : Order::MODE_MAN,
                'shipment_distance' =>  (!empty($data['shipmentDistance'])) ? $data['shipmentDistance'] : 0,
                'shipment_rate' =>  (!empty($data['shipmentRate'])) ? $data['shipmentRate'] : 0,
                'shipment_add_rate' =>  (!empty($data['shipmentAddRate'])) ? $data['shipmentAddRate'] : 0,
                'shipment_total' =>  (!empty($data['shipmentTotal'])) ? $data['shipmentTotal'] : 0,
                'status' =>  (!empty($data['status'])) ? $data['status'] : Order::STATUS_NEW,
                'total' =>  (!empty($data['total'])) ? $data['total'] : 0,
                'track_number' =>  (!empty($data['trackNumber'])) ? $data['trackNumber'] : null,
                'info_shipping' => (!empty($data['infoShipping']) ? $data['infoShipping'] : null),
                'contact_car_id' => null,
                'courier_id' => null,
                'legal_id' => null,
                'recipient_id' => null,
                'shipping_id' => null,
                'skiper_id' => null,
                'user_id' => null,
                'office_id' => $office->getId(),
                'contact_id' => $contact->getId(),
                'date_created' => date('Y-m-d H:i:s'),
                'status_account' => Order::STATUS_ACCOUNT_NO,
                'status_ex' => empty($data['statusEx']) ? Order::STATUS_EX_NO:$data['statusEx'],
            ];

            $company = null;
            if (!empty($data['company'])){
                $company = $this->entityManager->getRepository(Legal::class)
                        ->find($data['company']);
            }

            if (!$company){
                $company = $this->entityManager->getRepository(Office::class)
                        ->findDefaultCompany($office, !empty($data['dateOper']) ? $data['dateOper'] : null);            
            }

            $upd['company_id'] = $company->getId();

            $contactCar = $this->findContactCarByOrderData($contact, $data);
            $upd['contact_car_id'] = ($contactCar) ? $contactCar->getId():null;

            if (!empty($data['courier'])){
                $upd['courier_id'] = $data['courier'];
            }

            $legal = null;
            if (!empty($data['legal'])){
                $legal = $this->entityManager->getRepository(Legal::class)
                        ->find($data['legal']);
                $upd['legal_id'] = $data['legal'];
            }
            if (empty($data['legal']) && !empty($data['legalInn']) && !empty($data['legalName'])){
                $legal = $this->legalManager->addLegal($contact, [
                    'inn' => $data['legalInn'],
                    'name' => $data['legalName'],
                    'kpp' => empty($data['legalKpp']) ? null:$data['legalKpp'],
                    'ogrn' => empty($data['legalOgrn']) ? null:$data['legalOgrn'],
                    'okpo' => empty($data['legalOkpo']) ? null:$data['legalOkpo'],
                    'head' => empty($data['legalHead']) ? null:$data['legalHead'],
                    'chiefAccount' => empty($data['legalChiefAccount']) ? null:$data['legalChiefAccount'],
                    'info' => empty($data['legalInfo']) ? null:$data['legalInfo'],
                    'address' => empty($data['legalAddress']) ? null:$data['legalAddress'],
                ]);
                $upd['legal_id'] = $legal->getId();
            }

            if (!empty($data['bankAccount'])){
                $upd['bank_account_id'] = $data['bankAccount'];
            }
            if ($legal && empty($data['bankAccount']) && !empty($data['rs']) && !empty($data['bik']) && !empty($data['bankName'])){
                $bankAccount = $this->legalManager->addBankAccount($legal, [
                    'rs' => $data['rs'],
                    'bik' => $data['bik'],
                    'name' => $data['bankName'],
                    'city' => empty($data['bankCity']) ? null:$data['bankCity'],
                    'ks' => empty($data['ks']) ? null:$data['ks'],
                ], true);
                $upd['bank_account_id'] = $bankAccount->getId();
            }

            if (!empty($data['recipient'])){
                $upd['recipient_id'] = $data['recipient'];
            }
            if (empty($data['recipient']) && !empty($data['recipientInn']) && !empty($data['recipientName'])){
                $recipient = $this->legalManager->addLegal($contact, [
                    'inn' => $data['recipientInn'],
                    'name' => $data['recipientName'],
                    'kpp' => empty($data['recipientKpp']) ? null:$data['recipientKpp'],
                    'ogrn' => empty($data['recipientOgrn']) ? null:$data['recipientOgrn'],
                    'okpo' => empty($data['recipientOkpo']) ? null:$data['recipientOkpo'],
                    'head' => empty($data['recipientHead']) ? null:$data['recipientHead'],
                    'chiefAccount' => empty($data['recipientChiefAccount']) ? null:$data['recipientChiefAccount'],
                    'info' => empty($data['recipientInfo']) ? null:data['recipientInfo'],
                    'address' => empty($data['recipientAddress']) ? null:$data['recipientAddress'],
                ]);
                $upd['recipient_id'] = $recipient->getId();
            }

            if (!empty($data['shipping'])){
                $upd['shipping_id'] = $data['shipping'];
            } else {
                $shipping = $this->entityManager->getRepository(Shipping::class)
                        ->findOneBy(['office' => $office->getId(), 'status' => Shipping::STATUS_ACTIVE]);
                $upd['shipping_id'] = $shipping->getId();
            }

            if (!empty($data['skiper'])){
                $user = $this->entityManager->getRepository(User::class)
                        ->find($data['skiper']);
                if ($user){
                    $upd['skiper_id'] = $user->getId();
                }    
            }

            if (!empty($data['user'])){
                $user = $this->entityManager->getRepository(User::class)
                        ->find($data['user']);
                if ($user){
                    $upd['user_id'] = $user->getId();
                }    
            } elseif ($this->currentUser()){
                $upd['user_id'] = $this->currentUser()->getId();
            }

            $this->entityManager->getConnection()
                    ->insert('orders', $upd);
            $order = $this->entityManager->getRepository(Order::class)
                    ->findOneBy([], ['id'=>'DESC'],1,0);

            return $order;
        }
        
        return;
    }   

    /**
     * Перепроведение заказа
     * @param Order $order
     */
    public function repostOrder($order)
    {
        if ($order->getDateOper() < $this->getAllowDate()){
            return;
        }
        if (!$order->getContract() && $order->getLegal()){
            $contract = $this->findDefaultContract($order->getOffice(), $order->getLegal(), $order->getDocDate(), $order->getAplId());
            $this->entityManager->getConnection()->update('orders', ['contract_id' => $contract->getId()], ['id' => $order->getId()]);
        }            
        $this->entityManager->refresh($order);
        
        $docStamp = $this->entityManager->getRepository(Register::class)
                ->orderRegister($order);
        $this->entityManager->getRepository(Movement::class)
                ->removeDocMovements($order->getLogKey());        
        $this->entityManager->getRepository(Comiss::class)
                ->removeDocComiss($order->getLogKey());
        $this->entityManager->getRepository(Comitent::class)
                ->removeDocComitent($order->getLogKey());        
        $this->entityManager->getRepository(Retail::class)
                ->removeOrderRetails($order->getLogKey());
        $this->entityManager->getRepository(Mutual::class)
                ->removeDocMutuals($order->getLogKey());                
        $this->entityManager->getRepository(Reserve::class)
                ->updateReserve($order);
        $this->updateOrderMovement($order, $docStamp);            

        if ($order->getStatus() == Order::STATUS_SHIPPED){
            $this->updateOrderRetails($order, $docStamp);
            if ($order->getLegal()){
                $this->updateOrderMutuals($order, $docStamp);
            }
        }
        
        return;
    }
    
    /**
     * Получить последний комментарий
     * 
     * @param Order $order
     */
    public function lastComment($order)
    {
        return $this->entityManager->getRepository(Comment::class)
                ->lastComment($order);
    }
    
    /**
     * Подготовить зависимые данные
     * @param Order $order
     * @return array
     */
    private function dependInfo($order)
    {
        $result = [
            'comments' => [],
            'phones' => [],
            'emails' => [],
            'marketplaces' => [],
        ];
        
        foreach ($order->getComments() as $comment){
            $result['comments'][] = $comment->toLog();
        }
        
        foreach ($order->getContact()->getPhones() as $phone){
            $result['phones'][] = $phone->toLog();
        }

        foreach ($order->getContact()->getEmails() as $email){
            $result['emails'][] = $email->toLog();
        }
        
        foreach ($order->getMarketplaceOrders() as $marketplaceOrder){
            $result['marketplaceOrders'][] = $marketplaceOrder->toLog();
        }

        return $result;
    }
    
    /**
     * Обновить зависимые записи
     * @param Order $order
     * @param bool $flush
     */
    public function updateDependInfo($order, $flush = false)
    {

        $dependInfo = $this->dependInfo($order);
        $order->setDependInfo($dependInfo);
        
        if ($flush){
            $this->entityManager->persist($order);
            $this->entityManager->flush($order);
        }
        
        return Encoder::encode($dependInfo);
    }
    
    /**
     * Обновить итог по заказу
     * @param Order $order
     */
    public function updateOrderTotal($order)
    {
        $preLog = $this->entityManager->getRepository(Log::class)
                ->findOneByLogKey($order->getLogKey());
        if (!$preLog){
            $this->logManager->infoOrder($order, Log::STATUS_INFO);            
        }

        $result = $this->entityManager->getRepository(Bid::class)
                ->getOrderNum($order);
        
        $total = 0;
        if (count($result)){
            $total = $result[0]['total'];
        }
        
        $order->setTotal($total + $order->getShipmentTotal());

        $this->updateDependInfo($order);
        
        $this->entityManager->persist($order);
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
        
        $this->entityManager->refresh($order);
        $this->repostOrder($order);
        $this->logManager->infoOrder($order, Log::STATUS_UPDATE);
    }
    
    /**
     * Обновить итог по заказу
     * @param Order $order
     */
    public function updOrderTotal($order)
    {
        $preLog = $this->entityManager->getRepository(Log::class)
                ->findOneByLogKey($order->getLogKey());
        if (!$preLog){
            $this->logManager->infoOrder($order, Log::STATUS_INFO);            
        }

        $result = $this->entityManager->getRepository(Bid::class)
                ->getOrderNum($order);
        
        $total = 0;
        if (count($result)){
            $total = $result[0]['total'];
        }
        
        $dependInfo = $this->dependInfo($order);
        
        $this->entityManager->getConnection()
                ->update('orders', ['total' => $total + $order->getShipmentTotal(), 'depend_info' => Encoder::encode($dependInfo)], ['id' => $order->getId()]);
        
        $this->entityManager->refresh($order);
        $this->repostOrder($order);
        $this->logManager->infoOrder($order, Log::STATUS_UPDATE);
    }

    /**
     * Обновить заказ
     * @param Order $order
     * @param array $data
     */
    public function updateOrder($order, $data) 
    {
        $dateMod = !empty($data['dateMod']) ? $data['dateMod'] : date('Y-m-d H:i:s');
        $dateShipment = $this->_shipmentDateTime($data);
        $dateOper = !empty($dateShipment) ? $dateShipment : $dateMod;

        if ($dateOper > $this->allowDate){
            $order->setAddress(!empty($data['address']) ? $data['address'] : null);
            $order->setAplId(!empty($data['aplId']) ? $data['aplId'] : null);
            $order->setDateMod($dateMod);
            $order->setDateOper($dateOper);
            $order->setDateShipment($dateShipment);
            $order->setGeo(!empty($data['geo']) ? $data['geo'] : null);
            $order->setInfo(!empty($data['info']) ? $data['info'] : null);
            $order->setInvoiceInfo(!empty($data['invoiceInfo']) ? $data['invoiceInfo'] : null);
            $order->setMode(!empty($data['mode']) ? $data['mode'] : Order::MODE_MAN);
            $order->setShipmentDistance(!empty($data['shipmentDistance']) ? $data['shipmentDistance'] : 0);
            $order->setShipmentRate(!empty($data['shipmentRate']) ? $data['shipmentRate'] : 0);
            $order->setShipmetAddRate(!empty($data['shipmentAddRate']) ? $data['shipmentAddRate'] : 0);
            $order->setShipmetTotal(!empty($data['shipmentTotal']) ? $data['shipmentTotal'] : 0);
            $order->setStatus(!empty($data['status']) ? $data['status'] : Order::STATUS_NEW);
            $order->setTotal(!empty($data['total']) ? $data['total'] : 0);
            $order->setTrackNumber(!empty($data['trackNumber']) ? $data['trackNumber'] : null);
            $order->setInfoShipping(!empty($data['infoShipping']) ? $data['infoShipping'] : null);
            $order->setStatusAccount(Order::STATUS_ACCOUNT_NO);
            $order->setStatusEx(empty($data['statusEx']) ? Order::STATUS_EX_NO:$data['statusEx']);

            if ($order->getOffice()->getId() != $data['office']){
                $office = $this->entityManager->getRepository(Office::class)
                        ->find($data['office']);
                $order->setOffice($office);        
            }
            $order->setContactCar($this->findContactCarByOrderData($order->getContact(), $data));
            
            $order->setCourier(null);
            if (!empty($data['courier'])){
                $courier = $this->entityManager->getRepository(Courier::class)
                        ->find($data['courier']);
                $order->setCourier($courier);
            }

            $legal = null;
            $order->setLegal($legal);
            if (!empty($data['legal'])){
                $legal = $this->entityManager->getRepository(Legal::class)
                        ->find($data['legal']);
                $order->setLegal($legal);
            }
            if (empty($data['legal']) && !empty($data['legalInn']) && !empty($data['legalName'])){
                $legal = $this->legalManager->addLegal($order->getContact(), [
                    'inn' => $data['legalInn'],
                    'name' => $data['legalName'],
                    'kpp' => empty($data['legalKpp']) ? null:$data['legalKpp'],
                    'ogrn' => empty($data['legalOgrn']) ? null:$data['legalOgrn'],
                    'okpo' => empty($data['legalOkpo']) ? null:$data['legalOkpo'],
                    'head' => empty($data['legalHead']) ? null:$data['legalHead'],
                    'chiefAccount' => empty($data['legalChiefAccount']) ? null:$data['legalChiefAccount'],
                    'info' => empty($data['legalInfo']) ? null:$data['legalInfo'],
                    'address' => empty($data['legalAddress']) ? null:$data['legalAddress'],
                ]);
                $order->setLegal($legal);
            }

            $order->setBankAccount(null);
            if (!empty($data['bankAccount'])){
                $bankAccount = $this->entityManager->getRepository(BankAccount::class)
                        ->find($data['bankAccount']);
                $order->setBankAccount($bankAccount);
            }
            if ($legal && empty($data['bankAccount']) && !empty($data['rs']) && !empty($data['bik']) && !empty($data['bankName'])){
                $bankAccount = $this->legalManager->addBankAccount($legal, [
                    'rs' => $data['rs'],
                    'bik' => $data['bik'],
                    'name' => $data['bankName'],
                    'city' => empty($data['bankCity']) ? null:$data['bankCity'],
                    'ks' => empty($data['ks']) ? null:$data['ks'],
                ]);
                $order->setBankAccount($bankAccount);
            }

            $order->setRecipient(null);
            if (!empty($data['recipient'])){
                $recipient = $this->entityManager->getRepository(Legal::class)
                        ->find($data['recipient']);
                $order->setRecipient($recipient);
            }
            if (empty($data['recipient']) && !empty($data['recipientInn']) && !empty($data['recipientName'])){
                $recipient = $this->legalManager->addLegal($order->getContact(), [
                    'inn' => $data['recipientInn'],
                    'name' => $data['recipientName'],
                    'kpp' => empty($data['recipientKpp']) ? null:$data['recipientKpp'],
                    'ogrn' => empty($data['recipientOgrn']) ? null:$data['recipientOgrn'],
                    'okpo' => empty($data['recipientOkpo']) ? null:$data['recipientOkpo'],
                    'head' => empty($data['recipientHead']) ? null:$data['recipientHead'],
                    'chiefAccount' => empty($data['recipientChiefAccount']) ? null:$data['recipientChiefAccount'],
                    'info' => empty($data['recipientInfo']) ? null:$data['recipientInfo'],
                    'address' => empty($data['recipientAddress']) ? null:$data['recipientAddress'],
                ]);
                $order->setRecipient($recipient);
            }

            $order->setShipping(null);
            if (!empty($data['shipping'])){
                $shipping = $this->entityManager->getRepository(Shipping::class)
                        ->find($data['shipping']);
                $order->setShipping($shipping);
            }

            $order->setSkiper(null);
            if (!empty($data['skiper'])){
                $skiper = $this->entityManager->getRepository(User::class)
                        ->find($data['skiper']);
                $order->setSkiper($skiper);
            }
            
            $order->setUser($this->currentUser());
            if (!empty($data['user'])){
                $user = $this->entityManager->getRepository(User::class)
                        ->find($data['user']);
                $order->setUser($user);
                    
            }

            $this->entityManager->persist($order);
            // Применяем изменения к базе данных.
            $this->entityManager->flush();
        }    
        
        return;
    }    
    
    /**
     * Обновить заказ
     * @param Order $order
     * @param array $data
     */
    public function updOrder($order, $data) 
    {
        $dateMod = !empty($data['dateMod']) ? $data['dateMod'] : date('Y-m-d H:i:s');
        $dateShipment = $this->_shipmentDateTime($data);
        $dateOper = !empty($dateShipment) ? $dateShipment : $dateMod;
        
        if ($dateOper > $this->allowDate){

            $upd = [
                'address' =>  (!empty($data['address'])) ? $data['address'] : null,
                'apl_id' =>  (!empty($data['aplId'])) ? $data['aplId'] : null,
                'date_mod' =>  $dateMod,
                'date_oper' =>  $dateOper,
                'date_shipment' => $dateShipment,
                'geo' =>  (!empty($data['geo'])) ? $data['geo'] : null,
                'info' =>  (!empty($data['info'])) ? $data['info'] : null,
                'invoice_info' =>  (!empty($data['invoiceInfo'])) ? $data['invoiceInfo'] : null,
                'mode' =>  (!empty($data['mode'])) ? $data['mode'] : Order::MODE_MAN,
                'shipment_distance' =>  (!empty($data['shipmentDistance'])) ? $data['shipmentDistance'] : 0,
                'shipment_rate' =>  (!empty($data['shipmentRate'])) ? $data['shipmentRate'] : 0,
                'shipment_add_rate' =>  (!empty($data['shipmentAddRate'])) ? $data['shipmentAddRate'] : 0,
                'shipment_total' =>  (!empty($data['shipmentTotal'])) ? $data['shipmentTotal'] : 0,
                'status' =>  (!empty($data['status'])) ? $data['status'] : Order::STATUS_NEW,
                'total' =>  (!empty($data['total'])) ? $data['total'] : 0,
                'track_number' =>  (!empty($data['trackNumber'])) ? $data['trackNumber'] : null,
                'info_shipping' => (!empty($data['infoShipping']) ? $data['infoShipping'] : null),
                'contact_car_id' => null,
                'courier_id' => null,
                'legal_id' => null,
                'recipient_id' => null,
                'shipping_id' => null,
                'skiper_id' => null,
                'user_id' => null,
                'status_account' => Order::STATUS_ACCOUNT_NO,
                'status_ex' => empty($data['statusEx']) ? Order::STATUS_EX_NO:$data['statusEx'],
            ];

            if ($order->getOffice()->getId() != $data['office']){
                $office = $this->entityManager->getRepository(Office::class)
                        ->find($data['office']);
                if ($office){
                    $upd['office_id'] = $office->getId();        
                }    
            }

            $contactCar = $this->findContactCarByOrderData($order->getContact(), $data);
            $upd['contact_car_id'] = ($contactCar) ? $contactCar->getId():null;

            $upd['courier_id'] = null;
            if (!empty($data['courier'])){
                $upd['courier_id'] = $data['courier'];
            }

            $legal = null;
            if (!empty($data['legal'])){
                $legal = $this->entityManager->getRepository(Legal::class)
                        ->find($data['legal']);
                $upd['legal_id'] = $data['legal'];
            }
            if (empty($data['legal']) && !empty($data['legalInn']) && !empty($data['legalName'])){
                $legal = $this->legalManager->addLegal($order->getContact(), [
                    'inn' => $data['legalInn'],
                    'name' => $data['legalName'],
                    'kpp' => empty($data['legalKpp']) ? null:$data['legalKpp'],
                    'ogrn' => empty($data['legalOgrn']) ? null:$data['legalOgrn'],
                    'okpo' => empty($data['legalOkpo']) ? null:$data['legalOkpo'],
                    'head' => empty($data['legalHead']) ? null:$data['legalHead'],
                    'chiefAccount' => empty($data['legalChiefAccount']) ? null:$data['legalChiefAccount'],
                    'info' => empty(data['legalInfo']) ? null:$data['legalInfo'],
                    'address' => empty($data['legalAddress']) ? null:$data['legalAddress'],
                ]);
                $upd['legal_id'] = $legal->getId();
            }

            $upd['bank_account_id'] = null;
            if (!empty($data['bankAccount'])){
                $upd['bank_account_id'] = $data['bankAccount'];
            }
            if ($legal && empty($data['bankAccount']) && !empty($data['rs']) && !empty($data['bik']) && !empty($data['bankName'])){
                $bankAccount = $this->legalManager->addBankAccount($legal, [
                    'rs' => $data['rs'],
                    'bik' => $data['bik'],
                    'name' => $data['bankName'],
                    'city' => empty($data['bankCity']) ? null:$data['bankCity'],
                    'ks' => empty($data['ks']) ? null:$data['ks'],
                ], true);
                $upd['bank_account_id'] = $bankAccount->getId();
            }

            if (!empty($data['recipient'])){
                $upd['recipient_id'] = $data['recipient'];
            }
            if (empty($data['recipient']) && !empty($data['recipientInn']) && !empty($data['recipientName'])){
                $recipient = $this->legalManager->addLegal($order->getContact(), [
                    'inn' => data['recipientInn'],
                    'name' => data['recipientName'],
                    'kpp' => empty($data['recipientKpp']) ? null:$data['recipientKpp'],
                    'ogrn' => empty($data['recipientOgrn']) ? null:$data['recipientOgrn'],
                    'okpo' => empty($data['recipientOkpo']) ? null:$data['recipientOkpo'],
                    'head' => empty($data['recipientHead']) ? null:$data['recipientHead'],
                    'chiefAccount' => empty($data['recipientChiefAccount']) ? null:$data['recipientChiefAccount'],
                    'info' => empty($data['recipientInfo']) ? null:$data['recipientInfo'],
                    'address' => empty($data['recipientAddress']) ? null:$data['recipientAddress'],
                ]);
                $upd['recipient_id'] = $recipient->getId();
            }

            $upd['shipping_id'] = null;                
            if (!empty($data['shipping'])){
                $upd['shipping_id'] = $data['shipping'];
            }

            $upd['skiper_id'] = null;                
            if (!empty($data['skiper'])){
                $user = $this->entityManager->getRepository(User::class)
                        ->find($data['skiper']);
                if ($user){
                    $upd['skiper_id'] = $user->getId();
                }    
            }

            if (!empty($data['user'])){
                $user = $this->entityManager->getRepository(User::class)
                        ->find($data['user']);
                if ($user){
                    $upd['user_id'] = $user->getId();
                }    
            } else {
                if ($this->currentUser()){
                    $upd['user_id'] = $this->currentUser()->getId();
                }                    
            }

            $this->entityManager->getConnection()
                    ->update('orders', $upd, ['id' => $order->getId()]);

            return $order;
        }
        
        return;
    }    
    
    /**
     * Сдклать копию заказа
     * @param Order $order
     */
    public function duplicate($order)
    {
        $data = $order->toArray();
        
        unset($data['aplId']);
        unset($data['dateMod']);
        unset($data['geo']);
        unset($data['mode']);
        unset($data['status']);
        unset($data['trackNumber']);
        unset($data['infoShipping']);
        unset($data['statusEx']);
        unset($data['courier']);
        unset($data['skiper']);
        unset($data['user']);
        unset($data['dateShipment']);
        unset($data['timeShipment']);
        
        $newOrder = $this->addNewOrder($order->getOffice(), $order->getContact(), $data);
        
        foreach ($order->getSelections() as $selection){
            $this->addNewSelection($newOrder, [
                'oem' => $selection->getOe(),
                'comment' => $selection->getComment(),
            ]);
        }
        
        foreach ($order->getBids() as $bid){
            $newBid = $bid->toLog();
            $this->addNewBid($newOrder, $newBid, false);
        }
        
        $this->entityManager->flush();
        
        return $newOrder;
    }
    
    /**
     * Обновить ЮЛ заказа
     * @param Order $order
     * @param array $data
     */
    public function updOrderLegal($order, $data) 
    {
        
        if ($order->getDateOper() > $this->allowDate){

            $upd = [
                'legal_id' => null,
                'contract_id' => null,
                'recipient_id' => null,
                'bank_account_id' => null,
                'status_ex' => Order::STATUS_EX_NEW,
            ];

            $legal = $recipient = $bankAccount = $contract = null;
            if (empty($data['legal']) && !empty($data['legalInn']) && !empty($data['legalName'])){
                $legal = $this->legalManager->addLegal($order->getContact(), [
                    'inn' => $data['legalInn'],
                    'name' => $data['legalName'],
                    'kpp' => empty($data['legalKpp']) ? null:$data['legalKpp'],
                    'ogrn' => empty($data['legalOgrn']) ? null:$data['legalOgrn'],
                    'okpo' => empty($data['legalOkpo']) ? null:$data['legalOkpo'],
                    'head' => empty($data['legalHead']) ? null:$data['legalHead'],
                    'address' => empty($data['legalAddress']) ? null:$data['legalAddress'],
                ]);
                $upd['legal_id'] = $legal->getId();
                
                $contract = $this->findDefaultContract($order->getOffice(), $legal, $order->getDocDate(), $order->getAplId());
                $upd['contract_id'] = $contract->getId();
            }

            if (empty($data['recipient']) && !empty($data['recipientInn']) && !empty($data['recipientName'])){
                $recipient = $this->legalManager->addLegal($order->getContact(), [
                    'inn' => $data['recipientInn'],
                    'name' => $data['recipientName'],
                    'kpp' => empty($data['recipientKpp']) ? null:$data['recipientKpp'],
                    'ogrn' => empty($data['recipientOgrn']) ? null:$data['recipientOgrn'],
                    'okpo' => empty($data['recipientOkpo']) ? null:$data['recipientOkpo'],
                    'head' => empty($data['recipientHead']) ? null:$data['recipientHead'],
                    'address' => empty($data['recipientAddress']) ? null:$data['recipientAddress'],
                ]);
                $upd['recipient_id'] = $recipient->getId();
            }

            if ($legal && empty($data['bankAccount']) && !empty($data['rs']) && !empty($data['bik']) && !empty($data['bankName'])){
                $bankAccount = $this->legalManager->addBankAccount($legal, [
                    'rs' => $data['rs'],
                    'bik' => $data['bik'],
                    'name' => $data['bankName'],
                    'city' => empty($data['bankCity']) ? null:$data['bankCity'],
                    'ks' => empty($data['ks']) ? null:$data['ks'],
                ], true);
                $upd['bank_account_id'] = $bankAccount->getId();
            }
            
            $this->entityManager->getConnection()
                    ->update('orders', $upd, ['id' => $order->getId()]);

            $this->repostOrder($order);
            $this->logManager->infoOrder($order, Log::STATUS_UPDATE);

            return $order;
        }
        
        return;
    }    
    

    /**
     * Update order status.
     * @param Order $order
     * @param integer $status
     * @return integer
     */
    public function updateOrderStatus($order, $status)            
    {

        if ($order->getDateOper() > $this->allowDate){
            $order->setStatus($status);
            $order->setStatusEx(Order::STATUS_EX_NEW);
            
            if (empty($order->getUser()) && $this->currentUser()){
                $order->setUser($this->currentUser());
            }

            $this->entityManager->persist($order);
            $this->entityManager->flush($order);

            $this->repostOrder($order);
            $this->logManager->infoOrder($order, Log::STATUS_UPDATE);
        }    
        
        return;
    }
    
    /**
     * Обновить контакт клиента
     * @param Order $order
     * @param Contact $newContact
     */
    public function updateOrderContact($order, $newContact)
    {
        $order->setContact($newContact);
        $this->entityManager->persist($order);
        
        $contactCar = $order->getContactCar();
        if ($contactCar){
            $contactCar->setContact($newContact);
            $this->entityManager->persist($contactCar);
        }
        
        $legal = $order->getLegal();
        if ($legal){
            $legal->addContact($newContact);
            $this->entityManager->persist($legal);
        }
        
        $recipient = $order->getRecipient();
        if ($recipient){
            $recipient->addContact($newContact);
            $this->entityManager->persist($recipient);
        }
        
        $this->entityManager->flush();
        $this->entityManager->refresh($order);
    }
    
    /**
     * Удалить строки заказа
     * @param Order $order
     */
    public function removeOrderBids($order)
    {
        $bids = $this->entityManager->getRepository(Bid::class)
                    ->findByOrder($order->getId());
        
        foreach ($bids as $bid){
            $this->entityManager->remove($bid);
        }
        
        $this->entityManager->flush();
        return;
    }
    
    /**
     * Удалить строки подбора
     * @param Order $order
     */
    public function removeOrderSelections($order)
    {
        $selections = $this->entityManager->getRepository(Selection::class)
                    ->findByOrder($order->getId());
        
        foreach ($selections as $selection){
            $this->entityManager->remove($selection);
        }
        
        $this->entityManager->flush();
        return;
    }
    
    /**
     * Удалить заказы поставщикам
     * @param Order $order
     */
    public function removeSupplierOrders($order)
    {
        $supplierOrders = $this->entityManager->getRepository(SupplierOrder::class)
                    ->findByOrder($order->getId());
        
        foreach ($supplierOrders as $supplierOrder){
            $this->entityManager->remove($supplierOrder);
        }
        
        $this->entityManager->flush();
        return;
    }
    
    /**
     * Удалить заказ
     * @param Order $order
     * @return null
     */
    public function removeOrder($order) 
    {   
        if ($order->getDateOper() > $this->allowDate){
            $this->removeOrderBids($order);
            $this->removeOrderSelections($order);
            $this->entityManager->remove($order);
            $this->entityManager->flush();
        }    
        return;        
    }    

    /**
     * Обновить машину в заказе
     * @param Order $order
     * @param array $data
     */
    public function updateContactCar($order, $data)
    {
        $contactCar = $order->getContactCar();
        if ($contactCar){
            
        }
        
        return;
    }
    
    
    /**
     * Обновить подборы
     * @param Order $order
     * @param array $data
     */
    public function updateSelections($order, $data)
    {
        $this->removeOrderSelections($order);
        
        if (is_array($data)){
            foreach ($data as $selection){
                $this->insSelection($order, ['oem' => $selection]);
            }    
        }
        
        return;
    }
    
    /**
     * Обновить подборы
     * @param Order $order
     * @param string $strSelections
     */
    public function updateSelectionsFromJson($order, $strSelections)
    {
        
        try{
            $selections = Decoder::decode($strSelections);
        } catch (Exception $ex){
            $selections = [];
        }    
        
        $this->updateSelections($order, $selections);

        return;
    }
    
    /**
     * Перепроведение всех заказов
     */
    public function repostAllOrder()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        
        $orderQuery = $this->entityManager->getRepository(Order::class)
                ->queryAllOrder();
        $iterable = $orderQuery->iterate();
        
        foreach ($iterable as $row){
            foreach($row as $order){ 
                $this->repostOrder($order);
                $this->entityManager->detach($order);
                unset($order);
            }    
        }    
        
        return;
    }
    
    /**
     * Обновить заказ в Апл
     * @param Order $order
     * @return type
     */    
    protected function checkoutOrder($order)
    {

        $items = [];
        foreach ($order->getBids() as $bid){
            $items[] = [
                'offerId' => $bid->getGood()->getAplId(),
                'count' => $big->getNum(),
                'price' => $bid->getPrice(),
            ];            
        }

        if ($phone = $filtered['phone']){
            $data = [
                'bo' => $order->getOffice()->getAplId(),
                'name' => $order->getContact()->getName(),
                'info2' => $order->getInfo(),
                'phone' => $order->getContact()->getPhone(),
                'email' => $order->getContact()->getEmail(),
                'address' => $$order->getAddress(),
                'items' => $items,
            ];
//            var_dump($data); exit;

            $aplResponce = $this->aplService->checkout($data);
            if (is_array($aplResponce)){
                $orderData = (array) $aplResponce['order'];
                if ($orderId = $orderData['id']){
                    if (!$order->getAplId()){
                        $order->setAplId($orderId);
                        $this->entityManager->persist($order);
                        $this->entityManager->flush($order);
                    }
                    return $orderId;
//                    $text .= PHP_EOL."https://autopartslist.ru/admin/orders/view/id/$orderId";
                }
            }                
        }    
        
        return;
    }
    
    /**
     * Заменить товар
     * @param Goods $oldGood
     * @param Goods $newGood
     */
    public function changeGood($oldGood, $newGood)
    {
        $rows = $this->entityManager->getRepository(Bid::class)
                ->findBy(['good' => $oldGood->getId()]);
        foreach ($rows as $row){
            $row->setGood($newGood);
            $this->entityManager->persist($row);
            $this->entityManager->flush();
            $this->repostOrder($row->getOrder());
        }
        
        return;
    }  
    
    /**
     * Отменить старые заказы со статусом новый и обработан
     */
    public function cancelOld()
    {
        $orders = $this->entityManager->getRepository(Order::class)
                ->findForCancel();
        foreach ($orders as $order){
            if ($order->getDocDate() < date('Y-m-d H:i:s', strtotime(" -7 days"))){
                $order->setStatus(Order::STATUS_CANCELED);
                //$order->setStatusEx(Order::STATUS_EX_NO);
                $this->entityManager->persist($order);
            }
        }
        
        $this->entityManager->flush();
        
        return;
    }
}
