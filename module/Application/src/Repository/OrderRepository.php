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

        $queryBuilder->select('b, g, p, tg, gb')
            ->from(Bid::class, 'b')
            ->join('b.good', 'g')    
            ->join('g.producer', 'p')    
            ->leftJoin('g.tokenGroup', 'tg')    
            ->leftJoin('g.goodBalances', 'gb', 'WITH', 'gb.office = :office and gb.company = :company')    
            ->where('b.order = ?1')    
            ->orderBy('b.rowNo')
            ->setParameter('1', $order->getId())    
            ->setParameter('office', $order->getOffice()->getId())    
            ->setParameter('company', $order->getCompany()->getId())    
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
                
//var_dump($queryBuilder->getQuery()->getSQL()); exit;
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

        $queryBuilder->select('o, c, u, off, cc, m, sk, l, client')
            ->from(Order::class, 'o')
            ->join('o.contact', 'c')
            ->leftJoin('c.client', 'client')
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
                $orX->add($queryBuilder->expr()->like('o.trackNumber', "'".trim($params['search'])."'"));
                
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
//var_dump($queryBuilder->getQuery()->getSQL()); exit;
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
//            ->leftJoin('o.user', 'u')
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
                    $queryBuilder->leftJoin('o.contact', 'c')
                        ->andWhere('c.client = :client')
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
                $orX->add($queryBuilder->expr()->like('o.trackNumber', "'".trim($params['search'])."'"));
                
                $contacts = $this->searchContacts($params['search']);                
                foreach ($contacts as $contact){
                    $orX->add($queryBuilder->expr()->eq('o.contact', $contact['id']));                    
                }
                $orders = $this->searchOe($params['search']);                
                foreach ($orders as $order){
                    $orX->add($queryBuilder->expr()->eq('o.id', $order['orderId']));                    
                }
                $queryBuilder->andWhere($orX);
                $queryBuilder->setMaxResults(self::MAX_ORDER_SEARCH_RESULT);
            }
        }
        
//var_dump($queryBuilder->getQuery()->getSQL()); exit;
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
        
        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_ORDER));
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_VT));
        
        $queryBuilder->select('sum(-m.amount) as revenue, sum(-m.baseAmount) as purchase, '
                . 'sum(-m.amount + m.baseAmount) as income, '
                . 'sum(-m.quantity) as quantity, '
                . 'sum(distinct CASE WHEN m.docType = :orderDocType THEN 1 ELSE 0 END) as orderCount, '
                . 'sum(distinct CASE WHEN m.docType = :vtDocType THEN 1 ELSE 0 END) as vtCount')
                ->from(Movement::class, 'm')
                ->where('m.status = :status')
                ->setParameter('orderDocType', Movement::DOC_ORDER)    
                ->setParameter('vtDocType', Movement::DOC_VT)    
                ->setParameter('status', Movement::STATUS_ACTIVE)    
                ->andWhere($orX)
                ->groupBy('period')
                ;
        
        if (!empty($params['office'])){
            if (is_numeric($params['office'])){
                $queryBuilder->andWhere('m.office = :office')
                        ->setParameter('office', $params['office']);
            }    
        }
        if (!empty($params['client'])){
            if (is_numeric($params['client'])){
                $queryBuilder->andWhere('m.client = :client')
                        ->setParameter('client', $params['client']);
            }    
        }
        if (!empty($params['startDate'])){
            $queryBuilder
                ->andWhere('m.dateOper >= :startDate')    
                ->setParameter('startDate', $params['startDate'])    
                    ;
        }
        if (!empty($params['endDate'])){
            $queryBuilder
                ->andWhere('m.dateOper <= :endDate')    
                ->setParameter('endDate', $params['endDate']) 
                    ;
        }
        if (!empty($params['period'])){
            switch ($params['period']){
                case 'month':
                    $queryBuilder->addSelect('DAY(m.dateOper) as period');
                    break;
                case 'year':        
                case 'number':
                    $queryBuilder->addSelect('date_format(m.dateOper, \'%Y-%m\') as period');
                    break;
                default:
                    $queryBuilder->addSelect('YEAR(m.dateOper) as period');                    
                }
        }    
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }
    
    /**
     * Количество заказов по периодам
     * @param array $params
     */
    public function revenueByOrders($params)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq('r.docType', Movement::DOC_ORDER));
        $orX->add($queryBuilder->expr()->eq('r.docType', Movement::DOC_VT));
        
        $queryBuilder->select(''
                . 'sum(CASE WHEN r.docType = :orderDocType THEN 1 ELSE 0 END) as orderCount, '
                . 'sum(CASE WHEN r.docType = :vtDocType THEN 1 ELSE 0 END) as vtCount')
                ->addSelect('0 as newClient, 0 as newOrder')
                ->from(Retail::class, 'r')
//                ->join('r.contact', 'contact')
//                ->join('contact.client', 'c')
                ->where('r.status = :status')
                ->setParameter('orderDocType', Movement::DOC_ORDER)    
                ->setParameter('vtDocType', Movement::DOC_VT)    
                ->setParameter('status', Retail::STATUS_ACTIVE)    
                ->andWhere($orX)
                ->groupBy('period')
                ;
        
        if (!empty($params['office'])){
            if (is_numeric($params['office'])){
                $queryBuilder->andWhere('r.office = :office')
                        ->setParameter('office', $params['office']);
            }    
        }
        if (!empty($params['startDate'])){
            $queryBuilder
                ->andWhere('r.dateOper >= :startDate')    
                ->setParameter('startDate', $params['startDate'])    
                    ;
        }
        if (!empty($params['endDate'])){
            $queryBuilder
                ->andWhere('r.dateOper <= :endDate')    
                ->setParameter('endDate', $params['endDate']) 
                    ;
        }
        if (!empty($params['period'])){
            switch ($params['period']){
                case 'month':
                    $queryBuilder->addSelect('DAY(r.dateOper) as period');
//                    $queryBuilder->addSelect('sum(CASE WHEN DATE(r.dateOper) = DATE(c.dateOrder) THEN 1 ELSE 0 END) as newOrder');
//                    $queryBuilder->addSelect('sum(CASE WHEN DATE(r.dateOper) = DATE(c.dateRegistration) THEN 1 ELSE 0 END) as newClient');
                    break;
                case 'year':        
                case 'number':
                    $queryBuilder->addSelect('date_format(r.dateOper, \'%Y-%m\') as period');
//                    $queryBuilder->addSelect('sum(CASE WHEN MONTH(r.dateOper) = MONTH(c.dateOrder) and YEAR(r.dateOper) = YEAR(c.dateOrder) THEN 1 ELSE 0 END) as newOrder');
//                    $queryBuilder->addSelect('sum(CASE WHEN MONTH(r.dateOper) = MONTH(c.dateRegistration) and YEAR(r.dateOper) = YEAR(c.dateRegistration) THEN 1 ELSE 0 END) as newClient');
                    break;
                default:
                    $queryBuilder->addSelect('YEAR(r.dateOper) as period');                    
//                    $queryBuilder->addSelect('sum(CASE WHEN YEAR(r.dateOper) = YEAR(c.dateOrder) THEN 1 ELSE 0 END) as newOrder');
//                    $queryBuilder->addSelect('sum(CASE WHEN YEAR(r.dateOper) = YEAR(c.dateRegistration) THEN 1 ELSE 0 END) as newClient');
                }
        }    
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }
    
    /**
     * Новые клиенты за период
     * @param array $params
     */
    public function newRegistrations($params)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('0 as orderCount, 0 as vtCount, count(distinct c.id) as newClient, 0 as newOrder')
                ->from(Order::class, 'o')
                ->join('o.contact', 'contact')
                ->join('contact.client', 'c')
                ->groupBy('period')
                ;
        
        if (!empty($params['office'])){
            if (is_numeric($params['office'])){
                $queryBuilder->andWhere('o.office = :office')
                        ->setParameter('office', $params['office']);
            }    
        }
        if (!empty($params['startDate'])){
            $queryBuilder
                ->andWhere('c.dateRegistration >= :startDate')    
                ->setParameter('startDate', $params['startDate'])    
                    ;
        }
        if (!empty($params['endDate'])){
            $queryBuilder
                ->andWhere('c.dateRegistration <= :endDate')    
                ->setParameter('endDate', $params['endDate']) 
                    ;
        }
        if (!empty($params['period'])){
            switch ($params['period']){
                case 'month':
                    $queryBuilder->addSelect('DAY(c.dateRegistration) as period');
                    break;
                case 'year':        
                case 'number':
                    $queryBuilder->addSelect('date_format(c.dateRegistration, \'%Y-%m\') as period');
                    break;
                default:
                    $queryBuilder->addSelect('YEAR(c.dateRegistration) as period');                    
                }
        }    
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }

    /**
     * Новые заказы за период
     * @param array $params
     */
    public function newOrders($params)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('0 as orderCount, 0 as vtCount, 0 as newClient, count(distinct c.id) as newOrder')
                ->from(Order::class, 'o')
                ->join('o.contact', 'contact')
                ->join('contact.client', 'c')
                ->groupBy('period')
                ;
        
        if (!empty($params['office'])){
            if (is_numeric($params['office'])){
                $queryBuilder->andWhere('o.office = :office')
                        ->setParameter('office', $params['office']);
            }    
        }
        if (!empty($params['startDate'])){
            $queryBuilder
                ->andWhere('c.dateOrder >= :startDate')    
                ->setParameter('startDate', $params['startDate'])    
                    ;
        }
        if (!empty($params['endDate'])){
            $queryBuilder
                ->andWhere('c.dateOrder <= :endDate')    
                ->setParameter('endDate', $params['endDate']) 
                    ;
        }
        if (!empty($params['period'])){
            switch ($params['period']){
                case 'month':
                    $queryBuilder->addSelect('DAY(c.dateOrder) as period');
                    break;
                case 'year':        
                case 'number':
                    $queryBuilder->addSelect('date_format(c.dateOrder, \'%Y-%m\') as period');
                    break;
                default:
                    $queryBuilder->addSelect('YEAR(c.dateOrder) as period');                    
                }
        }    
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }

    /**
     * Выручка по товарам
     * @param array $params
     */
    public function revenueByGoods($params)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_ORDER));
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_VT));
        
        $queryBuilder->select('g.id, g.code, g.name, '
                . 'sum(-m.amount + m.baseAmount) as income, '
                . 'sum(-m.quantity) as quantity')
                ->from(Movement::class, 'm')
                ->join('m.good', 'g')
                ->where('m.status = :status')
                ->setParameter('status', Movement::STATUS_ACTIVE)    
                ->andWhere($orX)
                ->groupBy('g.id')
                ->having('income != 0')
//                ->setMaxResults(10)
                ;
        
        if (!empty($params['office'])){
            if (is_numeric($params['office'])){
                $queryBuilder->andWhere('m.office = :office')
                        ->setParameter('office', $params['office']);
            }    
        }
        if (!empty($params['withoutCategory'])){
            if (is_numeric($params['withoutCategory'])){
                $queryBuilder->leftJoin('g.categories', 'cat')
                        ->andWhere('cat.id is null');
            }    
        }
        if (!empty($params['startDate'])){
            $queryBuilder
                ->andWhere('m.dateOper >= :startDate')    
                ->setParameter('startDate', $params['startDate'])    
                    ;
        }
        if (!empty($params['endDate'])){
            $queryBuilder
                ->andWhere('m.dateOper <= :endDate')    
                ->setParameter('endDate', $params['endDate']) 
                    ;
        }
        if (isset($params['sort'])){
            $queryBuilder->orderBy($params['sort'], $params['order']);
        }            
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }
    
    /**
     * Выручка по товарам
     * @param array $params
     */
    public function revenueByGoodsCount($params)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_ORDER));
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_VT));
        
        $queryBuilder->select('count(distinct(m.good)) as countId, '
                . 'sum(-m.amount + m.baseAmount) as income, '
                . 'sum(-m.quantity) as quantity')
                ->from(Movement::class, 'm')
                ->where('m.status = :status')
                ->setParameter('status', Movement::STATUS_ACTIVE)    
                ->andWhere($orX)               
                ->setMaxResults(1)
                ;
        
        if (!empty($params['office'])){
            if (is_numeric($params['office'])){
                $queryBuilder->andWhere('m.office = :office')
                        ->setParameter('office', $params['office']);
            }    
        }
        if (!empty($params['withoutCategory'])){
            if (is_numeric($params['withoutCategory'])){
                $queryBuilder
                        ->join('m.good', 'g')
                        ->leftJoin('g.categories', 'cat')
                        ->andWhere('cat.id is null');
            }    
        }
        if (!empty($params['startDate'])){
            $queryBuilder
                ->andWhere('m.dateOper >= :startDate')    
                ->setParameter('startDate', $params['startDate'])    
                    ;
        }
        if (!empty($params['endDate'])){
            $queryBuilder
                ->andWhere('m.dateOper <= :endDate')    
                ->setParameter('endDate', $params['endDate']) 
                    ;
        }
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }

    /**
     * Выручка по группам товаров
     * @param array $params
     */
    public function revenueByTokenGroup($params)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_ORDER));
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_VT));
        
        $queryBuilder->select('ifnull(gs.fullName, \'Без категории\') as tgName, ifnull(gs.code, \'Без категории\') as tgLemms, '
                . 'sum(-m.amount + m.baseAmount) as income, '
                . 'sum(-m.quantity) as quantity')
                ->from(Movement::class, 'm')
                ->join('m.good', 'g')
                ->leftJoin('g.tokenGroup', 'tg')
                ->leftJoin('tg.groupSite', 'gs')
                ->where('m.status = :status')
                ->setParameter('status', Movement::STATUS_ACTIVE)    
                ->andWhere($orX)
                ->groupBy('tg.groupSite')
                ;
        
        if (!empty($params['office'])){
            if (is_numeric($params['office'])){
                $queryBuilder->andWhere('m.office = :office')
                        ->setParameter('office', $params['office']);
            }    
        }
        if (!empty($params['startDate'])){
            $queryBuilder
                ->andWhere('m.dateOper >= :startDate')    
                ->setParameter('startDate', $params['startDate'])    
                    ;
        }
        if (!empty($params['endDate'])){
            $queryBuilder
                ->andWhere('m.dateOper <= :endDate')    
                ->setParameter('endDate', $params['endDate']) 
                    ;
        }
        if (isset($params['sort'])){
            $queryBuilder->orderBy($params['sort'], $params['order']);
        }            
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }
    
    /**
     * Количество по группам товаров
     * @param array $params
     */
    public function revenueByTokenGroupCount($params)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_ORDER));
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_VT));
        
        $queryBuilder->select('count(distinct(ifnull(tg.groupSite, 0))) as tgCount, '
                . 'sum(-m.amount + m.baseAmount) as income, '
                . 'sum(-m.quantity) as quantity')
                ->from(Movement::class, 'm')
                ->join('m.good', 'g')
                ->leftJoin('g.tokenGroup', 'tg')
                ->where('m.status = :status')
                ->setParameter('status', Movement::STATUS_ACTIVE)    
                ->andWhere($orX)
                ;
        
        if (!empty($params['office'])){
            if (is_numeric($params['office'])){
                $queryBuilder->andWhere('m.office = :office')
                        ->setParameter('office', $params['office']);
            }    
        }
        if (!empty($params['startDate'])){
            $queryBuilder
                ->andWhere('m.dateOper >= :startDate')    
                ->setParameter('startDate', $params['startDate'])    
                    ;
        }
        if (!empty($params['endDate'])){
            $queryBuilder
                ->andWhere('m.dateOper <= :endDate')    
                ->setParameter('endDate', $params['endDate']) 
                    ;
        }
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }
    
    /**
     * Выручка по клиентам
     * @param array $params
     */
    public function revenueByClient($params)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_ORDER));
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_VT));
        
        $queryBuilder->select('c.name as clientName, c.pricecol, c.id as clientId, '
                . 'sum(-m.amount + m.baseAmount) as income, '
                . 'sum(-m.amount + m.baseAmount)*100/sum(-m.amount) as margin, '
                . 'sum(-m.amount) as amount, ' 
                . 'sum(-m.amount)/count(distinct(m.parentDocId)) as average, ' 
                . 'count(distinct(m.parentDocId)) as orderCount')
                ->addSelect('CASE WHEN c.dateOrder >= :startDate and c.dateOrder <= :endDate THEN 1 ELSE 0 END as newOrderFlag')
                ->addSelect('c.dateRegistration, c.dateOrder')
                ->from(Movement::class, 'm')
                ->join('m.client', 'c')
                ->where('m.status = :status')
                ->setParameter('status', Movement::STATUS_ACTIVE)    
                ->andWhere($orX)
                ->groupBy('m.client')
//                ->having('amount != 0')
                ;
        
        if (!empty($params['office'])){
            if (is_numeric($params['office'])){
                $queryBuilder->andWhere('m.office = :office')
                        ->setParameter('office', $params['office']);
            }    
        }
        if (!empty($params['client'])){
            if (is_numeric($params['client'])){
                $queryBuilder->andWhere('m.client = :client')
                        ->setParameter('client', $params['client']);
            }    
        }
        if (!empty($params['startDate'])){
            $queryBuilder
                ->andWhere('m.dateOper >= :startDate')    
                ->setParameter('startDate', $params['startDate'])    
                    ;
        }
        if (!empty($params['endDate'])){
            $queryBuilder
                ->andWhere('m.dateOper <= :endDate')    
                ->setParameter('endDate', $params['endDate']) 
                    ;
        }
        if (!empty($params['newClient'])){
            if ($params['newClient'] == 1){
                $queryBuilder                        
                    ->andWhere('c.dateOrder <= :endDate')    
                    ->andWhere('c.dateOrder >= :startDate')    
                        ;
            }    
        }
        if (isset($params['sort'])){
            $queryBuilder->orderBy($params['sort'], $params['order']);
        }            
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }
    
    /**
     * Количество по клиентам
     * @param array $params
     */
    public function revenueByClientCount($params)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_ORDER));
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_VT));
        
        $queryBuilder->select('count(distinct(m.client)) as clientCount, '
                . 'sum(-m.amount + m.baseAmount) as income, '
                . 'sum(-m.amount) as amount, '
                . 'sum(-m.amount + m.baseAmount)*100/sum(-m.amount) as margin, '
                . 'sum(-m.amount)/count(distinct(m.parentDocId)) as average, ' 
                . 'count(distinct(m.parentDocId)) as orderCount')
                ->from(Movement::class, 'm')
                ->join('m.client', 'c')
                ->where('m.status = :status')
                ->setParameter('status', Movement::STATUS_ACTIVE)    
                ->andWhere($orX)
//                ->having('amount != 0')
                ;
        
        if (!empty($params['office'])){
            if (is_numeric($params['office'])){
                $queryBuilder->andWhere('m.office = :office')
                        ->setParameter('office', $params['office']);
            }    
        }
        if (!empty($params['client'])){
            if (is_numeric($params['client'])){
                $queryBuilder->andWhere('m.client = :client')
                        ->setParameter('client', $params['client']);
            }    
        }
        if (!empty($params['startDate'])){
            $queryBuilder
                ->andWhere('m.dateOper >= :startDate')    
                ->setParameter('startDate', $params['startDate'])    
                    ;
        }
        if (!empty($params['endDate'])){
            $queryBuilder
                ->andWhere('m.dateOper <= :endDate')    
                ->setParameter('endDate', $params['endDate']) 
                    ;
        }
        if (!empty($params['newClient'])){
            if ($params['newClient'] == 1){
                $queryBuilder
                    ->andWhere('c.dateOrder <= :endDate')    
                    ->andWhere('c.dateOrder >= :startDate')    
                        ;
            }    
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
    
    /**
     * Выборка для обновления телефонов
     */
    public function findForUpdatePhone()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('o')
            ->distinct()    
            ->from(Order::class, 'o')
            ->leftJoin('o.phones', 'p')    
            ->where('o.status = :status')
            ->setParameter('status', Order::STATUS_SHIPPED)  
            ->andWhere('p.phone is null')    
            ->andWhere('o.aplId > 0')    
            ->setMaxResults(10000)
               ;
        
        return $queryBuilder->getQuery()->getResult();
        
    }
    
    /**
     * Выборка для исправления движения товаров
     */
    public function findForFixMovement()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('o')
            ->distinct()    
            ->from(Order::class, 'o')
            ->leftJoin('o.movements', 'm', 'WITH', 'm.docType = 5 and m.status != 2')    
            ->where('o.status = :status')
            ->setParameter('status', Order::STATUS_SHIPPED)  
            ->andWhere('m.docStamp is null')    
            ->andWhere('o.aplId > 0')    
            ->setMaxResults(10000)
               ;
        
        return $queryBuilder->getQuery()->getResult();
        
    }
    
    /**
     * Выборка для fasade
     * @param array $params Description
     */
    public function findForFasade($params)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('o')
            ->distinct()    
            ->from(Order::class, 'o')
            ->where('o.status = :status')
            ->setParameter('status', Order::STATUS_SHIPPED)  
            ->andWhere('o.fasadeEx = :fasade')    
            ->setParameter('fasade', $params['fasade'])  
            ->andWhere('o.aplId > 0')    
            ->setMaxResults($params['limit'])
               ;
        
        return $queryBuilder->getQuery()->getResult();
        
    }
    
    
}
