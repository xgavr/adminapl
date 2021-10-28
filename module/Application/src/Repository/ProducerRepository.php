<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Country;
use Application\Entity\Producer;
use Application\Entity\UnknownProducer;
use Application\Entity\Rawprice;
use Application\Entity\Raw;


/**
 * Description of RbRepository
 *
 * @author Daddy
 */
class ProducerRepository  extends EntityRepository{

    /**
     * Выборка не привязанных производителей из прайса
     */
    public function findRawpriceUnknownProducer()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
            ->from(Rawprice::class, 'r')
            ->where('r.unknownProducer is null')
            ->andWhere('r.status = ?1')
            ->setMaxResults(3000)    
            ->setParameter('1', Rawprice::STATUS_PARSED)    
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Выборка не связанных с прайсом производителей
     */
    public function findEmptyUnknownProducer()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('u')
            ->from(UnknownProducer::class, 'u')
            ->leftJoin(Rawprice::class, 'r')    
            ->where('r.unknownProducer is null')
            ->andWhere('r.status = ?1')
            ->setParameter('1', Rawprice::STATUS_PARSED)    
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Выборка строк прайсов неизвестного производителя
     * 
     * @param Application\Entity\UnknownProducer $unknownProducer
     * @return array
     */
    public function getRawprices($unknownProducer)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
            ->from(Rawprice::class, 'r')                
            ->where('r.unknownProducer = ?1')
            ->setParameter('1', $unknownProducer->getId())
                ;
        
        return $queryBuilder->getQuery()->getResult();        
    }
    
    /**
     * Количество записей в прайсах с этим неизвестным производителем
     * 
     * @param Application\Entity\UnknownProducer $unknownProducer
     */
    public function rawpriceCount($unknownProducer)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('count(r.id) as rawpriceCount')
            ->from(Rawprice::class, 'r')                
            ->where('r.unknownProducer = ?1')
            ->andWhere('r.status = ?2')
            ->groupBy('r.unknownProducer')    
            ->setParameter('1', $unknownProducer->getId())    
            ->setParameter('2', Rawprice::STATUS_PARSED)    
                ;
        
        return $queryBuilder->getQuery()->getResult();
        
    }
    
    /**
     * Выборка неизвестных производителей из прайса
     * 
     * @param Application\Entity\Raw $raw
     * @return object
     */
    public function findUnknownProducerFromRaw($raw)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r.producer')
                ->from(Rawprice::class, 'r')
                ->distinct()
                ->where('r.raw = ?1')
                ->andWhere('r.status = ?2')
                ->andWhere('r.unknownProducer is null')
                ->setParameter('1', $raw->getId())
                ->setParameter('2', Rawprice::STATUS_PARSED)
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Найти неизвестног производителя по наименованию
     * 
     * @param string $name
     * @return UnknownProducer
     */
    public function findUnknownProducerByName($name)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('u')
                ->from(UnknownProducer::class, 'u')
                ->where('u.name = ?1')
                ->orWhere('u.nameTd = ?1')
                ->setParameter('1', $name)
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Быстрая вставка неизвестного производителя
     * @param array $row 
     * @return integer
     */
    public function insertUnknownProducer($row)
    {
        return $this->getEntityManager()->getConnection()->insert('unknown_producer', $row);
    }    

    /**
     * Быстрое изменение неизвестного производителя
     * 
     * @param UnknownProducer $unknownProducer
     * @param array $data 
     * 
     * @return integer
     */
    public function updateUnknownProducer($unknownProducer, $data)
    {
        return $this->getEntityManager()->getConnection()->update('unknown_producer', $data, ['id' => $unknownProducer->getId()]);
    }    

    /**
     * Количество записей в прайсах с этим неизвестным производителем
     * в разрезе поставщиков
     * 
     * @param Application\Entity\UnknownProducer $unknownProducer
     * 
     * @return object
     */
    public function rawpriceCountBySupplier($unknownProducer)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('count(r.id) as rawpriceCount')
            ->from(Rawprice::class, 'r')
            ->join(Raw::class, 'w', 'WITH', 'w.id = r.raw')    
            ->addSelect('w')    
            ->where('r.unknownProducer = ?1')
            ->andWhere('w.status = ?2')
            ->groupBy('r.unknownProducer')    
            ->addGroupBy('w.id')    
            ->setParameter('1', $unknownProducer->getId())    
            ->setParameter('2', Raw::STATUS_PARSED)    
                ;
        //var_dump($queryBuilder->getQuery()->getDQL());
        return $queryBuilder->getQuery()->getResult();    
    }
    
    /**
     * Случайная выборка из прайсов по id неизвестного производителя и id поставщика 
     * @param array $params
     * @return object
     */
    public function randRawpriceBy($params)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
            ->from(Rawprice::class, 'r')
            ->where('r.unknownProducer = ?1')
            ->andWhere('r.raw = ?2')
            ->andWhere('r.status = ?3')
            ->setParameter('1', $params['unknownProducer'])    
            ->setParameter('2', $params['raw'])    
            ->setParameter('3', Rawprice::STATUS_PARSED)
            ->setMaxResults(5)
            //->orderBy('rand()')    
                ;
        return $queryBuilder->getQuery()->getResult();    
        
    }
    
    /**
     * Количество поставщиков данного неизвестного производителя
     * 
     * @param Application\Entity\UnknownProducer $unknownProducer
     * @return int
     */
    public function unknownProducerSupplierCount($unknownProducer)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('identity(r.raw)')
                ->distinct()
                ->from(Rawprice::class, 'r')
                ->where('r.unknownProducer = ?1')
                ->andWhere('r.status = ?2')
                ->setParameter('1', $unknownProducer->getId())    
                ->setParameter('2', Rawprice::STATUS_PARSED)
                ;
        $result = count($queryBuilder->getQuery()->getResult());
        
        return $result;
    }
    
    /**
     * Количество привязанных строка прайсов к неизвестним поставщикам и не привязанных
     * 
     * @return array
     */
    public function findBindNoBindRawprice()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('case when r.unknownProducer is null then 0 else 1 end as bind, COUNT(r.id) as bindCount')
            ->from(Rawprice::class, 'r')
            ->where('r.status = ?1')
            ->groupBy('bind')    
            ->setParameter('1', Rawprice::STATUS_PARSED)
                ;
        return $queryBuilder->getQuery()->getResult();            
    }

    /**
     * Найти неизвестных производителей для удаления
     * 
     * @return object
     */
    public function findUnknownProducerForDelete()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('u')
            ->addSelect('count(r.id) as rawpriceCount')    
            ->from(UnknownProducer::class, 'u')
            ->leftJoin('u.rawprice', 'r')
            ->groupBy('u.id')
            ->having('rawpriceCount = 0')
                ;
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }

    /**
     * Запрос по неизвестным производителям по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllUnknownProducer($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c', 'p')
            ->from(UnknownProducer::class, 'c')
            ->leftJoin('c.producer', 'p', 'WITH')    
            ->orderBy('c.name');
                ;
        
        if (is_array($params)){
            if (isset($params['unattached'])){
                $queryBuilder->where('c.producer is null');
            }
            if (isset($params['q'])){
                $queryBuilder->where('c.name like :search')
                    ->setParameter('search', '%' . $params['q'] . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->where('c.name > ?1')
                    ->setParameter('1', $params['next1'])
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('c.name < ?1')
                    ->setParameter('1', $params['prev1'])
                    ->orderBy('c.name', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('c.'.$params['sort'], $params['order']);                
            }            
        }

        return $queryBuilder->getQuery();
    }    
        
    public function findAllCountry()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Country::class, 'c')
            ->orderBy('c.name')
                ;

        return $queryBuilder->getQuery();
    }    
    
    /**
     * Запрос по производителям
     * 
     * @param array $params
     * @return query
     */
    public function findAllProducer($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('p.id, p.aplId, p.name, p.goodCount, p.movement, p.status, count(up.id) as unknownProducerCount, sum(up.supplierCount) as supplierCount, sum(up.rawpriceCount) as rawpriceCount')
            ->from(Producer::class, 'p')
            ->leftJoin('p.unknownProducer', 'up') 
            ->groupBy('p.id')    
            ->orderBy('p.name')
                ;
        
        if (is_array($params)){
            if (isset($params['q'])){
                $queryBuilder->where('p.name like :search')
                    ->setParameter('search', '%' . $params['q'] . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->where('p.name > ?1')
                    ->setParameter('1', $params['next1'])
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('p.name < ?1')
                    ->setParameter('1', $params['prev1'])
                    ->orderBy('p.name', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['sort'])){
                if ($params['sort'] == 'unknownProducerCount'){
                    $queryBuilder->orderBy('unknownProducerCount', $params['order']);
                } elseif ($params['sort'] == 'supplierCount'){
                    $queryBuilder->orderBy('supplierCount', $params['order']);
                } elseif ($params['sort'] == 'rawpriceCount'){
                    $queryBuilder->orderBy('rawpriceCount', $params['order']);
                } else {
                    $queryBuilder->orderBy('p.'.$params['sort'], $params['order']);                
                }    
            }            
        }

        return $queryBuilder->getQuery();
    }    
    
    public function searchByName($search){

        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('g')
            ->from(Producer::class, 'g')
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
    
    
    public function unknownProducerByCodeIntersect($code)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('identity(a.unknownProducer) as unknownProducerId')
                ->from(\Application\Entity\Article::class, 'a')
                ->distinct()
                ->where('a.code = ?1')
                ->setParameter('1', $code)
                ;
        return $queryBuilder->getQuery()->getResult(2);        
    }
        
    /**
     * Заполнить таблицу пересечения незвестных производителей
     * 
     * @return null
     */
    public function articleUnknownProducerIntersect()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('a.code, count(a.unknownProducer) as unknownProducerCount')
                ->from(\Application\Entity\Article::class, 'a')
                ->where('length(a.code) > 0')                
                ->andWhere('length(a.code) < 24')
                ->andWhere('a.code not like ?1')
                ->andWhere('a.code != ?2')
                ->groupBy('a.code')
                ->having('unknownProducerCount > 1')
                ->setParameter('1', '%_pk_%')
                ->setParameter('2', \Application\Entity\Article::LONG_CODE_NAME)
                ;
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        $codeRows = $queryBuilder->getQuery()->getResult(2);
        
        $this->getEntityManager()->getConnection()->query('delete from unknown_producer_intersect where 1');
        
        foreach ($codeRows as $codeRow){
            $unknownProducerRows = $this->unknownProducerByCodeIntersect($codeRow['code']);
            foreach ($unknownProducerRows as $unknownProducerRow){
                foreach ($unknownProducerRows as $unknownProducerRowIntersect){
                    if ($unknownProducerRow['unknownProducerId'] != $unknownProducerRowIntersect['unknownProducerId']){
                        $data = [
                            'code' => $codeRow['code'],
                            'unknown_producer' => $unknownProducerRow['unknownProducerId'],
                            'unknown_producer_intersect' => $unknownProducerRowIntersect['unknownProducerId'],
                        ];
                        $this->getEntityManager()->getConnection()->insert('unknown_producer_intersect', $data);
                    }    
                }
            }
        }
        
        // обновление метки проверки
//        $this->getEntityManager()->getConnection()->query('update unknown_producer set intersect_update_flag = 1 where 1');
        
        return;
    }
    
    /**
     * Количество и частота пересекающихся неизвестных производителей
     * 
     * @param \Application\Entity\UnknownProducer $unknownProducer
     * @param float $intersectCoef
     * @return type
     */
    public function unknownProducerIntersect($unknownProducer, $intersectCoef = UnknownProducer::INTERSECT_COEF)
    {
        $entityManager = $this->getEntityManager();
//        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();

        if ($unknownProducer->getRawpriceCount()){
            $sql = 'select t.unknown_producer_intersect as unknown_producer_id, '
                    . ' u.name as unknown_producer_name, '
                    . ' u.rawprice_count as unknown_producer_rawprice_count, '
                    . ' count(t.code) as countCode '
                    . ' from unknown_producer_intersect as t '
                    . ' inner join unknown_producer as u on t.unknown_producer_intersect = u.id '                
                    . ' where t.unknown_producer = :unknownProducer'
                    . ' and u.name != ""'
                    . ' group by t.unknown_producer, t.unknown_producer_intersect '
                    . ' having countCode/:rawpriceCount > :intersect_coef'
                    . ' order by countCode DESC';

    //        $query = $entityManager->createNativeQuery($sql, $rsm);
    //        $query->setParameter(1, $unknownProducer->getId());

//            var_dump($sql); exit;

            $stmt = $entityManager->getConnection()->prepare($sql);
            $stmt->execute([
                    'unknownProducer' => $unknownProducer->getId(),
                    'rawpriceCount' => $unknownProducer->getRawpriceCount(),
                    'intersect_coef' => $intersectCoef,
                ]);

            return $stmt->fetchAll();
        }    
        
        return [];
    }
    
    /**
     * Получить артикулы пересекающихся производителей
     * 
     * @param \Application\Entity\UnknownProducer $unknownProducer
     * @param \Application\Entity\UnknownProducer $intersectUnknownProducer
     * @return array
     */
    public function intersectesCode($unknownProducer, $intersectUnknownProducer)
    {
        $entityManager = $this->getEntityManager();

        $sql = 'select t.code '
                . ' from unknown_producer_intersect as t '
                . ' where t.unknown_producer_intersect = :unknownProducerIntersect'
                . ' and t.unknown_producer = :unknownProducer'
                . ' and t.code != :longCodeName'
                ;

        $stmt = $entityManager->getConnection()->prepare($sql);
        $stmt->execute([
                'unknownProducer' => $unknownProducer->getId(),
                'unknownProducerIntersect' => $intersectUnknownProducer->getId(),
                'longCodeName' => \Application\Entity\Article::LONG_CODE_NAME,
            ]);

        return $stmt->fetchAll();

    }
    
    /**
     * Получить количество пересечений производителя
     * 
     * @param \Application\Entity\UnknownProducer $unknownProducer
     * @return array
     */
    public function intersectCount($unknownProducer)
    {
        $entityManager = $this->getEntityManager();

        $sql = 'select count(t.unknown_producer_intersect) as intersectCount '
                . ' from unknown_producer_intersect as t '
                . ' where t.unknown_producer = :unknownProducer'
                . ' and t.code != :longCodeName'
                . ' group by t.unknown_producer'
                ;

        $stmt = $entityManager->getConnection()->prepare($sql);
        $stmt->execute([
                'unknownProducer' => $unknownProducer->getId(),
                'longCodeName' => \Application\Entity\Article::LONG_CODE_NAME,
            ]);

        return $stmt->fetchAll();

    }
    
    /**
     * Выборка пересекающихся артикулов
     * 
     * @param \Application\Entity\UnknownProducer $unknownProducer
     * @param \Application\Entity\UnknownProducer $intersectUnknownProducer
     * @return query
     */
    public function intersectesArticle($unknownProducer, $intersectUnknownProducer)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('a.id, a.code, identity(a.unknownProducer) as unknownProducer')
                ->from(\Application\Entity\Article::class, 'a')
//                ->andWhere($queryBuilder->expr()->orX(
//                        $queryBuilder->expr()->eq('a.unknownProducer', $unknownProducer->getId()),
//                        $queryBuilder->expr()->eq('a.unknownProducer', $intersectUnknownProducer->getId())
//                    )
//                )
                ->andWhere('a.unknownProducer = ?1')
                ->setParameter('1', $intersectUnknownProducer->getId())
                ->orderBy('a.code')
                ;
        
        $intersects = $this->intersectesCode($unknownProducer, $intersectUnknownProducer);
        
        $orX = $queryBuilder->expr()->orX();
        foreach ($intersects as $row){
            $orX->add($queryBuilder->expr()->eq('a.code', "'{$row['code']}'"));
        }
        
        $queryBuilder
                ->andWhere($orX)
                ;        

//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();                
    }
    
    /**
     * Выбрать неизвестных производителей из прайса для создания производителей
     * 
     * @return array
     */
    public function findUnknownProducerForAssemblyFromRaw($raw)
    {
        
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
                ->select('u')
                ->distinct()
                ->from(Rawprice::class, 'r')
                ->join(UnknownProducer::class, 'u', 'WITH', 'u.id = r.unknownProducer')
                ->where('r.raw = ?1')
                ->andWhere('r.status = ?2')
                ->andWhere('r.code is not null')
                ->andWhere('r.statusProducer = ?3')
                ->setParameter('1', $raw->getId())
                ->setParameter('2', Rawprice::STATUS_PARSED)
                ->setParameter('3', Rawprice::PRODUCER_NEW)
                ;
        
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();        
    }

    /**
     * Выбрать неизвестных производителей для создания производителей
     * 
     * @return array
     */
    public function findUnknownProducerForAssembly()
    {
        $unknowProducerCount = $this->getEntityManager()->getRepository(UnknownProducer::class)
                ->count([]);
        
        $limit = intdiv($unknowProducerCount, 12);
        $start = (date('g') - 1) * $limit;
        
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
                ->select('u')
                ->from(UnknownProducer::class, 'u')
                ->orderBy('u.rawpriceCount')
                ->setMaxResults($limit + 10)
                ->setFirstResult($start)
                ;
        
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();        
    }

    /**
     * Найти производителей для удаления
     * 
     * @return object
     */
    public function findProducerForDelete()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('p')
            ->addSelect('count(u.id) as unknownProducerCount')    
            ->addSelect('count(g.id) as goodsCount')    
            ->from(Producer::class, 'p')
            ->leftJoin(UnknownProducer::class, 'u', 'WITH', 'u.producer = p.id')
            ->leftJoin(\Application\Entity\Goods::class, 'g', 'WITH', 'g.producer = p.id')
            ->groupBy('p.id')
            ->having('unknownProducerCount = 0 and goodsCount = 0')
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }

    /**
     * Найти производителей для обновления AplId
     * 
     * @return object
     */
    public function findProducerForUpdateAplId()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Producer::class, 'p')
            ->where('p.aplId = 0')
            ->setMaxResults(10000)    
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }
}
