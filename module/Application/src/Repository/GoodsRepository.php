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
use Application\Entity\OemRaw;

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
       
    public function findImages($good)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('i')
            ->from(\Application\Entity\Images::class, 'i')
            ->where('i.good = ?1')    
            ->setParameter('1', $good->getId())
            ;
        
        return $queryBuilder->getQuery();            
        
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
            ->setMaxResults(5000)    
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
     * Количество товара с Апл ид
     * 
     * @return integer
     */
    public function findAplIds()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('count(g.id) as aplIdsCount')
            ->from(Goods::class, 'g')
            ->where('g.aplId > 0')    
            ;
        
        $data = $queryBuilder->getQuery()->getResult();
        
        foreach ($data as $row){
            return $row['aplIdsCount'];
        }
        
        return;
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
            ->setParameter('1', Goods::CAR_FOR_UPDATE)    
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
     * Найти товары для обновления групп по апи текдока
     * 
     * @return object
     */
    public function findGoodsForUpdateGroupTd()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->where('g.statusGroup = ?1')
            ->setParameter('1', Goods::GROUP_FOR_UPDATE)    
            ->setMaxResults(2000)    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }
    
    /**
     * Найти товары для обновления групп по апи текдока
     * 
     * @return object
     */
    public function findGoodsForUpdateDescriptionTd()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->where('g.statusDescription = ?1')
            ->setParameter('1', Goods::DESCRIPTION_FOR_UPDATE)    
            ->setMaxResults(2000)    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }
    
    /**
     * Найти товары для обновления номеров по апи текдока
     * 
     * @return object
     */
    public function findGoodsForUpdateOemTd()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->where('g.statusOem = ?1')
            ->setParameter('1', Goods::OEM_FOR_UPDATE)    
            ->setMaxResults(2000)    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }
    
    /**
     * Найти товары для обновления картинок по апи текдока
     * 
     * @return object
     */
    public function findGoodsForUpdateImageTd()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->where('g.statusImage = ?1')
            ->setParameter('1', Goods::IMAGE_FOR_UPDATE)    
            ->setMaxResults(2000)    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }
    
    /**
     * Найти товары для обновления атрибутов по апи текдока
     * 
     * @return object
     */
    public function findGoodsForUpdateAttributesTd()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->where('g.statusDescription = ?1')
            ->setParameter('1', Goods::DESCRIPTION_FOR_UPDATE)    
            ->setMaxResults(2000)    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }
    
    /**
     * Сброс метки обновления описаний
     * 
     * @return integer
     */
    public function resetUpdateAttributeTd()
    {
        $updated = $this->getEntityManager()->getConnection()->update('goods', ['status_description' => Goods::DESCRIPTION_FOR_UPDATE], ['status_description' => Goods::DESCRIPTION_UPDATED]);
        return $updated;
        
    }


    
    /**
     * Найти атрибуты товара
     * 
     * @param \Application\Entity\Goods $good
     * @return object
     */
    public function findGoodAttributeValues($good)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('a')
            ->from(\Application\Entity\GoodAttributeValue::class, 'a')
            ->where('a.good = ?1')    
            ->setParameter('1', $good->getId())
            ;
        
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Добавление значения атрибута к товару
     * 
     * @param \Application\Entity\Goods $good
     * @param \Application\Entity\Attribute $attribute
     * @param \Application\Entity\AttributeValue $attributeValue
     * 
     * @return integer
     */
    public function addGoodAttributeValue($good, $attribute, $attributeValue)
    {
       $inserted = $this->getEntityManager()->getConnection()->insert('good_attribute_value', ['good_id' => $good->getId(), 'attribute_id' => $attribute->getId(), 'value_id' => $attributeValue->getId()]);
       return $inserted;        
    }

    /**
     * Удаления атрибутов товара
     * 
     * @param \Application\Entity\Goods $good
     * @return integer
     */
    public function removeGoodAttributeValues($good)
    {
        $deleted = $this->getEntityManager()->getConnection()->delete('good_attribute_value', ['good_id' => $good->getId()]);
        return $deleted;        
    }
    
    
    /**
     * Найти номера для добавления
     * 
     * @param Application\Entity\Goods $good
     * @return array
     */
    public function findOemRaw($good)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('o')
            ->from(Goods::class, 'g')
            ->join('g.articles', 'a')    
            ->join(OemRaw::class, 'o', 'WITH', 'o.article = a.id')    
            ->where('g.id = ?1')
            ->andWhere('o.code != ?2')    
            ->setParameter('1', $good->getId())
            ->setParameter('2', OemRaw::LONG_CODE)    
                ;
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();                    
    }

    /**
     * Найти номера товара
     * 
     * @param \Application\Entity\Goods $good
     * @param array $params
     * @return object
     */
    public function findOems($good, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('o')
            ->from(\Application\Entity\Oem::class, 'o')
            ->where('o.good = ?1')    
            ->setParameter('1', $good->getId())
            ;
        
        if (is_array($params)){
            if ($params['q']){
                $filter = new \Application\Filter\ArticleCode();
                $queryBuilder->andWhere('o.oe like :search')
                    ->setParameter('search', '%' . $filter->filter($params['q']) . '%')
                        ;
            }
        }
        
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
     * Добавление номера к товару
     * 
     * @param array $data
     * @return integer
     */
    public function addGoodOem($data)
    {
       $inserted = $this->getEntityManager()->getConnection()->insert('oem', $data);
       return $inserted;        
    }

    /**
     * Удаления oem товара
     * 
     * @param Application\Entity\Goods $good
     * @return integer
     */
    public function removeGoodOem($good)
    {
        $deleted = $this->getEntityManager()->getConnection()->delete('oem', ['good_id' => $good->getId(), 'source' => \Application\Entity\Oem::SOURCE_TD]);
        $deleted = $this->getEntityManager()->getConnection()->delete('oem', ['good_id' => $good->getId(), 'source' => \Application\Entity\Oem::SOURCE_SUP]);
        $deleted = $this->getEntityManager()->getConnection()->delete('oem', ['good_id' => $good->getId(), 'source' => \Application\Entity\Oem::SOURCE_CROSS]);
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
        set_time_limit(1800);
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
