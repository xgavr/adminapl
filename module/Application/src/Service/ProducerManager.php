<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Application\Entity\Producer;
use Application\Entity\UnknownProducer;
use Application\Entity\Raw;
use Application\Entity\Rawprice;
use Application\Filter\ProducerName;

/**
 * Description of RbService
 *
 * @author Daddy
 */
class ProducerManager
{
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    /**
     * Goods manager.
     * @var \Application\Entity\GoodsManager
     */
    private $goodsManager;
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $goodsManager)
    {
        $this->entityManager = $entityManager;
        $this->goodsManager = $goodsManager;
    }
    
    /**
     * Добавить нового производителя
     * 
     * @param array $data
     * @return Producer
     */
    public function addNewProducer($data) 
    {
        
        $producer = $this->entityManager->getRepository(Producer::class)
                ->findOneByName(trim($data['name']));
        
        if ($producer){
            return $producer;
        }
        
        $this->entityManager->getConnection()->insert('producer', 
                [
                    'name' => trim($data['name']),
                    'apl_id' => 0,
                ]);
        
        return $this->addNewProducer($data);
    }   
    
    public function updateProducer($producer, $data) 
    {
        $producerByName = $this->entityManager->getRepository(Producer::class)
                ->findOneByName(trim($data['name']));
        
        if ($producerByName){
            throw new \Exception('Уже есть производитель с наименованием '.$data['name']);
        }

        $producer->setName($data['name']);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush($producer);
        
        return $producer;
    }    
    
    /**
     * Удаление производителя
     * 
     * @param \Application\Entity\Producer $producer
     * @return boolean
     */
    public function removeProducer($producer) 
    {   
        $goodCount = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->count(['producer' => $producer->getId()]);
        if ($goodCount > 0){
            return false;
        }
        
        $this->entityManager->getConnection()->update('unknown_producer', ['producer_id' => null], ['producer_id' => $producer->getId()]);
        
        $this->entityManager->remove($producer);
        
        $this->entityManager->flush($producer);
        
        return true;
    }    
    
    /**
     * Поиск и удаление производителей не привязаных к товарам и неизвестным производителям
     */
    public function removeEmptyProducer()
    {
        $producersForDelete = $this->entityManager->getRepository(Producer::class)
                ->findProducerForDelete();

        foreach ($producersForDelete as $row){
            $this->removeProducer($row[0]);
        }
        
        return count($producersForDelete);
    }
    
    
    /**
     * Создать производителя из неизвестного производителя
     *@param UnknownProducer $unknownProducer
     *  
     */
    
    public function addProducerFromUnknownProducer($unknownProducer)
    {
       if ($unknownProducer->getName()){           
            return $this->addNewProducer(['name' => $unknownProducer->getName()]);        
       } 
       
       return;
    }
    
    public function addProducerFromArticle($article)
    {
        return $this->addNewProducer(['name' => $article->getUnknownProducer()->getName()]);        
    }

    /**
     * Связать производителя с неизвестным
     * 
     * @param UnknowmProducer $unknownProducer
     * @param Producer $producer
     */
    public function bindUnknownProducer($unknownProducer, $producer)
    {
        $this->entityManager->getConnection()->update('unknown_producer',
                [
                    'producer_id' => ($producer) ? $producer->getId():NULL,
                    'intersect_update_flag' => UnknownProducer::INTERSECT_UPDATE_FLAG,
                ],
                [
                    'id' => $unknownProducer->getId(),
                ]);
        return;
    }        
    
    /*
     * @string $name
     * @bool flushnow
     */
    public function addUnknownProducer($name, $flushnow = true)
    {
        $nameFilter = new ProducerName();
        $producerName = $nameFilter->filter($name);
        
        $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                    ->findOneByName($producerName);

        if ($unknownProducer == null){

            // Создаем новую сущность UnknownProducer.
            $unknownProducer = new UnknownProducer();
            $unknownProducer->setName($producerName);

            $currentDate = date('Y-m-d H:i:s');
            $unknownProducer->setDateCreated($currentDate);

//            $producer = new Producer();
//            $unknownProducer->setProducer($producer);

            // Добавляем сущность в менеджер сущностей.
            $this->entityManager->persist($unknownProducer);

            // Применяем изменения к базе данных.
            if ($flushnow){
                $this->entityManager->flush();
            }    
        }  
        
        return $unknownProducer;
        
    }        
    
    /**
     * Добавление нового неизвестного производителя
     * 
     * @param Application\Entity\Rawprice $rawprice
     * @param bool $flush
     */
    public function addNewUnknownProducerFromRawprice($rawprice, $flush = true) 
    {
        $nameFilter = new ProducerName();
        $producerName = $nameFilter->filter($rawprice->getProducer());
        
        $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                    ->findOneByName($producerName);

        if ($unknownProducer == null){

            // Создаем новую сущность UnknownProducer.
            $unknownProducer = new UnknownProducer();
            $unknownProducer->setName($producerName);

            $currentDate = date('Y-m-d H:i:s');
            $unknownProducer->setDateCreated($currentDate);

            $this->entityManager->persist($unknownProducer);

        }    

        $rawprice->setUnknownProducer($unknownProducer);
        $this->entityManager->persist($rawprice);

        $this->entityManager->flush();
    }  
    
    /**
     * Выборка производителей из прайсов и добавление их в неизвестные производители
     * привязка к строкам прайса
     * 
     * @param Application\Entity\Raw $raw
     */
    public function grabUnknownProducerFromRaw($raw)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(900);        
        $startTime = time();
        $finishTime = $startTime + 840;

        $unknownProducers = $this->entityManager->getRepository(UnknownProducer::class)
                ->findUnknownProducerFromRaw($raw);
        
        $nameFilter = new ProducerName();
        
        foreach ($unknownProducers as $row){
            
            $unknownProducerName = $nameFilter->filter($row['producer']);
            
            $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                    ->findOneByName($unknownProducerName);

            if (!$unknownProducer){
                $data = [
                    'name' => $unknownProducerName,
                    'date_created' => date('Y-m-d H:i:s'),
                ];

                $this->entityManager->getRepository(UnknownProducer::class)
                        ->insertUnknownProducer($data);

                $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                        ->findOneByName($unknownProducerName);
            }
            
            if ($unknownProducer){
                
                $rawpricesQuery = $this->entityManager->getRepository(Rawprice::class)
                        ->findAllRawprice(['rawId' => $raw->getId(), 'producerName' => $row['producer']]);
                $iterable = $rawpricesQuery->iterate();
                
                foreach ($iterable as $rawpriceRow){
                    foreach ($rawpriceRow as $rawprice){
                        $this->entityManager->getRepository(Rawprice::class)
                            ->updateRawpriceUnknownProducer($rawprice, $unknownProducer);
                        $this->entityManager->detach($rawprice);
                    }    
                    if (time() >= $finishTime){
                        return;
                    }
                }    
            }            
        }
        
        $raw->setParseStage(Raw::STAGE_PRODUCER_PARSED);
        $this->entityManager->persist($raw);
        $this->entityManager->flush($raw);
    }
    

    /**
     * Обновление неизвестного производителя
     * 
     * @param Application\Entity\UnknownProducer $unknownProducer
     * @param array $data
     */
    public function updateUnknownProducer($unknownProducer, $data) 
    {
        if ($data['producer']){    
            $producer = $this->entityManager->getRepository(Producer::class)
                        ->findOneById($data['producer']);
        } elseif ($data['producer_name']){
            $producer = $this->entityManager->getRepository(Producer::class)
                        ->findOneByName($data['producer_name']);            
        }    
        
        $unknownProducer->setProducer($producer);
               
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    

    /**
     * Обновление количества товара у неизвестного производителя
     * 
     * @param Application\Entity\UnknownProducer $unknownProducer
     * @param bool $flush
     */
    public function updateUnknownProducerRawpriceCount($unknownProducer, $flush = true)
    {
        $rawpriceCount = $this->entityManager->getRepository(Rawprice::class)
                ->count(['unknownProducer' => $unknownProducer->getId(), 'status' => Rawprice::STATUS_PARSED]);
        
        $unknownProducer->setRawpriceCount($rawpriceCount);
        $this->entityManager->persist($unknownProducer);
        
        if ($flush){
            $this->entityManager->flush($unknownProducer);
        }
    }
    
    /**
     * Обновление количества поставщиков у неизвестного производителя
     * 
     * @param Application\Entity\UnknownProducer $unknownProducer
     * @param bool $flush
     */
    public function updateUnknownProducerSupplierCount($unknownProducer, $flush = true)
    {
        $supplierCount = $this->entityManager->getRepository(UnknownProducer::class)
                ->unknownProducerSupplierCount($unknownProducer);

        $unknownProducer->setSupplierCount($supplierCount);
        $this->entityManager->persist($unknownProducer);
        
        if ($flush){
            $this->entityManager->flush($unknownProducer);
        }
    }
    
    public function updateUnknownProducerIntersect()
    {
        set_time_limit(900);
        
        $this->entityManager->getRepository(Producer::class)
                ->articleUnknownProducerIntersect();
        return;
    }
    
    /**
     * Удаление неизвестного производителя
     * 
     * @param \Application\Entity\UnknownProducer $unknownProducer
     */
    public function removeUnknownProducer($unknownProducer) 
    {   
        $codeCount = $this->entityManager->getRepository(\Application\Entity\Article::class)
                ->count(['unknownProducer' => $unknownProducer->getId()]);
        $rawpriceCount = $this->entityManager->getRepository(Rawprice::class)
                ->count(['unknownProducer' => $unknownProducer->getId()]);
        
        if ($codeCount == 0 && $rawpriceCount == 0){

            $this->entityManager->remove($unknownProducer);

            $this->entityManager->flush($unknownProducer);
        }
        
        return;
    }    
    
    /**
     * Случайная выборка из прайсов по id неизвестного производителя и id поставщика 
     * @param array $params
     * @return object      
     */
    public function randRawpriceBy($params)
    {
        return $this->entityManager->getRepository(UnknownProducer::class)
                ->randRawpriceBy($params);
    }
    
    /**
     * Поиск и удаление неизвестных производителей не привязаных к строкам прайсов
     */
    public function removeEmptyUnknownProducer()
    {
        set_time_limit(900);        
        $startTime = time();
        $finishTime = $startTime + 840;

        $unknownProducersForDelete = $this->entityManager->getRepository(UnknownProducer::class)
                ->findUnknownProducerForDelete();

        foreach ($unknownProducersForDelete as $row){
            $this->removeUnknownProducer($row[0]);
            if (time() >= $finishTime){
                return;
            }
        }
        
        return;
    }
    
    public function searchProducerNameAssistant($search)
    {
        $result = [];    
        if (strlen($search) >= 1){
            $names = $this->entityManager->getRepository(Producer::class)
                    ->searchNameForSearchAssistant($search);

            foreach ($names as $name){
                $result[] = [
                    'value' => $name->getName(),
                    'text' => $name->getId(),
                ];
            }
        }
        
        return $result;
    }  
    
    /**
     * Лучшее наименование
     * 
     * @param \Application\Entity\Producer $producer
     */
    public function bestName($producer)
    {
        $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                ->findOneByProducer($producer->getId(), ['rawpriceCount' => 'DESC']);

        if ($unknownProducer){
            $rawprice = $this->entityManager->getRepository(Rawprice::class)
                    ->findOneByUnknownProducer($unknownProducer->getId());

            if ($rawprice){
                $priceDescription = $this->entityManager->getRepository(\Application\Entity\PriceDescription::class)
                        ->findOneById($rawprice->getPriceDescription());
                if ($priceDescription){
                    $rawValues = $rawprice->getRawdataAsArray();
                    $newName = $rawValues[$priceDescription->getProducer() - 1];
//                    var_dump($priceDescription->getProducer());
                    if ($newName != $producer->getName()){
                        $producerWithName = $this->entityManager->getRepository(Producer::class)
                                ->findOneByName($newName);
                        if (!$producerWithName){
                            $producer->setName($newName);
                            $this->entityManager->persist($producer);
                            $this->entityManager->flush();
                        }    
                    }    
                }    
            }    
        }
        
        return;
    }
        
}
