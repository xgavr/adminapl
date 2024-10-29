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
use Application\Entity\ArticleTitle;
use Stock\Entity\Movement;
use Application\Entity\Oem;
use Stock\Entity\GoodBalance;
use Application\Entity\GoodSupplier;
use Stock\Entity\Comiss;
use Application\Filter\Lemma;
use Application\Filter\Tokenizer;
use Application\Entity\TokenGroup;
use Application\Entity\Article;
use Application\Entity\ArticleToken;
use Application\Entity\GoodToken;
use Stock\Entity\ComitentBalance;
use GoodMap\Filter\DecodeFoldCode;

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
        
        try {
            $updated = $this->getEntityManager()->getConnection()->update('goods', $data, ['id' => $goodId]);
        } catch (\Doctrine\DBAL\Exception\LockWaitTimeoutException $ex){
            return;
        }    
        return $updated;
    }    

    /**
     * Быстрое обновление полей товара
     * 
     * @param int $goodId
     * @param array $data
     * @return integer
     */
    public function updateGood($goodId, $data)
    {
        if (!count($data)){
            return;
        }
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->update(Goods::class, 'g')
                ->where('g.id = ?1')
                ->setParameter('1', $goodId)
                ;
        foreach ($data as $key => $value){
            $queryBuilder->set("g.$key", $value);
        }
        
        try {
            return $queryBuilder->getQuery()->getResult();        
        } catch (\Doctrine\DBAL\Exception\LockWaitTimeoutException $ex){
            return;
        }    
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
        $queryBuilder->select('r.id as rawpriceId, '
                . 'g.id as goodId, '
                . 'g.datePrice, '
                . 'g.price, '
                . 'g.meanPrice, '
                . 'g.fixPrice, '
                . 'identity(g.genericGroup) as genericGroupId, '
                . 'identity(g.tokenGroup) as tokenGroupId, '
                . 'identity(g.producer) as producerId')
                ->from(Rawprice::class, 'r')
                ->join('r.good', 'g')
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
     * Найди товары токенов
     * 
     * @param string $phrase
     * 
     * @return Goods|null
     */
    public function findTokenGroupByPhrase($phrase)
    {
//        var_dump($phrase); exit;
        $entityManager = $this->getEntityManager();
        $lemmaFilter = new Lemma($entityManager);
        $tokenFilter = new Tokenizer();
        $result = [0];

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('tg.id, tg.name')
            ->distinct()    
            ->from(TokenGroup::class, 'tg')
            ->where('tg.movement > 0')
            ->orderBy('tg.movement', 'DESC')
//            ->setMaxResults(5)    
            ;
        $orX = $queryBuilder->expr()->orX();
                
        $andX = $queryBuilder->expr()->andX();
        $lemms = $lemmaFilter->filter($tokenFilter->filter($phrase));
        if (count($lemms)){                                                
            foreach ($lemms as $k => $words){
                foreach ($words as $key => $word){
                    if ($word){
                        $andX->add($queryBuilder->expr()->like('tg.lemms', '\'%'.$word.'%\''));
                    }    
                }
            }    
        }    
        if ($andX->count()){
            $orX->add($andX);
        }    
        
        if ($orX->count()){
            $queryBuilder->andWhere($orX);
//                            var_dump($queryBuilder->getQuery()->getSQL()); exit;
            $data = $queryBuilder->getQuery()->getResult();
            foreach ($data as $row){
                $result[] = $row['id'];
            }                                
        }
        
        return $result;
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

        $queryBuilder->select('g', 'p', 'tg')
//            ->distinct()    
            ->from(Goods::class, 'g')
            ->join('g.producer', 'p', 'WITH')    
            ->leftJoin('g.tokenGroup', 'tg')    
                ;

        if (is_array($params)){
            if (isset($params['producer'])){
                $queryBuilder->where('g.producer = ?1')
                    ->setParameter('1', $params['producer']->getId())
                        ;
            }
            if (isset($params['producerId'])){
                if ($params['producerId']){
                    $queryBuilder->andWhere('g.producer = ?2')
                        ->setParameter('2', $params['producerId'])
                     ;
                }    
            }
            if (isset($params['unknownProducer'])){
                $queryBuilder
                    ->join('g.articles', 'r', 'WITH')
                    ->andWhere('r.unknownProducer = ?3')
                    ->setParameter('3', $params['unknownProducer']->getId())
                        ;
            }
            if (isset($params['q'])){
                $codeFilter = new ArticleCode();
                $q = $codeFilter->filter($params['q']);

                $searchOpt = Goods::SEARCH_CODE;
                if (isset($params['accurate'])){
                    $searchOpt= $params['accurate'];
                }

                if ($q || $searchOpt == Goods::SEARCH_NAME){
                    switch ($searchOpt){
                        case Goods::SEARCH_APLID:
                            $queryBuilder
                                ->andWhere('g.aplId = :aplId')                           
                                ->setParameter('aplId', $q)    
                                ;
                            break;    
                        case Goods::SEARCH_OE:
                            $orX = $queryBuilder->expr()->orX(
                                    $queryBuilder->expr()->eq('o.oe', '?4')    
                                );
                            $queryBuilder->join('g.oems', 'o')
                                ->andWhere($orX) 
                                ->setParameter('4', $q)    
                                ->addOrderBy('MATCH (g.code) AGAINST (:field)', 'DESC')    
                                ->setParameter('field', $q)    
                                ->andWhere('o.status = :status')
                                ->setParameter('status', Oem::STATUS_ACTIVE)    
                                ;
                            break;    
                        case Goods::SEARCH_NAME:
                            $tg = $this->findTokenGroupByPhrase($params['q']);
                            if (count($tg)){
                                $inX = $queryBuilder->expr()->in('g.tokenGroup', $tg);
                                $queryBuilder
                                        ->andWhere($inX);                
                            }                                    
                            break;    
                        default:
                            $queryBuilder
                                ->andWhere('g.code = :code')                           
                                ->setParameter('code', $q)    
                                ;
                            break;    
                    }
                } else {
                    //$lastGood = $this->findLastGoodId($params);   
                    $queryBuilder
                        //->andWhere('g.id > :lastId')   
                        ->orderBy('g.id', 'DESC')    
                        ->setMaxResults(100)    
                        //->setParameter('lastId', $lastGood['maxId'] - 1000)    
                     ;                    
                }    
            }
            if (isset($params['next1'])){
                $queryBuilder->andWhere('g.code > ?5')
                    ->select('g')
                    ->resetDQLPart('join')    
                    ->setParameter('5', $params['next1'])
                    ->orderBy('g.code', 'ASC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->andWhere('g.code < ?6')
                    ->select('g')
                    ->resetDQLPart('join')    
                    ->setParameter('6', $params['prev1'])
                    ->orderBy('g.code', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['groupId'])){
                if ($params['groupId']){
                    $queryBuilder->andWhere('g.genericGroup = ?7')
                        ->setParameter('7', $params['groupId'])
                     ;
                }    
            }
            if (isset($params['withTokenGroup'])){
                $queryBuilder->select('g')
                    ->resetDQLPart('join')
                        ;
                if ($params['withTokenGroup']){
                    $queryBuilder->andWhere('g.tokenGroup is not null');
                } else {    
                    $queryBuilder->andWhere('g.tokenGroup is null');
                }    
            }
            if (isset($params['sort'])){
                $queryBuilder->addOrderBy('g.'.$params['sort'], $params['order']);                
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }
    
    /**
     * Запрос по поиска
     * 
     * @param array $params
     * @return object
     */
    public function liveSearch($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('g', 'p')
            ->from(Goods::class, 'g')
            ->join('g.producer', 'p', 'WITH')
            ->where('g.id = 0')    
                ;
//        var_dump($params); exit;
        if (is_array($params)){
            if (isset($params['search'])){
                $codeFilter = new ArticleCode();
                $q = $codeFilter->filter($params['search']);
                if ($q){
                    $queryBuilder
                        ->where('g.code like :code')                           
                        ->setParameter('code', $q.'%')    
                            ;
                }    
            }
            if (isset($params['limit'])){
                $queryBuilder->setMaxResults($params['limit']);
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('g.'.$params['sort'], $params['order']);                
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }


    /**
     * Запрос на выборку товаров для экспорта строк прайса
     * 
     * @return object
     */
    public function findForRawpriceEx()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('g')
                ->from(Goods::class, 'g')
                ->andWhere('g.aplId > 0')
                ->andWhere('g.dateEx < ?1')
                ->setParameter('1', date('Y-m-d H:i:s', strtotime("-12 hours")))
//                ->orderBy('g.retailCount', 'DESC')
                ->addOrderBy('g.dateEx', 'ASC')
                ->setMaxResults(50000)
                ;
//        var_dump(date('Y-m-d H:i:s', strtotime("-8 hours"))); exit;
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
            ->from(Rawprice::class, 'r')
            ->join('r.code', 'a')    
            ->where('a.good = ?1')
            ->andWhere('r.status = ?2')
            ->setParameter('1', $good->getId()) 
            ->setParameter('2', Rawprice::STATUS_PARSED)    
                ;
        return $queryBuilder->getQuery()->getResult();    
    }
    
    /**
     * Наименования этого товара
     * 
     * @param int $goodId
     * 
     * @return object
     */
    private function articleTitles($goodId)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('at')
            ->from(Goods::class, 'g')
            ->join('g.articles', 'a')
            ->join(ArticleTitle::class, 'at', 'WITH', 'at.article = a.id')    
            ->where('g.id = ?1')
            ->setParameter('1', $goodId) 
                ;
        
        return $queryBuilder->getQuery()->getResult(2);    
    }
    
    /**
     * Обновить группы токенов и наименований товаров
     * 
     * @param int $goodId
     * @param integer $tokenGroupId
     */
    public function updateTokenGroupGoodArticleTitle($goodId, $tokenGroupId = 0)
    {
        if ($tokenGroupId){
            $titles = $this->articleTitles($goodId);
        
            foreach ($titles as $articleTitle){
                $this->getEntityManager()->getConnection()->update('article_title', 
                    ['token_group_id' => $tokenGroupId], ['id' => $articleTitle['id']]);
                $this->getEntityManager()->getConnection()->update('article_token', 
                    ['token_group_id' => $tokenGroupId], ['title_id' => $articleTitle['id']]);
                $this->getEntityManager()->getConnection()->update('article_bigram', 
                    ['token_group_id' => $tokenGroupId], ['title_id' => $articleTitle['id']]);
            }
        }  
        
        return;
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
            ->from(Goods::class, 'g')
            ->where('g.updWeek != ?1')
            ->setParameter('1', date('W'))    
                ;
        return $queryBuilder->getQuery();            
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
            ->setMaxResults(100000)    
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
            ->setMaxResults(100000)    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Найти товары для обновления наименований
     * 
     * @return object
     */
    public function findGoodsForUpdateName()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->andWhere('g.aplId > 0')    
            ->andWhere('g.statusNameEx = ?1')
            ->setParameter('1', Goods::NAME_EX_NEW)
            ->setMaxResults(100000)    
//            ->andWhere('g.tokenGroup = ?2')
//            ->setParameter('2', 26288) // только воздушный фильтр    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Найти товары для обновления цен
     * 
     * @return object
     */
    public function findGoodsForUpdatePrice()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->andWhere('g.aplId > 0')    
            ->andWhere('g.statusPriceEx = ?1')
//            ->andWhere('g.statusNameEx = ?2')
//            ->andWhere('g.price > 0')
            ->setParameter('1', Goods::PRICE_EX_NEW)
//            ->setParameter('2', Goods::NAME_EX_TRANSFERRED)
            ->setMaxResults(100000)    
                ;
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
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
            ->setMaxResults(100000)    
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
            ->setMaxResults(100000)    
                
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
            ->setMaxResults(100000)    
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
            ->setMaxResults(100000)    
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
            ->andWhere('g.genericGroup > ?2')
            ->setParameter('2', 0)    
            ->setMaxResults(1000) 
                
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
            if (isset($params['constructionFrom']) || isset($params['constructionTo'])){
                $queryBuilder->join('c.model', 'm');                
            }
            if (isset($params['constructionFrom'])){
                $queryBuilder->andWhere('m.constructionFrom > ?2')
                        ->setParameter('2', $params['constructionFrom'])
                        ;
            }
            if (isset($params['constructionTo'])){
                $queryBuilder->andWhere('m.constructionTo > ?3')
                        ->setParameter('3', $params['constructionTo'])
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
            ->setMaxResults(10000)    
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
            ->setMaxResults(5000)    
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
        $queryBuilder->select('g.id as goodId, g.code as code, gg.tdId as genericGroupTdId, tg.id as tokenGroupId')
            ->from(Goods::class, 'g')
            ->join('g.genericGroup', 'gg')    
            ->leftJoin('g.tokenGroup', 'tg')    
            ->where('g.statusOem = ?1')
            ->setParameter('1', Goods::OEM_FOR_UPDATE) 
//            ->orderBy('g.id')
            ->setMaxResults(1000)    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Найти неизвестных производ телей связанных с товаром
     * @param integer $goodId 
     * @return object
     */
    public function findUnknownProducerNames($goodId)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('u.name, u.nameTd')
            ->from(Goods::class, 'g')
            ->join('g.producer', 'p')    
            ->join('p.unknownProducer', 'u')    
            ->where('g.id = ?1')
            ->setParameter('1', $goodId) 
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }

    /**
     * Найти товары для обновления пересечений номеров
     * 
     * @return object
     */
    public function findGoodsForUpdateOemIntersect()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->where('g.statusOem = ?1')
            ->setParameter('1', Goods::OEM_INTERSECT) 
//            ->orderBy('g.id')
            ->setMaxResults(50000)    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
    }

    /**
     * Найти товары для обновления номеров поставщиков и кроссов
     * 
     * @return object
     */
    public function findGoodsForUpdateOemSupCross()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g.id as goodId')
            ->from(Goods::class, 'g')
            ->where('g.statusOem = ?1')
            ->setParameter('1', Goods::OEM_SUP_CROSS) 
//            ->orderBy('g.id')
            ->setMaxResults(50000)    
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
            ->setMaxResults(10000)    
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
            ->setMaxResults(100000)    
            ->orderBy('g.statusGroup', 'DESC')
            ->addOrderBy('g.id')    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }
    
    /**
     * NOT USE
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
     * NOT USE
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
     * NOT USE
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
     * NOT USE
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
     * НЕ ИСПОЛЬЗОВАТЬ
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
     * 
     * Очистить группу товаров связанных с группой наменований
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
     * @param integer $goodId
     * @return array
     */
    public function findOemRaw($goodId)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('o')
            ->from(OemRaw::class, 'o')
            ->join('o.article', 'a')    
            ->where('a.good = ?1')
            ->andWhere('o.code != ?2')    
            ->setParameter('1', $goodId)
            ->setParameter('2', OemRaw::LONG_CODE)    
                ;
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();                    
    }

    /**
     * Найти номера товара
     * 
     * @param integer $goodId
     * @param array $params
     * @return Query
     */
    public function findOems($goodId, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('o')
            ->from(Oem::class, 'o')
                
            ->andWhere('o.source != :myCode')    
            ->andWhere('o.source != :iid')    
            ->andWhere('o.source != 7')    
            ->setParameter('myCode', Oem::SOURCE_MY_CODE)    
            ->setParameter('iid', Oem::SOURCE_IID)    
            ;
        
        if (is_array($params)){
            if (!empty($params['q'])){
                $filter = new \Application\Filter\ArticleCode();
                $queryBuilder->andWhere('o.oe like :search')
                    ->setParameter('search', '%' . $filter->filter($params['q']) . '%')
                        ;
            }
            if (!empty($params['limit'])){
                if (is_numeric($params['limit'])){
                    $queryBuilder->setMaxResults($params['limit']);
                }    
            }
            if (!empty($params['exclude_source'])){
                if (is_numeric($params['exclude_source'])){
                    $queryBuilder->andWhere('o.source != :exc')
                            ->setParameter('exc', $params['exclude_source']);
                }    
            }
            if (!empty($params['source'])){
                if (is_numeric($params['source'])){
                    
                    $queryBuilder->resetDQLPart('where')
                            ->setParameters([])
                            ;
                            
                    $queryBuilder->andWhere('o.source = :source')
                            ->setParameter('source', $params['source']);
                }    
            }
        }
        
        $queryBuilder->andWhere('o.good = :good')
                ->setParameter('good', $goodId)
                ;
        
//        var_dump($queryBuilder->getQuery()->getSql());
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Движения товара
     * 
     * @param Goods $good
     * @param array $params
     * @return Query
     */
    public function movements($good, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('m, o, c, p, s, ot, contact, vt, vtOrder')
            ->from(Movement::class, 'm')
            ->join('m.office', 'o')    
            ->join('m.company', 'c')
            ->leftJoin('m.ptu', 'p', 'WITH', 'm.baseType = '.Movement::DOC_PTU) 
            ->leftJoin('p.supplier', 's')    
            ->leftJoin('m.ot', 'ot', 'WITH', 'm.baseType = '.Movement::DOC_OT) 
            ->leftJoin('ot.comiss', 'contact')    
            ->leftJoin('m.vt', 'vt', 'WITH', 'm.baseType = '.Movement::DOC_VT) 
            ->leftJoin('vt.order', 'vtOrder')    
            ->where('m.good = ?1')
            ->setParameter('1', $good->getId())
//            ->orderBy('m.docStamp','ASC')    
            ;
        
        if (is_array($params)){
            if (!empty($params['sort'])){
                $sort = $params['sort'];
                if ($sort == 'dateOper'){
                    $sort = 'docStamp';
                }
                $queryBuilder->addOrderBy('m.'.$sort, $params['order']);
            }
            if (!empty($params['office'])){
                if (is_numeric($params['office'])){
                    $queryBuilder->andWhere('m.office = ?2')
                        ->setParameter('2', $params['office']);
                }    
            }
            if (!empty($params['startDate'])){
                $queryBuilder->andWhere('m.dateOper >= :startDate')
                        ->setParameter('startDate', $params['startDate']);
            }
            if (!empty($params['endDate'])){
                $queryBuilder->andWhere('m.dateOper <= :endDate')
                        ->setParameter('endDate', $params['endDate']);
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(m.dateOper) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(m.dateOper) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Движения товара
     * 
     * @param Goods $good
     * @param array $params
     * @return Query
     */
    public function movementRest($good, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('m, o')
            ->from(Movement::class, 'm')
            ->join('m.office', 'o')    
            ->where('m.good = ?1')
            ->setParameter('1', $good->getId())
//            ->orderBy('m.dateOper','ASC')    
            ;
        
        if (is_array($params)){
            if (!empty($params['sort'])){
                $queryBuilder->addOrderBy('m.'.$params['sort'], $params['order']);
            }
        }
        
        return $queryBuilder->getQuery();            
    }    
    
    /**
     * Наличие товара
     * 
     * @param array $params
     * @return Query
     */
    public function presence($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('g.id, g.aplId, g.code, g.statusRawpriceEx, g.name, g.retailCount')
                ->addSelect('p.id as producerId, p.name as producerName')
                ->addSelect('off.name as officeName, off.id as officeId')        
                ->addSelect('gb.rest, gb.reserve, gb.delivery, gb.vozvrat, gb.rest-gb.reserve-gb.delivery-gb.vozvrat as available')        
                ->addSelect('tg.name')
                ->addSelect('gs.rest as aplRest')
                ->addSelect('fb.foldName')
                ->addSelect('fb.foldCode')
                ;
        
        $queryBuilder->from(GoodBalance::class, 'gb')
                ->join('gb.good', 'g')
                ->where('gb.rest != 0 and gb.rest-gb.reserve-gb.delivery-gb.vozvrat > 0')    
                ;
        
        if (is_array($params)){
            if (isset($params['accurate'])){
                if ($params['accurate'] == Goods::SEARCH_COMISS){
                    $comiss = $this->getEntityManager()->getRepository(Comiss::class)
                            ->goodInCommiss(['asArray' => 1]);
                    
                    $inX = $queryBuilder->expr()->in('g.id', implode(',', $comiss));
                    $queryBuilder
                            ->resetDQLPart('where')
                            ->andWhere($inX); 
                }                    
            }
            if (isset($params['foldCode'])){
                $foldCodeFilter = new DecodeFoldCode(['entityManager' => $entityManager]);
                $folds = $foldCodeFilter->filter($params['foldCode']);
                
                if (!empty($folds['cell'])){
                    $queryBuilder->andWhere('fb.cell = :cell')
                            ->setParameter('cell', $folds['cell']->getId());                     
                }
                if (!empty($folds['shelf'])){
                    $queryBuilder->andWhere('fb.shelf = :shelf')
                            ->setParameter('shelf', $folds['shelf']->getId());                     
                }
                if (!empty($folds['rack'])){
                    $queryBuilder->andWhere('fb.rack = :rack')
                            ->setParameter('rack', $folds['rack']->getId());                     
                }
            }
            if (isset($params['q'])){                
                $codeFilter = new ArticleCode();
                $q = $codeFilter->filter($params['q']);

                $searchOpt= Goods::SEARCH_CODE;
                if (isset($params['accurate'])){
                    $searchOpt= $params['accurate'];
                }
//                var_dump($q); exit;
                if ($q){
                    
                    $queryBuilder->resetDQLPart('from')
                            ->resetDQLPart('join')
                            ->resetDQLPart('where')
                            ->from(Goods::class, 'g')
                            ->leftJoin('g.goodBalances', 'gb')
                            ;
                
                    switch ($searchOpt){
                        case Goods::SEARCH_APLID:
                            $queryBuilder
                                ->andWhere('g.aplId = :aplId')                           
                                ->setParameter('aplId', $q)    
                                ;
                            break;    
                        case Goods::SEARCH_ID:
                            $queryBuilder
                                ->andWhere('g.id = :id')                           
                                ->setParameter('id', $q)    
                                ;
                            break;    
                        case Goods::SEARCH_OE:
                            $orX = $queryBuilder->expr()->orX(
                                    $queryBuilder->expr()->eq('o.oe', '?4')    
                                );
                            $queryBuilder->join('g.oems', 'o')
                                ->andWhere($orX) 
                                ->setParameter('4', $q)    
                                ;
                            break;    
                        case Goods::SEARCH_NAME: 
                            $tg = $this->findTokenGroupByPhrase($params['q']);
        //                    var_dump($tg); exit;
                            if (count($tg)){
                                $inX = $queryBuilder->expr()->in('tg.id', $tg);
                                $queryBuilder
                                        ->andWhere($inX)
                                        ->andWhere('gb.rest != 0 and gb.rest-gb.reserve-gb.delivery-gb.vozvrat > 0') 
                                        ;                                        
                            }                                    
                            break;
                        default:
                            $queryBuilder
                                ->andWhere('g.code = :code')                           
                                ->setParameter('code', $q)    
                                ;
                            break;    
                    }
                }   
            }
            
            $queryBuilder->join('g.producer', 'p')    
                    ->leftJoin('gb.office', 'off') 
                    ->leftJoin('g.tokenGroup', 'tg')
                    ->leftJoin('g.goodSuppliers', 'gs', 'WITH', 'gs.good=g.id and gs.supplier=7 and gs.update = :update')
                    ->leftJoin('g.foldBalances', 'fb', 'WITH', 'fb.rest != 0 and fb.office = gb.office')
                    ->setParameter('update', date('Y-m-d'))
                 ;   
            
            if (!empty($params['office'])){
                if (is_numeric($params['office'])){
                    $queryBuilder->andWhere('gb.office = :office')
                            ->setParameter('office', $params['office'])    
                            ;
                }    
            }
            
            if (!empty($params['sort'])){
                $queryBuilder->addOrderBy('g.'.$params['sort'], $params['order']);
            }
        }
        
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
    }    
    
    
    /**
     * Наличие товара у комитента
     * 
     * @param array $params
     * @return Query
     */
    public function presenceComitent($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('g.id, g.aplId, g.code, g.statusRawpriceEx, g.name, g.retailCount')
                ->addSelect('p.id as producerId, p.name as producerName')
                ->addSelect('off.name as officeName')        
                ->addSelect('cb.rest, 0 as reserve, 0 as delivery, 0 as vozvrat, cb.rest as available')        
                ->addSelect('tg.name')
                ->addSelect('0 as aplRest')
                ;
        
        $queryBuilder->from(ComitentBalance::class, 'cb')
                ->join('cb.good', 'g')
                ->where('cb.rest != 0')    
                ;
        
        if (is_array($params)){
            if (isset($params['q'])){                
                $codeFilter = new ArticleCode();
                $q = $codeFilter->filter($params['q']);

                $searchOpt= Goods::SEARCH_CODE;
                if (isset($params['accurate'])){
                    $searchOpt= $params['accurate'];
                }
//                var_dump($q); exit;
                if ($q){
                    
                    $queryBuilder->resetDQLPart('from')
                            ->resetDQLPart('join')
                            ->resetDQLPart('where')
                            ->from(Goods::class, 'g')
                            ->leftJoin('g.comitentBalances', 'cb')
                            ;
                
                    switch ($searchOpt){
                        case Goods::SEARCH_APLID:
                            $queryBuilder
                                ->andWhere('g.aplId = :aplId')                           
                                ->setParameter('aplId', $q)    
                                ;
                            break;    
                        case Goods::SEARCH_ID:
                            $queryBuilder
                                ->andWhere('g.id = :id')                           
                                ->setParameter('id', $q)    
                                ;
                            break;    
                        case Goods::SEARCH_OE:
                            $orX = $queryBuilder->expr()->orX(
                                    $queryBuilder->expr()->eq('o.oe', '?4')    
                                );
                            $queryBuilder->join('g.oems', 'o')
                                ->andWhere($orX) 
                                ->setParameter('4', $q)    
                                ;
                            break;    
                        case Goods::SEARCH_NAME: 
                            $tg = $this->findTokenGroupByPhrase($params['q']);
        //                    var_dump($tg); exit;
                            if (count($tg)){
                                $inX = $queryBuilder->expr()->in('tg.id', $tg);
                                $queryBuilder
                                        ->andWhere($inX)
                                        ->andWhere('cb.rest != 0') 
                                        ;                                        
                            }                                    
                            break;
                        default:
                            $queryBuilder
                                ->andWhere('g.code = :code')                           
                                ->setParameter('code', $q)    
                                ;
                            break;    
                    }
                }   
            }
            
            $queryBuilder->join('g.producer', 'p')    
                    ->leftJoin('cb.legal', 'off') 
                    ->leftJoin('g.tokenGroup', 'tg')
                 ;   
            
            if (!empty($params['sort'])){
                $queryBuilder->addOrderBy('g.'.$params['sort'], $params['order']);
            }
        }
        
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
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
            ->orderBy('rr.dateCreated', 'DESC')    
            ;
        
        if (is_array($params)){
            if ($params['status']){
                if (is_numeric($params['status'])){
                    $queryBuilder->andWhere('r.status = ?2')
                            ->setParameter('2', $params['status'])
                            ;
                }    
            }
            if ($params['supplier']){
                if (is_numeric($params['supplier'])){
                    $queryBuilder->andWhere('rr.supplier = ?3')
                            ->setParameter('3', $params['supplier'])
                            ;
                }    
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
     * @return integer
     */
    public function addGoodOem($data)
    {
        try{
            $inserted = $this->getEntityManager()->getConnection()->insert('oem', $data);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $exx){
            $inserted = 0;
        }    
        return $inserted;        
    }
    
    /**
     * Удаление ое по возможности
     * @param integer $goodId
     * @param integer $source
     */
    public function deleteGoodOem($goodId, $source = null)
    {
        $query = ['good' => $goodId];
        if ($source){
            $query['source'] = $source;
        }
        $oems = $this->getEntityManager()->getRepository(Oem::class)
                ->findBy($query);
        $result = true;
        foreach ($oems as $oem){
            $this->getEntityManager()->getConnection()->delete('oem', ['id' => $oem->getId()]);
        }
        return $result;
    }
    
    /**
     * Удаления oem товара
     * 
     * @param Goods $good
     * @return integer
     */
    public function removeGoodOem($good)
    {
        $this->deleteGoodOem($good->getId(), Oem::SOURCE_TD);
        $this->deleteGoodOem($good->getId(), Oem::SOURCE_SUP);
        $this->deleteGoodOem($good->getId(), Oem::SOURCE_CROSS);
        
        return;        
    }
    
    /**
     * Удаления oem товара по источнику
     * 
     * @param integer $goodId
     * @param integer $source
     * @return integer
     */
    public function removeGoodSourceOem($goodId, $source)
    {
        $this->deleteGoodOem($goodId, $source);
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
    
    /**
     * Выборка строк прайса для создания описания
     * 
     * @param \Application\Entity\Raw $raw
     * @return array
     */
    public function findRawpriceForDescription($raw)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r.id as rawpriceId, g.id as goodId, g.name as goodName, g.description as goodDescription')
                ->from(Rawprice::class, 'r')
                ->join('r.good', 'g')
                ->where('r.raw = ?1')
                ->andWhere('r.statusGood = ?2')
                ->andWhere('r.statusToken != ?3')
                ->andWhere('r.status = ?4')
                ->setParameter('1', $raw->getId())
                ->setParameter('2', Rawprice::GOOD_OK)
                ->setParameter('3', Rawprice::DESCRIPTION_UPDATE)
                ->setParameter('4', Rawprice::STATUS_PARSED)
//                ->setMaxResults(100000)
                ;

//var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();        
        
    }    

    /**
     * Выборка строк прайса для создания групп наименований
     * 
     * @param \Application\Entity\Raw $raw
     * @return array
     */
    public function findRawpriceForBestName($raw)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r.id as rawpriceId, g.id as goodId')
                ->from(Rawprice::class, 'r')
                ->join('r.good', 'g')
                ->where('r.raw = ?1')
                ->andWhere('r.statusGood = ?2')
                ->andWhere('r.statusToken != ?3')
                ->andWhere('r.status = ?4')
                ->andWhere('g.groupTokenUpdateFlag != ?5')
                ->setParameter('1', $raw->getId())
                ->setParameter('2', Rawprice::GOOD_OK)
                ->setParameter('3', Rawprice::BEST_NAME_UPDATE)
                ->setParameter('4', Rawprice::STATUS_PARSED)
                ->setParameter('5', date('n'))
//                ->setMaxResults(100000)
                ;

//var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();        
        
    }    
    
    /**
     * Найти минимальную цену товара
     * 
     * @param array $params
     * @return type
     */
    public function findMinPrice($params = null)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('min(g.minPrice) as minPrice')
                ->from(Goods::class, 'g')
                ->where('g.minPrice > 1')
                ->setMaxResults(1)
                ;

        if (is_array($params)){
            if (isset($params['producer'])){
                $queryBuilder->andWhere('g.producer = ?1')
                        ->setParameter('1', $params['producer']);
            }
            if (isset($params['genericGroup'])){
                $queryBuilder->andWhere('g.genericGroup = ?2')
                        ->setParameter('2', $params['genericGroup']);
            }
            if (isset($params['tokenGroup'])){
                $queryBuilder->andWhere('g.tokenGroup = ?3')
                        ->setParameter('3', $params['tokenGroup']);
            }
            if (isset($params['supplier'])){
                $queryBuilder->join('g.articles', 'a')
                        ->join('a.rawprice', 'r')
                        ->join('r.raw', 'raw')
                        ->join('raw.supplier', 's')
                        ->andWhere('s.id = ?4')
                        ->setParameter('4', $params['supplier'])
                        ;                
            }
        }

        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        return $result['minPrice'];
    }
    
    /**
     * Найти максимальную цену товара
     * 
     * @param array $params
     * @return type
     */
    public function findMaxPrice($params = null)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('max(g.minPrice) as maxPrice')
                ->from(Goods::class, 'g')
                ->setMaxResults(1)
                ;
        if (is_array($params)){
            if (isset($params['producer'])){
                $queryBuilder->andWhere('g.producer = ?1')
                        ->setParameter('1', $params['producer']);
            }
            if (isset($params['genericGroup'])){
                $queryBuilder->andWhere('g.genericGroup = ?2')
                        ->setParameter('2', $params['genericGroup']);
            }
            if (isset($params['tokenGroup'])){
                $queryBuilder->andWhere('g.tokenGroup = ?3')
                        ->setParameter('3', $params['tokenGroup']);
            }
            if (isset($params['supplier'])){
                $queryBuilder->join('g.articles', 'a')
                        ->join('a.rawprice', 'r')
                        ->join('r.raw', 'raw')
                        ->join('raw.supplier', 's')
                        ->andWhere('s.id = ?4')
                        ->setParameter('4', $params['supplier']);
                        ;                
            }
        }
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        return $result['maxPrice'];
    }
    
    /**
     * Найти поставщиков товара
     * 
     * @param Goods $good
     * @return array
     */
    public function findGoodSuppliers($good)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('s.id, s.name')
                ->distinct()
                ->from(GoodSupplier::class, 'gs')
                ->join('gs.supplier', 's')
                ->where('gs.good = ?1')
                ->setParameter('1', $good->getId())
                ->orderBy('s.name', 'ASC')
                ;
        
        return $queryBuilder->getQuery()->getResult();
    } 

    /**
     * Найти поставщиков товара
     * 
     * @param Goods $good
     * @return array
     */
    public function goodSuppliersForSelect($good)
    {
        $result = [];
        $data = $this->findGoodSuppliers($good);
        foreach ($data as $row){
            $result[row['id']] = $row['name'];
        }
        
        return $result;
    }
    
    /**
     * Количество генерированных наименований
     * 
     * @return integer
     */
    public function counWithBestName()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('count(g.id) as goodCount')
                ->from(Goods::class, 'g')
                ->where('g.name != ?1')
                ->setParameter('1', '')
                ->andWhere('g.name != g.description')                
                ;
        
        $data = $queryBuilder->getQuery()->getResult();
        foreach ($data as $row){
            return $row['goodCount'];
        }
        return 0;        
    }

    /**
     * Количество товаров передавшие строки прайсов в АПЛ а сегодня
     * 
     * @return integer
     */
    public function countDateEx()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('count(g.id) as goodCount')
                ->from(Goods::class, 'g')
                ->where('g.dateEx >= ?1')
                ->setParameter('1', date("Y-m-d"))
                ;
        
        $data = $queryBuilder->getQuery()->getResult();
        foreach ($data as $row){
            return $row['goodCount'];
        }
        return 0;        
    }
    
    /**
     * Выборка для формы
     * 
     * @param array params
     */
    public function formFind($params)
    {
        $good = null;
        if (!empty($params['good'])){
            $good = $params['good'];
        }

        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->where('g.id = ?1')    
            ->setParameter('1', -1)    
                ;
        if ($good){
            $queryBuilder->setParameter(1, $good->getId());
        }

        return $queryBuilder->getQuery()->getResult();       
    }
    
    /**
     * Запрос для автозаполения
     * 
     * @param array $params
     * @return query
     */
    public function autocompleteGood($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->where('g.id = 0')                           
                ;
        
        if (is_array($params)){
            if (isset($params['search'])){
                //$ktFilter = new KeyboardTranslit();
                //$search = $ktFilter->filter($params['search']);
                $codeFilter = new ArticleCode();
                $search = $codeFilter->filter($params['search']);
//                var_dump($search);
                $queryBuilder
                    ->where('g.code like ?1')                           
                    ->setParameter('1', $search.'%')
                    ->setMaxResults(20)    
                        ;
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();
    } 
    
    /**
     * Обновление токенов товара
     * @param Goods $good
     */
    public function updateGoodToken($good)
    {
        $entityManager = $this->getEntityManager();
        
        $articles = $entityManager->getRepository(Article::class)
                ->findBy(['good' => $good->getId()]);
        foreach ($articles as $article){
            $articleTokens = $entityManager->getRepository(ArticleToken::class)
                    ->findBy(['article' => $article->getId()]);
            foreach ($articleTokens as $articleToken){
                $goodToken = $entityManager->getRepository(GoodToken::class)
                        ->findOneBy(['good' => $good->getId(), 'lemma' => $articleToken->getLemma()]);
                if (empty($goodToken)){
                    $entityManager->getConnection()
                            ->insert('good_token', [
                                'good_id' => $good->getId(),
                                'lemma' => $articleToken->getLemma(),
                                'status' => 0,
                            ]);
                }
            }
        }
        
        return;
    }
}
