<?php
/**
 * This file is part of the ApiMarketPlace.
 *
 */

namespace ApiMarketPlace\Service;

use ApiMarketPlace\Exception\ApiMarketPlaceException;
use ApiMarketPlace\Entity\Marketplace;
use ApiMarketPlace\Entity\MarketplaceUpdate;

class MarketplaceService
{
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Добавить запись о торговой площадки
     * @param array $data
     * @return Marketplace
     */
    public function add($data)
    {
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
        $marketplace->setApiToken($data['apiToken']);
        $marketplace->setComment($data['comment']);
        $marketplace->setLogin($data['login']);
        $marketplace->setMerchantId($data['merchantId']);
        $marketplace->setName($data['name']);
        $marketplace->setPassword($data['password']);
        $marketplace->setRemoteAddr($data['remoteAddr']);
        $marketplace->setSite($data['site']);
        $marketplace->setStatus($data['status']);
        
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
        $updates = $this->entityManager->getRepository(MarketplaceUpdate::class)
                ->count(['marketplace' => $marketplace->getId()]);
        if (!$updates){
            $this->entityManager->remove($marketplace);
            $this->entityManager->flush();
        }
        
        return;
    }
    
}
