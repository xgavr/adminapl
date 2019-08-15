<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Article;
use Application\Entity\Goods;
use Application\Entity\OemRaw;
use Application\Entity\Oem;
use Application\Entity\CrossList;
use Application\Entity\Rawprice;
use Application\Filter\ArticleCode;


/**
 * Description of OemRepository
 *
 * @author Daddy
 */
class OemRepository  extends EntityRepository{

    
    public function findOemForInsert($raw)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r.id, r.oem, r.vendor, identity(r.code) as articleId')
                ->from(Rawprice::class, 'r')
                ->where('r.raw = ?1')
                ->andWhere('r.statusOem = ?2')
                ->andWhere('r.status = ?3')
                ->setParameter('1', $raw->getId())
                ->setParameter('2', Rawprice::OEM_NEW)
                ->setParameter('3', Rawprice::STATUS_PARSED)
                ;
        
        return $queryBuilder->getQuery()->getResult();        
    }
    
    /**
     * Быстрая вставка номера
     * @param array $row 
     * @return integer
     */
    public function insertOemRaw($row)
    {
        $inserted = $this->getEntityManager()->getConnection()->insert('oem_raw', $row);
        return $inserted;
    }    
    
    /**
     * Добавление номера к товару
     * 
     * @param Goods $good
     * @param array $oems
     */
    public function addOemToGood($good, $oems, $source = Oem::SOURCE_TD)
    {
        $filter = new ArticleCode();
        $oe = $filter->filter($oems['oeNumber']);
        $oem = $this->getEntityManager()->getRepository(Oem::class)
                ->findOneBy(['good' => $good->getId(), 'oe' => $oe]);
        
        $brandName = null;
        if (isset($oems['brandName'])){
            $brandName = $oems['brandName'];
        }
        
        if ($oem == null){

            $intescetGoodId = NULL;
            if (isset($oems['intescetGoodId'])){
                $intescetGoodId = $oems['intescetGoodId'];
            }

            $data = [
                'good_id' => $good->getId(),
                'oe' => $oe,
                'oe_number' => $oems['oeNumber'],
                'brand_name' => $brandName,
                'status' => Oem::STATUS_ACTIVE,
                'source' => $source,
                'intersect_good_id' => $intescetGoodId,
            ];
//            var_dump($data);
            $this->getEntityManager()->getRepository(Goods::class)
                    ->addGoodOem($data, $good->getStatusOemEx());
            
            if ($source != Oem::SOURCE_INTERSECT && $source != Oem::SOURCE_MY_CODE){
                $this->addIntersectOem($good, $oe);
            }    
        } else {
            if ($source == Oem::SOURCE_TD && $oem->getSource() != Oem::SOURCE_TD){
                $oem->setSource(Oem::SOURCE_TD);
                $oem->setOe($oe);
                $oem->setOeNumber($oems['oeNumber']);
                $oem->setBrandName($brandName);
                $this->getEntityManager()->persist($oem);
                $this->getEntityManager()->flush($oem);
            }
        }
        
        return $oem;
    }
        
    /**
     * Добавить свой артикул в таблицу номеров
     * 
     * @param Goods $good
     */
    public function addMyCodeAsOe($good)
    {
        $oem = $this->addOemToGood($good, [
            'oeNumber' => $good->getCode(), 
            'brandName' => $good->getProducer()->getName(),
          ], Oem::SOURCE_MY_CODE);
            
        $this->getEntityManager()->detach($oem);
        
        return;
    }

    
    
    /**
     * Выборка не привязанных артикулов из прайса
     */
    public function findRawpriceArticle()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
            ->from(Rawprice::class, 'r')
            ->where('r.code is null')
            ->andWhere('r.status = ?1')
            ->setMaxResults(10000)    
            ->setParameter('1', Rawprice::STATUS_PARSED)    
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Выборка не связанных с прайсом артикулов
     */
    public function findEmptyArticle()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('u')
            ->from(Article::class, 'u')
            ->leftJoin(Rawprice::class, 'r')    
            ->where('r.code is null')
            ->andWhere('r.status = ?1')
            ->setParameter('1', Rawprice::STATUS_PARSED)    
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Количество записей в прайсах с этим артикулом
     * 
     * @param Application\Entity\Article $article
     */
    public function rawpriceCount($article)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('count(r.id) as rawpriceCount')
            ->from(Rawprice::class, 'r')                
            ->where('r.code = ?1')
            ->andWhere('r.status = ?2')
            ->groupBy('r.code')    
            ->setParameter('1', $article->getId())    
            ->setParameter('2', Rawprice::STATUS_PARSED)    
                ;
        
        return $queryBuilder->getQuery()->getResult();
        
    }

    /**
     * Выборка артикулов из прайса
     * 
     * @param Application\Entity\Raw $raw
     * @return object
     */
    public function findArticleFromRaw($raw)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r.code, r.unknownProducer')
                ->from(Rawprice::class, 'r')
                ->distinct()
                ->where('r.raw = ?1')
                ->andWhere('r.unknownProducer is not null')
                ->setParameter('1', $raw->getId())
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    
    /**
     * Количество записей в прайсах с этим номером
     * в разрезе поставщиков
     * 
     * @param Application\Entity\Article $oem
     * 
     * @return object
     */
    public function rawpriceCountBySupplier($oem)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('o.id as oemId')
            ->from(OemRaw::class, 'o')
            ->join('o.article', 'a')    
            ->addSelect('a.id as articleId')
            ->join('a.rawprice', 'r')    
            ->addSelect('count(r.id) as rawpriceCount')
            ->join('r.raw', 'w')    
            ->join('w.supplier', 's')
            ->addSelect('s.id as supplierId', 's.name as supplierName')    
            ->where('o.code = ?1')
            ->andWhere('r.status = ?2')
            ->groupBy('s.id')    
            ->addGroupBy('o.id')    
            ->setParameter('1', $oem->getCode())    
            ->setParameter('2', Rawprice::STATUS_PARSED)    
                ;
        //var_dump($queryBuilder->getQuery()->getDQL());
        return $queryBuilder->getQuery()->getResult();    
    }
    
    /**
     * Случайная выборка из прайсов по id артикула и id поставщика 
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
            ->where('r.code = ?1')
            ->andWhere('w.supplier = ?2')
            ->andWhere('r.status = ?3')
            ->setParameter('1', $params['article'])    
            ->setParameter('2', $params['supplier'])    
            ->setParameter('3', Rawprice::STATUS_PARSED)
            ->setMaxResults(5)
            //->orderBy('rand()')    
                ;
        return $queryBuilder->getQuery()->getResult();    
        
    }
    
    /**
     * Количество привязанных строк прайсов к артикулу и не привязанных
     * 
     * @return array
     */
    public function findBindNoBindRawprice()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('case when r.code is null then 0 else 1 end as bind, COUNT(r.id) as bindCount')
            ->from(Rawprice::class, 'r')
            ->where('r.status = ?1')
            ->groupBy('bind')    
            ->setParameter('1', Rawprice::STATUS_PARSED)
                ;
        return $queryBuilder->getQuery()->getResult();            
    }

    /**
     * Найти номера для удаления
     * 
     * @return object
     */
    public function findOemRawForDelete()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('o')
            ->addSelect('count(r.id) as rawpriceCount')    
            ->from(OemRaw::class, 'o')
            ->leftJoin('o.rawprice', 'r')
            ->groupBy('o.id')
            ->having('rawpriceCount = 0')    
            //->setParameter('1', Rawprice::STATUS_PARSED)
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }

    /**
     * Запрос по кроссам по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllOemRaw($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('o, a, u')
            ->from(OemRaw::class, 'o')
            ->join('o.article', 'a') 
            ->join('a.unknownProducer', 'u')    
            ->orderBy('o.code')                
                ;
        
        if (!is_array($params)){
            $params['q'] = 'moreThan';
        } elseif (isset($params['q'])){ 
            if (strlen($params['q']) < 3){
                $params['q'] = 'moreThan';
            }
        }    
        
        if (is_array($params)){
            if (isset($params['q'])){
                $filter = new \Application\Filter\ArticleCode();
                $queryBuilder->where('o.code like :search')
                    ->setParameter('search', '%' . $filter->filter($params['q']) . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->where('o.code > ?1')
                    ->setParameter('1', $params['next1'])
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('o.code < ?2')
                    ->setParameter('2', $params['prev1'])
                    ->orderBy('o.code', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
        }

        return $queryBuilder->getQuery();
    }            
    
    /**
     * Запрос по номерам по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllOem($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('o, g')
            ->from(Oem::class, 'o')
            ->join('o.good', 'g')    
            ->orderBy('o.id', 'DESC')
            ->setMaxResults(100)                
                ;   
        
        if (is_array($params)){
            if ($params['q']){
                $filter = new \Application\Filter\ArticleCode();
                $queryBuilder->where('o.oe like :search')
                    ->setParameter('search', '%' . $filter->filter($params['q']) . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->where('o.oe > ?1')
                    ->setParameter('1', $params['next1'])
                    ->orderBy('o.oe')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('o.oe < ?2')
                    ->setParameter('2', $params['prev1'])
                    ->orderBy('o.oe', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
        }
//var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }            

    /**
     * Найти артикулы производителей для удаления
     * 
     * @return object
     */
    public function findOemForDelete()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('u')
            ->addSelect('count(r.id) as rawpriceCount')    
            ->from(OemRaw::class, 'u')
            ->leftJoin('rawprice_oem_raw', 'r', 'WITH', 'r.code = u.id')
            ->groupBy('u.id')
            ->having('rawpriceCount = 0')    
                ;
        
        return $queryBuilder->getQuery()->getResult();            
    }

    /**
     * Добавить артикул товара в список номеров по кроссу
     * 
     * @param Goods $good
     * @param string $oe
     */
    public function addIntersectOem($good, $oe)
    {
        if (!$good->getGenericGroup()){
            return;
        }
        if ($good->getGenericGroup()->getTdId() <= 0){
            return;
        }

        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
                ->distinct()
                ->from(Goods::class, 'g')
                ->join('g.oems', 'o')
                ->where('g.genericGroup = ?1')
                ->andWhere('g.id != ?2')
                ->andWhere('o.oe = ?3')
                ->setParameter('1', $good->getGenericGroup()->getId())
                ->setParameter('2', $good->getId())
                ->setParameter('3', $oe)
                ;
        
        $data = $queryBuilder->getQuery()->getResult();
        foreach($data as $rowGood){
            $this->addOemToGood($rowGood, [
                'oeNumber' => $good->getCode(), 
                'brandName' => $good->getProducer()->getName(),
                'intescetGoodId' => $good->getId(),
              ], Oem::SOURCE_INTERSECT);
        }
        
        return;
    }    
    
    /**
     * Добавить пересечение номеров товаров
     * 
     * @param Goods $good
     * @return type
     */
    public function addIntersectGood($good)
    {
        $this->removeIntersectOem($good);
        
        $oemsQuery = $this->getEntityManager()->getRepository(Goods::class)
                ->findOems($good);

        $iterable = $oemsQuery->iterate();

        foreach($iterable as $item){
            foreach ($item as $oe){
                $this->addIntersectOem($good, $oe->getOe());
                $this->getEntityManager()->detach($oe);
            }
        }
        
        return;
        
    }

    /**
     * Добавить номера из прайса
     * 
     * @param Goods $good
     */
    public function addSupOem($good)
    {
        $this->getEntityManager()->getRepository(Goods::class)
                ->removeGoodSourceOem($good, Oem::SOURCE_SUP);
        
        $oemsRaw = $this->getEntityManager()->getRepository(Goods::class)
                ->findOemRaw($good);
        
        foreach ($oemsRaw as $oemRaw){
            if ($oemRaw->getCode()){
                $this->addOemToGood($good, ['oe' => $oemRaw->getCode(), 'oeNumber' => $oemRaw->getFullCode()], Oem::SOURCE_SUP);            
            }    
        }        
    }    
        
    /**
     * Добавить номера из кросса
     * 
     * @param Goods $good
     */
    public function addCrosOem($good)
    {
        $this->getEntityManager()->getRepository(Goods::class)
                ->removeGoodSourceOem($good, Oem::SOURCE_CROSS);
        
        $codeFilter = new ArticleCode();
        $crossList = $this->getEntityManager()->getRepository(CrossList::class)
                ->findBy(['codeId' => $good->getId()]);        
        foreach ($crossList as $line){
            if ($codeFilter->filter($line->getOe())){
                $this->addOemToGood($good, [
                            'oe' => $codeFilter->filter($line->getOe()),
                            'brandName' => $line->getOeBrand(), 
                            'oeNumber' => $line->getOe()
                         ], Oem::SOURCE_CROSS);
            }    
        }
    }        
    
    /**
     * Удаление номеров товара
     * @param Goods $good
     * @param integer $status
     * 
     */
    public function removeAllGoodOem($good)
    {
        $oemsQuery = $this->getEntityManager()->getRepository(Goods::class)
                ->findOems($good);
        
        $iterable = $oemsQuery->iterate();

        foreach($iterable as $item){
            foreach ($item as $oe){
                $this->getEntityManager()->getConnection()->delete('oem', ['id' => $oe->getId()]);        
            }
            unset($item);
        }
        
        $myCodes = $this->getEntityManager()->getRepository(Oem::class)
                ->findBy(['good' => $good->getId()]);
        foreach ($myCodes as $myCode){
            $this->getEntityManager()->getConnection()->delete('oem', ['id' => $myCode->getId()]);                    
        }
        
        return;
    }

    
    /**
     * Удаление пересечений номеров товара
     * 
     * @param Goods $good
     */
    public function removeIntersectOem($good)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('o')
            ->from(Oem::class, 'o')
            ->where('o.intersectGoodId = ?1')    
            ->setParameter('1', $good->getId())
            ;
                
        $oemsQuery = $queryBuilder->getQuery();            
        
        $iterable = $oemsQuery->iterate();

        foreach($iterable as $item){
            foreach ($item as $oe){
                $this->getEntityManager()->getConnection()->delete('oem', ['id' => $oe->getId()]);        
            }
            unset($item);
        }        
        return;
    }
}
