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
use Application\Filter\ArticleCode;
use Application\Entity\Article;
use Application\Filter\ProducerName;
use Application\Entity\Supplier;

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

        $queryBuilder->select('i, s')
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
                $queryBuilder->andWhere('i.status = ?2')
                        ->setParameter('2', $params['status']);
            }
        }
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
                $queryBuilder->andWhere('i.status = ?2')
                        ->setParameter('2', $params['status']);
            }
        }
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['countIdoc'];
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
        $childs = $this->_findChildSupplier($supplier);
        foreach ($childs as $child){
            $orX->add($queryBuilder->expr()->eq('raw.supplier', $child->getId()));            
        }
        $queryBuilder->andWhere($orX);

        $rawprice = $queryBuilder->getQuery()->getOneOrNullResult();        
        if ($rawprice){
            if ($rawprice->getGood()){
                return $rawprice->getGood();
            }
            if ($rawprice->getCode()){
                if ($rawprice->getCode()->getGood()){
                    return $rawprice->getCode()->getGood();
                }                    
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
}
