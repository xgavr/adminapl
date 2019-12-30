<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Goods;
use Application\Entity\Scale;
use Application\Entity\ScaleTreshold;
use Application\Entity\Rate;
use Company\Entity\Office;

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

    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $mlManager)
    {
        $this->entityManager = $entityManager;
        $this->mlManager = $mlManager;
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

        $this->entityManager->flush($treshold);
        
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
        $this->entityManager->flush($treshold);
        
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
        
        $samples = $this->mlManager::RATE_SAMPLES;
        
        $scale = $this->addScale($params);
        $this->addTreshold($scale, [
            'treshold' => $minPrice,
            'rounding' => ScaleTreshold::DEFAULT_ROUNDING,
            'rate' => $this->mlManager->predictPrimaryScale($minPrice),
        ]);
        
        $tresholds[] = $minPrice; 
        foreach ($samples as $sample){
            if ($minPrice < $sample && $maxPrice > $sample){
                $this->addTreshold($scale, [
                    'treshold' => $sample,
                    'rounding' => ScaleTreshold::DEFAULT_ROUNDING,
                    'rate' => $this->mlManager->predictPrimaryScale($sample),
                ]);
            }
        }
        $this->addTreshold($scale, [
            'treshold' => $maxPrice,
            'rounding' => ScaleTreshold::DEFAULT_ROUNDING,
            'rate' => $this->mlManager->predictPrimaryScale($maxPrice),
        ]);
        
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
        $scales = $this->entityManager->getRepository(Scale::class)
                ->findBy([]);
        foreach ($scales as $scale){
            return $scale;
        }        
        
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
        $rate = new Rate();
        $rate->setName($data['name']);
        $rate->setStatus(Rate::STATUS_ACTIVE);
        $rate->setMode(Rate::MODE_MARKUP);
        
        $defaultOffice = $this->entityManager->getRepository(Office::class)
                ->findOneById(1);
        
        $rate->setOffice($defaultOffice);
        $rate->setScale($this->getDefaultScale($data));
        
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
        $this->entityManager->remove($rate);
        $this->entityManager->flush($rate);
    }
}
