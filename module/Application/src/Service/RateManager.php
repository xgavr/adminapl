<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Laminas\ServiceManager\ServiceManager;
use Application\Entity\Goods;
use Application\Entity\Scale;
use Application\Entity\ScaleTreshold;
use Application\Entity\Rate;
use Company\Entity\Office;
use Admin\Entity\Log;

/**
 * Description of ShopService
 *
 * @author Daddy
 */
class RateManager
{
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * MlManager.
     * @var \Application\Service\MlManager
     */
    private $mlManager;
    
    /**
     * Log manager
     * @var \Admin\Service\LogManager
     */
    private $logManager;    

    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $mlManager, $logManager)
    {
        $this->entityManager = $entityManager;
        $this->mlManager = $mlManager;
        $this->logManager = $logManager;
    }    
    
    /**
     * Обновить фиксированную цену
     * 
     * @param Goods $good
     * @param float $fixPrice
     * @return null
     */
    public function updateFixPrice($good, $fixPrice)
    {
        $good->setFixPrice($fixPrice);
        $this->entityManager->persist($good);
        $this->entityManager->flush($good);
        return;
    }
    
    
    /**
     * Добавить шаг шкалы
     * 
     * @params Scale $scale
     * @param ScaleTreshold $data
     * 
     * @return ScaleTreshold
     */
    public function addTreshold($scale, $data)
    {
        $treshold = new ScaleTreshold();
        $treshold->setRate($data['rate']);
        $treshold->setRounding($data['rounding']);
        $treshold->setTreshold($data['treshold']);

        $treshold->setScale($scale);
        
        $this->entityManager->persist($treshold);

        //$this->entityManager->flush($treshold);
        
        return $treshold;
    }

    /**
     * Изменить шаг шкалы
     * 
     * @param ScaleTreshold $treshold
     * @param array $data
     */
    public function updateTreshold($treshold, $data)
    {
        if (isset($data['rate'])){
            $treshold->setRate($data['rate']);
        }
        if (isset($data['rounding'])){
            $treshold->setRounding($data['rounding']);
        }
        if (isset($data['treshold'])){
            $treshold->setTreshold($data['treshold']);
        }

        $this->entityManager->persist($treshold);
        $this->entityManager->flush($treshold);
        
        return;
    }
    
    /**
     * Добавить шкалу
     * 
     * @param array $data
     * @return Scale
     */
    public function addScale($data)
    {
        $scale = new Scale();
        $scale->setName($data['name']);
        $scale->setMinPrice(0);
        $scale->setMaxPrice(0);
        
        $this->entityManager->persist($scale);
        $this->entityManager->flush($scale);
        
        return $scale;
    }
    
    /**
     * Изменить шкалу
     * 
     * @param Scale $scale
     * @param array $data
     */
    public function updateScale($scale, $data)
    {
        $scale->setName($data['name']);

        $this->entityManager->persist($scale);
        $this->entityManager->flush($scale);
        
        return;
    }
    
    /**
     * Удалить шаг шкалы
     * 
     * @param ScaleTreshold $treshold
     */
    public function removeTreshold($treshold)
    {
        $this->entityManager->remove($treshold);
        //$this->entityManager->flush($treshold);
        
        return;
    }
    
    /**
     * Удалить шкалу
     * 
     * @param Scale $scale
     */
    public function removeScale($scale)
    {
        foreach ($scale->getTresholds() as $treshold){
            $this->removeTreshold($treshold);
        }
        
        foreach ($scale->getRates() as $rate){
            $this->entityManager->remove($rate);
        }
        
        $this->entityManager->remove($scale);
        $this->entityManager->flush();
    }
    
    /**
     * Обновить шаги шкалы
     * 
     * @param Scale $scale
     * @param float $minPrice
     * @param float $maxPrice 
     * @param string $modelFileName
     */
    public function createDefaultTresholds($scale, $minPrice, $maxPrice, $modelFileName = null)
    {
        foreach ($scale->getTresholds() as $treshold){
            $this->removeTreshold($treshold);
        }
        
        $samples = $this->mlManager::RATE_SAMPLES;
        
        if (count($samples)){
            if (!$minPrice){
                $minPrice = $samples[0];
            }
            if (!$maxPrice){
                $maxPrice = $samples[count($samples) - 1];
            }

            $scale->setMinPrice($minPrice);
            $scale->setMaxPrice($maxPrice);
            $this->entityManager->persist($scale);

            $this->addTreshold($scale, [
                'treshold' => $minPrice,
                'rounding' => ScaleTreshold::DEFAULT_ROUNDING,
                'rate' => $this->mlManager->predictRateScale($minPrice, $modelFileName),
            ]);

            $tresholds[] = $minPrice; 
            foreach ($samples as $sample){
                if ($minPrice < $sample && $maxPrice > $sample){
                    $this->addTreshold($scale, [
                        'treshold' => $sample,
                        'rounding' => ScaleTreshold::DEFAULT_ROUNDING,
                        'rate' => $this->mlManager->predictRateScale($sample, $modelFileName),
                    ]);
                }
            }
            $this->addTreshold($scale, [
                'treshold' => $maxPrice,
                'rounding' => ScaleTreshold::DEFAULT_ROUNDING,
                'rate' => $this->mlManager->predictRateScale($maxPrice, $modelFileName),
            ]);        
        }
        
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Создать шкалу по умолчанию
     * @param array $params
     * @return Scale
     */
    public function createDefaultScale($params = null)
    {
        $minPrice = $this->entityManager->getRepository(Goods::class)
                ->findMinPrice($params);
        $maxPrice = $this->entityManager->getRepository(Goods::class)
                ->findMaxPrice($params);
        
        $scale = $this->addScale($params);

        $this->createDefaultTresholds($scale, $minPrice, $maxPrice);

        return $scale;
    }
    
    /**
     * Получить/создать шкалу по умолчанию
     * 
     * @param array $params
     * @return Scale
     */
    public function getDefaultScale($params)
    {
//        $findParams = [];
//        if (isset($params['producer'])){
//            $findParams['producer'] = $params['producer'];
//        }
//        if (isset($params['genericGroup'])){
//            $findParams['genericGroup'] = $params['genericGroup'];
//        }
//        if (isset($params['supplier'])){
//            $findParams['supplier'] = $params['supplier'];
//        }
//        
//        $rates = $this->entityManager->getRepository(Rate::class)
//                ->findBy($findParams);
//        
//        foreach ($rates as $rate){
//            return $rate->getScale();
//        }        
        
        return $this->createDefaultScale($params);
    }
    
    /**
     * Добавить расценку
     * 
     * @param array $data
     * @return Rate 
     */
    public function addRate($data)
    {
        $defaultOffice = $this->entityManager->getRepository(Office::class)
                ->findOneById(1);
        
        $scale = $this->getDefaultScale($data);
        
        $rate = new Rate();
        
        $rate->setOffice($defaultOffice);

        if (isset($data['supplier'])){
            $rate->setSupplier($data['supplier']);
        }
        if (isset($data['genericGroup'])){
            $rate->setGenericGroup($data['genericGroup']);
        }        
        if (isset($data['tokenGroup'])){
            $rate->setTokenGroup($data['tokenGroup']);
        }        
        if (isset($data['producer'])){
            $rate->setProducer($data['producer']);
        }        
        if (isset($data['name'])){
            $name = $data['name'];
        }        
        
        $rate->setName($name);
        $rate->setStatus(Rate::STATUS_ACTIVE);
        $rate->setMode(Rate::MODE_MARKUP);
        
        $rate->setScale($scale);
        
        $rate->setMinPrice($scale->getMinPrice());
        $rate->setMaxPrice($scale->getMaxPrice());
        
        $this->entityManager->persist($rate);
        $this->entityManager->flush($rate);
        
        $this->logManager->infoRate($rate, Log::STATUS_NEW);
                
    }
    
    /**
     * Обновить шкалу расценки
     * 
     * @param Rate $rate
     */
    public function updateRateScale($rate)
    {
        
        $params = [
            'name' => $rate->getName(),
        ];
        if ($rate->getSupplier()){
            $params['supplier'] = $rate->getSupplier()->getId();
        }
        if ($rate->getGenericGroup()){
            $params['genericGroup'] = $rate->getGenericGroup()->getId();
        }
        if ($rate->getTokenGroup()){
            $params['tokenGroup'] = $rate->getTokenGroup()->getId();
        }
        if ($rate->getProducer()){
            $params['producer'] = $rate->getProducer()->getId();
        }
        
        $minPrice = $this->entityManager->getRepository(Goods::class)
                ->findMinPrice($params);
        $maxPrice = $this->entityManager->getRepository(Goods::class)
                ->findMaxPrice($params);
                
        $scale = $rate->getScale();

        $this->createDefaultTresholds($scale, $minPrice, $maxPrice, $rate->getRateModelFileName());
        
        $rate->setMinPrice($scale->getMinPrice());
        $rate->setMaxPrice($scale->getMaxPrice());
        $this->entityManager->persist($rate);
        $this->entityManager->flush($rate);
        
        $this->logManager->infoRate($rate, Log::STATUS_UPDATE);        
    }
    
    /**
     * Изменить наценки шкалы 
     * 
     * @param Rate $rate
     * @param float $change
     */
    public function changeRateScale($rate, $change)
    {
        if ($change != 0){
            $samples = [];
            $targets = [];
            foreach ($rate->getScale()->getTresholds() as $treshold){
                $samples[] = $treshold->getTreshold();
                $targets[] = $treshold->getRate() + $change*$treshold->getRate()/100;
            }
            $this->mlManager->trainRateScale($rate, $samples, $targets);
        } else {
            $this->mlManager->removeModelRateScale($rate);
        }   
        $this->updateRateScale($rate);
    }
    
    /**
     * Изменить статус расценки
     * 
     * @param Rate $rate
     * @param integer $status
     */
    public function updateRateStatus($rate, $status)
    {
        $rate->setStatus($status);
        $this->entityManager->persist($rate);
        $this->entityManager->flush($rate);
        $this->logManager->infoRate($rate, Log::STATUS_UPDATE);
    }
    
    /**
     * Изменить наименование расценки
     * 
     * @param Rate $rate
     * @param string $name
     */
    public function updateRateName($rate, $name)
    {
        $rate->setName($name);
        $this->entityManager->persist($rate);
        $this->entityManager->flush($rate);        
    }
    
    /**
     * Удалить расценку
     * 
     * @param Rate $rate
     */
    public function removeRate($rate)
    {
        $this->logManager->infoRate($rate, Log::STATUS_DELETE);
        $this->mlManager->removeModelRateScale($rate);
        $this->removeScale($rate->getScale());
        
//        $this->entityManager->remove($rate);
//        $this->entityManager->flush($rate);
    }    
}
