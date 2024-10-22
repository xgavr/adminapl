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
use Admin\Filter\TransferName;
use Application\Entity\Bid;
use Application\Entity\Selection;


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
        $queryBuilder->select('r')
                ->from(Rawprice::class, 'r')
                ->where('r.raw = ?1')
                ->andWhere('r.statusOem = ?2')
                ->andWhere('r.status = ?3')
                ->setParameter('1', $raw->getId())
                ->setParameter('2', Rawprice::OEM_NEW)
                ->setParameter('3', Rawprice::STATUS_PARSED)
                ;
        
        return $queryBuilder->getQuery();        
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
     * @param integer $goodId
     * @param array $oems
     * @param integer $source
     * @param integer $supplierId
     */
    public function addOemToGood($goodId, $oems, $source = Oem::SOURCE_TD, $supplierId = null)
    {
        $filter = new ArticleCode();
        $oe = $filter->filter($oems['oeNumber']);
        
        if (is_numeric($supplierId) && $source == Oem::SOURCE_IID){
            $oe = $supplierId.'@'.$oe;
        }
        
        $oem = $this->getEntityManager()->getRepository(Oem::class)
                ->findOneBy(['good' => $goodId, 'oe' => $oe]);
        
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
                'good_id' => $goodId,
                'oe' => $oe,
                'oe_number' => $oems['oeNumber'],
                'brand_name' => $brandName,
                'status' => Oem::STATUS_ACTIVE,
                'source' => $source,
                'intersect_good_id' => $intescetGoodId,
            ];
//            var_dump($data);
            $this->getEntityManager()->getRepository(Goods::class)
                    ->addGoodOem($data);
            
            $this->getEntityManager()->getRepository(Goods::class)
                    ->updateGoodId($goodId, ['status_oem_ex' => Goods::OEM_EX_NEW]);            
        } else {
            if ($source == Oem::SOURCE_TD && $oem->getSource() != Oem::SOURCE_TD){
                $this->getEntityManager()->getConnection()->update('oem', 
                        [
                            'oe' => $oe, 
                            'oe_number' => $oems['oeNumber'],
                            'brand_name' => $brandName,
                            'source' => Oem::SOURCE_TD,
                        ], 
                        ['id' => $oem->getId()]);
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
        $oem = $this->addOemToGood($good->getId(), [
            'oeNumber' => $good->getCode(), 
            'brandName' => $good->getProducer()->getName(),
          ], Oem::SOURCE_MY_CODE);
        
        if ($oem){    
            $this->getEntityManager()->detach($oem);
        }    
        
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

        $queryBuilder->select('o as oem, identity(o.good) as goodId')
            ->from(Oem::class, 'o')
            ->orderBy('o.id', 'DESC')
            ->setMaxResults(100)                
                ;   
        
        if (is_array($params)){
            if (isset($params['source'])){
                if (is_numeric($params['source'])){
                    $queryBuilder->andWhere('o.source = ?1')
                            ->setParameter('1', $params['source']);
                }    
            }
            if (!empty($params['q'])){                
                $filter = new \Application\Filter\ArticleCode();
                $queryBuilder->andWhere('o.oe like :search')
                    ->setParameter('search', $filter->filter($params['q']))
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->where('o.oe > ?2')
                    ->setParameter('2', $params['next1'])
                    ->orderBy('o.oe')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('o.oe < ?3')
                    ->setParameter('3', $params['prev1'])
                    ->orderBy('o.oe', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['mycode'])){
                if (!$params['mycode']){
                    $queryBuilder->andWhere('o.source != ?4')
                        ->setParameter('4', Oem::SOURCE_MY_CODE)
                            ;
                }    
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
                ->andWhere('o.source = ?4')
                ->setParameter('1', $good->getGenericGroup()->getId())
                ->setParameter('2', $good->getId())
                ->setParameter('3', $oe)
                ->setParameter('4', Oem::SOURCE_TD)
                ;
        
        $data = $queryBuilder->getQuery()->getResult();
        foreach($data as $rowGood){
            $this->addOemToGood($rowGood->getId(), [
                'oeNumber' => $good->getCode(), 
                'brandName' => $good->getProducer()->getName(),
                'intescetGoodId' => $good->getId(),
              ], Oem::SOURCE_INTERSECT);
            
            $this->addOemToGood($good->getId(), [
                'oeNumber' => $rowGood->getCode(), 
                'brandName' => $rowGood->getProducer()->getName(),
                'intescetGoodId' => $rowGood->getId(),
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
        $this->removeIntersectOem($good->getId());
        
        $oemsQuery = $this->getEntityManager()->getRepository(Goods::class)
                ->findOems($good->getId());

        $iterable = $oemsQuery->iterate();

        foreach($iterable as $item){
            foreach ($item as $oe){
                if ($oe->getSource() != Oem::SOURCE_INTERSECT && $oe->getSource() != Oem::SOURCE_MY_CODE){
                    $this->addIntersectOem($good, $oe->getOe());
                }
            }    
        }

        $this->getEntityManager()->getConnection()->update('goods', ['status_oem' => Goods::OEM_UPDATED], ['id' => $good->getId()]);
        
        return;
        
    }

    /**
     * Добавить номера из прайса
     * 
     * @param integer $goodId
     */
    public function addSupOem($goodId)
    {
        $this->getEntityManager()->getRepository(Goods::class)
                ->removeGoodSourceOem($goodId, Oem::SOURCE_SUP);
        
        $oemsRaw = $this->getEntityManager()->getRepository(Goods::class)
                ->findOemRaw($goodId);
        
        foreach ($oemsRaw as $oemRaw){
            if ($oemRaw->getCode()){
                $this->addOemToGood($goodId, ['oe' => $oemRaw->getCode(), 'oeNumber' => $oemRaw->getFullCode()], Oem::SOURCE_SUP);            
            }    
        }        
    }    
        
    /**
     * Добавить номера из кросса
     * 
     * @param integer $goodId
     */
    public function addCrossOem($goodId)
    {
        $this->getEntityManager()->getRepository(Goods::class)
                ->removeGoodSourceOem($goodId, Oem::SOURCE_CROSS);
        
        $codeFilter = new ArticleCode();
        $crossList = $this->getEntityManager()->getRepository(CrossList::class)
                ->findBy(['codeId' => $goodId]);        
        foreach ($crossList as $line){
            if ($codeFilter->filter($line->getOe())){
                $this->addOemToGood($goodId, [
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
        return $this->getEntityManager()->getRepository(Goods::class)
                ->deleteGoodOem($good->getId());        
    }

    /**
     * УСТАРЕЛО
     * Можно удалить номер
     * @param Oem $oem
     * @return bool
     */
    public function allowDeleteOem($oem)
    {
        $bidCount = $this->getEntityManager()->getRepository(Bid::class)
                ->count(['oe' => $oem->getOe()]);
        if ($bidCount){
            return false;
        }
        $selectionCount = $this->getEntityManager()->getRepository(Selection::class)
                ->count(['oe' => $oem->getOe()]);
        if ($selectionCount){
            return false;
        }
        
        return true;
    }
    
    /**
     * Удаление пересечений номеров товара
     * 
     * @param integer $goodId
     */
    public function removeIntersectOem($goodId)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('o')
            ->from(Oem::class, 'o')
            ->where('o.intersectGoodId = ?1')    
            ->setParameter('1', $goodId)
            ;

        $oemsQuery = $queryBuilder->getQuery();            

        $iterable = $oemsQuery->iterate();

        $k = 0;
        foreach($iterable as $item){
            foreach ($item as $oe){
                if ($this->allowDeleteOem($oe)){
                    $this->getEntityManager()->getConnection()->delete('oem', ['id' => $oe->getId()]);
                    $entityManager->detach($oe);
                }    
                $k++;
            }
        }        
        if ($k){
            $entityManager->getRepository(Goods::class)
                    ->updateGoodId($goodId, ['status_oem_ex' => Goods::OEM_EX_NEW]);
        }    
        return;
    }
    
    /**
     * Выбрать наименования брендов товара
     * 
     * @param int $goodId
     * @param integer @splice
     * @return string
     */
    public function cars($goodId, $splice = 5)
    {
        $result = [];
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('o.brandName')
                ->distinct()
                ->from(Oem::class, 'o')
                ->where('o.good = ?1')    
                ->andWhere('o.status = ?2')
                ->andWhere('o.source = ?3')
                ->setParameter('1', $goodId)
                ->setParameter('2', Oem::STATUS_ACTIVE)
                ->setParameter('3', Oem::SOURCE_TD)
            ;
                
        $data = $queryBuilder->getQuery()->getResult();            
        
        $transferFilter = new TransferName();
        foreach($data as $row){            
            $result[] = $transferFilter->filter($row['brandName']);
        }        
        
        $out = array_filter($result);
        if (count($out)){
            array_splice($out, $splice);
            return implode(' ', $out);
        }
        
        return;
    }
    
    /**
     * Запрос по oem
     * 
     * @param array $params
     * @return object
     */
    public function querySearchOem($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('o, g, p, tg')
            ->distinct()    
            ->from(Oem::class, 'o')
            ->join('o.good', 'g')
            ->join('g.producer', 'p', 'WITH')    
            ->leftJoin('g.tokenGroup', 'tg') 
            ->andWhere('o.status = :status')
            ->setParameter('status', Oem::STATUS_ACTIVE)    
                ;

        if (is_array($params)){
            if (isset($params['q'])){
                $codeFilter = new ArticleCode();
                $q = $codeFilter->filter($params['q']);

                $searchOpt = Goods::SEARCH_CODE;
                if (isset($params['accurate'])){
                    $searchOpt= $params['accurate'];
                }

                if ($q){
                    switch ($searchOpt){
                        case Goods::SEARCH_OE:
                            $queryBuilder->andWhere('o.oe = :oe') 
                                ->setParameter('oe', $q)    
                                //->addOrderBy('MATCH (o.oe) AGAINST (:field)', 'DESC')    
                                //->setParameter('field', $q)    
                                ;
                            break;    
                        default:
                            $queryBuilder
                                ->andWhere('g.code = :code')                           
                                ->setParameter('code', $q)    
                                ;
                            break;    
                    }
                } else {
                    $queryBuilder
                        ->andWhere('o.id = 0')   
                        ->orderBy('g.id', 'DESC')    
                        ->setMaxResults(100)    
                     ;                    
                }    
            }
            if (isset($params['sort'])){
                $queryBuilder->addOrderBy('o.'.$params['sort'], $params['order']);                
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }
    
}
