<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Company\Repository;

use Doctrine\ORM\EntityRepository;
use Company\Entity\Country;
/**
 * Description of SupplierRepository
 *
 * @author Daddy
 */
class CountryRepository extends EntityRepository
{
    /**
     * Запрос Country для автозаполения
     * 
     * @param array $params
     * @return query
     */
    public function autocompeteCountry($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Country::class, 'c')
                ;
        
        if (is_array($params)){
            if (isset($params['search'])){
                $queryBuilder
                    ->where('c.name like ?1')                           
                    ->setParameter('1', $params['search'].'%')    
                        ;
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();
    }    
    

}