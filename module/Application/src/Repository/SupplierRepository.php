<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Supplier;
use Company\Entity\Legal;
use Application\Entity\Contact;
use Application\Entity\GoodSupplier;
use Application\Entity\Goods;
use Application\Entity\MarketPriceSetting;
use Application\Entity\SupplySetting;
use Company\Entity\Office;
use Company\Entity\Region;

/**
 * Description of SupplierRepository
 *
 * @author Daddy
 */
class SupplierRepository extends EntityRepository{

    /**
     * Запрос на поставщиков
     * 
     * @param array $params
     * @return type
     */
    public function findAllSupplier($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('s')
            ->from(Supplier::class, 's')
            ->orderBy('s.status')
//            ->addOrderBy('s.name')
                ;
        if (is_array($params)){
            if (isset($params['status'])){
                $queryBuilder->andWhere('s.status = ?1')
                        ->setParameter('1', $params['status']);
            }
            if (isset($params['q'])){
                if ($params['q']){
                    $queryBuilder->andWhere('s.name like :search')
                        ->setParameter('search', '%' . $params['q'] . '%')
                            ;
                }    
            }
            if (isset($params['next1'])){
                $queryBuilder->where('s.name > ?2')
                    ->setParameter('2', $params['next1'])
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('s.name < ?3')
                    ->setParameter('3', $params['prev1'])
                    ->addOrderBy('s.name', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['sort'])){
                $queryBuilder->addOrderBy('s.'.$params['sort'], $params['order']);                
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }     
    
    /*
     * Поиск поставщиков у которых отсутствует описание полей
     */
    public function absentPriceDescriptions()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('s, count(pd.id) as price_description_count')
                ->from(Supplier::class, 's')
                ->groupBy('s.id')
                ->where('s.status = ?1')
                ->setParameter('1', Supplier::STATUS_ACTIVE)
                ->leftJoin(\Application\Entity\PriceDescription::class, 'pd', 'WITH', 'pd.supplier = s.id')
                ->having('price_description_count = 0')
                ;
        
        return $queryBuilder->getQuery()->getResult();
        
    }
    
    /*
     * Поиск поставщиков у которых нет загруженных прайсов
     */
    public function absentRaws()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('s, count(r.id) as raw_count')
                ->from(Supplier::class, 's')
                ->groupBy('s.id')
                ->where('s.status = ?1')
                ->setParameter('1', Supplier::STATUS_ACTIVE)
                ->leftJoin(\Application\Entity\Raw::class, 'r', 'WITH', 'r.supplier = s.id and r.status = ?2')
                ->setParameter('2', \Application\Entity\Raw::STATUS_PARSED)
                ->having('raw_count = 0')
                ;
        
        return $queryBuilder->getQuery()->getResult();
        
    }
    
    /*
     * Получить статусы поставщиков
     * @var Apllication\Entity\Raw
     * 
     */
    public function statuses()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('r.status as status, count(r.id) as status_count')
                ->from(Supplier::class, 'r')
                ->groupBy('r.status')
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Получить юрлицо по умолчанию поставщика
     * 
     * @param Supplier $supplier
     * @param date $dateDoc
     * 
     * @return Legal
     */
    public function findDefaultSupplierLegal($supplier, $dateDoc = null)
    {
        if (!$dateDoc){
            $dateDoc = date();
        }
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('l')
                ->from(Legal::class, 'l')
                ->join('l.contacts', 'c')
                ->where('c.supplier = ?1')
                ->setParameter('1', $supplier->getId())
                ->andWhere('c.status = ?2')
                ->setParameter('2', Contact::STATUS_LEGAL)
                ->andWhere('l.dateStart <= ?3')
                ->setParameter('3', $dateDoc)
                ->orderBy('l.dateStart', 'DESC')
                ->setMaxResults(1)
                ;
        
        return $queryBuilder->getQuery()->getOneOrNullResult();
        
    }
    
    /**
     * Выборка для ПТУ
     * 
     * @return Legal
     */
    public function findForPtu()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('s')
                ->from(Supplier::class, 's')
                ->where('s.amount > ?1')
                ->setParameter('1', 0)
                ->orderBy('s.status')
                ->addOrderBy('s.name')
                ;
        
        return $queryBuilder->getQuery()->getResult();
        
    }
    
    /**
     * Поставщики товара
     * @param integer $goodId
     * @param MarketPriceSetting $market
     */
    public function goodSuppliers($goodId, $market = null)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('gs, s')
                ->from(GoodSupplier::class, 'gs')
                ->join('gs.supplier', 's')
                ->where('gs.good = ?1')
                ->setParameter('1', $goodId)
                ->andWhere('gs.update > ?2')
                ->setParameter('2', date('Y-m-d', strtotime('-3 days')))
                ;
        
        if ($market instanceof MarketPriceSetting){
            if ($market->getSupplier()){
                $queryBuilder->andWhere('gs.supplier = ?3')
                        ->setParameter('3', $market->getSupplier()->getId())
                        ;
            }    
        }
        
        return $queryBuilder->getQuery()->getResult(2);        
    }
    
    /**
     * Варианты доставок
     * @param integer $supplierId
     * @param Office $office
     * @param Region $region
     */
    public function supplySettings($supplierId, $office = null, $region = null)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('ss')
                ->from(SupplySetting::class, 'ss')
                ->where('ss.supplier = ?1')
                ->setParameter('1', $supplierId)
                ->join('ss.supplier', 's')
                ;
        
        if ($office){
            $queryBuilder->andWhere('ss.office = ?2')
                    ->setParameter('2', $office->getId())
                    ;
        }
        
        if ($region){
            $queryBuilder
                    ->join('ss.office', 'o')
                    ->andWhere('o.region = ?3')
                    ->setParameter('3', $region->getId())
                    ;
        }
        
        return $queryBuilder->getQuery()->getResult();                
    }
}
