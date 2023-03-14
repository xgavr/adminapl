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
use Company\Entity\Legal;
use Company\Entity\BankAccount;

/**
 * Description of ClientRepository
 *
 * @author Daddy
 */
class ClientRepository extends EntityRepository{

    
    public function clientByPhone($phone)
    {
        
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
        
        if (isset($params['sort'])){
            $queryBuilder->orderBy('c.'.$params['sort'], $params['order']);
        }
        if (!empty($params['search'])){
            $orX = $queryBuilder->expr()->orX();
            $search = trim($params['search']);
            if (is_numeric($search)){//aplId
                $orX->add($queryBuilder->expr()->eq('c.aplId', $search));
            }            
            $emailValidator = new EmailAddress();
            if ($emailValidator->isValid($search)){
                $queryBuilder->join('c.contacts', 'cs')
                        ->join('cs.emails', 'es');
                $orX->add($queryBuilder->expr()->eq('es.name', ':search'));
                $queryBuilder->setParameter(':search', $search);
            } else {    
                if (strlen($search) > 9){
                    $phoneFilter = new PhoneFilter();
                    $phone = $phoneFilter->filter($params['search']);
    //                var_dump($phone); exit;
                    if ($phone){
                        $queryBuilder->join('c.contacts', 'cs')
                                ->join('cs.phones', 'ps');
                        $orX->add($queryBuilder->expr()->eq('ps.name', $phone));                        
                    }
                }
            }    
            $queryBuilder->where($orX);
        }
        
        return $queryBuilder->getQuery();
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
        $queryBuilder->select('r, o, c, ct')
            ->from(Retail::class, 'r')
            ->join('r.office', 'o')    
            ->join('r.company', 'c')
            ->join('r.contact', 'ct')
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
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(r.dateOper) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(r.dateOper) = :year')
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
            ;
        
        return $queryBuilder->getQuery()->getResult(2);
    }
    
}
