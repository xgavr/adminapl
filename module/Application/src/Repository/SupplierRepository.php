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
use Stock\Entity\Mutual;
use Application\Entity\Email;

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

//        $queryBuilder->select('s, o')
        $queryBuilder->select('s.id as id, s.aplId as aplId, s.name as name, o.name as officeName, s.amount as amount, s.toBillEmail, s.toSupEmail')
            ->from(Supplier::class, 's')
            ->join('s.office', 'o')    
            ->orderBy('s.status')
//            ->addOrderBy('s.name')
                ;
        if (is_array($params)){
            if (isset($params['status'])){
                $queryBuilder->andWhere('s.status = ?1')
                        ->setParameter('1', $params['status']);
            }
            if (isset($params['priceListStatus'])){
                $queryBuilder->andWhere('s.priceListStatus = :priceListStatus')
                        ->setParameter('priceListStatus', $params['priceListStatus']);
            }
            if (isset($params['office'])){
                if (is_numeric($params['office'])){
                    $queryBuilder->andWhere('s.office = :office')
                            ->setParameter('office', $params['office']);
                }    
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
            if (!empty($params['mutualBalance'])){
                $mutualQb = $entityManager->getRepository(Mutual::class)
                        ->mutualBalanceQb(null, 'mm');
                $mutualQb->andWhere('mmmcn.supplier = s.id')
                        ;
                
                $queryBuilder->addSelect('ifnull(('. $mutualQb->getQuery()->getDQL().'), 0) as supplierTotal');
            }
            if (isset($params['sort'])){
                $queryBuilder->addOrderBy($params['sort'], $params['order']);                
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
                ->andWhere('s.priceListStatus = :priceListStatus')
                ->setParameter('priceListStatus', Supplier::PRICE_LIST_ON)
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
                ->andWhere('s.priceListStatus = :priceListStatus')
                ->setParameter('priceListStatus', Supplier::PRICE_LIST_ON)
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
            $dateDoc = date('Y-m-d');
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
     * @return array
     */
    public function findForPtu()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('s')
                ->from(Supplier::class, 's')
                ->where('s.parent is null')
                ->orderBy('s.status')
                ->addOrderBy('s.name')
                ;
        
        return $queryBuilder->getQuery()->getResult();        
    }
    
    /**
     * Выборка для ПТУ form
     * 
     * @return array
     */
    public function findForFormPtu()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('s')
                ->from(Supplier::class, 's')
                ->where('s.parent is null')
                ->orderBy('s.status')
                ->addOrderBy('s.name')
                ;
        
        return $queryBuilder->getQuery()->getResult();        
    }

    /**
     * Поставщики товара для заказа
     * @param integer $goodId
     * @param MarketPriceSetting $market
     */
    public function orderGoodSuppliers($goodId)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('gs')
                ->from(GoodSupplier::class, 'gs')
                ->where('gs.good = ?1')
                ->setParameter('1', $goodId)
                ->andWhere('gs.update > ?2')
                ->setParameter('2', date('Y-m-d', strtotime('-2 days')))
                //->andWhere('g.price > gs.price')
//                ->orderBy('gs.update', 'desc')
                ->addOrderBy('gs.price', 'asc')
                ;
        
        return $queryBuilder->getQuery();        
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
                ->join('gs.good', 'g')
                ->where('gs.good = ?1')
                ->setParameter('1', $goodId)
                ->andWhere('gs.update > ?2')
                ->setParameter('2', date('Y-m-d', strtotime('-2 days')))
                //->andWhere('g.price > gs.price')
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
     * Наличие у поставщиков
     * @param Goods $good
     */
    public function goodAvailable($good)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('gs')
                ->from(GoodSupplier::class, 'gs')
                ->where('gs.good = ?1')
                ->setParameter('1', $good->getId())
                ->andWhere('gs.update > ?2')
                ->setParameter('2', date('Y-m-d', strtotime('-2 days')))
                //->andWhere('g.price > gs.price')
                ;
        
        return $queryBuilder->getQuery()->getResult();        
    }

    /**
     * Найти ид
     * @param integer $goodId
     * @param integer $supplierId
     * @return int 
     */
    public function findGoodSupplierId($goodId, $supplierId)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('gs.id')
                ->from(GoodSupplier::class, 'gs')
                ->where('gs.good = ?1')
                ->andWhere('gs.supplier = ?2')
                ->setParameter('1', $goodId)
                ->setParameter('2', $supplierId)
                ;
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
//        var_dump($result); exit;
        if ($result){
            return $result['id'];
        }
        return;        
    }
    
    /**
     * Поиск связанных поставщиков
     * @param Supplier $supplier
     * @retrun array
     */
    public function findChildSupplier($supplier)
    {
        $result = [$supplier];
        
        if ($supplier->getParent()){
            $parentSupplier = $supplier->getParent();
        } else {
            $parentSupplier = $supplier;
        }
        
        $childs = $this->getEntityManager()->getRepository(Supplier::class)
                ->findBy(['parent' => $parentSupplier->getId()]);
        foreach ($childs as $child){
            $result[] = $child;
        }
        
        return $result;
    }

    /**
     * Найти товары связанных поставщиков
     * @param Goods $good
     * @param Supplier $supplier
     * @return GoodSupplier
     */
    public function findGoodChildSupplier($good, $supplier)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('gs')
                ->from(GoodSupplier::class, 'gs')
                ->where('gs.good = ?1')
                ->setParameter('1', $good->getId())
                ->setMaxResults(1)
                ->orderBy('gs.update', 'DESC')
                ;
        
        $orX = $queryBuilder->expr()->orX();
        $childs = $this->findChildSupplier($supplier);
        foreach ($childs as $child){
            $orX->add($queryBuilder->expr()->eq('gs.supplier', $child->getId()));            
        }
        $queryBuilder->andWhere($orX);
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
    
    /**
     * Найти товар связанных поставщиков по артиклю
     * @param string $code
     * @param float $price
     * @param Supplier $supplier
     * @return Goods
     */
    public function findGoodChildSupplierByCode($code, $price, $supplier)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select("g.id as goodId, ABS(gs.price-$price) as diff")
                ->from(GoodSupplier::class, 'gs')
                ->join('gs.good', 'g')
                ->where('g.code = ?1')
                ->setParameter('1', $code)
                ->orderBy('diff', 'ASC')
                ->addOrderBy('gs.update', 'DESC')
                ->setMaxResults(1)
                ;
        
        $orX = $queryBuilder->expr()->orX();
        $childs = $this->findChildSupplier($supplier);
        foreach ($childs as $child){
            $orX->add($queryBuilder->expr()->eq('gs.supplier', $child->getId()));            
        }
        $queryBuilder->andWhere($orX);
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        
        $row = $queryBuilder->getQuery()->getOneOrNullResult();
        if (is_array($row)){
            return $this->getEntityManager()->getRepository(Goods::class)
                    ->find($row['goodId']);
        }
        return;
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
    
    /**
     * Наличие
     * @param Goods $good
     */
    public function isApl($good)
    {
        $entityManager = $this->getEntityManager();
        
        return $entityManager->getRepository(GoodSupplier::class)
                ->findOneBy(['good' => $good->getId(), 'supplier' => 7, 'update' => date('Y-m-d')]);
    }
    
    /**
     * Поставщик по почте отправителя
     */
    public function suplierByFromEmail($fromEmail)
    {
        
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('s')
                ->distinct()
                ->from(Email::class, 'e')
                ->join('e.contact', 'c')
                ->join(Supplier::class, 's', 'WITH', 'c.supplier = s.id')
                ->where('e.name like :email')
                ->setParameter('email', strtolower(trim($fromEmail)))
                ->setMaxResults(1)
                ;
                
        return $queryBuilder->getQuery()->getOneOrNullResult();                        
    }
}
