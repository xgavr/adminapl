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
use Application\Entity\Goods;

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
    
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
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
        $this->entityManager->remove($scale);
        $this->entityManager->flush($scale);
    }
    
    
}
