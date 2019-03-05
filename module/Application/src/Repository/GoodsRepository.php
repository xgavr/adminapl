<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Goods;
use Application\Entity\Rawprice;

/**
 * Description of GoodsRepository
 *
 * @author Daddy
 */
class GoodsRepository extends EntityRepository
{
    
    /**
     * Быстрая обновление товара
     * 
     * @param integer $goodId
     * @param array $data 
     * @return integer
     */
    public function updateGoodId($goodId, $data)
    {
        if (!count($data)){
            return;
        }
        
        $updated = $this->getEntityManager()->getConnection()->update('goods', $data, ['id' => $goodId]);
        return $updated;
    }    

    /**
     * Быстрое обновление полей товара
     * 
     * @param Application\Entity\Goods $good
     * @param array $data
     * @return integer
     */
    public function updateGood($good, $data)
    {
        if (!count($data)){
            return;
        }
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->update(Goods::class, 'g')
                ->where('g.id = ?1')
                ->setParameter('1', $good->getId())
                ;
        foreach ($data as $key => $value){
            $queryBuilder->set($key, $value);
        }
        
        return $queryBuilder->getQuery()->getResult();        
    }
    
    /**
     * Выборка строк прайса для создания товаров
     * 
     * @param Application\Entity\Rawprice $raw
     * @return array
     */
    public function findGoodsForAccembly($raw)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
                ->from(Rawprice::class, 'r')
                ->where('r.raw = ?1')
                ->andWhere('r.statusGood = ?2')
                ->andWhere('r.status = ?3')
                ->setParameter('1', $raw->getId())
                ->setParameter('2', Rawprice::GOOD_NEW)
                ->setParameter('3', Rawprice::STATUS_PARSED)
                ->setMaxResults(100000)
                ;

//var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();        
        
    }

    /**
     * Запрос по товарам по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllGoods($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c', 'p')
            ->from(Goods::class, 'c')
            ->join('c.producer', 'p', 'WITH')    
                ;

        if (is_array($params)){
            if (isset($params['producer'])){
                $queryBuilder->where('c.producer = ?1')
                    ->setParameter('1', $params['producer']->getId())
                        ;
            }
            if (isset($params['unknownProducer'])){
                $queryBuilder
                    ->join('c.articles', 'r', 'WITH')
                    ->andWhere('r.unknownProducer = ?2')
                    ->setParameter('2', $params['unknownProducer']->getId())
                        ;
            }
            if (isset($params['q'])){
                if ($params['q']){
                    $queryBuilder->andWhere('c.code like :search')
                        ->setParameter('search', '%' . $params['q'] . '%')                            
                        ;
                } else {
                    $queryBuilder
                        ->orderBy('c.id', 'DESC')    
                        ->setMaxResults(100)    
                     ;                    
                }    
            }
            if (isset($params['next1'])){
                $queryBuilder->andWhere('c.code > ?3')
                    ->setParameter('3', $params['next1'])
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->andWhere('c.code < ?4')
                    ->setParameter('4', $params['prev1'])
                    ->orderBy('c.code', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('c.'.$params['sort'], $params['order']);                
            }            
        }
//var_dump($queryBuilder->getQuery()->getDQL()); exit;
        return $queryBuilder->getQuery();
    }
    
    /**
     * Количество записей в прайсах с этим товара
     * 
     * @param Application\Entity\Goods $goods
     * 
     * @return object
     */
    public function rawprices($goods)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
            ->from(Rawprice::class, 'r')
            ->where('r.good = ?1')
            //->andWhere('r.status = ?2')
            ->setParameter('1', $goods->getId()) 
            ->orderBy('r.status')    
            //->setParameter('2', Rawprice::STATUS_PARSED)    
                ;
        //var_dump($queryBuilder->getQuery()->getDQL());
        return $queryBuilder->getQuery()->getResult();    
    }
    
    /**
     * Выборка из прайсов по id товара и id поставщика 
     * @param array $params
     * @return object
     */
    public function randRawpriceBy($params)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
            ->from(Rawprice::class, 'r')
            ->join('r.raw', 'w')    
            ->where('r.good = ?1')
            ->andWhere('w.supplier = ?2')
            ->andWhere('r.status = ?3')
            ->setParameter('1', $params['good'])    
            ->setParameter('2', $params['supplier'])    
            ->setParameter('3', Rawprice::STATUS_PARSED)
            ->setMaxResults(5)
            //->orderBy('rand()')    
                ;
        return $queryBuilder->getQuery()->getResult();    
        
    }    
    
    
    public function searchByName($search){

        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('g, p')
            ->from(Goods::class, 'g')
            ->join("g.producer", 'p', 'WITH') 
            ->where('g.name like :search')    
            ->orderBy('g.name')
            ->setParameter('search', '%' . $search . '%')
                ;
        return $queryBuilder->getQuery();
    }
    
    public function searchNameForSearchAssistant($search)
    {        
        return $this->searchByName($search)->getResult();
    }  
    
    /**
     * @param Apllication\Entity\Goods $good
     */
    public function findGoodRawprice($good)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Rawprice::class, 'c')
            ->where('c.good = ?1')    
            ->orderBy('c.id')
            ->setParameter('1', $good->getId())    
                ;

        return $queryBuilder->getQuery();
    }
    
    
    public function getMaxPrice($good)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(Rawprice::class, 'r')
            ->select('MAX(r.price) as price')
            ->where('r.good = ?1')    
            ->groupBy('r.good')
            ->setParameter('1', $good->getId())
                ;
        return $queryBuilder->getQuery()->getResult();
        
    }
       
    /**
     * Найти товары для удаления
     * 
     * @return object
     */
    public function findGoodsForDelete()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->addSelect('count(a.id) as articleCount')    
            ->from(Goods::class, 'g')
            ->leftJoin('g.articles', 'a')
            ->groupBy('g.id')
            ->having('articleCount = 0')
                ;
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }

    
    /**
     * Найти товары для обновления AplId
     * 
     * @return object
     */
    public function findGoodsForUpdateAplId()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->where('g.aplId = 0')
            ->setMaxResults(10000)    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }
    
    /**
     * Найти товары для обновления машин по апи текдока
     * 
     * @return object
     */
    public function findGoodsForUpdateCar()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->where('g.statusCar = ?1')
            ->setParameter('1', Goods::GROUP_FOR_UPDATE)    
            ->setMaxResults(2000)    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }
    
    /**
     * Найти машины товара
     * 
     * @param Application\Entity\Goods $good
     * @return object
     */
    public function findCars($good)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('c')
            ->from(\Application\Entity\Car::class, 'c')
            ->join('c.goods', 'g')
            ->where('g.id = ?1')    
            ->setParameter('1', $good->getId())
            ;
        
        return $queryBuilder->getQuery();            
    }
    
    
    /**
     * Добавление машины к товару
     * 
     * @param Application\Entity\Goods $good
     * @return integer
     */
    public function addGoodCar($good, $car)
    {
       $inserted = $this->getEntityManager()->getConnection()->insert('good_car', ['good_id' => $good->getId(), 'car_id' => $car->getId()]);
       return $inserted;        
    }

    /**
     * Удаления машин товара
     * 
     * @param Application\Entity\Goods $good
     * @return integer
     */
    public function removeGoodCars($good)
    {
        $deleted = $this->getEntityManager()->getConnection()->delete('good_car', ['good_id' => $good->getId()]);
        return $deleted;        
    }
    
    /**
     * Удаление картинок товара
     * 
     * @param Applcation\Entity\Goods $good
     * @param integer $status
     * @return integer
     */
    public function removeGoodImage($good, $status = null)
    {
        $where = [
            'good_id' => $good->getId(),
        ];
        
        if ($status){
            $where['status'] = $status;
        }
        
        
        $deleted = $this->getEntityManager()->getConnection()->delete('images', $where);
        return $deleted;        
        
    }
    
    /**
     * Обновить количество машин в товаре
     * @return null
     */
    public function updateGoodCarCount()
    {
        set_time_limit(900);
        ini_set('memory_limit', '2048M');
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g.id, count(c.id) as carCount')
            ->from(Goods::class, 'g')
            ->leftJoin('g.cars', 'c')  
            ->groupBy('g.id')
            ;
                
        $goodIds = $queryBuilder->getQuery()->getResult();
        
        foreach ($goodIds as $row){
            $this->getEntityManager()->getConnection()->update('goods', ['car_count' => $row['carCount']], ['id' => $row['id']]);
        }      
        
        return;        
    }

    
}
