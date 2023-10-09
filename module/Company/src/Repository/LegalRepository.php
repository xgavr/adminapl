<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Company\Repository;

use Doctrine\ORM\EntityRepository;
use Company\Entity\Legal;
use Application\Entity\Contact;
use Company\Entity\Contract;
use Company\Entity\BankAccount;
use Company\Entity\EdoOperator;

/**
 * Description of SupplierRepository
 *
 * @author Daddy
 */
class LegalRepository extends EntityRepository
{

    /**
     * Поиск юрлица по инн и кпп
     * 
     * @param string $inn
     * @param string $kpp 
     * @param null|integer $resultMode
     */
    public function findOneByInnKpp($inn, $kpp = null, $resultMode = null)
    {
        if ($inn){
            $entityManager = $this->getEntityManager();

            $queryBuilder = $entityManager->createQueryBuilder();

            $queryBuilder->select('r')
                ->from(Legal::class, 'r')
                ->where('r.inn = ?1')    
                ->setParameter('1', $inn)
                   
    //            ->orderBy(['id DESC'])    
                    ;

            if ($kpp){
                $queryBuilder
                    ->andWhere('r.kpp = ?2')    
                    ->setParameter('2', $kpp)
                    ;

            }        

//            $query = $queryBuilder->getQuery();

            if ($resultMode){
                return $queryBuilder->getQuery()->getResult($resultMode);        
            } else {
                
                $queryBuilder->setMaxResults(1);
                
                return $queryBuilder->getQuery()->getOneOrNullResult();                    
            }    
        }
        
        return;
    }
    
    /**
     * Получить юрлица офиса
     * 
     * @param array $params
     */
    public function formOfficeLegals($params)
    {
        $officeId = -1;
        if (isset($params['officeId'])){
           $officeId =  $params['officeId'];
        }

        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('l')
            ->from(Legal::class, 'l')
            ->join('l.contacts', 'c')
            ->where('c.office = ?1')    
            ->setParameter('1', $officeId)    
            ->andWhere('c.status = ?2')
            ->setParameter('2', Contact::STATUS_LEGAL)    
                ;

        return $queryBuilder->getQuery()->getResult();        
    }

    /**
     * Получить юрлица офисов
     * 
     * @param array $params
     */
    public function companies($params)
    {

        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('l')
            ->from(Legal::class, 'l')
            ->join('l.contacts', 'c')
            ->where('c.office > 0')    
            ->andWhere('c.status = ?2')
            ->setParameter('2', Contact::STATUS_LEGAL)    
                ;

        return $queryBuilder->getQuery()->getResult();        
    }
    
    /**
     * Получить юрлица поставщика
     * 
     * @param array $params
     */
    public function formSupplierLegals($params)
    {
        $supplierId = -1;
        if (!empty($params['supplierId'])){
            $supplierId = $params['supplierId'];
        }

        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('l')
            ->from(Legal::class, 'l')
            ->join('l.contacts', 'c')
            ->where('c.supplier = ?1')    
            ->setParameter('1', $supplierId)    
            ->andWhere('c.status = ?2')
            ->setParameter('2', Contact::STATUS_LEGAL)    
                ;

        return $queryBuilder->getQuery()->getResult();        
    }
    
    /**
     * Получить договора между компанией и юрлицом
     * 
     * @param array $params
     */
    public function formOfficeLegalContracts($params)
    {
        $companyId = $legalId = -1;
        if (!empty($params['companyId'])){
            $companyId = $params['companyId'];
        }
        if (!empty($params['legalId'])){
            $legalId = $params['legalId'];
        }

        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('c')
            ->from(Contract::class, 'c')
            ->where('c.company = ?1')    
            ->setParameter('1', $companyId)    
            ->andWhere('c.legal = ?2')
            ->setParameter('2', $legalId)    
                ;

        return $queryBuilder->getQuery()->getResult();        
    }
    
    /**
     * Получить юрлица контакта
     * 
     * @param Contact $contact
     */
    public function formContactLegals($contact)
    {

        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('l')
            ->from(Legal::class, 'l')
            ->join('l.contacts', 'c')
            ->where('c.id = ?1')    
            ->setParameter('1', $contact->getId())    
            ->orderBy('l.dateStart', 'DESC')    
                ;

        return $queryBuilder->getQuery()->getResult();        
    }

    /**
     * Получить юрлицо контакта
     * 
     * @param Contact $contact
     */
    public function formContactLegal($contact)
    {

        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('l')
            ->from(Legal::class, 'l')
            ->join('l.contacts', 'c')
            ->where('c.id = ?1')    
            ->setParameter('1', $contact->getId())    
            ->orderBy('l.dateStart', 'DESC')    
            ->setMaxResults(1)    
                ;

        return $queryBuilder->getQuery()->getOneOrNullResult();        
    }
    
    /**
     * Получить актуальный банковский счет
     * @param Legal $legal
     * @param date $dateStart
     * @return BankAccount
     */
    public function findDefaultBankAccount($legal, $dateStart = null)
    {
        if (empty($dateStart)){
            $dateStart = date('Y-m-d'); 
        }
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('ba')
            ->from(BankAccount::class, 'ba')
            ->where('ba.legal = ?1')    
            ->setParameter('1', $legal->getId())
            ->andWhere('ba.status = ?2')     
            ->setParameter('2', BankAccount::STATUS_ACTIVE)
            ->andWhere('ba.dateStart <= :dateStart')     
            ->setParameter('dateStart', $dateStart)
            ->orderBy('ba.dateStart', 'DESC')    
            ->addOrderBy('ba.id', 'DESC')    
            ->setMaxResults(1)    
                ;

        return $queryBuilder->getQuery()->getOneOrNullResult();        
        
    }
    
    /**
     * Запрос по опреаторам эдо по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllEdoOperator($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('e')
            ->from(EdoOperator::class, 'e')
                ;
        
        if (is_array($params)){
            if (isset($params['q'])){
                $queryBuilder->where('e.name like :search')
                    ->setParameter('search', '%' . $params['q'] . '%')
                        ;
            }
            if (isset($params['status'])){
                $queryBuilder->andWhere('e.status = ?3')
                    ->setParameter('3', $params['status'])
                        ;
            }            
            if (isset($params['sort'])){
                $queryBuilder->orderBy('e.'.$params['sort'], $params['order']);                
            }            
        }

        return $queryBuilder->getQuery();
    }   
    
    /**
     * Найти договра контакта
     * @param Contact $contact
     * @param integer $kind
     */
    public function contactSelect($contact, $kind = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('ct')
            ->from(Contract::class, 'ct')
            ->distinct()    
            ->join('ct.legal', 'l')
            ->join('l.contacts', 'cs')                 
            ->andWhere('cs.id = :contact')
            ->setParameter('contact', $contact->getId())
                ;
        
        $client = $contact->getClient();
        if ($client){
            $queryBuilder->join('cs.client', 'cl')
                    ->orWhere('cl.id = :client')
                    ->setParameter('client', $client->getId())
                    ;
        }

        if ($kind){
            $queryBuilder->andWhere('ct.kind = :kind')
                    ->setParameter('kind', $kind)
                    ;            
        }
                

        return $queryBuilder->getQuery()->getResult();        
    }
}