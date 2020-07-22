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

            $query = $queryBuilder->getQuery();

            if ($resultMode){
                return $queryBuilder->getQuery()->getResult($resultMode);        
            } else {
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
        if (isset($params['officeId'])){
            
        }
        
//        var_dump($params);
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('l')
            ->from(Legal::class, 'l')
            ->join('l.contacts', 'c')
            ->where('c.office = ?1')    
            ->setParameter('1', $params['officeId'])    
            ->andWhere('c.status = ?2')
            ->setParameter('2', Contact::STATUS_LEGAL)    
//            ->where('l.status = ?3')    
//            ->setParameter('3', Legal::STATUS_ACTIVE)
                ;

        return $queryBuilder->getQuery()->getResult();        
    }
}