<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bank\Repository;

use Doctrine\ORM\EntityRepository;
use Company\Entity\BankAccount;
use Application\Entity\Supplier;
use Company\Entity\Legal;
use Company\Entity\Contract;
use Bank\Entity\QrCode;

/**
 * Description of QrCodeRepository
 *
 * @author Daddy
 */
class QrCodeRepository extends EntityRepository
{

    /**
     * Поиск QrCode по номеру заказа апл
     * @param integer $orderAplId
     * @param integer $amount
     * @return QrCode
     */
    public function qrCodeByOrderAplId($orderAplId, $amount)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('q')
                ->from(QrCode::class, 'q')
                ->where('q.orderAplId = ?1')
                ->setParameter('1', $orderAplId)
                ->andWhere('q.status = ?2')
                ->setParameter('2', QrCode::STATUS_ACTIVE)
                ->andWhere('q.amount = ?3')
                ->setParameter('3', $amount)
                ->andWhere('q.dateCreated >= ?4')
                ->setParameter('4', date('Y-m-d H:i:s', strtotime('-3 days')))
                ->setMaxResults(1)
                ;
        
        return $queryBuilder->getQuery()->getOneOrNullResult();
    }


    /**
     * Получить выборку записей платежек
     * 
     * @param string $q поисковый запрос
     * @param string $rs счет
     * @param array $params
     * @return object
     */
    public function findPayments($q = null, $rs = null, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('p, ba, u, s')
                ->from(Payment::class, 'p')
                ->join('p.bankAccount', 'ba')
                ->join('p.user', 'u')
                ->leftJoin('p.supplier', 's')
                ;
        if (is_array($params)){
            if (!empty($params['sort'])){
                $queryBuilder->addOrderBy('p.'.$params['sort'], $params['order']);
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(p.paymentDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(p.paymentDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
            if (!empty($params['supplier'])){
                $supplier = $entityManager->getRepository(Supplier::class)
                        ->findOneById($params['supplier']);
                if ($supplier){
                    $queryBuilder->andWhere('p.supplier = :supplier')
                            ->setParameter('supplier', $supplier->getId());
                }    
            }            
            if (!empty($params['status'])){
                if (is_numeric($params['status'])){
                    $queryBuilder->andWhere('p.status = :status')
                            ->setParameter('status', $params['status']);
                }    
            }            
            if (!empty($params['paymentType'])){
                if (is_numeric($params['paymentType'])){
                    $queryBuilder->andWhere('p.paymentType = :paymentType')
                            ->setParameter('paymentType', $params['paymentType']);
                }    
            }            
        }
        if ($q){
            $or = $queryBuilder->expr()->orX();
            $or->add($queryBuilder->expr()->like('p.counterpartyInn', '?1'));
            $or->add($queryBuilder->expr()->like('p.counterpartyName', '?1'));
            $or->add($queryBuilder->expr()->like('p.purpose', '?1'));
            $queryBuilder->setParameter('1', '%' . $q . '%');

            if (is_numeric($q)){
                $or->add($queryBuilder->expr()->eq('FLOOR(p.amount)', floor($q)));
//                $or->add($queryBuilder->expr()->eq('FLOOR(s.amount)', -floor($q)));                
            }
            $queryBuilder->andWhere($or);
        }
                
        return $queryBuilder->getQuery();
        
    }    

    /**
     * Получить всего количество записей платежек
     * 
     * @param string $q поисковый запрос
     * @param string $rs счет
     * @param array $params
     * @return object
     */
    public function findTotalPayments($q = null, $rs = null, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('count(p) as countP')
                ->from(Payment::class, 'p')
                ;
        if (is_array($params)){
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(p.paymentDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(p.paymentDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
            if (!empty($params['supplier'])){
                $supplier = $entityManager->getRepository(Supplier::class)
                        ->findOneById($params['supplier']);
                if ($supplier){
                    $queryBuilder->andWhere('p.supplier = :supplier')
                            ->setParameter('supplier', $supplier->getId());
                }    
            }            
            if (!empty($params['status'])){
                if (is_numeric($params['status'])){
                    $queryBuilder->andWhere('p.status = :status')
                            ->setParameter('status', $params['status']);
                }    
            }            
            if (!empty($params['paymentType'])){
                if (is_numeric($params['paymentType'])){
                    $queryBuilder->andWhere('p.paymentType = :paymentType')
                            ->setParameter('paymentType', $params['paymentType']);
                }    
            }            
        }
        if ($q){
            $or = $queryBuilder->expr()->orX();
            $or->add($queryBuilder->expr()->like('p.counterpartyInn', '?1'));
            $or->add($queryBuilder->expr()->like('p.counterpartyName', '?1'));
            $or->add($queryBuilder->expr()->like('p.purpose', '?1'));
            $queryBuilder->setParameter('1', '%' . $q . '%');

            if (is_numeric($q)){
                $or->add($queryBuilder->expr()->eq('FLOOR(p.amount)', floor($q)));
//                $or->add($queryBuilder->expr()->eq('FLOOR(s.amount)', -floor($q)));                
            }
            $queryBuilder->andWhere($or);
        }
                
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        return $result['countP'];
    }    

    /**
     * Сумма платежей
     * @param integer $status
     * @return float
     */
    public function statusTotal($status = Payment::STATUS_ACTIVE)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('sum(p.amount) as totalP')
                ->from(Payment::class, 'p')
                ->where('p.status = ?1')
                ->setParameter('1', $status)
                ;
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        if (is_array($result)){
            return round($result['totalP'], 2);                
        }    
        
        return 0;
    }
}