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
use Zend\Config\Config;
use Zend\Config\Writer\PhpArray;

/**
 * Description of GoodsService
 *
 * @author Daddy
 */
class GoodsManager
{
    const IMAGE_DIR = './data/images'; //папка для хранения картинок
    const GOOD_IMAGE_DIR = './data/images/goods'; //папка для хранения картинок товаров    
    const TD_IMAGE_DIR = './data/images/goods/TD'; //папка для хранения картинок товаров
    
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
        
    public function addNewGoods($data, $flushnow=true) 
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
        if (array_key_exists('tax', $data)){
            if (!$data['tax']) $data['tax'] = $this->getSettings()->defaultTax;
        } else {
            $data['tax'] = $this->getSettings()->defaultTax;
        }    
        
        $tax = $this->entityManager->getRepository(Tax::class)
                    ->findOneById($data['tax']);
        if ($tax == null){
            $tax = new Tax();
        }
        
        $goods->setTax($tax);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($goods);
        
        if ($flushnow){
            // Применяем изменения к базе данных.
            $this->entityManager->flush();
        }
        
        return $goods;
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
        
        return $goods;
    }  
    
    /**
     * Обновить наименование товара
     * 
     * @param Application\Entity\Goods $good
     * @param string $name
     */
    public function updateGoodName($good, $name)
    {
        $good->setName($name);
        $this->entityManager->persist($good);
        $this->entityManager->flush($good);        
    }
    
    /**
     * Проверка возможности удаления товара
     * 
     * @param Application\Entity\Goods $good
     * @return boolean
     */
    public function allowRemove($good)
    {
        return true;
    }
    
    /**
     * Удаление связи со строками прайса
     * 
     * @param Application\Entity|goods $good
     */
    public function removeRawpriceAssociation($good)
    {
        foreach ($good->getRawprice() as $rawprice){
            $rawprice->setGood(null);
            $rawprice->setStatusGood($rawprice::GOOD_NEW);
            $this->entityManager->persist($rawprice);            
        }
        
        $this->entityManager->flush();        
    }

    /**
     * Удалене карточки товара
     * 
     * @param Application\Entity\Goods $good
     */
    public function removeGood($good) 
    {   

        if (!$this->allowRemove($good)){
            return false;
        }
        
        $this->removeRawpriceAssociation($good);
        
        $this->entityManager->remove($good);
        
        $this->entityManager->flush($good);
        
        return true;
    }    
    
    
    /**
     * Поиск и удаление товаров не привязаных к прайсам
     */
    public function removeEmpty()
    {
        $goodsForDelete = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForDelete();

        foreach ($goodsForDelete as $row){
            $this->removeGood($row[0]);
        }
        
        return count($goodsForDelete);
    }
    
    
    /**
     * Получить картинки из папки
     * 
     * @param string $folderImage
     * @return array
     */
    public function imagesFromFolder($folderImage, $images = null)
    {
        if (!$images){
            $images = [];
        }    

        if (is_dir($folderImage)){
            foreach (new \DirectoryIterator($folderImage) as $fileInfo) {
                if ($fileInfo->isDot()) continue;
                if ($fileInfo->isFile()){
                    $images[basename($folderImage)] = $fileInfo->getFilename();                            
                }
                if ($fileInfo->isDir()){
                    $this->images($fileInfo->getFilename(), $images);                    
                }
            }
        }
        return $images;
    }
    
    
    /**
     * Получить картинки товара
     * 
     * @param Application\Entity\Goods $good
     * @return array
     */
    public function images($good)
    {
        var_dump($good->getId()); exit;
        return $this->imagesFromFolder(self::GOOD_IMAGE_DIR.'/'.$good->getId());
    }
}
