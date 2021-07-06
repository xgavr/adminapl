<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Order;
use Application\Entity\Bid;
use Application\Entity\Contact;
use Application\Entity\ContactCar;

/**
 * Description of OrderRepository
 *
 * @author Daddy
 */
class OrderRepository extends EntityRepository{

    public function findAllOrder()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('o')
            ->from(Order::class, 'o')
                ;
        
        return $queryBuilder->getQuery();
    }       
    
    /**
     * Запрос на все заказаы
     * @return Query
     * 
     */
    public function queryAllOrder()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('o')
            ->from(Order::class, 'o')
                ;
        
        return $queryBuilder->getQuery();
    }       
    
    /**
     * @param Apllication\Entity\Client $client
     */
    public function findClientOrder($client)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Order::class, 'c')
            ->where('c.client = ?1')    
            ->orderBy('c.id')
            ->setParameter('1', $client->getId())    
                ;

        return $queryBuilder->getQuery();
    }       
    

    /**
     * @param Order $order
     */
    public function getOrderNum($order)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(Bid::class, 'r')
            ->select('SUM(r.num) as num, SUM(r.num*r.price) as total')
            ->where('r.order = ?1')    
            ->groupBy('r.order')
            ->setParameter('1', $order->getId())
                ;
        return $queryBuilder->getQuery()->getResult();
        
    }
    
    /**
     * @param Order $order
     */
    public function findBidOrder($order)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Bid::class, 'c')
            ->where('c.order = ?1')    
            ->orderBy('c.id')
            ->setParameter('1', $order->getId())    
                ;

        return $queryBuilder->getQuery();
    }        
    
    /**
     * Найти машину клиента
     * @param Contact $contact
     * @param array $data
     * @return ContactCar
     */
    public function findContactCar($contact, $data)
    {
//        var_dump($data); exit;
        $entityManager = $this->getEntityManager();                
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(ContactCar::class, 'c')
            ->where('c.contact = ?1')    
            ->orderBy('c.id', 'DESC')
            ->setParameter('1', $contact->getId())    
                ;
        
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        $rows = $queryBuilder->getQuery()->getResult();
        foreach ($rows as $row){
            if (!empty($data['vin']) && $row->getVin()){
                if ($row->getVin() == $data['vin']){
                    return $row;
                }
                if (!empty($data['vin2'])){
                    if ($row->getVin() == $data['vin2']){
                        return $row;
                    }
                }    
            }                    
            if (!empty($data['vin2']) && $row->getVin2()){
                if ($row->getVin2() == $data['vin2']){
                    return $row;
                }
                if (!empty($data['vin'])){
                    if ($row->getVin2() == $data['vin']){
                        return $row;
                    }
                }    
            }                    
            if (!empty($data['make']) && $row->getMake()){
                if ($row->getMake()->getId() == $data['make']){
                    if (!empty($data['model']) && $row->getModel()){
                        if ($row->getModel()->getId() == $data['model']){
                            if (!empty($data['car']) && $row->getCar()){
                                if ($row->getCar()->getId() == $data['car']){
                                    return $row;
                                }                                
                            } else {
                                return $row;
                            }    
                        }                       
                    } else {
                        return $row;                        
                    }                    
                }
            }                    
        }
        
        return;
    }
}
