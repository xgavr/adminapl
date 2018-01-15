<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Goods;
use Application\Entity\Cart;
use Application\Entity\Client;
use User\Entity\User;

/**
 * Description of ShopService
 *
 * @author Daddy
 */
class ShopManager
{
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    private $authService;
    
    /*
     * Менеджер сессий
     * @var Zend\Seesion
     */
    private $sessionContainer;        
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $authService, $sessionContainer)
    {
        $this->entityManager = $entityManager;
        $this->authService = $authService;
        $this->sessionContainer = $sessionContainer;
    }
    
    public function searchGoodNameAssistant($search)
    {
        $result = [];    
        if (strlen($search) > 2){
            $names = $this->entityManager->getRepository(Goods::class)
                    ->searchNameForSearchAssistant($search);

            foreach ($names as $name){
                $result[] = $name->getName();
            }
        }
        
        return $result;
    }  
    
    public function addCart($data)
    {
        $cart = new Cart();
        $cart->setNum($data['num']);
        $cart->setPrice($data['price']);

        $currentDate = date('Y-m-d H:i:s');        
        $cart->setDateCreated($currentDate);
        
        $client = $this->entityManager->getRepository(Client::class)
                    ->findOneById($data['client']);        
        $cart->setClient($client);
        
        $good = $this->entityManager->getRepository(Goods::class)
                    ->findOneById($data['good']);        
        $cart->setGood($good);
        
        $currentUser = $this->entityManager->getRepository(User::class)
                ->findOneByEmail($this->authService->getIdentity());
        $cart->setUser($currentUser);  
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($cart);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
        
    }
    
    public function updateCart($cart, $data)
    {
        $cart->setNum($data['num']);
                
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($cart);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
        
    }
    
    public function currentClient()
    {
        if (!isset($this->sessionContainer->currentClient)){
            return null;
        }
        
        $currentClient = $this->entityManager->getRepository(Client::class)
                ->findOneById($this->sessionContainer->currentClient);  
        
        return $currentClient;        
    }
    
    public function currentClientNum()
    {
        if (!isset($this->sessionContainer->currentClient)){
            return 0;
        }
        
        $currentClient = $this->entityManager->getRepository(Client::class)
                ->findOneById($this->sessionContainer->currentClient);  
        
        $result = $this->entityManager->getRepository(Cart::class)
            ->getClientNum($currentClient);
        
        $num = $total = 0;
        if (is_array($result) && count($result)){
            if (array_key_exists('num', $result[0])){
                $num = $result[0]['num'];
            }
            if (array_key_exists('total', $result[0])){
                $total = $result[0]['total'];
            }
        }
        
        return $num;
    }
    
    public function getGoodInCart($goodId)
    {
        if (!isset($this->sessionContainer->currentClient)){
            return 0;
        }
        
        if (!$goodId) return 0;
        
        if (!is_numeric($goodId)) return 0;
        
        $currentClient = $this->entityManager->getRepository(Client::class)
                ->findOneById($this->sessionContainer->currentClient);  
        
        $result = $this->entityManager->getRepository(Cart::class)
                    ->getGoodInClientCart($currentClient, $goodId);
                        
        $num = $total = 0;
        if (is_array($result) && count($result)){
            if (array_key_exists('num', $result[0])){
                $num = $result[0]['num'];
            }
            if (array_key_exists('total', $result[0])){
                $total = $result[0]['total'];
            }
        }
        
        return $num;        
    }

    public function removeCart($cart)
    {
        $this->entityManager->remove($cart);
        
        $this->entityManager->flush();
        
    }
    
    public function checkout($carts)
    {
        foreach ($carts as $cart){
            
        }
    }
}
