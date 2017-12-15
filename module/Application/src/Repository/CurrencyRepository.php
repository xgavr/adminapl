<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Currency;
use Application\Entity\Currencyrate;
/**
 * Description of CurrencyRepository
 *
 * @author Daddy
 */
class CurrencyRepository extends EntityRepository{

    public function findAllCurrency()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Currency::class, 'c')
            ->orderBy('c.id')
                ;

        return $queryBuilder->getQuery();
    }        
}
