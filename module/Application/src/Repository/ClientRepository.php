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
}
