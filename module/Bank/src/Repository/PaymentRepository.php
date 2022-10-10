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
use Bank\Entity\Payment;

/**
 * Description of PaymentRepository
 *
 * @author Daddy
 */
class PaymentRepository extends EntityRepository
{

    /**
     * рс поставщиков
     * @param Legal $company
     * @return array
     */
    public function supplierAccounts($company)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('ba')
                ->distinct()
                ->from(BankAccount::class, 'ba')
                ->join('ba.legal', 'l')
                ->join('l.contacts', 'c')
                ->join('l.contracts', 'ct')
                ->join('c.supplier', 's')
                ->where('ba.status = ?1')
                ->setParameter('1', BankAccount::STATUS_ACTIVE)
                ->andWhere('s.status = ?2')
                ->setParameter('2', Supplier::STATUS_ACTIVE)
                ->andWhere('ct.company = ?3')
                ->setParameter('3', $company->getId())
                ->andWhere('ct.status = ?4')
                ->setParameter('4', Contract::STATUS_ACTIVE)
                ->andWhere('ct.pay = ?5')
                ->setParameter('5', Contract::PAY_CASHLESS)
                ->addOrderBy('s.amount', 'DESC')
                ->addOrderBy('ba.id', 'DESC')
                ;
        
        return $queryBuilder->getQuery()->getResult();
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
                
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        return $result['countP'];
    }        
}