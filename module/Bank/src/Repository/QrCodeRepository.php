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
use Bank\Entity\QrCodePayment;

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
     * Получить выборку записей кодов
     * 
     * @param string $q поисковый запрос
     * @param string $rs счет
     * @param array $params
     * @return object
     */
    public function findQrcodes($q = null, $rs = null, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('q, o')
                ->from(QrCode::class, 'q')
                ->leftJoin('q.order', 'o')
//                ->leftJoin('q.qrcodePayments', 'p')
                ;
        
        if (is_array($params)){
            if (!empty($params['sort'])){
                $queryBuilder->addOrderBy('q.'.$params['sort'], $params['order']);
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(q.dateCreated) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(q.dateCreated) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
            if (!empty($params['status'])){
                if (is_numeric($params['status'])){
                    $queryBuilder->andWhere('q.status = :status')
                            ->setParameter('status', $params['status']);
                }    
            }            
        }
                
        return $queryBuilder->getQuery();
        
    }    

    /**
     * Получить выборку оплат по qr коду
     * 
     * @param QrCode $qrCode
     * @param array $params
     * @return object
     */
    public function findQrcodePayments($qrCode, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('qp, o')
                ->from(QrCodePayment::class, 'qp')
                ->leftJoin('qp.order', 'o')
                ->where('qp.qrCode = :qrCodeId')
                ->setParameter('qrCodeId', $qrCode->getId())
                ;
        
        if (is_array($params)){
            if (!empty($params['sort'])){
                $queryBuilder->addOrderBy('qp.'.$params['sort'], $params['order']);
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(qp.dateCreated) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(qp.dateCreated) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
            if (!empty($params['status'])){
                if (is_numeric($params['status'])){
                    $queryBuilder->andWhere('qp.status = :status')
                            ->setParameter('status', $params['status']);
                }    
            }            
        }
                
        return $queryBuilder->getQuery();
        
    }    

    /**
     * Получить всего количество записей кодов
     * 
     * @param string $q поисковый запрос
     * @param string $rs счет
     * @param array $params
     * @return object
     */
    public function findTotalQrcodes($q = null, $rs = null, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('count(q) as countQ')
                ->from(QrCode::class, 'q')
                ;
        if (is_array($params)){
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(q.dateCreated) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(q.dateCreated) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
            if (!empty($params['status'])){
                if (is_numeric($params['status'])){
                    $queryBuilder->andWhere('q.status = :status')
                            ->setParameter('status', $params['status']);
                }    
            }            
        }
                
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        return $result['countQ'];
    }    

}