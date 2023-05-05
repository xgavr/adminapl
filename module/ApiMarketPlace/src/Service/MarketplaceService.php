<?php
/**
 * This file is part of the ApiMarketPlace.
 *
 */

namespace ApiMarketPlace\Service;

use ApiMarketPlace\Exception\ApiMarketPlaceException;
use ApiMarketPlace\Entity\Marketplace;
use ApiMarketPlace\Entity\MarketplaceOrder;
use ApiMarketPlace\Entity\MarketplaceUpdate;
use Application\Entity\Contact;
use Company\Entity\Contract;

class MarketplaceService
{
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Order manager.
     * @var \Application\Service\OrderManager
     */
    private $orderManager;

    public function __construct($entityManager, $orderManager)
    {
        $this->entityManager = $entityManager;
        $this->orderManager = $orderManager;
    }
    
    /**
     * Добавить запись о торговой площадки
     * @param array $data
     * @return Marketplace
     */
    public function add($data)
    {
        $contact = $contract = null;
        if (isset($data['contact'])){
            $contact = $this->entityManager->getRepository(Contact::class)
                    ->find($data['contact']);
        }

        if (isset($data['contract'])){
            $contract = $this->entityManager->getRepository(Contract::class)
                    ->find($data['contract']);
        }

        $marketplace = new Marketplace();
        $marketplace->setApiToken($data['apiToken']);
        $marketplace->setComment($data['comment']);
        $marketplace->setDateCreated(date('Y-m-d H:i:s'));
        $marketplace->setLogin($data['login']);
        $marketplace->setMerchantId($data['merchantId']);
        $marketplace->setName($data['name']);
        $marketplace->setPassword($data['password']);
        $marketplace->setRemoteAddr($data['remoteAddr']);
        $marketplace->setSite($data['site']);
        $marketplace->setStatus($data['status']);
        $marketplace->setContact($contact);
        $marketplace->setContract($contract);
        $marketplace->setMarketType($data['maerketType']);
        
        $this->entityManager->persist($marketplace);
        $this->entityManager->flush();
        
        return $marketplace;
    }
    
    /**
     * Изменить запись о торговой площадки
     * 
     * @param Marketplace $marketplace
     * @param array $data
     * @return Marketplace
     */
    public function update($marketplace, $data)
    {
        $contact = $contract = null;
        if (isset($data['contact'])){
            $contact = $this->entityManager->getRepository(Contact::class)
                    ->find($data['contact']);
        }

        if (isset($data['contract'])){
            $contract = $this->entityManager->getRepository(Contract::class)
                    ->find($data['contract']);
        }
        
        $marketplace->setApiToken($data['apiToken']);
        $marketplace->setComment($data['comment']);
        $marketplace->setLogin($data['login']);
        $marketplace->setMerchantId($data['merchantId']);
        $marketplace->setName($data['name']);
        $marketplace->setPassword($data['password']);
        $marketplace->setRemoteAddr($data['remoteAddr']);
        $marketplace->setSite($data['site']);
        $marketplace->setStatus($data['status']);
        $marketplace->setContact($contact);
        $marketplace->setContract($contract);
        $marketplace->setMarketType($data['marketType']);
        
        $this->entityManager->persist($marketplace);
        $this->entityManager->flush();
        
        return $marketplace;
    }
    
    /**
     * Удалить запись о торговой площадки
     * 
     * @param Marketplace $marketplace
     */
    public function remove($marketplace)
    {
        $orders = $this->entityManager->getRepository(MarketplaceOrder::class)
                ->count(['marketplace' => $marketplace->getId()]);
        if (!$orders){
            $this->entityManager->remove($marketplace);
            $this->entityManager->flush();
        }
        
        return;
    }
    
    /**
     * Добавить запись заказа торговой площадки
     * @param Marketplace $marketplace
     * @param array $data
     * @return MarketplaceOrder
     */
    public function addMarketplaceOrder($marketplace, $data)
    {
        $marketplaceOrder = new MarketplaceOrder();
        $marketplaceOrder->setDateCreated(date('Y-m-d H:i:s'));
        $marketplaceOrder->setMarketplace($marketplace);
        $marketplaceOrder->setOrder((empty($data['order'])) ? null:$data['order']);
        $marketplaceOrder->setOrderId((empty($data['orderId'])) ? null:$data['orderId']);
        $marketplaceOrder->setOrderNumber((empty($data['orderNumber'])) ? null:$data['orderNumber']);
        $marketplaceOrder->setPostingNumber((empty($data['postingNumber'])) ? null:$data['postingNumber']);
        $marketplaceOrder->setStatus((empty($data['status'])) ? MarketplaceOrder::STATUS_ACTIVE:$data['status']);
        
        $this->entityManager->persist($marketplaceOrder);
        $this->entityManager->flush();
        
        if (isset($data['order'])){
            $this->orderManager->updateDependInfo($data['order'], true);
        }    
        
        return $marketplaceOrder;
    }

    /**
     * Добавить запись заказа торговой площадки
     * @param MarketplaceOrder $marketplaceOrder
     * @param array $data
     * @return MarketplaceOrder
     */
    public function updateMarketplaceOrder($marketplaceOrder, $data)
    {
        $marketplaceOrder->setOrder((empty($data['order'])) ? null:$data['order']);
        $marketplaceOrder->setOrderId((empty($data['orderId'])) ? null:$data['orderId']);
        $marketplaceOrder->setOrderNumber((empty($data['orderNumber'])) ? null:$data['orderNumber']);
        $marketplaceOrder->setPostingNumber((empty($data['postingNumber'])) ? null:$data['postingNumber']);
        $marketplaceOrder->setStatus((empty($data['status'])) ? MarketplaceOrder::STATUS_ACTIVE:$data['status']);
        
        $this->entityManager->persist($marketplaceOrder);
        $this->entityManager->flush();
        $this->entityManager->refresh($marketplaceOrder);
        
        if (isset($data['order'])){
            $this->orderManager->updateDependInfo($data['order'], true);
        }    
        
        return $marketplaceOrder;
    }
    
    /**
     * Добавить или обновить запись заказа торговой площадки
     * @param array $data
     * @return array
     */    
    public function addOrUpdateMarketplaceOrder($data)
    {
        $message = [];
        $marketplace = null;
        if (!empty($data['marketplace'])){
            $marketplace = $this->entityManager->getRepository(Marketplace::class)
                    ->find($data['marketplace']);
        }
        if (!empty($data['order'])){
            $marketplaceOrders = $this->entityManager->getRepository(MarketplaceOrder::class)
                    ->findBy(['order' => $data['order']->getId()]);
            foreach ($marketplaceOrders as $mpOrder){
                if ($mpOrder->getMarketplace()->getId() != $marketplace->getId()){
                    $message[] = 'Уже имеются заказы от '.$mpOrder->getMarketplace()->getName();
                }
            }
        }
        if (!empty($data['postingNumber'])){
            $marketplaceOrder = $this->entityManager->getRepository(MarketplaceOrder::class)
                    ->findOneBy(['marketplace' => $marketplace->getId(), 'postingNumber' => $data['postingNumber']]);   
            if ($marketplaceOrder){
                $message[] = 'Уже имеются заказы c отправлением '.$data['postingNumber'];                
            }
        }
        
        if (!count($message)){
            if ($marketplace){
                $this->addMarketplaceOrder($marketplace, $data);
            }
        }
        
        return [
            'message' => $message,
        ];
    }

    /**
     * Удалить запись заказа торговой площадки
     * 
     * @param MarketplaceOrder $marketplaceOrder
     */
    public function removeMarketplaceOrder($marketplaceOrder)
    {
        $order = $marketplaceOrder->getOrder();
        $updates = $this->entityManager->getRepository(MarketplaceUpdate::class)
                ->count(['marketplaceOrder' => $marketplaceOrder->getId()]);
        if (!$updates){
            $this->entityManager->remove($marketplaceOrder);
            $this->entityManager->flush();
            if ($order){
                $this->orderManager->updateDependInfo($order, true);
            }    
        }
        
        return;
    }
}
