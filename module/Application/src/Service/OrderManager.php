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
use Application\Entity\Cart;
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
        
    
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $authService)
    {
        $this->entityManager = $entityManager;
        $this->authService = $authService;
    }
    
    /**
     * Обновить взаиморасчеты заказа
     * 
     * @param Order $order
     */
    public function updateOrderMutuals($order)
    {
        
        $this->entityManager->getRepository(Mutual::class)
                ->removeDocMutuals($order->getLogKey());
        
        $data = [
            'doc_key' => $order->getLogKey(),
            'date_oper' => $order->getDateOper(),
            'status' => $order->getStatus(),
            'revise' => Mutual::REVISE_NOT,
            'amount' => $order->getTotal(),
            'legal_id' => $ptu->getLegal()->getId(),
            'contract_id' => $ptu->getContract()->getId(),
            'office_id' => $order->getOffice()->getId(),
            'company_id' => $order->getCompany()->getId(),
        ];

        $this->entityManager->getRepository(Mutual::class)
                ->insertMutual($data);
        
        return;
    }    
    
    /**
     * Обновить движения документа
     * 
     * @param Ptu $ptu
     */
    public function updatePtuMovement($ptu)
    {
        
        $this->entityManager->getRepository(Movement::class)
                ->removeDocMovements($ptu->getLogKey());
        
        $ptuGoods = $this->entityManager->getRepository(PtuGood::class)
                ->findByPtu($ptu->getId());
        foreach ($ptuGoods as $ptuGood){
            $data = [
                'doc_key' => $ptu->getLogKey(),
                'doc_row_key' => $ptuGood->getDocRowKey(),
                'doc_row_no' => $ptuGood->getRowNo(),
                'date_oper' => $ptu->getDocDate(),
                'status' => $ptu->getStatus(),
                'quantity' => $ptuGood->getQuantity(),
                'amount' => $ptuGood->getAmount(),
                'good_id' => $ptuGood->getGood()->getId(),
                'office_id' => $ptu->getOffice()->getId(),
                'company_id' => $ptu->getContract()->getCompany()->getId(),
            ];

            $this->entityManager->getRepository(Movement::class)
                    ->insertMovement($data);
        }
        
        return;
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
        $bid->setPrice($data['price']);
        $bid->setDisplayName((empty($data['displayName'])) ? null:$data['displayName']);
        $currentDate = date('Y-m-d H:i:s');        
        $bid->setDateCreated($currentDate);
        
        if ($data['good'] instanceof Goods){
            $bid->setGood($data['good']);            
        } else {
            $good = $this->entityManager->getRepository(Goods::class)
                        ->findOneById($data['good']);        
            $bid->setGood($good);
        }    
        
        $bid->setOem(null);
        
        if (!empty($data['oem'])){
            $filter = new ArticleCode();
            $oe = $filter->filter($data['oem']);
            if ($oe){
                $oem = $this->entityManager->getRepository(Oem::class)
                        ->findOneByOe($oe);
                $bid->setOem($oem);
            }    
        }
        
        $currentUser = $this->entityManager->getRepository(User::class)
                ->findOneByEmail($this->authService->getIdentity());
        if ($currentUser){
            $bid->setUser($currentUser);  
        }    
        
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
     * @param array $data
     */
    public function insBid($order, $data)
    {
        $upd = [
            'num' => $data['num'],
            'price' => $data['price'],
            'display_name' => (empty($data['displayName'])) ? null:$data['displayName'],
            'current_date' => date('Y-m-d H:i:s'),
            'oem_id' => null,
            'order_id' => $order->getId(),
        ];

        if ($data['good'] instanceof Goods){
            $upd['good_id'] = $data['good']->getId();
        } else {
            $upd['good_id'] = $data['good'];
        }    
        
        if (!empty($data['oem'])){
            $filter = new ArticleCode();
            $oe = $filter->filter($data['oem']);
            if ($oe){
                $oem = $this->entityManager->getRepository(Oem::class)
                        ->findOneByOe($oe);
                $upd['oem_id'] = $oem->getId();
            }    
        }
        
        $currentUser = $this->entityManager->getRepository(User::class)
                ->findOneByEmail($this->authService->getIdentity());
        if ($currentUser){
            $upd['user_id'] = $currentUser->getId();
        }    
        
        $this->entityManager->getConnection()
                ->insert('bid', $upd);        
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
                $oem = $this->entityManager->getRepository(Oem::class)
                        ->findOneByOe($oe);
                if ($oem){
                    $selection = new Selection();
                    $selection->setComment((empty($data['comment'])) ? null:$data['comment']);
                    $selection->setOem($oem);

                    $selection->setOrder($order);

                    // Добавляем сущность в менеджер сущностей.
                    $this->entityManager->persist($selection);

                    // Применяем изменения к базе данных.
                    $this->entityManager->flush(); 
                }    
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
                $oem = $this->entityManager->getRepository(Oem::class)
                        ->findOneByOe($oe);
                if ($oem){
                    $upd = [
                        'comment' => (empty($data['comment'])) ? null:$data['comment'],
                        'oem_id' => $oem->getId(),
                        'order_id' => $order->getId(),
                    ];
                    
                    $this->entityManager->getConnection()
                            ->insert('selection', $upd);
                }    
            }    
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
    public function addNewOrder($office, $contact, $data) 
    {
        // Создаем новую сущность.
        $order = new Order();
        $order->setAddress(!empty($data['address']) ? $data['address'] : null);
        $order->setAplId(!empty($data['aplId']) ? $data['aplId'] : null);
        $order->setDateMod(!empty($data['dateMod']) ? $data['dateMod'] : date('Y-m-d H:i:s'));
        $order->setDateOper(!empty($data['dateOper']) ? $data['dateOper'] : null);
        $order->setDateShipment(!empty($data['dateShipment']) ? $data['dateShipment'] : null);
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
        
        $order->setContactCar(null);
        if (!empty($data['contactCar'])){
            $contactCar = $this->entityManager->getRepository(ContactCar::class)
                    ->find($data['contactCar']);
            $order->setContactCar($contactCar);
        }
        
        $order->setCourier(null);
        if (!empty($data['courier'])){
            $courier = $this->entityManager->getRepository(Courier::class)
                    ->find($data['courier']);
            $order->setCourier($courier);
        }
        
        $order->setLegal(null);
        if (!empty($data['legal'])){
            $legal = $this->entityManager->getRepository(Legal::class)
                    ->find($data['legal']);
            $order->setRecipient($legal);
        }

        $order->setRecipient(null);
        if (!empty($data['recipient'])){
            $recipient = $this->entityManager->getRepository(Legal::class)
                    ->find($data['recipient']);
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
        }

        $currentDate = date('Y-m-d H:i:s');        
        $order->setDateCreated($currentDate);
                
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($order);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
        
        return $order;
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
        $upd = [
            'address' =>  (!empty($data['address'])) ? $data['address'] : null,
            'apl_id' =>  (!empty($data['aplId'])) ? $data['aplId'] : null,
            'date_mod' =>  (!empty($data['dateMod'])) ? $data['dateMod'] : null,
            'date_oper' =>  (!empty($data['dateOper'])) ? $data['dateOper'] : null,
            'date_shipment' =>  (!empty($data['dateShipment'])) ? $data['dateShipment'] : null,
            'geo' =>  (!empty($data['geo'])) ? $data['geo'] : null,
            'info' =>  (!empty($data['info'])) ? $data['info'] : null,
            'invoice_info' =>  (!empty($data['invoiceInfo'])) ? $data['invoiceInfo'] : null,
            'mode' =>  (!empty($data['mode'])) ? $data['mode'] : Order::MODE_MAN,
            'shipment_distance' =>  (!empty($data['shipmentDistance'])) ? $data['shipmentDistance'] : null,
            'shipment_rate' =>  (!empty($data['shipmentRate'])) ? $data['shipmentRate'] : null,
            'shipment_add_rate' =>  (!empty($data['shipmentAddRate'])) ? $data['shipmentAddRate'] : null,
            'shipment_total' =>  (!empty($data['shipmentTotal'])) ? $data['shipmentTotal'] : null,
            'status' =>  (!empty($data['status'])) ? $data['mode'] : Order::STATUS_NEW,
            'total' =>  (!empty($data['total'])) ? $data['total'] : null,
            'track_number' =>  (!empty($data['trackNumber'])) ? $data['trackNumber'] : null,
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
        ];

        if (empty($data['company'])){
            $company = $this->entityManager->getRepository(Office::class)
                    ->findDefaultCompany($office, !empty($data['dateOper']) ? $data['dateOper'] : null);
        } else {
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($data['company']);
        }
        $upd['company_id'] = $company->getId();
        
        if (!empty($data['contactCar'])){
            $upd['contact_car_id'] = $data['contactCar'];
        }
        
        if (!empty($data['courier'])){
            $upd['courier_id'] = $data['courier'];
        }
        
        if (!empty($data['legal'])){
            $upd['legal_id'] = $data['legal'];
        }

        if (!empty($data['recipient'])){
            $upd['recipient_id'] = $data['recipient'];
        }

        if (!empty($data['shipping'])){
            $upd['shipping_id'] = $data['shipping'];
        } else {
            $shipping = $this->entityManager->getRepository(Shipping::class)
                    ->findOneBy(['office' => $office->getId(), 'status' => Shipping::STATUS_ACTIVE]);
            $upd['shipping_id'] = $shipping->getId();
        }

        if (!empty($data['skiper'])){
            $upd['skiper_id'] = $data['skiper'];
        }

        if (!empty($data['user'])){
            $upd['user_id'] = $data['user'];
        }
        
        $this->entityManager->getConnection()
                ->insert('orders', $upd);
        $order = $this->entityManager->getRepository(Order::class)
                ->findOneBy([], ['id'=>'DESC'],1,0);
        
        return $order;
    }   

    /**
     * Обновить итог по заказу
     * @param Order $order
     */
    public function updateOrderTotal($order)
    {
        $result = $this->entityManager->getRepository(Bid::class)
                ->getOrderNum($order);
        
        $total = 0;
        if (count($result)){
            $total = $result[0]['total'];
        }
        $order->setTotal($total);


        $this->entityManager->persist($order);
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }
    
    /**
     * Обновить итог по заказу
     * @param Order $order
     */
    public function updOrderTotal($order)
    {
        $result = $this->entityManager->getRepository(Bid::class)
                ->getOrderNum($order);
        
        $total = 0;
        if (count($result)){
            $total = $result[0]['total'];
        }
        $this->entityManager->getConnection()
                ->update('orders', ['total' => $total], ['id' => $order->getId()]);
    }

    /**
     * Обновить заказ
     * @param Order $order
     * @param array $data
     */
    public function updateOrder($order, $data) 
    {
        $order->setAddress(!empty($data['address']) ? $data['address'] : null);
        $order->setAplId(!empty($data['aplId']) ? $data['aplId'] : null);
        $order->setDateMod(!empty($data['dateMod']) ? $data['dateMod'] : null);
        $order->setDateOper(!empty($data['dateOper']) ? $data['dateOper'] : null);
        $order->setDateShipment(!empty($data['dateShipment']) ? $data['dateShipment'] : null);
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
                
        $order->setContactCar(null);
        if (!empty($data['contactCar'])){
            $contactCar = $this->entityManager->getRepository(ContactCar::class)
                    ->find($data['contactCar']);
            $order->setContactCar($contactCar);
        }
        
        $order->setCourier(null);
        if (!empty($data['courier'])){
            $courier = $this->entityManager->getRepository(Courier::class)
                    ->find($data['courier']);
            $order->setCourier($courier);
        }
        
        $order->setLegal(null);
        if (!empty($data['legal'])){
            $legal = $this->entityManager->getRepository(Legal::class)
                    ->find($data['legal']);
            $order->setLegal($legal);
        }

        $order->setRecipient(null);
        if (!empty($data['recipient'])){
            $recipient = $this->entityManager->getRepository(Legal::class)
                    ->find($data['recipient']);
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
        }

        $this->entityManager->persist($order);
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
        
        return;
    }    
    
    /**
     * Обновить заказ
     * @param Order $order
     * @param array $data
     */
    public function updOrder($order, $data) 
    {
        $upd = [
            'address' =>  (!empty($data['address'])) ? $data['address'] : null,
            'apl_id' =>  (!empty($data['aplId'])) ? $data['aplId'] : null,
            'date_mod' =>  (!empty($data['dateMod'])) ? $data['dateMod'] : null,
            'date_oper' =>  (!empty($data['dateOper'])) ? $data['dateOper'] : null,
            'date_shipment' =>  (!empty($data['dateShipment'])) ? $data['dateShipment'] : null,
            'geo' =>  (!empty($data['geo'])) ? $data['geo'] : null,
            'info' =>  (!empty($data['info'])) ? $data['info'] : null,
            'invoice_info' =>  (!empty($data['invoiceInfo'])) ? $data['invoiceInfo'] : null,
            'mode' =>  (!empty($data['mode'])) ? $data['mode'] : Order::MODE_MAN,
            'shipment_distance' =>  (!empty($data['shipmentDistance'])) ? $data['shipmentDistance'] : null,
            'shipment_rate' =>  (!empty($data['shipmentRate'])) ? $data['shipmentRate'] : null,
            'shipment_add_rate' =>  (!empty($data['shipmentAddRate'])) ? $data['shipmentAddRate'] : null,
            'shipment_total' =>  (!empty($data['shipmentTotal'])) ? $data['shipmentTotal'] : null,
            'status' =>  (!empty($data['status'])) ? $data['mode'] : Order::STATUS_NEW,
            'total' =>  (!empty($data['total'])) ? $data['total'] : null,
            'track_number' =>  (!empty($data['trackNumber'])) ? $data['trackNumber'] : null,
            'contact_car_id' => null,
            'courier_id' => null,
            'legal_id' => null,
            'recipient_id' => null,
            'shipping_id' => null,
            'skiper_id' => null,
            'user_id' => null,
        ];
                
        if (!empty($data['contactCar'])){
            $upd['contact_car_id'] = $data['contactCar'];
        }
        
        if (!empty($data['courier'])){
            $upd['courier_id'] = $data['courier'];
        }
        
        if (!empty($data['legal'])){
            $upd['legal_id'] = $data['legal'];
        }

        if (!empty($data['recipient'])){
            $upd['recipient_id'] = $data['recipient'];
        }

        if (!empty($data['shipping'])){
            $upd['shipping_id'] = $data['shipping'];
        } else {
            $shipping = $this->entityManager->getRepository(Shipping::class)
                    ->findOneBy(['office' => $office->getId(), 'status' => Shipping::STATUS_ACTIVE]);
            $upd['shipping_id'] = $shipping->getId();
        }

        if (!empty($data['skiper'])){
            $upd['skiper_id'] = $data['skiper'];
        }

        if (!empty($data['user'])){
            $upd['user_id'] = $data['user'];
        }
        
        $this->entityManager->getConnection()
                ->update('orders', $upd, ['id' => $order->getId()]);

        return $order;
    }    
    
    /**
     * Удалить строки заказа
     * @param Order $order
     */
    public function removeOrderBids($order)
    {
        $bids = $this->entityManager->getRepository(Order::class)
                    ->findBidOrder($order)->getResult();
        
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
    
    public function removeOrder($order) 
    {   
        $this->removeOrderBids($order);
        $this->entityManager->remove($order);
        $this->entityManager->flush();
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
    
    /*
     * @var Application\Entity\Clent $client
     * @var Application\Entity\Cart $carts
     * 
     */
    public function checkoutClient($client)
    {
        
        $carts = $this->entityManager->getRepository(Cart::class)
                    ->findClientCart($client)->getResult();
        
        $order = null;
        if (count($carts)){         
            $orderData = ['client' => $client];
            $order = $this->addNewOrder($orderData);

            foreach ($carts as $cart){
                $bidData = [
                    'num' => $cart->getNum(),
                    'price' => $cart->getPrice(),
                    'good' => $cart->getGood(),
                ];

                $this->addNewBid($order, $bidData, false);

                $this->entityManager->remove($cart);
            }

            $this->entityManager->flush();
            
            $this->updateOrderTotal($order);
        }
        
        return $order;
    }
    
}
