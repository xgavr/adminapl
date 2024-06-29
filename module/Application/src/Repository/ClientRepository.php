<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Client;
use User\Filter\PhoneFilter;
use Laminas\Validator\EmailAddress;
use Stock\Entity\Retail;
use Application\Entity\Order;
use Stock\Entity\Movement;
use Stock\Entity\Comiss;
use Company\Entity\Legal;
use Application\Entity\Phone;
use Application\Entity\Email;
use Company\Entity\Contract;

/**
 * Description of ClientRepository
 *
 * @author Daddy
 */
class ClientRepository extends EntityRepository{

    /**
     * Контакты по телефону
     * @param string $strPhone
     * @return type
     */
    public function contactByPhone($strPhone)
    {
        $phoneFilter = new PhoneFilter();
        $phoneName = $phoneFilter->filter($strPhone);

        $result = [];
        if ($phoneName){
            $entityManager = $this->getEntityManager();

            $queryBuilder = $entityManager->createQueryBuilder();
            $queryBuilder->select('identity(p.contact) as contactId')
                    ->from(Phone::class, 'p')
                    ->where('p.name = :phoneName')
                    ->setParameter('phoneName', $phoneName)
                    ;
            
            $data = $queryBuilder->getQuery()->getResult();
            foreach ($data as $row){
                $result[] = $row['contactId'];
            }
        }            
         
        return $result;
    }

    /**
     * Контакты по почте
     * @param string $strEmail
     * @return type
     */
    public function contactByEmail($strEmail)
    {
        $result = [];
        if ($strEmail){
            
            $entityManager = $this->getEntityManager();

            $queryBuilder = $entityManager->createQueryBuilder();
            $queryBuilder->select('identity(e.contact) as contactId')
                    ->from(Email::class, 'e')
                    ->where('e.name = :emailName')
                    ->setParameter('emailName', $strEmail)
                    ;
            
            $data = $queryBuilder->getQuery()->getResult();
            foreach ($data as $row){
                $result[] = $row['contactId'];
            }
        }            
         
        return $result;
    }

    /**
     * Контакты по ИНН
     * @param string $strInn
     * @return type
     */
    public function contactByInn($strInn)
    {
        $result = [];
        if ($strInn){
            
            $entityManager = $this->getEntityManager();

            $queryBuilder = $entityManager->createQueryBuilder();
            $queryBuilder->select('c.id as contactId')
                    ->from(Legal::class, 'l')
                    ->join('l.contacts', 'c')
                    ->where('l.inn = :inn')
                    ->setParameter('inn', $strInn)
                    ;
            
            $data = $queryBuilder->getQuery()->getResult();
            foreach ($data as $row){
                $result[] = $row['contactId'];
            }
        }            
         
        return $result;
    }

    /**
     * Контакты по заказу
     * @param string $strOrder
     * @return type
     */
    public function contactByOrder($strOrder)
    {
        $result = [];
        if (is_numeric($strOrder)){
            
            $entityManager = $this->getEntityManager();

            $queryBuilder = $entityManager->createQueryBuilder();
            $queryBuilder->select('identity(o.contact) as contactId')
                    ->from(Order::class, 'o')
                    ->where('o.id = :order')
                    ->orWhere('o.aplId = :order')
                    ->setParameter('order', $strOrder)
                    ;
            
            $data = $queryBuilder->getQuery()->getResult();
            foreach ($data as $row){
                $result[] = $row['contactId'];
            }
        }            
         
        return $result;
    }
    
    /**
     * @param array $params
     */
    public function findAllClient($params)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Client::class, 'c')
            ->orderBy('c.id', 'DESC')
                ;
        
        $balanceFlag = true;
        
        if (isset($params['sort'])){
            $queryBuilder->orderBy('c.'.$params['sort'], $params['order'])
                    ->addOrderBy('c.id', $params['order'])
                    ;
        }
        if (!empty($params['pricecol'])){
            if (is_numeric($params['pricecol'])){
                $queryBuilder->andWhere('c.pricecol = :pricecol')
                        ->setParameter('pricecol', $params['pricecol']);
            }    
        }    
        if (!empty($params['legal'])){
            $queryBuilder->join('c.contacts', 'cntl')
                    ->join('cntl.legals', 'l')
                    ->join('l.contracts', 'contract')
                    ->addSelect('cntl')
                    ->addSelect('l')
                    ->addSelect('contract')
                    ->andWhere('contract.kind in (:customerKind, :comitentKind)')
                    ->setParameter('contractKind', Contract::KIND_CUSTOMER)
                    ->setParameter('comitentKind', Contract::KIND_COMITENT)
                    ;
        }    
        if (!empty(trim($params['search']))){
            $balanceFlag = false;
            
            $search = trim($params['search']);

            $orX = $queryBuilder->expr()->orX()->add($queryBuilder->expr()->eq('c.id', 0));

            if (is_numeric($search)){//aplId
                $orX->add($queryBuilder->expr()->eq('c.aplId', $search));
            }            

            $contacts = $this->contactByPhone($search) + $this->contactByEmail($search) 
                    + $this->contactByInn($search) + $this->contactByOrder($search);
            
            if (count($contacts)){
                
                $queryBuilder->join('c.contacts', 'cnt');
                
                $orX->add($queryBuilder->expr()->in('cnt.id', $contacts));                    
            }

            if ($orX->count()){
                $queryBuilder->andWhere($orX);
            }    
        }
        
        if ($balanceFlag){
            if (!empty($params['legal'])){
                $queryBuilder->andWhere('round(contract.balance) != 0');
            } else {
                $queryBuilder->andWhere('round(c.balance) != 0');                
            }   
        }
        
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();
    }   
    
    /**
     * @param array $params
     */
    public function totalAllClient($params)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(c.id) as countC')
                ->addSelect('sum(case when c.balance > 0 then c.balance else 0 end) as balanceIn')
                ->addSelect('sum(case when c.balance < 0 then -c.balance else 0 end) as balanceOut')
                ->from(Client::class, 'c')
                ;
        $balanceFlag = true;
        
        if (!empty($params['pricecol'])){
            if (is_numeric($params['pricecol'])){
                $queryBuilder->andWhere('c.pricecol = :pricecol')
                        ->setParameter('pricecol', $params['pricecol']);
            }    
        }    
        if (!empty($params['legal'])){
            $queryBuilder->join('c.contacts', 'cntl')
                    ->join('cntl.legals', 'l')
                    ->join('l.contracts', 'contract')
                    ->addSelect('sum(case when contract.balance > 0 then contract.balance else 0 end) as contractBalanceIn')
                    ->addSelect('sum(case when contract.balance < 0 then -contract.balance else 0 end) as contractBalanceOut')
                    ->andWhere('contract.kind in (:customerKind, :comitentKind)')
                    ->setParameter('contractKind', Contract::KIND_CUSTOMER)
                    ->setParameter('comitentKind', Contract::KIND_COMITENT)
                    ;
        }    
        if (!empty($params['search'])){
            $balanceFlag = false;
            
            $search = trim($params['search']);

            $orX = $queryBuilder->expr()->orX()->add($queryBuilder->expr()->eq('c.id', 0));

            if (is_numeric($search)){//aplId
                $orX->add($queryBuilder->expr()->eq('c.aplId', $search));
            }            

            $contacts = $this->contactByPhone($search) + $this->contactByEmail($search) 
                    + $this->contactByInn($search) + $this->contactByOrder($search);
            
            if (count($contacts)){
                
                $queryBuilder->join('c.contacts', 'cnt');
                
                $orX->add($queryBuilder->expr()->in('cnt.id', $contacts));                    
            }

            if ($orX->count()){
                $queryBuilder->andWhere($orX);
            }    
        }
        if ($balanceFlag){
            if (!empty($params['legal'])){
                $queryBuilder->andWhere('round(contract.balance) != 0');
            } else {
                $queryBuilder->andWhere('round(c.balance) != 0');                
            }    
        }
        
        return $queryBuilder->getQuery()->getOneOrNullResult();
    }        
    
    /**
     * Поиск дублей Апл
     * @return type
     */
    public function findDoubleApl()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c.aplId, count(c.id) as countApl')
            ->from(Client::class, 'c')
            ->groupBy('c.aplId')
            ->having('countApl > 1')    
                ;
        return $queryBuilder->getQuery()->getResult();        
        
    }
    
    /**
     * Взаиморасчеты
     * 
     * @param Client $client
     * @param array $params
     * @return Query
     */
    public function retails($client, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r, o, c, contract, rd, cd, cash, user, msr, legal')
            ->from(Retail::class, 'r')
            ->join('r.contact', 'ct')
            ->join('r.company', 'c')
            ->join('r.office', 'o')    
            ->leftJoin('r.legal', 'legal')    
            ->leftJoin('r.contract', 'contract')    
            ->leftJoin('r.reviseDoc', 'rd', 'WITH', 'r.docType = '.Movement::DOC_REVISE) 
            ->leftJoin('r.ptu', 'p', 'WITH', 'r.docType = '.Movement::DOC_PTU) 
            ->leftJoin('r.cashDoc', 'cd', 'WITH', 'r.docType = '.Movement::DOC_CASH) 
            ->leftJoin('r.marketSaleReport', 'msr', 'WITH', 'r.docType = '.Movement::DOC_MSR) 
//            ->leftJoin('cd.order', 'ord') 
            ->leftJoin('cd.cash', 'cash') 
            ->leftJoin('cd.user', 'user') 
            ->where('ct.client = ?1')
            ->setParameter('1', $client->getId())
//            ->orderBy('m.docStamp','ASC')    
            ;
        
        if (is_array($params)){
            if (!empty($params['sort'])){
                $sort = $params['sort'];
                $queryBuilder->addOrderBy('r.'.$sort, $params['order']);
            }
            if (!empty($params['office'])){
                if (is_numeric($params['office'])){
                    $queryBuilder->andWhere('r.office = ?2')
                        ->setParameter('2', $params['office']);
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
            if (!empty($params['docKey'])){
                $queryBuilder->andWhere('r.docKey = :docKey')
                        ->setParameter('docKey', $params['docKey']);
            }
            if (!empty($params['legal'])){
                if ($params['legal'] == Client::RETAIL_ID){
                    $queryBuilder->andWhere('r.legal is null')
                            ;
                }    
                if (is_numeric($params['legal'])){
                    $queryBuilder->andWhere('r.legal = :legal')
                            ->setParameter('legal', $params['legal'])
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
            if (!empty($params['contract'])){
                if (is_numeric($params['contract'])){
                    $queryBuilder->andWhere('r.contract = :contract')
                            ->setParameter('contract', $params['contract'])
                            ;
                }    
            }
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();            
    } 
    
    /**
     * Долг на дату
     * 
     * @param Client $client
     * @param array $params
     * @return Query
     */
    public function restRetails($client, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('sum(r.amount) as amount')
            ->from(Retail::class, 'r')
            ->join('r.contact', 'ct')
            ->where('ct.client = ?1')
            ->setParameter('1', $client->getId())
            ->andWhere('r.status = :status')    
            ->setParameter('status', Retail::STATUS_ACTIVE)
            ->setMaxResults(1)    
            ;
        
        if (is_array($params)){
            if (!empty($params['office'])){
                if (is_numeric($params['office'])){
                    $queryBuilder->andWhere('r.office = ?2')
                        ->setParameter('2', $params['office']);
                }    
            }
            if (!empty($params['restDate'])){
                $queryBuilder->andWhere('r.dateOper < :restDate')
                        ->setParameter('restDate', $params['restDate']);
            }
            if (!empty($params['legal'])){
                if ($params['legal'] == Client::RETAIL_ID){
                    $queryBuilder->andWhere('r.legal is null')
                            ;
                }    
                if (is_numeric($params['legal'])){
                    $queryBuilder->andWhere('r.legal = :legal')
                            ->setParameter('legal', $params['legal'])
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
            if (!empty($params['contract'])){
                if (is_numeric($params['contract'])){
                    $queryBuilder->andWhere('r.contract = :contract')
                            ->setParameter('contract', $params['contract'])
                            ;
                }    
            }
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();            
    }    
    
    /**
     * Товары на комиссии
     * 
     * @param Client $client
     * @param array $params
     * @return Query
     */
    public function comiss($client, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('c, o, company, ct, g, p, tg')
            ->from(Comiss::class, 'c')
            ->join('c.office', 'o')    
            ->join('c.company', 'company')
            ->join('c.contact', 'ct')
            ->join('c.good', 'g')
            ->join('g.producer', 'p')
            ->leftJoin('g.tokenGroup', 'tg')    
            ->where('ct.client = ?1')
            ->setParameter('1', $client->getId())
//            ->orderBy('m.docStamp','ASC')    
            ;
        
        if (is_array($params)){
            if (!empty($params['sort'])){
                $sort = $params['sort'];
                $queryBuilder->addOrderBy('c.'.$sort, $params['order']);
            }
            if (!empty($params['office'])){
                if (is_numeric($params['office'])){
                    $queryBuilder->andWhere('c.office = ?2')
                        ->setParameter('2', $params['office']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(c.dateOper) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(c.dateOper) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();            
    }    

    /**
     * Юрлица клиента
     * @param Client $client
     * @return array
     */
    public function findLegals($client) 
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('l')
            ->from(Legal::class, 'l')
            ->join('l.contacts', 'c')
            ->distinct()    
            ->where('c.client = ?1')
            ->setParameter('1', $client->getId())
            ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Юрлица клиента
     * @param Client $client
     * @return array
     */
    public function findClientLegals($client) 
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('o, l, p, b')
            ->from(Order::class, 'o')
            ->join('o.contact', 'c')
            ->distinct()    
            ->join('o.legal', 'l', 'WITH', 'o.legal = l.id')
            ->leftJoin('o.recipient', 'p', 'WITH', 'o.recipient = l.id')
            ->leftJoin('o.bankAccount', 'b', 'WITH', 'o.bankAccount = b.id')
            ->where('c.client = ?1')
            ->setParameter('1', $client->getId())
            ->andWhere('o.dateCreated > ?2')
            ->setParameter('2', strtotime('- 3 month'))    
            ;
        
        return $queryBuilder->getQuery()->getResult(2);
    }
    
    /**
     * Получить розничный баланс клиента
     * @param Client $client
     */
    public function  getRetailBalance($client)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select("sum(r.amount) as total")
                ->from(Retail::class, 'r')
                ->join('r.contact', 'c')
                ->where('c.client = :client')
                ->setParameter('client', $client->getId())
                ->andWhere('r.status = :status')
                ->setParameter('status', Retail::STATUS_ACTIVE)
                ->andWhere('r.contract is null')
                ;

        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['total'];
    }  

    /**
     * Обновить баланс клиента
     * @param Client $client
     */
    public function  updateBalance($client)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select("sum(r.amount) as total, max(r.dateOper) as balanceDate")
                ->from(Retail::class, 'r')
                ->join('r.contact', 'c')
                ->where('c.client = :client')
                ->setParameter('client', $client->getId())
                ->andWhere('r.status = :status')
                ->setParameter('status', Retail::STATUS_ACTIVE)
                ;

        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        $entityManager->getConnection()
                ->update('client', ['balance' => round($result['total'], 2), 'balance_date' => $result['balanceDate']], ['id' => $client->getId()]);
        return;
    }  
    
    /**
     * Посиск клиентов для обнуления
     * @param int $year
     */
    public function findClientsForReset($year = 2014)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
                ->from(Client::class, 'c')
                ->where('c.balanceDate < :year')
                ->setParameter('year', $year.'-01-01')
                ->andWhere('c.balance != 0')
                ;

        $result = $queryBuilder->getQuery()->getResult();

        return $result;        
    }
    
    /**
     * Юр лица клиента
     * @param Client $client
     * @return array
     */
    public function clientLegals($client)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('l')
                ->from(Legal::class, 'l')
                ->join('l.contacts', 'c')
                ->where('c.client = :client')
                ->setParameter('client', $client->getId())
                ;

        $result = $queryBuilder->getQuery()->getResult();

        return $result;                
    }
    
    /**
     * Договоры клиента
     * @param Client $client
     * @param Legal $legal
     * @return array
     */
    public function clientContracts($client, $legal)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
                ->from(Contract::class, 'c')
                ->join('c.legal', 'l')
                ->join('l.contacts', 'contact')
                ->where('contact.client = :client')
                ->setParameter('client', $client->getId())
                ->andWhere('c.legal = :legal')
                ->setParameter('legal', $legal->getId())
                ;
        
        $result = $queryBuilder->getQuery()->getResult();

        return $result;                
    }
}
