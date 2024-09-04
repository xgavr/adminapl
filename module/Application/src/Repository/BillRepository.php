<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Idoc;
use Application\Entity\Rawprice;
use Application\Entity\Goods;
use Application\Entity\Supplier;
use Application\Entity\BillSetting;
use Stock\Entity\Mutual;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Description of BillRepository
 *
 * @author Daddy
 */
class BillRepository  extends EntityRepository{

    /**
     * Запрос на все доки
     * @param array $params
     * @return Query
     * 
     */
    public function queryAllIdocs($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('i.id, i.dateCreated, i.status, i.docKey, i.info, s.id as supplierId,'
                . 's.name as supplierName, i.name, m.amount as mutualAmount')
            ->from(Idoc::class, 'i') 
            ->leftJoin('i.supplier', 's')
            ->leftJoin(Mutual::class, 'm', Join::WITH, 'i.docKey = m.docKey')    
            ->addOrderBy('i.id', 'DESC')    
                ;
        if (is_array($params)){
            if (is_numeric($params['supplier'])){
                $queryBuilder->andWhere('i.supplier = ?1')
                        ->setParameter('1', $params['supplier']);
            }
            if (is_numeric($params['status'])){
                if ($params['status'] == Idoc::STATUS_TO_CORRECT){
                    $queryBuilder->andWhere('i.docKey is not null')
                                ->andWhere('round(i.info) != abs(round(m.amount))')
                            ;
                } else {
                    $queryBuilder->andWhere('i.status = ?2')
                            ->setParameter('2', $params['status']);
                }    
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(i.dateCreated) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(i.dateCreated) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
            if (!empty($params['search'])){
                $queryBuilder->andWhere($queryBuilder->expr()->like('i.name', '\'%'.$params['search'].'%\''));
            }
        }
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }       
    
    /**
     * Запрос количества
     * @param array $params
     * @return Query
     * 
     */
    public function totalAllIdocs($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(i) as countIdoc')
            ->from(Idoc::class, 'i') 
            ->leftJoin('i.supplier', 's')    
            ->addOrderBy('i.id', 'DESC')    
                ;
        if (is_array($params)){
            if (is_numeric($params['supplier'])){
                $queryBuilder->andWhere('i.supplier = ?1')
                        ->setParameter('1', $params['supplier']);
            }
            if (is_numeric($params['status'])){
                if ($params['status'] == Idoc::STATUS_TO_CORRECT){
                    $queryBuilder->leftJoin(Mutual::class, 'm', 'WITH', 'i.docKey = m.docKey')
                            ->andWhere('i.docKey is not null')
                            ->andWhere('round(i.info) != abs(round(m.amount))')
                            ;
                } else {
                    $queryBuilder->andWhere('i.status = ?2')
                            ->setParameter('2', $params['status']);
                }    
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(i.dateCreated) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(i.dateCreated) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
            if (!empty($params['search'])){
                $queryBuilder->andWhere($queryBuilder->expr()->like('i.name', '\'%'.$params['search'].'%\''));
            }
        }
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['countIdoc'];
    }       
    
    /**
     * Найти шаблон для Idoc
     * @param Idoc $idoc
     * @return BillSetting
     */
    public function billSettingForIdoc($idoc)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('b')
            ->from(BillSetting::class, 'b') 
            ->where('b.supplier = ?1')    
            ->setParameter('1', $idoc->getSupplier()->getId()) 
            ->andWhere('b.status = ?2')
            ->setParameter('2', BillSetting::STATUS_ACTIVE) 
            ->orderBy('b.id', 'DESC')    
            ;
        $billSettings = $queryBuilder->getQuery()->getResult();
        
        $result = null;
        foreach ($billSettings as $billSetting){
            $result = $billSetting;
            if ($billSetting->getName() == BillSetting::gname($idoc->getName())){
                return $billSetting;
            }
        }
        
        return $result;
    }
    

    /**
     * Поиск связанных поставщиков
     * @param Supplier $supplier
     * @retrun array
     */
    private function _findChildSupplier($supplier)
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
     * Найти товар по коду поставщика
     * @param Supplier $supplier
     * @param string $iid
     * 
     * @return Goods 
     */
    public function findGoodFromRawprice($supplier, $iid)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r')
                ->from(Rawprice::class, 'r')
                ->join('r.raw', 'raw')
                ->where('r.iid = ?1')
                ->setParameter('1', $iid)
//                ->andWhere('raw.supplier = ?2')
//                ->setParameter('2', $supplier->getId())
                ->setMaxResults(1)
                ;            

        $orX = $queryBuilder->expr()->orX();
        $childs = $entityManager->getRepository(Supplier::class)
                ->findChildSupplier($supplier);
        foreach ($childs as $child){
            $orX->add($queryBuilder->expr()->eq('raw.supplier', $child->getId()));            
        }
        $queryBuilder->andWhere($orX);

        $rawprice = $queryBuilder->getQuery()->getOneOrNullResult();        
        if ($rawprice){
            if ($rawprice->getCode()){
                if ($rawprice->getCode()->getGood()){
                    return $rawprice->getCode()->getGood();
                }                    
            }
            if ($rawprice->getGood()){
                return $rawprice->getGood();
            }
            $producer = null;
            if ($rawprice->getUnknownProducer()){
                if ($rawprice->getUnknownProducer()->getProducer()){
                    $producer = $rawprice->getUnknownProducer()->getProducer();
                }
            }
            return ['article' => $rawprice->getArticle(), 'producer' => $producer, 'goodName' => $rawprice->getGoodname()];
        }
        return;        
    }
    
    /**
     * Поиск некорректных записей записей
     * @return type
     */
    public function findForCorrection()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

//        $queryBuilder->select('i, abs(m.amount) as mAmount')
        $queryBuilder->select('i.id as iid')
            ->from(Idoc::class, 'i') 
            ->join(Mutual::class, 'm', 'WITH', 'i.docKey = m.docKey')
            ->where('i.docKey is not null')    
            ->andWhere('round(i.info) != abs(round(m.amount))')                    
                ;
        
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();
        
    }
    
}
