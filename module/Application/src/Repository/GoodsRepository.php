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
use Application\Filter\ArticleCode;



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
     * @param Goods $good
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
     * @param Rawprice $raw
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
                //->setMaxResults(100000)
                ;

//var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();        
        
    }

    /**
     * Выборка строк прайса для создания товаров
     * 
     * @param Rawprice $raw
     * @return array
     */
    public function findRawpriceForUpdatePrice($raw)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
                ->from(Rawprice::class, 'r')
                ->where('r.raw = ?1')
                ->andWhere('r.statusPrice = ?2')
                ->andWhere('r.status = ?3')
                ->setParameter('1', $raw->getId())
                ->setParameter('2', Rawprice::PRICE_NEW)
                ->setParameter('3', Rawprice::STATUS_PARSED)
                //->setMaxResults(50000)
                ;

//var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();        
        
    }

    /**
     * Получить последний ид товаров
     * 
     * @param array $params
     * @return array
     */
    public function findLastGoodId($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('max(g.id) as maxId')
                ->from(Goods::class, 'g')
                ;
        
        if (is_array($params)){
            if (isset($params['producer'])){
                return 0;
            }
            if (isset($params['producerId'])){
                return 0;
            }
            if (isset($params['groupId'])){
                return 0;
            }
        }
        
        return $queryBuilder->getQuery()->getOneOrNullResult();        
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
//            ->distinct()    
            ->from(Goods::class, 'c')
            ->join('c.producer', 'p', 'WITH')    
                ;

        if (is_array($params)){
            if (isset($params['producer'])){
                $queryBuilder->where('c.producer = ?1')
                    ->setParameter('1', $params['producer']->getId())
                        ;
            }
            if (isset($params['producerId'])){
                if ($params['producerId']){
                    $queryBuilder->andWhere('c.producer = ?2')
                        ->setParameter('2', $params['producerId'])
                     ;
                }    
            }
            if (isset($params['unknownProducer'])){
                $queryBuilder
                    ->join('c.articles', 'r', 'WITH')
                    ->andWhere('r.unknownProducer = ?3')
                    ->setParameter('3', $params['unknownProducer']->getId())
                        ;
            }
            if (isset($params['q'])){
                $codeFilter = new ArticleCode();
                $q = $codeFilter->filter($params['q']);
                if ($q){
                    $orX = $queryBuilder->expr()->orX(
//                            $queryBuilder->expr()->eq('c.code', '?4'),
                            $queryBuilder->expr()->eq('o.oe', '?4')    
                        );
                    $queryBuilder->join('c.oems', 'o')
                        ->andWhere($orX) 
                        ->setParameter('4', $q)    
                        ;
                } else {
                    $lastGood = $this->findLastGoodId($params);   
                    $queryBuilder
                        ->andWhere('c.id > :lastId')   
                        ->orderBy('c.id', 'DESC')    
                        ->setMaxResults(100)    
                        ->setParameter('lastId', $lastGood['maxId'] - 1000)    
                     ;                    
                }    
            }
            if (isset($params['next1'])){
                $queryBuilder->andWhere('c.code > ?5')
                    ->select('c')
                    ->resetDQLPart('join')    
                    ->setParameter('5', $params['next1'])
                    ->orderBy('c.code', 'ASC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->andWhere('c.code < ?6')
                    ->select('c')
                    ->resetDQLPart('join')    
                    ->setParameter('6', $params['prev1'])
                    ->orderBy('c.code', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['groupId'])){
                if ($params['groupId']){
                    $queryBuilder->andWhere('c.genericGroup = ?7')
                        ->setParameter('7', $params['groupId'])
                     ;
                }    
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('c.'.$params['sort'], $params['order']);                
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }
    
    /**
     * Запрос на выборку товаров для экспорта
     * 
     * @param int $statusRawpriceEx
     * @param array $params
     * @return object
     */
    public function findForRawpriceEx($statusRawpriceEx, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('g.id', 'g.dateEx')
                ->from(Goods::class, 'g')
                ->where('g.statusRawpriceEx = ?1')
                ->andWhere('g.aplId > 0')
                ->setParameter('1', $statusRawpriceEx)
                ;
        if (is_array($params)){
            if (isset($params['goodId'])){
                $queryBuilder->andWhere('g.id = ?2')
                        ->setParameter('2', $params['goodId']);
            }
            if (isset($params['limit'])){
                $queryBuilder->setMaxResults($params['limit']);
            }
        }
        
        return $queryBuilder->getQuery();        
    }
    
    /**
     * Количество записей в прайсах с этим товара
     * 
     * @param Goods $goods
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
     * Строки прайсов этого товара
     * 
     * @param Goods|int $good
     * @param array $params
     * 
     * @return object
     */
    public function rawpriceArticlesEx($good, $params=null)
    {
        if (is_numeric($good)){
            $goodId = $good;
        } else {
            $goodId = $good->getId();
        }
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
            ->from(Goods::class, 'g')
            ->join('g.articles', 'a')
            ->join(Rawprice::class, 'r', 'WITH', 'r.code = a.id')    
            ->where('g.id = ?1')
            ->andWhere('g.aplId > 0')    
            ->setParameter('1', $goodId) 
                ;
        
        if (is_array($params)){
            if (isset($params['status'])){
                $queryBuilder->andWhere('r.status = ?2')
                        ->setParameter('2', $params['status'])
                        ;
            }
            if (isset($params['statusEx'])){
                $queryBuilder->andWhere('r.statusEx = ?3')
                        ->setParameter('3', $params['statusEx'])
                        ;
            }
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery()->getResult();    
    }
    
    /**
     * Строки прайсов этого товаров
     * 
     * @param array $params
     * 
     * @return object
     */
    public function rawpriceGoodsEx($params=null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
            ->from(Goods::class, 'g')
            ->join('g.articles', 'a')
            ->join(Rawprice::class, 'r', 'WITH', 'r.code = a.id')   
            ->andWhere('g.aplId > 0')
//            ->orderBy('g.dateEx')    
            ;
        
        if (is_array($params)){
            if (isset($params['goodId'])){
                $queryBuilder->andWhere('g.id = ?1')
                        ->setParameter('1', $params['goodId'])
                        ;
            }
            if (isset($params['statusRawpriceEx'])){
                $queryBuilder->andWhere('g.statusRawpriceEx = ?2')
                        ->setParameter('2', $params['statusRawpriceEx'])
                        ;
            }
            if (isset($params['statusEx'])){
                $queryBuilder->andWhere('r.statusEx = ?3')
                        ->setParameter('3', $params['statusEx'])
                        ;
            }
            if (isset($params['status'])){
                $queryBuilder->andWhere('r.status = ?4')
                        ->setParameter('4', $params['status'])
                        ;
            }
            if (isset($params['limit'])){
                $queryBuilder->setMaxResults($params['limit']);                        
            }
        }
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();    
    }

    /**
     * Строки прайсов этого товара
     * 
     * @param Goods $good
     * 
     * @return object
     */
    public function rawpriceArticles($good)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
            ->from(Goods::class, 'g')
            ->join('g.articles', 'a')
            ->join(Rawprice::class, 'r', 'WITH', 'r.code = a.id')    
            ->where('g.id = ?1')
            ->andWhere('r.status = ?2')
            ->setParameter('1', $good->getId()) 
            ->setParameter('2', Rawprice::STATUS_PARSED)    
                ;
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
     * @param Goods $good
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
     * Выбока картинок товара
     * 
     * @param Goods $good
     * @return query
     */
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
     * @return \Doctrine\ORM\Query
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
            //->setMaxResults(10000)    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Найти товары для обновления группы
     * 
     * @return object
     */
    public function findGoodsForUpdateGroupAplId()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->where('g.aplId != 0')
            ->andWhere('g.groupApl = ?1')
            ->setParameter('1', Goods::DEFAULT_GROUP_APL_ID)    
            //->setMaxResults(10000)    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Найти товары для обновления прайсов
     * 
     * @return object
     */
    public function findGoodsForUpdateRawprice()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->andWhere('g.aplId > 0')    
            ->andWhere('g.statusRawpriceEx = ?1')
            ->setParameter('1', Goods::RAWPRICE_EX_NEW)    
            ->setMaxResults(25000)    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }
    
    /**
     * Найти товары для обновления номеров
     * 
     * @return object
     */
    public function findGoodsForUpdateOem()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->andWhere('g.aplId > 0')    
            ->andWhere('g.statusOemEx = ?1')
            ->setParameter('1', Goods::OEM_EX_NEW)    
            //->setMaxResults(10000)    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Найти товары для обновления картинок
     * 
     * @return object
     */
    public function findGoodsForUpdateImg()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->andWhere('g.aplId > 0')    
            ->andWhere('g.statusImgEx = ?1')
            ->setParameter('1', Goods::IMG_EX_NEW)    
//            ->setMaxResults(10000)    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Найти товары для обновления машин в апл
     * 
     * @return object
     */
    public function findGoodsForUpdateCar()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->where('g.statusCarEx = ?1')
            ->andWhere('g.groupApl != ?2')    
            ->andWhere('g.aplId > 0')    
            ->setParameter('1', Goods::CAR_EX_NEW)    
            ->setParameter('2', Goods::DEFAULT_GROUP_APL_ID)    
            //->setMaxResults(10000) 
                
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
    }    
    
    /**
     * Найти товары для обновления групп
     * 
     * @return object
     */
    public function findGoodsForUpdateGroup()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->andWhere('g.aplId > 0')    
            ->andWhere('g.statusGroupEx = ?1')
            ->setParameter('1', Goods::GROUP_EX_NEW)    
//            ->setMaxResults(10000)    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Найти товары для обновления атрибутов
     * 
     * @return object
     */
    public function findGoodsForUpdateAttribute()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->andWhere('g.aplId > 0')    
            ->andWhere('g.statusAttrEx = ?1')
            ->setParameter('1', Goods::ATTR_EX_NEW)    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();//->iterate();            
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
     * Количество товара группой Апл
     * 
     * @return integer
     */
    public function findAplGroups()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('count(g.id) as aplGroupCount')
            ->from(Goods::class, 'g')
            ->where('g.groupApl != ?1')
            ->setParameter('1', Goods::DEFAULT_GROUP_APL_ID)    
            ;
        
        $data = $queryBuilder->getQuery()->getResult();
        
        foreach ($data as $row){
            return $row['aplGroupCount'];
        }
        
        return;
    }
    
    /**
     * Найти товары для обновления машин по апи текдока
     * 
     * @return object
     */
    public function findGoodsForUpdateCarTd()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->where('g.statusCar = ?1')
            ->setParameter('1', Goods::CAR_FOR_UPDATE)    
            //->setMaxResults(2000) 
                
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Найти машины товара
     * 
     * @param Goods $good
     * @param array $params
     * @return object
     */
    public function findCars($good, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('c')
            ->from(\Application\Entity\Car::class, 'c')
            ->join('c.goods', 'g')
            ->where('g.id = ?1')    
            ->setParameter('1', $good->getId())
            ;
        if (is_array($params)){
            if (isset($params['constructionFrom'])){
                $queryBuilder->join('c.model', 'm')
                        ->andWhere('m.constructionFrom > ?2')
                        ->setParameter('2', $params['constructionFrom'])
                        ;
            }
            if (isset($params['limit'])){
                $queryBuilder->setMaxResults($params['limit']);
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('c.'.$params['sort'], $params['order']);                
            }            
        }

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
            //->setMaxResults(2000)    
            //->orderBy('g.statusOem', 'DESC')
            //->addOrderBy('g.id')    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Найти товары для обновления описаний по апи текдока
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
            //->setMaxResults(2000)    
            //->orderBy('g.statusGroup', 'DESC')
            //->addOrderBy('g.id')    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
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
            ->orderBy('g.id')
//            ->setMaxResults(2000)    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
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
//            ->setMaxResults(2000)    
//            ->orderBy('g.statusDescription', 'DESC')
//            ->addOrderBy('g.id')    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
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
            ->orderBy('g.statusGroup', 'DESC')
            ->addOrderBy('g.id')    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }
    
    /**
     * Сброс метки обновления номеров
     * 
     * @return integer
     */
    public function resetUpdateOemTd()
    {
        $updated = $this->getEntityManager()->getConnection()->update('goods', ['status_oem' => Goods::OEM_FOR_UPDATE], ['status_oem' => Goods::OEM_UPDATED]);
        return $updated;
        
    }

    /**
     * Сброс метки обновления групп
     * 
     * @return integer
     */
    public function resetUpdateGroupTd()
    {
        $updated = $this->getEntityManager()->getConnection()->update('goods', ['status_group' => Goods::GROUP_FOR_UPDATE], ['status_group' => Goods::GROUP_UPDATED]);
        return $updated;
        
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
     * Сброс метки обновления машин
     * 
     * @return integer
     */
    public function resetUpdateCarTd()
    {
        $this->getEntityManager()->getConnection()->update('goods', ['status_car' => Goods::CAR_FOR_UPDATE], ['status_car' => Goods::CAR_UPDATED]);
        $this->getEntityManager()->getConnection()->update('goods', ['status_car' => Goods::CAR_FOR_UPDATE], ['status_car' => Goods::CAR_UPDATING]);
        $this->getEntityManager()->getConnection()->update('car', ['update_flag' => 0], ['update_flag' => date('m')]);
        return;        
    }

    /**
     * Сброс метки обновления картинок
     * 
     * @return integer
     */
    public function resetUpdateImageTd()
    {
        $this->getEntityManager()->getConnection()->update('goods', ['status_image' => Goods::IMAGE_FOR_UPDATE], ['status_image' => Goods::IMAGE_UPDATED]);
        return;  
    }

    /**
     * Очистить группу товаров связанных с группой нименований
     * 
     * @param \Application\Entity\GenericGroup $genericGroup
     * @param \Application\Entity\TokenGroup $tokenGroup
     */
    public function resetGoodGenericTokenGroup($genericGroup, $tokenGroup)
    {
        $this->getEntityManager()->getConnection()->update('goods', ['generic_group_id' => 0], 
                ['generic_group_id' => $genericGroup->getId(), 'token_group_id' => $tokenGroup->getId()]);                
        return;
    }
    
    /**
     * Найти атрибуты товара
     * 
     * @param Goods $good
     * @param array $params
     * @return object
     */
    public function findGoodAttributeValues($good, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('gav')
            ->from(\Application\Entity\GoodAttributeValue::class, 'gav')
            ->where('gav.good = ?1')    
            ->setParameter('1', $good->getId())
            ;
        
        if (is_array($params)){
            if (isset($params['status'])){
                $queryBuilder
                        ->join('gav.attribute', 'a')
                        ->andWhere('a.status = ?2')
                        ->setParameter('2', $params['status'])
                        ;
            }
        }
        
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Найти атрибуты товара
     * 
     * @param Goods $good
     * @return object
     */
    public function findGoodAttributeValuesEx($good)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('gav')
            ->from(\Application\Entity\GoodAttributeValue::class, 'gav')
            ->join('gav.attribute', 'a')
            ->join('gav.attributeValue', 'av')    
            ->where('gav.good = ?1')    
            ->andWhere('a.status = ?2') 
            ->andWhere('a.aplId > 0')    
            ->andWhere('av.aplId > 0')    
            ->setParameter('1', $good->getId())
            ->setParameter('2', \Application\Entity\Attribute::STATUS_ACTIVE)
            ;
        
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Добавление значения атрибута к товару
     * 
     * @param Goods $good
     * @param \Application\Entity\Attribute $attribute
     * @param \Application\Entity\AttributeValue $attributeValue
     * 
     * @return integer
     */
    public function addGoodAttributeValue($good, $attribute, $attributeValue)
    {
        $inserted = $this->getEntityManager()->getConnection()->insert('good_attribute_value', 
                [
                    'good_id' => $good->getId(), 
                    'attribute_id' => $attribute->getId(), 
                    'value_id' => $attributeValue->getId(),
                    'status_ex' => \Application\Entity\GoodAttributeValue::EX_TO_TRANSFER,
                ]);
        $this->updateGoodId($good->getId(), ['status_attr_ex' => Goods::ATTR_EX_NEW]);
        return $inserted;        
    }

    /**
     * Удаления атрибутов товара
     * 
     * @param Goods $good
     * @return integer
     */
    public function removeGoodAttributeValues($good)
    {
        $deleted = $this->getEntityManager()->getConnection()->delete('good_attribute_value', ['good_id' => $good->getId()]);
        $this->updateGoodId($good->getId(), ['status_attr_ex' => Goods::ATTR_EX_NEW]);
        return $deleted;        
    }
        
    /**
     * Найти номера для добавления
     * 
     * @param Goods $good
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
     * @param Goods $good
     * @param array $params
     * @return Query
     */
    public function findOems($good, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('o')
            ->from(\Application\Entity\Oem::class, 'o')
            ->where('o.good = ?1')
            ->andWhere('o.source != ?2')    
            ->setParameter('1', $good->getId())
            ->setParameter('2', \Application\Entity\Oem::SOURCE_MY_CODE)    
            ;
        
        if (is_array($params)){
            if (isset($params['q'])){
                $filter = new \Application\Filter\ArticleCode();
                $queryBuilder->andWhere('o.oe like :search')
                    ->setParameter('search', '%' . $filter->filter($params['q']) . '%')
                        ;
            }
            if (isset($params['limit'])){
                if (is_numeric($params['limit'])){
                    $queryBuilder->setMaxResults($params['limit']);
                }    
            }
            if (isset($params['exclude_source'])){
                if (is_numeric($params['exclude_source'])){
                    $queryBuilder->andWhere('o.source != ?3')
                            ->setParameter('3', $params['exclude_source']);
                }    
            }
            if (isset($params['source'])){
                if (is_numeric($params['source'])){
                    $queryBuilder->andWhere('o.source = ?4')
                            ->setParameter('4', $params['source']);
                }    
            }
        }
        
        return $queryBuilder->getQuery();            
    }
    
    
    /**
     * Найти прайсы товара
     * 
     * @param Goods $good
     * @param array $params
     * @return object
     */
    public function findPrice($good, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r.id, s.name as supplier, s.id as supplierId, rr.dateCreated, r.article, c.code, c.id as codeId, r.producer, identity(r.unknownProducer) as producerId, r.goodname, r.rest, r.price, r.statusEx')
            ->from(Goods::class, 'g')
            ->join('g.articles', 'c') 
            ->join('c.rawprice', 'r') 
            ->join('r.raw', 'rr')
            ->join('rr.supplier', 's')    
            ->where('g.id = ?1')    
            ->setParameter('1', $good->getId())
            ;
        
        if (is_array($params)){
            if ($params['status']){
                $queryBuilder->andWhere('r.status = ?2')
                        ->setParameter('2', $params['status'])
                        ;
            }
        }
        
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Добавление машины к товару
     * 
     * @param Goods $good
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
     * @param Goods $good
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
     * @param integer $statusOemEx
     * @return integer
     */
    public function addGoodOem($data, $statusOemEx = 0)
    {
        $inserted = $this->getEntityManager()->getConnection()->insert('oem', $data);
        if ($statusOemEx != Goods::ATTR_EX_NEW){
            $this->updateGoodId($data['good_id'], ['status_oem_ex' => Goods::OEM_EX_NEW]);
        }    
        return $inserted;        
    }
    
    /**
     * Удаления oem товара
     * 
     * @param Goods $good
     * @return integer
     */
    public function removeGoodOem($good)
    {
        $this->getEntityManager()->getConnection()->delete('oem', ['good_id' => $good->getId(), 'source' => \Application\Entity\Oem::SOURCE_TD]);
        $this->getEntityManager()->getConnection()->delete('oem', ['good_id' => $good->getId(), 'source' => \Application\Entity\Oem::SOURCE_SUP]);
        $this->getEntityManager()->getConnection()->delete('oem', ['good_id' => $good->getId(), 'source' => \Application\Entity\Oem::SOURCE_CROSS]);
        return;        
    }
    
    /**
     * Удаления oem товара по источнику
     * 
     * @param Goods $good
     * @param integer $source
     * @return integer
     */
    public function removeGoodSourceOem($good, $source)
    {
        $this->getEntityManager()->getConnection()->delete('oem', ['good_id' => $good->getId(), 'source' => $source]);
        return;        
    }

    /**
     * Удаление картинок товара
     * 
     * @param Goods $good
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
    
    /**
     * Быстрая вставка товара наименования
     * @param array $row 
     * @return integer
     */
    public function insertGoodTitle($row)
    {
        $inserted = $this->getEntityManager()->getConnection()->insert('good_title', $row);
        return $inserted;
    }    
    
    
    /**
     * Выбрать наименования артикулов
     * 
     * @param Goods $good
     * 
     */
    public function findArticleTitles($good)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('at.id')
                ->from(\Application\Entity\Article::class, 'a')
                ->join('a.articleTitles', 'at')
                ->where('a.good = ?1')
                ->setParameter('1', $good->getId())
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Удаление наименований товара
     * 
     * @param Goods $good
     * @return integer
     */
    public function removeGoodTitles($good)
    {
        $where = [
            'good_id' => $good->getId(),
        ];
        
        
        $deleted = $this->getEntityManager()->getConnection()->delete('good_title', $where);
        return $deleted;        
        
    }
    
}
