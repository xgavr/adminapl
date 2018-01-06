<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Supplier;
use Application\Entity\Contact;
use Application\Entity\Phone;
use Application\Entity\Email;
use Application\Entity\Pricesettings;

/**
 * Description of SupplierService
 *
 * @author Daddy
 */
class SupplierManager
{
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Contact manager
     * @var Application\Service\ContactManager
     */
    private $contactManager;
  
    /**
     * Price manager
     * @var Application\Service\PriceManager
     */
    private $priceManager;
  
    /**
     * Raw manager
     * @var Application\Service\RawManager
     */
    private $rawManager;
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $contactManager, $priceManager, $rawManager)
    {
        $this->entityManager = $entityManager;
        $this->contactManager = $contactManager;
        $this->priceManager = $priceManager;
        $this->rawManager = $rawManager;
    }
    
    public function addPriceFolder($supplier)
    {
        //Создать папк для прайсов
        $price_data_folder_name = $this->priceManager->getPriceFolder();
        if (!is_dir($price_data_folder_name)){
            mkdir($price_data_folder_name);
        }
        
        $price_supplier_folder_name = $price_data_folder_name.'/'.$supplier->getId();
        if (!is_dir($price_supplier_folder_name)){
            mkdir($price_supplier_folder_name);
        }

        $arx_price_data_folder_name = $this->priceManager->getPriceArxFolder();
        if (!is_dir($arx_price_data_folder_name)){
            mkdir($arx_price_data_folder_name);
        }
        
        $arx_price_supplier_folder_name = $arx_price_data_folder_name.'/'.$supplier->getId();
        if (!is_dir($arx_price_supplier_folder_name)){
            mkdir($arx_price_supplier_folder_name);
        }
    }        
    
    public function addNewSupplier($data) 
    {
        // Создаем новую сущность.
        $supplier = new Supplier();
        $supplier->setName($data['name']);
        $supplier->setInfo($data['info']);
        $supplier->setAddress($data['address']);  
        $supplier->setStatus($data['status']);
        
        $currentDate = date('Y-m-d H:i:s');
        $supplier->setDateCreated($currentDate);        
        
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($supplier);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();

        $this->addPriceFolder($supplier);
    }   
    
    public function updateSupplier($supplier, $data) 
    {
        $supplier->setName($data['name']);
        $supplier->setInfo($data['info']);
        $supplier->setAddress($data['address']);
        $supplier->setStatus($data['status']);

        $this->entityManager->persist($supplier);
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
        
        $this->addPriceFolder($supplier);
    }    
    
    public function removeSupplier($supplier) 
    {   
        
        $contacts = $supplier->getContacts();
        foreach ($contacts as $contact) {
            $this->contactManager->remove($contact);
        }        
        
        $pricesettings = $supplier->getPricwsettings();
        foreach ($pricesettings as $pricesetting) {
            $this->removePricesettings($pricesetting);
        }

        $raws = $supplier->getRaw();
        foreach ($raws as $raw) {
            $this->rawManager->removeRaw($raw);
        }
        
        $this->entityManager->remove($supplier);
        
        $this->entityManager->flush();
    }    

     // Этот метод добавляет новый контакт.
    public function addContactToSupplier($supplier, $data) 
    {
       $this->contactManager->addNewContact($supplier, $data);
    }   
    
    public function addNewPricesettings($supplier, $data)
    {
        $pricesettings = new Pricesettings();
        $pricesettings->setArtice($data['article']);
        $pricesettings->setIid($data['iid']);
        $pricesettings->setName($data['name']);
        $pricesettings->setPrice($data['price']);
        $pricesettings->setProducer($data['producer']);
        $pricesettings->setRest($data['rest']);
        $pricesettings->setStatus($data['status']);
        $pricesettings->setSupplier($supplier);
        $pricesettings->setTitle($data['title']);
        
        $currentDate = date('Y-m-d H:i:s');
        $pricesettings->setDateCreated($currentDate);        
        
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($pricesettings);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }
    
    public function updatePricesettings($pricesettings, $data)
    {
        $pricesettings->setArtice($data['article']);
        $pricesettings->setIid($data['iid']);
        $pricesettings->setName($data['name']);
        $pricesettings->setPrice($data['price']);
        $pricesettings->setProducer($data['producer']);
        $pricesettings->setRest($data['rest']);
        $pricesettings->setStatus($data['status']);
        $pricesettings->setTitle($data['title']);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($pricesettings);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }
    
    public function removePricesettings($pricesettings)
    {
        $this->entityManager->remove($pricesettings);
        $this->entityManager->flush();
    }
}
