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
use Company\Entity\Office;
use Application\Entity\SupplierOrder;
use Application\Entity\SupplySetting;
use Stock\Entity\Retail;
use Application\Entity\Client;
use Stock\Entity\Movement;

/**
 * Description of OrderRepository
 *
 * @author Daddy
 */
class OrderRepository extends EntityRepository{

    const MAX_ORDER_SEARCH_RESULT = 50; // максимальный для поиска
    
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
     * Заказы клиента
     * 
     * @param Client $client
     * @param array $params
     * 
     * 
     */
    public function findClientOrder($client, $params=null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('o')
            ->distinct()    
            ->from(Order::class, 'o')
            ->join('o.contact', 'c')    
            ->where('c.client = :client')    
            ->orderBy('o.id', 'desc')
            ->setParameter('client', $client->getId())    
                ;
        
        if (is_array($params)){
            if (!empty($params['orderStatus'])){
                if (is_numeric($params['orderStatus'])){
                    $queryBuilder->andWhere('o.status = :status')
                            ->setParameter('status', $params['orderStatus'])
                            ;
                }
            }
        }

        return $queryBuilder->getQuery();
    }       
    

    /**
     * @param Order $order
     */
    public function getOrderNum($order)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('SUM(b.num) as num, SUM(b.num*b.price) as total')
            ->from(Bid::class, 'b')
            ->where('b.order = ?1')    
            ->groupBy('b.order')
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
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }        
      
    /**
     * 
     * @param string $searchStr
     * @return array
     */
    private function searchOe($searchStr) 
    {
        $result = [];
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $search = trim($searchStr);
        if (strlen($search) > 2){
            $alnumFilter = new ArticleCode();
            $alnum = $alnumFilter->filter($search);
            if ($alnum){
                $queryBuilder->select('identity(b.order) as orderId')
                    ->distinct()    
                    ->from(Bid::class, 'b')
                    ;
            
                $queryBuilder
                    ->join('b.good', 'g')
//                    ->leftJoin('g.oems', 'oe')
                        ;
                
                $orX = $queryBuilder->expr()->orX();
                $orX->add($queryBuilder->expr()->like('g.code', ':alnum'));
//                $queryBuilder->setParameter('alnum', '%' . $alnum . '%');
                $queryBuilder->setParameter('alnum', $alnum);
                
                $queryBuilder->andWhere($orX);
                
                $result = $queryBuilder->getQuery()->getResult();
            }    
        }    
        return $result;        
    }
    
    /**
     * 
     * @param string $searchStr
     */
    private function searchContacts($searchStr)
    {
        $result = [];
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $search = trim($searchStr);
        if (strlen($search) > 2){
            
            $queryBuilder->select('c.id')
                    ->distinct()
                    ->from(Contact::class, 'c')
                    ;
            
            $orX = $queryBuilder->expr()->orX();
            $queryBuilder->leftJoin('c.emails', 'e');

            $orX->add($queryBuilder->expr()->like('e.name', ':search'));
            $queryBuilder->setParameter('search', '%' . $search . '%');

            $digitsFilter = new Digits();
            $digits = $digitsFilter->filter($search);

            $alnumFilter = new Alnum();
            $alnum = $alnumFilter->filter($search);

            if ($digits || $alnum){
                if ($digits && strlen($digits) > 7){

                    $queryBuilder->leftJoin('c.phones', 'p');
                    $orX->add($queryBuilder->expr()->like('p.name', ':digits'));
//                    $queryBuilder->setParameter('digits', '%' . $digits . '%');
                    $queryBuilder->setParameter('digits', $digits);


                }    
                if ($alnum && strlen($alnum) > 5){
                    $queryBuilder->leftJoin('c.contactCars', 'cc');
                    $orX->add($queryBuilder->expr()->like('cc.vin', ':alnum'));
                    $orX->add($queryBuilder->expr()->like('cc.vin2', ':alnum'));
//                    $queryBuilder->setParameter('alnum', '%' . $alnum . '%');
                    $queryBuilder->setParameter('alnum', $alnum);
                }                
            }    
            
            if ($orX->count()){
                $queryBuilder->andWhere($orX);
                $result = $queryBuilder->getQuery()->getResult();
            }    
        }    
        return $result;
    }
    
    /**
     * 
     * @param QueryBuilder $queryBuilder
     * @param array $params
     */
    private function findAllOrderParams($queryBuilder, $params)
    {
        
        if (!empty($params['officeId'])){
            if (is_numeric($params['officeId'])){
                $queryBuilder->andWhere('o.office = ?1')
                    ->setParameter('1', $params['officeId'])
                        ;
            }    
        }            
        if (!empty($params['userId'])){
            if (is_numeric($params['userId'])){
                $orX = $queryBuilder->expr()->orX();
                if (!empty($params['status'])){
                    if ($params['status'] == Order::STATUS_NEW){
                        $orX->add($queryBuilder->expr()->isNull('o.user'));
                    }
                }
                $orX->add($queryBuilder->expr()->eq('o.user', $params['userId']));
                $queryBuilder->andWhere($orX);
            }    
        }            
        if (!empty($params['clientId'])){
            if (is_numeric($params['clientId'])){
                $queryBuilder->andWhere('c.client = :client')
                    ->setParameter('client', $params['clientId'])
                        ;
            }    
        }            
        if (!empty($params['status'])){
            if (is_numeric($params['status'])){
                $queryBuilder->andWhere('o.status = ?3')
                    ->setParameter('3', $params['status'])
                        ;
            }    
        }            
        if (!empty($params['shipping'])){
            if (is_numeric($params['shipping'])){
                $queryBuilder->andWhere('o.shipping = :shipping')
                    ->setParameter('shipping', $params['shipping'])
                        ;
            }    
        }            
        if (isset($params['sort'])){
            $queryBuilder->addOrderBy('o.'.$params['sort'], $params['order']);
        }        

        if (!empty($params['orderId'])){
            if (is_numeric($params['orderId'])){
                $queryBuilder->andWhere('o.id = :orderId')
                        ->setParameter('orderId', $params['orderId']);
            }    
        }
        if (!empty($params['startDate'])){
            $queryBuilder->andWhere('o.dateOper >= :startDate')
                    ->setParameter('startDate', $params['startDate']);
        }
        if (!empty($params['endDate'])){
            $queryBuilder->andWhere('o.dateOper <= :endDate')
                    ->setParameter('endDate', $params['endDate']);
        }

        if (!empty($params['search'])){
            $orX = $queryBuilder->expr()->orX();
            $orX->add($queryBuilder->expr()->eq('o.id', 0));
            $orX->add($queryBuilder->expr()->eq('o.trackNumber', trim($params['search'])));

            $contacts = $this->searchContacts($params['search']);                
            foreach ($contacts as $contact){
                $orX->add($queryBuilder->expr()->eq('c.id', $contact['id']));                    
            }
            $orders = $this->searchOe($params['search']);                
            foreach ($orders as $order){
                $orX->add($queryBuilder->expr()->eq('o.id', $order['orderId']));                    
            }
            $queryBuilder->andWhere($orX);
            $queryBuilder->setMaxResults(self::MAX_ORDER_SEARCH_RESULT);
        }
        
        return $queryBuilder;
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

        $queryBuilder->select('o, c, u, off, cc, m, sk, l')
            ->from(Order::class, 'o')
            ->leftJoin('o.contact', 'c')
//            ->leftJoin('o.comments', 'com')
            ->leftJoin('o.contactCar', 'cc')
            ->leftJoin('cc.make', 'm')    
//            ->leftJoin('c.phones', 'p')
//            ->leftJoin('c.emails', 'e')    
            ->leftJoin('o.user', 'u')
            ->leftJoin('o.skiper', 'sk')
            ->leftJoin('o.office', 'off')
            ->leftJoin('o.legal', 'l')
//            ->orderBy('o.dateCreated', 'DESC')                 
//            ->addOrderBy('o.dateOper', 'DESC') 
                ;
        
        if (is_array($params)){
            if (!empty($params['officeId'])){
                if (is_numeric($params['officeId'])){
                    $queryBuilder->andWhere('o.office = ?1')
                        ->setParameter('1', $params['officeId'])
                            ;
                }    
            }            
            if (!empty($params['userId'])){
                if (is_numeric($params['userId'])){
                    $orX = $queryBuilder->expr()->orX();
                    if (!empty($params['status'])){
                        if ($params['status'] == Order::STATUS_NEW){
                            $orX->add($queryBuilder->expr()->isNull('o.user'));
                        }
                    }
                    $orX->add($queryBuilder->expr()->eq('o.user', $params['userId']));
                    $queryBuilder->andWhere($orX);
                }    
            }            
            if (!empty($params['clientId'])){
                if (is_numeric($params['clientId'])){
                    $queryBuilder->andWhere('c.client = :client')
                        ->setParameter('client', $params['clientId'])
                            ;
                }    
            }            
            if (!empty($params['status'])){
                if (is_numeric($params['status'])){
                    $queryBuilder->andWhere('o.status = ?3')
                        ->setParameter('3', $params['status'])
                            ;
                }    
            }            
            if (!empty($params['shipping'])){
                if (is_numeric($params['shipping'])){
                    $queryBuilder->andWhere('o.shipping = :shipping')
                        ->setParameter('shipping', $params['shipping'])
                            ;
                }    
            }            
            if (isset($params['sort'])){
                $queryBuilder->addOrderBy('o.'.$params['sort'], $params['order']);
            }        
            
            if (!empty($params['orderId'])){
                if (is_numeric($params['orderId'])){
                    $queryBuilder->andWhere('o.id = :orderId')
                            ->setParameter('orderId', $params['orderId']);
                }    
            }
            if (!empty($params['startDate'])){
                $queryBuilder->andWhere('o.dateOper >= :startDate')
                        ->setParameter('startDate', $params['startDate']);
            }
            if (!empty($params['endDate'])){
                $queryBuilder->andWhere('o.dateOper <= :endDate')
                        ->setParameter('endDate', $params['endDate']);
            }
            
            if (!empty($params['search'])){
                $orX = $queryBuilder->expr()->orX();
                $orX->add($queryBuilder->expr()->eq('o.id', 0));
                $orX->add($queryBuilder->expr()->eq('o.trackNumber', trim($params['search'])));
                
                $contacts = $this->searchContacts($params['search']);                
                foreach ($contacts as $contact){
                    $orX->add($queryBuilder->expr()->eq('c.id', $contact['id']));                    
                }
                $orders = $this->searchOe($params['search']);                
                foreach ($orders as $order){
                    $orX->add($queryBuilder->expr()->eq('o.id', $order['orderId']));                    
                }
                $queryBuilder->andWhere($orX);
                $queryBuilder->setMaxResults(self::MAX_ORDER_SEARCH_RESULT);
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
            if (!empty($params['clientId'])){
                if (is_numeric($params['clientId'])){
                    $queryBuilder->andWhere('c.client = :client')
                        ->setParameter('client', $params['clientId'])
                            ;
                }    
            }            
            if (is_numeric($params['status'])){
                $queryBuilder->andWhere('o.status = ?3')
                    ->setParameter('3', $params['status'])
                        ;
            }            
            if (!empty($params['shipping'])){
                if (is_numeric($params['shipping'])){
                    $queryBuilder->andWhere('o.shipping = :shipping')
                        ->setParameter('shipping', $params['shipping'])
                            ;
                }    
            }            
            if (!empty($params['startDate'])){
                $queryBuilder->andWhere('o.dateOper >= :startDate')
                        ->setParameter('startDate', $params['startDate']);
            }
            if (!empty($params['endDate'])){
                $queryBuilder->andWhere('o.dateOper <= :endDate')
                        ->setParameter('endDate', $params['endDate']);
            }
            if (!empty($params['search'])){
                $orX = $queryBuilder->expr()->orX();
                $orX->add($queryBuilder->expr()->eq('o.id', 0));
                $orX->add($queryBuilder->expr()->eq('o.trackNumber', trim($params['search'])));
                
                $contacts = $this->searchContacts($params['search']);                
                foreach ($contacts as $contact){
                    $orX->add($queryBuilder->expr()->eq('c.id', $contact['id']));                    
                }
                $orders = $this->searchOe($params['search']);                
                foreach ($orders as $order){
                    $orX->add($queryBuilder->expr()->eq('o.id', $order['orderId']));                    
                }
                $queryBuilder->andWhere($orX);
                return self::MAX_ORDER_SEARCH_RESULT;
            }
        }
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['orderCount'];
    }    
    
    /**
     * Запрос на все машины
     * @param array $params
     * @return Query
     * 
     */
    public function queryAllContactCar($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('cc, c, mk, md, car')
            ->from(ContactCar::class, 'cc') 
            ->leftJoin('cc.contact', 'c')    
            ->leftJoin('cc.make', 'mk')    
            ->leftJoin('cc.model', 'md')    
            ->leftJoin('cc.car', 'car')    
            ->addOrderBy('c.id', 'DESC')    
                ;
        
        if (is_array($params)){
            if (!empty($params['clientId'])){
                if (is_numeric($params['clientId'])){
                    $queryBuilder->andWhere('c.client = :client')
                        ->setParameter('client', $params['clientId'])
                            ;
                }    
            }            
        }
        
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
    
    /**
     * Найти товары для перемещения между офисами
     * @param Office $office Откуда перемещать
     * @param Office $office2 Куда перемещать
     * @param date $ptDate
     */
    public function findForPt($office, $office2, $ptDate)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('identity(s.office) as office, so.quantity, identity(so.good) as goodId, o.aplId as orderAplId, s.name as supplierName')
//            ->distinct()    
            ->from(SupplierOrder::class, 'so')
            ->join('so.order', 'o')
            ->where('o.dateOper >= ?1')    
            ->andWhere('o.dateOper <= ?2')    
            ->setParameter('1', date('Y-m-d', strtotime($ptDate)))
            ->setParameter('2', date('Y-m-d 23:59:59', strtotime($ptDate))) 
            ->andWhere('o.office = ?3')
            ->setParameter('3', $office2->getId())
            ->andWhere('o.status = ?4 or o.status = ?5')
            ->setParameter('4', Order::STATUS_CONFIRMED)                
            ->setParameter('5', Order::STATUS_DELIVERY)
//            ->andWhere('so.status != ?6')
//            ->setParameter('6', SupplierOrder::STATUS_NEW)                
            ->join('so.supplier', 's')
            ->andWhere('s.office = ?7')    
            ->setParameter('7', $office->getId())
            ;
        
        return $queryBuilder->getQuery()->getResult();        
    }
    
    /**
     * Найти максиальную дату для перемещения между офисами
     * @param Office $office2 Куда перемещать
     * @param date $ptDate
     */
    public function findMaxDateOper($office2)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('max(o.dateOper) as maxDateOper')
            ->distinct()    
            ->from(Order::class, 'o')
            ->where('o.dateOper >= ?1')    
            ->setParameter('1', date('Y-m-d'))
            ->andWhere('o.office = ?3')
            ->setParameter('3', $office2->getId())
            ->andWhere('o.status = ?4 or o.status = ?5')
            ->setParameter('4', Order::STATUS_CONFIRMED)                
            ->setParameter('5', Order::STATUS_DELIVERY)
            ->setMaxResults(1)    
            ;
        $row = $queryBuilder->getQuery()->getOneOrNullResult();
        return date('Y-m-d', strtotime($row['maxDateOper']));        
    }

    /**
     * Выручка по годам
     * @param array $params
     */
    public function revenueByYears($params)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('YEAR(o.dateOper) as year, sum(o.total) as total')
                ->from(Order::class, 'o')
                ->where('o.status = :status')
                ->setParameter('status', Order::STATUS_SHIPPED)
                ->groupBy('year')
                ;
        if (!empty($params['base'])){
            if ($params['base'] == 'bid'){
                $queryBuilder->select('YEAR(o.dateOper) as year, sum(b.num*b.price) as total')
                        ->from(Order::class, 'o')                
                        ->join('o.bids', 'b');                
            }
            if ($params['base'] == 'retail'){
                $queryBuilder->select('YEAR(o.dateOper) as year, sum(o.amount) as total')
                        ->from(Retail::class, 'o')
                        ->where('o.status = :status')
                        ->setParameter('status', Retail::STATUS_ACTIVE)
                        ;
            }
            if ($params['base'] == 'movement'){
                $queryBuilder->select('YEAR(o.dateOper) as year, sum(o.amount) as total')
                        ->from(Retail::class, 'o')
                        ->where('o.status = :status')
                        ->setParameter('status', Movement::STATUS_ACTIVE)
                        ;
            }
        }
        if (!empty($params['office'])){
            if (is_numeric($params['office'])){
                $queryBuilder->andWhere('o.office = :office')
                        ->setParameter('office', $params['office']);
            }    
        }
        if (!empty($params['year'])){
            $queryBuilder->select('MONTH(o.dateOper) as month, sum(o.total) as total')
                    ->andWhere('YEAR(o.dateOper) = :year')
                    ->setParameter('year', $params['year'])
                    ->groupBy('month');
        }
        if (!empty($params['month'])){
            $queryBuilder->select('DAY(o.dateOper) as day, sum(o.total) as total')
                    ->andWhere('MONTH(o.dateOper) = :month')
                    ->setParameter('month', $params['month']);
        }
        if (!empty($params['base'])){
            
        }
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }

    /**
     * Найти записи для отправки в АПЛ
     */
    public function findForUpdateApl()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('o')
            ->from(Order::class, 'o')
            ->where('o.statusEx = ?1')
            ->setParameter('1', Order::STATUS_EX_NEW)  
            ->setMaxResults(1)
               ;
        
            //восстановление утеряных данных
//            if (date('N') < 7 && date('G') > 7 && date('G') < 21){    
//                $queryBuilder->andWhere('o.status = ?2')
//                    ->setParameter('2', Order::STATUS_CANCELED)  
//                    ->andWhere('o.aplId > 0')
//                    ;
//            }    
        
        $data = $queryBuilder->getQuery()->getResult();
        foreach ($data as $order){
//            var_dump($order->getId()); 
            $flag = true;
            $bids = $entityManager->getRepository(Bid::class)
                    ->findBy(['order' => $order->getId()]);
            foreach ($bids as $bid){
               if (empty($bid->getGood()->getAplId())){
                   $flag = false;
                   break;
               }  
            }
            if ($flag){
                return $order;
            }    
        }
        
        return;                
        
    }                    

    /**
     * Найти заказы для отмены
     */
    public function findForCancel()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('o')
            ->from(Order::class, 'o')
            ->where('o.dateCreated < ?1')
            ->setParameter('1', date('Y-m-d H:i:s', strtotime('-7 days')))    
                ;
        
        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq('o.status', Order::STATUS_NEW));
        $orX->add($queryBuilder->expr()->eq('o.status', Order::STATUS_PROCESSED));
        
        $queryBuilder->andWhere($orX);
        
        return $queryBuilder->getQuery()->getResult();
        
    }        
    
    /**
     * Получить сводные продажи закупки
     * @param array $params
     * @return array
     */
    public function findRetails($params)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq('r.docType', Movement::DOC_ORDER));
        $orX->add($queryBuilder->expr()->eq('r.docType', Movement::DOC_VT));
        
        $queryBuilder->select('identity(r.user) as userId, r.amount as amount')
            ->addSelect('o.id as orderId, o.aplId as orderAplId')
            ->addSelect('vto.id as vtOrderId, vto.aplId as vtOrderAplId')
            ->addSelect('r.docId as docId')
            ->addSelect('r.docType as docType')
            ->addSelect('r.dateOper as dateOper')
            ->addSelect('o.shipmentTotal as shipmentTotal')
            ->addSelect('o.statusAccount as orderStatusAccount')
            ->addSelect('vt.statusAccount as vtStatusAccount')
            ->addSelect('(select -sum(mr.amount) from Stock\Entity\Movement mr where mr.docId = r.docId and mr.docType = r.docType) as revenue')
            ->addSelect('(select -sum(mp.baseAmount) from Stock\Entity\Movement mp where mp.docId = r.docId and mp.docType = r.docType) as purchase')
            ->from(Retail::class, 'r')
            ->leftJoin('r.order', 'o', 'WITH', 'r.docType = '.Movement::DOC_ORDER)
            ->leftJoin('r.vt', 'vt', 'r.docType = '.Movement::DOC_VT)
            ->leftJoin('vt.order', 'vto')
            ->andWhere('r.status = :status')
            ->setParameter('status', Retail::STATUS_ACTIVE)    
            ->andWhere($orX)    
                ;
                
            if (!empty($params['user'])){
                if (is_numeric($params['user'])){
                    $queryBuilder->andWhere('r.user = :user')
                    ->setParameter('user', $params['user'])
                            ;
                }    
            }            
            if (!empty($params['company'])){
                if (is_numeric($params['company'])){
                    $queryBuilder->andWhere('r.company = :company')
                    ->setParameter('company', $params['company'])
                            ;
                }    
            }            
            if (!empty($params['userId'])){
                if (is_numeric($params['userId'])){
                    $queryBuilder->andWhere('r.user = :user')
                    ->setParameter('user', $params['userId'])
                            ;
                }    
            }            
            if (isset($params['sort'])){
                $queryBuilder->addOrderBy('r.'.$params['sort'], $params['order']);
            }        
            
            if (!empty($params['startDate'])){
                $queryBuilder->andWhere('r.dateOper >= :startDate')
                        ->setParameter('startDate', $params['startDate']);
            }
            if (!empty($params['endDate'])){
                $queryBuilder->andWhere('r.dateOper <= :endDate')
                        ->setParameter('endDate', $params['endDate']);
            }
                
            $queryBuilder->addOrderBy('r.docStamp', 'DESC');
            
//        var_dump($queryBuilder->getQuery()->getSQL());    
        return $queryBuilder->getQuery();       
    }   
    
    /**
     * Получить итоги сводные продажи
     * @param array $params
     * @return array
     */
    public function findRetailsTotal($params)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq('r.docType', Movement::DOC_ORDER));
        $orX->add($queryBuilder->expr()->eq('r.docType', Movement::DOC_VT));
        
        $queryBuilder->select('sum(r.amount) as amount')
            ->addSelect('sum(o.shipmentTotal) as shipmentTotal')
            ->from(Retail::class, 'r')
            ->leftJoin('r.order', 'o', 'WITH', 'r.docType = '.Movement::DOC_ORDER)
            ->andWhere('r.status = :status')
            ->setParameter('status', Retail::STATUS_ACTIVE)    
            ->andWhere($orX)    
            ->setMaxResults(1)    
                ;
                
            if (!empty($params['user'])){
                if (is_numeric($params['user'])){
                    $queryBuilder->andWhere('r.user = :user')
                    ->setParameter('user', $params['user'])
                            ;
                }    
            }            
            if (!empty($params['company'])){
                if (is_numeric($params['company'])){
                    $queryBuilder->andWhere('r.company = :company')
                    ->setParameter('company', $params['company'])
                            ;
                }    
            }            
            if (!empty($params['userId'])){
                if (is_numeric($params['userId'])){
                    $queryBuilder->andWhere('r.user = :user')
                    ->setParameter('user', $params['userId'])
                            ;
                }    
            }            
            if (!empty($params['startDate'])){
                $queryBuilder->andWhere('r.dateOper >= :startDate')
                        ->setParameter('startDate', $params['startDate']);
            }
            if (!empty($params['endDate'])){
                $queryBuilder->andWhere('r.dateOper <= :endDate')
                        ->setParameter('endDate', $params['endDate']);
            }
                
//        var_dump($queryBuilder->getQuery()->getSQL());    
        return $queryBuilder->getQuery()->getOneOrNullResult();       
    } 
    
    /**
     * Получить итоги сводные закупки
     * @param array $params
     * @return array
     */
    public function findMovementsTotal($params)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_ORDER));
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_VT));
        
        $queryBuilder->select('-sum(m.amount) as revenue, -sum(m.baseAmount) as purchase')
            ->from(Movement::class, 'm')
            ->andWhere('m.status = :status')
            ->setParameter('status', Movement::STATUS_ACTIVE)    
            ->andWhere($orX)  
            ->setMaxResults(1)    
                ;
                
            if (!empty($params['user'])){
                if (is_numeric($params['user'])){
                    $queryBuilder->andWhere('m.user = :user')
                    ->setParameter('user', $params['user'])
                            ;
                }    
            }            
            if (!empty($params['company'])){
                if (is_numeric($params['company'])){
                    $queryBuilder->andWhere('m.company = :company')
                    ->setParameter('company', $params['company'])
                            ;
                }    
            }            
            if (!empty($params['userId'])){
                if (is_numeric($params['userId'])){
                    $queryBuilder->andWhere('m.user = :user')
                    ->setParameter('user', $params['userId'])
                            ;
                }    
            }            
            if (!empty($params['startDate'])){
                $queryBuilder->andWhere('m.dateOper >= :startDate')
                        ->setParameter('startDate', $params['startDate']);
            }
            if (!empty($params['endDate'])){
                $queryBuilder->andWhere('m.dateOper <= :endDate')
                        ->setParameter('endDate', $params['endDate']);
            }
                
//        var_dump($queryBuilder->getQuery()->getSQL());    
        return $queryBuilder->getQuery()->getOneOrNullResult();       
    }        
}
