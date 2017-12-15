<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Currency;
use Application\Entity\Currencyrate;

/**
 * Description of CurrencyService
 *
 * @author Daddy
 */
class CurrencyManager
{
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    public function addNewCurrency($data) 
    {
        // Создаем новую сущность.
        $currency = new Currency();
        $currency->setName($data['name']);
        $currency->setDescription($data['description']);
        
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($currency);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }   
    
    public function updateCurrency($currency, $data) 
    {
        $currency->setName($data['name']);
        $currency->setDescription($data['description']);

        $this->entityManager->persist($currency);
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    
    
    public function removeCurrency($currency) 
    {   
        
         // Удаляем связанные rate.
        $rates = $currency->getRate();
        foreach ($rates as $rate) {
            $this->entityManager->remove($rate);
        }
        
        $this->entityManager->remove($currency);
        
        $this->entityManager->flush();
    }    

    public function addRateToCurrency($currency, $data){
        
        $rate = new Currencyrate();
        $rate->setDateRate($data['dateRate']);
        $rate->setRate($data['rate']);
        $rate->setCurrency($currency);
        
        $this->entityManager->persist($rate);
        $this->entityManager->flush();
    }
    
    public function removeRate($rate) 
    {   
        
        $this->entityManager->remove($rate);
        
        $this->entityManager->flush();
    }    

    
}
