<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Goods;
use Application\Entity\Producer;
use Application\Entity\Tax;
use MvlabsPHPExcel\Service;

/**
 * Description of GoodsService
 *
 * @author Daddy
 */
class GoodsManager
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
    
    public function addNewGoods($data) 
    {
        // Создаем новую сущность Goods.
        $goods = new Goods();
        $goods->setName($data['name']);
        $goods->setCode($data['code']);
        $goods->setAvailable($data['available']);
        $goods->setDescription($data['description']);
        
        $producer = $this->entityManager->getRepository(Producer::class)
                    ->findOneById($data['producer']);
        if ($producer == null){
            $producer = new Producer();
        }
        
        $goods->setProducer($producer);
        
        $tax = $this->entityManager->getRepository(Tax::class)
                    ->findOneById($data['tax']);
        if ($tax == null){
            $tax = new Tax();
        }
        
        $goods->setTax($tax);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($goods);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }   
    
    public function updateGoods($goods, $data) 
    {
        $goods->setName($data['name']);
        $goods->setCode($data['code']);
        $goods->setAvailable($data['available']);
        $goods->setDescription($data['description']);
               
        $producer = $this->entityManager->getRepository(Producer::class)
                    ->findOneById($data['producer']);
        if ($producer == null){
            $producer = new Producer();
        }
        
        $goods->setProducer($producer);
        
        $tax = $this->entityManager->getRepository(Tax::class)
                    ->findOneById($data['tax']);
        if ($tax == null){
            $tax = new Tax();
        }
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    
    
    public function removeGoods($goods) 
    {   
        $this->entityManager->remove($goods);
        
        $this->entityManager->flush();
    }    

}
