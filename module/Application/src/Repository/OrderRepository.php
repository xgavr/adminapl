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
use Laminas\Filter\Digits;
use Laminas\I18n\Filter\Alnum;
use Application\Filter\ArticleCode;

/**
 * Description of OrderRepository
 *
 * @author Daddy
 */
class OrderRepository extends EntityRepository{

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

        $queryBuilder->select('b, g, p, tg')
            ->from(Bid::class, 'b')
            ->join('b.good', 'g')    
            ->join('g.producer', 'p')    
            ->leftJoin('g.tokenGroup', 'tg')    
            ->where('b.order = ?1')    
            ->orderBy('b.rowNo')
            ->setParameter('1', $order->getId())    
                ;

        return $queryBuilder->getQuery();
    }        
      
    /**
     * Запрос по заказам
     * 
     * @param array $params
     * @return query
     */
    public function findAllOrder($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('o, c, u')
            ->from(Order::class, 'o')
            ->leftJoin('o.contact', 'c')
            ->leftJoin('o.user', 'u')
//            ->orderBy('o.dateCreated', 'DESC')                 
//            ->addOrderBy('o.dateOper', 'DESC')                 
                ;
        
        if (is_array($params)){
            if (is_numeric($params['officeId'])){
                $queryBuilder->andWhere('o.office = ?1')
                    ->setParameter('1', $params['officeId'])
                        ;
            }            
            if (is_numeric($params['userId'])){
                $queryBuilder->andWhere('o.user = ?2')
                    ->setParameter('2', $params['userId'])
                        ;
            }            
            if (is_numeric($params['status'])){
                $queryBuilder->andWhere('o.status = ?3')
                    ->setParameter('3', $params['status'])
                        ;
            }            
            if (isset($params['sort'])){
                $queryBuilder->addOrderBy('o.'.$params['sort'], $params['order']);
            }            
            if (isset($params['search'])){
                $search = trim($params['search']);
                if ($search){
                    $queryBuilder->join('c.emails', 'e');

                    $orX = $queryBuilder->expr()->orX();
                    $orX->add($queryBuilder->expr()->like('e.name', ':search'));
                    $queryBuilder->setParameter('search', '%' . $search . '%');

                    $digitsFilter = new Digits();
                    $alnumFilter = new Alnum();
                    $digits = $digitsFilter->filter($search);
                    $alnum = $alnumFilter->filter($search);
                    if ($digits){
                        $queryBuilder->join('c.phones', 'p');
                        $orX->add($queryBuilder->expr()->like('p.name', ':digits'));
                        $queryBuilder->setParameter('digits', '%' . $digits . '%');
                    }    
                    if ($alnum){
                        $queryBuilder
                            ->join('o.bids', 'b')
                            ->join('b.good', 'g')
                            ->leftJoin('g.oems', 'oe')
                            ->leftJoin('o.contactCar', 'cc')
                                ;

                        $orX->add($queryBuilder->expr()->like('oe.oe', ':alnum'));
                        $orX->add($queryBuilder->expr()->like('cc.vin', ':alnum'));
                        $orX->add($queryBuilder->expr()->like('cc.vin2', ':alnum'));
                        $queryBuilder->setParameter('alnum', '%' . $alnum . '%');
                    }    
                    $queryBuilder->andWhere($orX);
                }    
            }
        }
//var_dump($queryBuilder->getParameters('alnum')); exit;
        return $queryBuilder->getQuery();
    }      
    
    /**
     * Запрос по количеству order
     * 
     * @param array $params
     * @return query
     */
    public function findAllOrderTotal($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(o.id) as orderCount')
            ->from(Order::class, 'o')
            ->leftJoin('o.contact', 'c')
            ->leftJoin('o.user', 'u')
                ;
        
        if (is_array($params)){
            if (is_numeric($params['officeId'])){
                $queryBuilder->andWhere('o.office = ?1')
                    ->setParameter('1', $params['officeId'])
                        ;
            }            
            if (is_numeric($params['userId'])){
                $queryBuilder->andWhere('o.user = ?2')
                    ->setParameter('2', $params['userId'])
                        ;
            }            
            if (is_numeric($params['status'])){
                $queryBuilder->andWhere('o.status = ?3')
                    ->setParameter('3', $params['status'])
                        ;
            }            
            if (isset($params['search'])){
                $search = trim($params['search']);
                if ($search){
                    $queryBuilder->join('c.emails', 'e');

                    $orX = $queryBuilder->expr()->orX();
                    $orX->add($queryBuilder->expr()->like('e.name', ':search'));
                    $queryBuilder->setParameter('search', '%' . $search . '%');

                    $digitsFilter = new Digits();
                    $alnumFilter = new Alnum();
                    $digits = $digitsFilter->filter($search);
                    $alnum = $alnumFilter->filter($search);
                    if ($digits){
                        $queryBuilder->join('c.phones', 'p');
                        $orX->add($queryBuilder->expr()->like('p.name', ':digits'));
                        $queryBuilder->setParameter('digits', '%' . $digits . '%');
                    }    
                    if ($alnum){
                        $queryBuilder
                            ->join('o.bids', 'b')
                            ->join('b.good', 'g')
                            ->leftJoin('g.oems', 'oe')
                            ->leftJoin('o.contactCar', 'cc')
                                ;

                        $orX->add($queryBuilder->expr()->like('oe.oe', ':alnum'));
                        $orX->add($queryBuilder->expr()->like('cc.vin', ':alnum'));
                        $orX->add($queryBuilder->expr()->like('cc.vin2', ':alnum'));
                        $queryBuilder->setParameter('alnum', '%' . $alnum . '%');
                    }    
                    $queryBuilder->andWhere($orX);
                }    
            }
        }
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['orderCount'];
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
