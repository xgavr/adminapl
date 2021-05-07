<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Raw;
use Application\Entity\Rawprice;
use Application\Entity\UnknownProducer;

/**
 * Description of SupplierRepository
 *
 * @author Daddy
 */
class RawRepository extends EntityRepository
{
    
    public function lockRawpriceForUpdate($raw)
    {
        $query = $this->getEntityManager()->createQuery('select r from Application\Entity\Rawprice r where r.raw = :1');
        $query->setParameter('1', $raw->getId());
        return $query->setLockMode(\Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);
        
    }
    
    /**
     * Быстрая вставка строки прайса
     * @param array $row 
     * @return integer
     */
    public function insertRawprice($row)
    {
        return $this->getEntityManager()->getConnection()->insert('rawprice', $row);
    }
    
    /**
     * Запрос по строкам прайса по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllRawprice($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(Rawprice::class, 'r')
                ;

        if (is_array($params)){
            if (isset($params['unknownProducer'])){
                $queryBuilder->where('r.unknownProducer = ?1')
                    ->setParameter('1', $params['unknownProducer']->getId())
                        ;
            }
            if (isset($params['unknownProducerId'])){
                if ($params['unknownProducerId']){
                    $queryBuilder->andWhere('r.unknownProducer = ?2')
                        ->setParameter('2', $params['unknownProducerId'])
                     ;
                }    
            }
            if (isset($params['rawId'])){
                if ($params['rawId']){
                    $queryBuilder->andWhere('r.raw = ?3')
                        ->setParameter('3', $params['rawId'])
                     ;
                }    
            }
            if (isset($params['status'])){
                if ($params['status']){
                    $queryBuilder->andWhere('r.status = ?4')
                        ->setParameter('4', $params['status'])
                     ;
                }    
            }
            if (isset($params['producerName'])){
                $queryBuilder->andWhere('r.producer = ?5')
                    ->setParameter('5', $params['producerName'])
                 ;
            }
            if (isset($params['statusProducer'])){
                if ($params['statusProducer']){
                    $queryBuilder->andWhere('r.statusProducer = ?6')
                        ->setParameter('6', $params['statusProducer'])
                     ;
                }    
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('r.'.$params['sort'], $params['order']);                
            }            
        }
//            var_dump($queryBuilder->getQuery()->getDQL()); exit;
        return $queryBuilder->getQuery();
    }    
    
    /**
     * Найти прайсы неизветсного производителя
     * 
     * @param UnknownProducer $unknownProducer
     * @param array $params
     * @return object
     */
    public function findPrice($unknownProducer, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r.id, r.article, c.id as codeId, c.code as code, r.producer, identity(r.unknownProducer) as producerId, r.goodname, r.rest, r.price')
            ->from(Rawprice::class, 'r')
            ->leftJoin('r.code', 'c') 
//            ->join('r.raw', 'rr')
//            ->join('rr.supplier', 's')    
            ->where('r.unknownProducer = ?1')    
            ->setParameter('1', $unknownProducer->getId())
            ;
        
        if (is_array($params)){
            if ($params['status']){
                $queryBuilder->andWhere('r.status = ?2')
                        ->setParameter('2', $params['status'])
                        ;
            }
            if ($params['limit']){
                $queryBuilder->setMaxResults($params['limit']);
            }
        }
        
        return $queryBuilder->getQuery();            
    }    
    
    /**
     * Быстрое обновлеие строки прайса
     * @param \Application\Entity\Rawprice $rawprice
     * @return integer
     */
    public function updateRawprice($rawprice)
    {
        $metadata = $this->getEntityManager()->getClassMetadata(Rawprice::class);
        $colums = $metadata->getColumnNames();
        
        $data = [];
        foreach ($colums as $columnName){
            if (in_array($columnName, ['id', 'rawdata', 'date_created'])) continue;
            $data[$columnName] = $metadata->getFieldValue($rawprice, $metadata->getFieldName($columnName));
        }
        
        if ($rawprice->getPriceDescription()){
            $data['price_description_id'] = (int) $rawprice->getPriceDescription();
        }
        
        //var_dump($data); exit;
        return $this->getEntityManager()->getConnection()->update('rawprice', $data, ['id' => $rawprice->getId()]);
    }
    
    /**
     * Быстрое обновлеие статуса всех строк прайса
     * @param Application\Entity\Raw $raw
     * @param integer $status 
     * @return integer
     */
    public function updateAllRawpriceStatus($raw, $status)
    {
        $data = ['status' => $status];
        return $this->getEntityManager()->getConnection()->update('rawprice', $data, ['raw_id' => $raw->getId()]);
    }
    
    /**
     * Быстрое обновление полей в строке прайса
     * @param integer $rawpriceId
     * @param array $data 
     * @return integer
     */
    public function updateRawpriceField($rawpriceId, $data)
    {
        $updated = $this->getEntityManager()->getConnection()->update('rawprice', $data, ['id' => $rawpriceId]);
        return $updated;
    }    
    
    /**
     * Быстрое обновление статуса разборки прайса
     * @param Raw $raw
     * @param integer $parseStage 
     * @return integer
     */
    public function updateRawParseStage($raw, $parseStage)
    {
        $updated = $this->getEntityManager()->getConnection()->update('raw', 
                ['parse_stage' => $parseStage], ['id' => $raw->getId()]);
        return $updated;
    }    
    
    /**
     * Быстрое обновлеие некоторых полей всех строк прайса
     * @param \Application\Entity\Raw $raw
     * @param array $data 
     * @return integer
     */
    public function updateAllRawpriceField($raw, $data)
    {
        return $this->getEntityManager()->getConnection()->update('rawprice', $data, ['raw_id' => $raw->getId()]);
    }
    
    /**
     * Быстрая привязка неизвестного производителя
     * @param Rawprice $rawprice
     * @param UnknownProducer $unknownProducer 
     * @return integer
     */
    public function updateRawpriceUnknownProducer($rawprice, $unknownProducer)
    {
        $entityManager = $this->getEntityManager();
        
        if ($unknownProducer->getStatus() == UnknownProducer::STATUS_ACTIVE){
            $data = ['unknown_producer_id' => $unknownProducer->getId()];
        } else {
            $data = ['status' => Rawprice::STATUS_BLACK_LIST];            
        }  
        return $this->getEntityManager()->getConnection()->update('rawprice', $data, [
            'id' => $rawprice->getId(), 
           ]);
    }
    
    /**
     * Быстрая установка статуса сборки производителя
     * 
     * @param Rawprice $rawprice
     * @return integer
     */
    public function updateRawpriceAssemblyProducerStatus($rawprice)
    {
        $entityManager = $this->getEntityManager();
        
        $data = ['status_producer' => Rawprice::PRODUCER_ASSEMBLY];
        return $entityManager->getConnection()->update('rawprice', $data, [
            'id' => $rawprice->getId(), 
         ]);
    }
    
    /**
     * Выборка прайсов
     * 
     * @param array $params
     * @return Query 
     */
    public function findAllRaw($params)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select("c, s")
            ->from(Raw::class, 'c')
            ->join('c.supplier', 's')    
                ;
        
        if (isset($params['status'])){
            if (is_numeric($params['status'])){
                $queryBuilder->andWhere('c.status = ?1')
                ->setParameter('1', (int) $params['status'])    
                    ;                    
            }    
        }
        if (isset($params['stage'])){
            if (is_numeric($params['stage'])){
                $queryBuilder->andWhere('c.parseStage = ?2')
                ->setParameter('2', (int) $params['stage'])    
                    ;                    
            }    
        }

        if (isset($params['supplier'])){
            $queryBuilder->andWhere('c.supplier = ?3')
            ->setParameter('3', $params['supplier'])    
            ->addOrderBy('c.filename', 'DESC')        
                ;                    
        }

        if (isset($params['exceptRaw'])){
            $queryBuilder->andWhere('c.id != ?4')
            ->setParameter('4', $params['exceptRaw'])    
            ->addOrderBy('c.filename', 'DESC')        
                ;                    
        }
        
        $queryBuilder->addOrderBy('c.id', 'DESC');
        
//var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }        

    /**
     * @param Apllication\Entity\Raw
     * @param int status
     */
    public function findRawRawprice($raw, $status = null, $limit = 0)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Rawprice::class, 'c')
            ->where('c.raw = ?1')    
            //->orderBy('c.id')
            ->setParameter('1', $raw->getId())    
                ;
        
        if ($status){
            $queryBuilder->andWhere('c.status = ?2')
            ->setParameter('2', (int) $status)    
                ;                    
        }

        if ($limit){
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery();
    }
    
    /**
     * Выбрать записи с непривязанным неизвестным производителем
     * 
     * @param Apllication\Entity\Raw $raw
     * @return object
     */
    public function findUnknownProducerRawprice($raw, $limit = 0)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Rawprice::class, 'c')
            ->where('c.raw = ?1')
            ->andWhere('c.unknownProducer is null')
            ->andWhere('c.status = ?2')    
            ->setParameter('1', $raw->getId())    
            ->setParameter('2', Rawprice::STATUS_PARSED)    
                ;

        if ($limit){
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Выбрать записи с непривязанным артикулом
     * 
     * @param Apllication\Entity\Raw $raw
     * @return object
     */
    public function findCodeRawprice($raw, $limit = 0)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(Rawprice::class, 'r')
            ->where('r.raw = ?1')
            ->andWhere('r.code is null')    
            ->andWhere('r.status = ?2')    
            ->setParameter('1', $raw->getId())
            ->setParameter('2', Rawprice::STATUS_PARSED)    
                ;

        if ($limit){
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery();
    }

    /**
     * Выбрать уникальных производителей из прайса
     * @param Apllication\Entity\Raw $raw
     */
    public function findProducerRawprice($raw)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c.producer')
            ->from(Rawprice::class, 'c')
            ->where('c.raw = ?1')    
            ->distinct()    
            ->setParameter('1', $raw->getId())    
                ;

        return $queryBuilder->getQuery()->getResult();
    }        
    
    /*
     * Выбрать уникальные товары из прайса
     * @var Apllication\Entity\Raw
     */
    public function findGoodRawprice($raw)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c.article, IDENTITY(c.unknownProducer) as unknownProducer, c.goodname')
            ->from(Rawprice::class, 'c')
            ->where('c.raw = ?1')    
            ->distinct()    
            ->setParameter('1', $raw->getId())    
                ;

        return $queryBuilder->getQuery()->getResult();
    }  
    
    /*
     * Получить количество строк прайса
     * @var Apllication\Entity\Raw
     * 
     */
    public function rawpriceCount($raw)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('count(r.id) as rawprice_count')
                ->from(Rawprice::class, 'r')
                ->where('r.raw = ?1')
                ->groupBy('r.raw')
                ->setParameter('1', $raw->getId())
                ;

        return $queryBuilder->getQuery()->getResult();
    }
    
    /*
     * Получить статусы обработки прайса
     * @var Apllication\Entity\Raw
     * 
     */
    public function rawpriceStatuses($raw = null)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('r.status as status, count(r.id) as status_count')
                ->from(Rawprice::class, 'r')
                ->groupBy('r.status')
                ;
        if ($raw){
            $queryBuilder->where('r.raw = ?1')
                    ->setParameter('1', $raw->getId())
                    ;
        }
        return $queryBuilder->getQuery()->getResult();
    }
    
    /*
     * Получить статусы состояния прайсов
     * @var Apllication\Entity\Raw
     * 
     */
    public function rawStatuses()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('r.status as status, count(r.id) as status_count, sum(r.rows) as row_count')
                ->from(Raw::class, 'r')
                ->groupBy('r.status')
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /*
     * Получить стадии состояния прайсов
     * @var Apllication\Entity\Raw
     * 
     */
    public function rawStages()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('r.parseStage as stage, count(r.id) as status_count, sum(r.rows) as row_count')
                ->from(Raw::class, 'r')
                ->where('r.status = ?1')
                ->groupBy('r.parseStage')
                ->setParameter('1', Raw::STATUS_PARSED)
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Получить запрос записей для разбора
     * 
     * @param Raw $raw
     */
    public function queryRawpriceForParse($raw)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('r')
                ->from(Rawprice::class, 'r')
                ->where('r.raw = ?1')
                ->andWhere('r.status = ?2')
                ->setParameter('1', $raw->getId())
                ->setParameter('2', Rawprice::STATUS_NEW)
                ;
        
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
                
        return $queryBuilder->getQuery();
    }
    
    /**
     * Получить записи для разбора
     * 
     * @param Raw $raw
     */
    public function findRawpriceForParse($raw)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('rp')
                ->from(Rawprice::class, 'rp')
                ->where('rp.raw = ?1')
                ->andWhere('rp.status = ?2')
                ->setParameter('1', $raw->getId())
                ->setParameter('2', Rawprice::STATUS_NEW)
                ;
        
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
                
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Поиск старых прайсов по отношению к данному прайсу
     * @param \Apllication\Entity\Raw
     * 
     */
    public function findOldRaw($raw)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('r')
                ->from(Raw::class, 'r')
                ->where('r.supplier = ?1')
                ->andWhere('r.id < ?2')
                ->andWhere('r.status = ?3 or r.status = ?4')
                ->setParameter('1', $raw->getSupplier()->getId())
                ->setParameter('2', $raw->getId())
                ->setParameter('3', Raw::STATUS_PARSED)
                ->setParameter('4', Raw::STATUS_ACTIVE)
                ;
        
        return $queryBuilder->getQuery()->getResult();
        
    }
    
    /**
     * Поиск старых прайсов по отношению к данному прайсу
     * @param Apllication\Entity\Raw $raw
     * 
     */
    public function findPreRetiredRaw($raw)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('r')
                ->from(Raw::class, 'r')
                ->where('r.supplier = ?1')
                ->andWhere('r.id < ?2')
                ->andWhere('r.status = ?3')
                ->setParameter('1', $raw->getSupplier()->getId())
                ->setParameter('2', $raw->getId())
                ->setParameter('3', Raw::STATUS_PRE_RETIRED)
                ;
        
        return $queryBuilder->getQuery()->getResult();
        
    }
    
    /**
     * Поиск старых прайсов для удаления в апл
     * @param Raw $raw
     * 
     */
    public function findToExDeleteRaw($raw)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('r')
                ->from(Raw::class, 'r')
                ->where('r.supplier = ?1')
                ->andWhere('r.id < ?2')
                ->andWhere('r.status = ?3')
                ->setParameter('1', $raw->getSupplier()->getId())
                ->setParameter('2', $raw->getId())
                ->setParameter('3', Raw::EX_TO_DELETE)
                ;
        
        return $queryBuilder->getQuery()->getResult();        
    }
    
    /**
     * Поиск старых прайсов по отношению к данному прайсу
     * @param \Apllication\Entity\Raw
     * 
     */
    public function findOldDeletedRaw($raw)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('r')
                ->from(Raw::class, 'r')
                ->where('r.supplier = ?1')
                ->andWhere('r.id < ?2')
                ->andWhere('r.status = ?3')
                ->setParameter('1', $raw->getSupplier()->getId())
                ->setParameter('2', $raw->getId())
                ->setParameter('3', Raw::STATUS_RETIRED)
                ;
        
        return $queryBuilder->getQuery()->getResult();
        
    }
    
    /**
     * Поиск прайсов для удаления
     */
    public function findRawForRemove()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('r')
                ->from(Raw::class, 'r')
                ->where('r.dateCreated <= ?1')
                ->setParameter('1', date('Y-m-d', strtotime('-1 week')))
                ->setMaxResults(5)
                ;
        
        return $queryBuilder->getQuery()->getResult();        
    }


    /**
     * Запрос на удаление строк raw
     * @param Raw $raw
     */
    public function deleteRawRawpricesQuery($raw)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('r.id')
                ->from(Rawprice::class, 'r')
                ->where('r.raw = ?1')
                ->setParameter('1', $raw->getId())
                ;
        
        return $queryBuilder->getQuery();
    }

    /**
     * Удаление raw
     * @param Raw $raw
     */
    public function deleteRawRawprices($raw)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('r.id')
                ->from(Rawprice::class, 'r')
                ->where('r.raw = ?1')
                ->setParameter('1', $raw->getId())
                ;
        
        $iterator = $queryBuilder->getQuery()->iterate();
        
        foreach ($iterator as $item){
            foreach ($item as $row){
                $this->getEntityManager()->getConnection()->delete('rawprice', ['id' => $row['id']]);                
            }
        }            
        return;
    }

    /*
     * Удаление raw
     * @var Apllication\Entity\Raw
     * 
     */
    public function deleteRawprices($raw)
    {
        $rawId = $raw->getId();
        
        if ($rawId){
            $entityManager = $this->getEntityManager();
            $query = $entityManager->createQuery("delete from Application\Entity\Rawprice m where m.raw = $rawId");
            $numDeleted = $query->execute();
        }    
        return;
    }
    
    /**
     * Сравнить строку прайса с предыдущей
     * 
     * @param Rawprice $rawprice
     * @param date $dateEx
     * @return boolean
     */
    public function isOldRawpriceCompare($rawprice, $dateEx)
    {
        return false;
        
        if ($dateEx <= date('Y-m-d H:i:s', strtotime('-1 week'))){
            return false;
        }
        
        if ($rawprice->getStatus() != Rawprice::STATUS_PARSED){
            return false;
        }
        
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('r')
                ->from(Rawprice::class, 'r')
                ->join('r.raw', 'raw')
                ->where('r.code = ?1')
                ->andWhere('r.id < ?2')
                ->andWhere('raw.supplier = ?3')
                ->orderBy('r.id', 'DESC')
                ->setMaxResults(1)
                ->setParameter('1', $rawprice->getCode()->getId())
                ->setParameter('2', $rawprice->getId())
                ->setParameter('3', $rawprice->getRaw()->getSupplier()->getId())
                ;
        
        $oldRawprices = $queryBuilder->getQuery()->getResult();
        if (count($oldRawprices) == 0){
            return false;
        }
        foreach ($oldRawprices as $oldRawprice){
            if ($oldRawprice->getStatusEx() != Rawprice::EX_TRANSFERRED){
                return false;
            }
            if ($rawprice->getPrice() != $oldRawprice->getPrice()){
                return false;
            }
            if ($rawprice->getRest() != $oldRawprice->getRest()){
                return false;
            }
        }
        
        return true;
    }
        
    
    /**
     * Очистить таблицу наименований для обучения
     */
    public function clearMlTitles()
    {
        $this->getEntityManager()->getConnection()->delete('ml_title', ['1' => '1']);                    
    }
    
    /**
     * Заполнение таблицы наименований для обучения
     */
    public function fillMlTitles()                        
    {
        
        $this->clearMlTitles();
        
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('g.id')
                ->from(\Application\Entity\Goods::class, 'g')
//                ->where('g.tokenGroup is not null')
                ->setMaxResults(1000)
                ->orderBy('RAND()')
                ;
        
        $goods = $queryBuilder->getQuery()->getResult(2);
        $data = [];
        foreach ($goods as $good){
            $data[] = $good['id'];
        }

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('max(r.id) as rid')
                ->from(Rawprice::class, 'r')
                ->where('r.status = ?1')
                ->andWhere('r.good in ('.implode(',', $data).')')
                ->setParameter('1', Rawprice::STATUS_PARSED)
                ->groupBy('r.goodname')
//                ->setParameter('2', implode(',', $data))
                ;
        
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;        
//        var_dump(implode(',', $data)); exit;        
        $data = $queryBuilder->getQuery()->getResult(); 
        
        foreach ($data as $row){
            $this->getEntityManager()->getConnection()->insert('ml_title', ['rawprice_id' => $row['rid']]);            
        }
        
        return;
    }
}
