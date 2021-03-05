<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Laminas\ServiceManager\ServiceManager;
use Application\Entity\Supplier;
use Application\Entity\PriceDescription;
use Application\Entity\PriceGetting;
use Application\Entity\BillGetting;
use Application\Entity\BillSetting;
use Application\Entity\RequestSetting;
use Application\Entity\SupplySetting;
use Company\Entity\Office;
use Laminas\File\ClassFileLocator;
use Stock\Entity\Mutual;
use Application\Entity\SupplierApiSetting;


/**
 * Description of SupplierService
 *
 * @author Daddy
 */
class SupplierManager
{
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Contact manager
     * @var \Application\Service\ContactManager
     */
    private $contactManager;
  
    /**
     * Price manager
     * @var \Application\Service\PriceManager
     */
    private $priceManager;
  
    /**
     * Raw manager
     * @var \Application\Service\RawManager
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
        $supplier->setAplId($data['aplId']);
        $supplier->setStatus($data['status']);
        $supplier->setPrepayStatus($data['prepayStatus']);
        $supplier->setPriceListStatus($data['priceListStatus']);
        $supplier->setAmount(0);
        $supplier->setQuantity(0);
        
        $currentDate = date('Y-m-d H:i:s');
        $supplier->setDateCreated($currentDate);        
        
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($supplier);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();

        $this->addPriceFolder($supplier);
        
        return $supplier;
    }   
    
    public function getPriceFolder($supplier)
    {
        $this->addPriceFolder($supplier);
        $price_data_folder_name = $this->priceManager->getPriceFolder();
        return $price_data_folder_name.'/'.$supplier->getId();
    }  
    
    public function getPriceArxFolder($supplier)
    {
        $this->addPriceFolder($supplier);
        $arx_price_data_folder_name = $this->priceManager->getPriceArxFolder();
        return $arx_price_data_folder_name.'/'.$supplier->getId();
    }  
    
    /*
     * Показать файлы в папке с прайсами
     * @var Application\Entity\Supplier $supplier
     * 
     * return array
     */
    public function getLastPriceFile($supplier)
    {
        $path = $this->getPriceFolder($supplier);

        $result = [];
        foreach (new \DirectoryIterator($path) as $fileInfo) {
            if($fileInfo->isDot()) continue;
            $result[$fileInfo->getFilename()] = [
                'path' => $fileInfo->getPathname(), 
                'date' => $fileInfo->getMTime(),
                'size' => $fileInfo->getSize(),
            ];
        }        

        return $result;
    }    
    
    /*
     * Показать файлы в архивной папке с прайсами
     * @var Application\Entity\Supplier $supplier
     * 
     * return array
     */
    public function getArxPriceFile($supplier)
    {
        $arxPath = $this->getPriceArxFolder($supplier);
        $result = [];
        $i = 0;
        foreach (new \DirectoryIterator($arxPath) as $fileInfo) {
            if($fileInfo->isDot()) continue;
            $result[$fileInfo->getFilename()] = [
                'path' => $fileInfo->getPathname(), 
                'date' => $fileInfo->getMTime(),
                'size' => $fileInfo->getSize(),
            ];
            $i++;
            if ($i > 10) break;
        }    
        return $result;
    }    
    
    /*
     * Получить массив файлов с прайсами для загрузки
     * 
     * return array
     */
    public function getPriceFilesToUpload()
    {
        $result = [];
        $sort_array = [];
        $priceGettings = $this->entityManager->getRepository(PriceGetting::class)
                    ->findBy(['status' => PriceGetting::STATUS_ACTIVE]);
        
        foreach ($priceGettings as $priceGetting){
            $files = $this->getLastPriceFile($priceGetting->getSupplier());
            if (count($files)){
                foreach ($files as $filename=>$value){
                    $sort_array[] = $value['date'];
                    $result[] = [
                        'priceGetting' => $priceGetting, 
                        'filename' => $filename, 
                        'value' => $value,
                        ];
                }
            }
        }
        
        array_multisort($sort_array, $result);
        
        return $result;
    }
    
    /**
     * Обновить поставщика
     * 
     * @param Supplier $supplier
     * @param array $data
     */
    public function updateSupplier($supplier, $data) 
    {
        $supplier->setName($data['name']);
        $supplier->setAplId($data['aplId']);
        $supplier->setStatus($data['status']);
        $supplier->setPrepayStatus($data['prepayStatus']);
        $supplier->setPriceListStatus($data['priceListStatus']);

        $this->entityManager->persist($supplier);
        // Применяем изменения к базе данных.
        $this->entityManager->flush($supplier);
        
        $this->addPriceFolder($supplier);
    }    
    
    /**
     * Обновить статусы поставщика
     * 
     * @param Supplier $supplier
     * @param array $data
     */
    public function updateSupplierStatus($supplier, $data) 
    {
        if (isset($data['status'])){
            $supplier->setStatus($data['status']);
        }    
        if (isset($data['prepayStatus'])){
            $supplier->setPrepayStatus($data['prepayStatus']);
        }    
        if (isset($data['priceListStatus'])){
            $supplier->setPriceListStatus($data['priceListStatus']);
        }    

        $this->entityManager->persist($supplier);
        // Применяем изменения к базе данных.
        $this->entityManager->flush($supplier);
    }    
    
    /**
     * Удалить поставщика 
     * 
     * @param Supplier $supplier
     */
    public function removeSupplier($supplier) 
    {   
        
        $contacts = $supplier->getContacts();
        foreach ($contacts as $contact) {
            $this->contactManager->remove($contact);
        }        
        
        $priceDescriptions = $supplier->getPriceDescriptions();
        foreach ($priceDescriptions as $priceDescription) {
            $this->removePriceDescription($priceDescription);
        }

        $priceGettings = $supplier->getPriceGettings();
        foreach ($priceGettings as $priceGetting) {
            $this->removePriceGetting($priceGetting);
        }

        $requestSettings = $supplier->getRequestSettings();
        foreach ($requestSettings as $requestSetting) {
            $this->removeRequestSetting($requestSetting);
        }

        $supplySettings = $supplier->getSupplySettings();
        foreach ($supplySettings as $supplySetting) {
            $this->removeSupplySetting($supplySetting);
        }

        $billSettings = $supplier->getBillSettings();
        foreach ($billSettings as $billSettings) {
            $this->removeBillSetting($billSetting);
        }

        $billGettings = $supplier->getBillGettings();
        foreach ($billGettings as $billGettings) {
            $this->removeBillGetting($billGetting);
        }

        $raws = $supplier->getRaw();
        foreach ($raws as $raw) {
            $this->rawManager->removeRaw($raw);
        }
        
        foreach ($supplier->getRates() as $rate){
            $this->entityManager->remove($rate);
        }
        
        $this->entityManager->remove($supplier);
        
        $this->entityManager->flush();
    }    

     // Этот метод добавляет новый контакт.
    public function addContactToSupplier($supplier, $data) 
    {
       $this->contactManager->addNewContact($supplier, $data);
    }   
    
    public function addNewPriceDescription($supplier, $data)
    {
        $priceDescription = new PriceDescription();
        $priceDescription->setArtice($data['article']);
        $priceDescription->setBar($data['bar']);
        $priceDescription->setCar($data['car']);
        $priceDescription->setComment($data['comment']);
        $priceDescription->setCountry($data['country']);
        $priceDescription->setCurrency($data['currency']);
        $priceDescription->setIid($data['iid']);
        $priceDescription->setLot($data['lot']);
        $priceDescription->setName($data['name']);
        $priceDescription->setOem($data['oem']);
        $priceDescription->setBrand($data['brand']);
        $priceDescription->setPrice($data['price']);
        $priceDescription->setProducer($data['producer']);
        $priceDescription->setDefaultProducer($data['defaultProducer']);
        $priceDescription->setRest($data['rest']);
        $priceDescription->setStatus($data['status']);
        $priceDescription->setUnit($data['unit']);
        $priceDescription->setPack($data['pack']);
        $priceDescription->setTitle($data['title']);
        $priceDescription->setVendor($data['vendor']);
        $priceDescription->setWeight($data['weight']);
        $priceDescription->setMarkdown($data['markdown']);
        $priceDescription->setSale($data['sale']);
        $priceDescription->setImage($data['image']);
        $priceDescription->setType($data['type']);
        
        $currentDate = date('Y-m-d H:i:s');
        $priceDescription->setDateCreated($currentDate);        
        
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($priceDescription);

        $priceDescription->setSupplier($supplier);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }
    
    public function updatePriceDescription($priceDescription, $data)
    {
        $priceDescription->setArtice($data['article']);
        $priceDescription->setBar($data['bar']);
        $priceDescription->setCar($data['car']);
        $priceDescription->setComment($data['comment']);
        $priceDescription->setCountry($data['country']);
        $priceDescription->setCurrency($data['currency']);
        $priceDescription->setIid($data['iid']);
        $priceDescription->setLot($data['lot']);
        $priceDescription->setName($data['name']);
        $priceDescription->setOem($data['oem']);
        $priceDescription->setBrand($data['brand']);
        $priceDescription->setPrice($data['price']);
        $priceDescription->setProducer($data['producer']);
        $priceDescription->setDefaultProducer($data['defaultProducer']);
        $priceDescription->setRest($data['rest']);
        $priceDescription->setStatus($data['status']);
        $priceDescription->setUnit($data['unit']);
        $priceDescription->setPack($data['pack']);
        $priceDescription->setTitle($data['title']);
        $priceDescription->setVendor($data['vendor']);
        $priceDescription->setWeight($data['weight']);
        $priceDescription->setMarkdown($data['markdown']);
        $priceDescription->setSale($data['sale']);
        $priceDescription->setImage($data['image']);
        $priceDescription->setType($data['type']);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($priceDescription);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }
    
    public function removePriceDescription($priceDescription)
    {
        $this->entityManager->remove($priceDescription);
        $this->entityManager->flush();
    }
    
    /**
     * Описание полей прайса для ParsPriceList
     * @param Application\Entity\PriceDescription $priceDescription
     * 
     * @return string
     */
    public function parsPriceListText($priceDescription)
    {
        $aplLabels = $priceDescription->getAplLabels();
        if (count($aplLabels)){
            $result  = '            if ($this->_supplier === '.$priceDescription->getSupplier()->getAplId().'){ //'.$priceDescription->getSupplier()->getName().PHP_EOL;
            $result .= '                if ($this->_datatype == "price"){'.PHP_EOL;
            foreach ($aplLabels as $key => $value){
                switch ($key){
                    case 'price': 
                        $result .= '                    $result["'.$key.'"] = $this->_getPrice($desc["col'.$value.'"]);'.PHP_EOL;
                        break;  
                    case 'oe':
			$result .= '                    $oes = $desc["col'.$value.'"];'.PHP_EOL;
			$result .= '                    $oes = str_replace(array(",", "\\\", "/", "+", "|", "\t"), ";", $oes);'.PHP_EOL;
			$result .= '                    $oes = explode(";", $oes);'.PHP_EOL;
			$result .= '                    $newoes = array();'.PHP_EOL;
			$result .= '                    foreach ($oes as $oe){'.PHP_EOL;
			$result .= '                        $newoes[] = $this->_preg_oe($oe);'.PHP_EOL;
			$result .= '                    }'.PHP_EOL;					
			$result .= '                    $sourse_oe = implode(";", $newoes);'.PHP_EOL;
			$result .= '                    $result["oe"] = $sourse_oe;'.PHP_EOL;
			$result .= '                    $firstsp = trim($newoes[0]);'.PHP_EOL;
			$result .= '                    $result["source_oe"] = trim($firstsp);'.PHP_EOL;
                        break;
                    case 'rest': 
                        $result .= '                    $result["presence"]  = (trim($desc["col'.$value.'"]) && trim($desc["col'.$value.'"]) != "-") ? 1:0;'.PHP_EOL;
                    default :
                        $result .= '                    $result["'.$key.'"] = trim($desc["col'.$value.'"]);'.PHP_EOL;
                        break;
                }
            }    
            $result .= '                }'.PHP_EOL;
            $result .= '            }'.PHP_EOL;
        }
        
        return $result;
    }
    
    public function addNewPriceGetting($supplier, $data)
    {
        $priceGetting = new PriceGetting();
        $priceGetting->setName($data['name']);
        $priceGetting->setFtp($data['ftp']);
        $priceGetting->setFtpDir($data['ftpDir']);
        $priceGetting->setFtpLogin($data['ftpLogin']);
        $priceGetting->setFtpPassword($data['ftpPassword']);
        $priceGetting->setEmail($data['email']);
        $priceGetting->setEmailPassword($data['emailPassword']);
        $priceGetting->setLink($data['link']);
        $priceGetting->setStatus($data['status']);
        $priceGetting->setStatusFilename($data['statusFilename']);
        $priceGetting->setFilename($data['filename']);
        $priceGetting->setOrderToApl($data['orderToApl']);
        
        $priceSupplier = $this->entityManager->getRepository(Supplier::class)
                ->findOneById($data['priceSupplier']);
        if ($priceSupplier){
            $priceGetting->setPriceSupplier($priceSupplier);
        } else{
            $priceGetting->setPriceSupplier(NULL);
        }    
        
        $currentDate = date('Y-m-d H:i:s');
        $priceGetting->setDateCreated($currentDate);        
        
        $priceGetting->setSupplier($supplier);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($priceGetting);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }
    
    public function updatePriceGetting($priceGetting, $data)
    {
        $priceGetting->setName($data['name']);
        $priceGetting->setFtp($data['ftp']);
        $priceGetting->setFtpDir($data['ftpDir']);
        $priceGetting->setFtpLogin($data['ftpLogin']);
        $priceGetting->setFtpPassword($data['ftpPassword']);
        $priceGetting->setEmail($data['email']);
        $priceGetting->setEmailPassword($data['emailPassword']);
        $priceGetting->setLink($data['link']);
        $priceGetting->setStatus($data['status']);
        $priceGetting->setStatusFilename($data['statusFilename']);
        $priceGetting->setFilename($data['filename']);
        $priceGetting->setOrderToApl($data['orderToApl']);
        $priceSupplier = $this->entityManager->getRepository(Supplier::class)
                ->findOneById($data['priceSupplier']);
        if ($priceSupplier){
            $priceGetting->setPriceSupplier($priceSupplier);
        } else{
            $priceGetting->setPriceSupplier(NULL);
        }    
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($priceGetting);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }
    
    public function removePriceGetting($priceGetting)
    {
        $this->entityManager->remove($priceGetting);
        $this->entityManager->flush();
    }
    
    public function addNewBillGetting($supplier, $data)
    {
        $billGetting = new BillGetting();
        $billGetting->setName($data['name']);
        $billGetting->setEmail($data['email']);
        $billGetting->setEmailPassword($data['emailPassword']);
        $billGetting->setStatus($data['status']);
        
        $currentDate = date('Y-m-d H:i:s');
        $billGetting->setDateCreated($currentDate);        
        
        $billGetting->setSupplier($supplier);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($billGetting);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
        
//        var_dump($billGetting); exit;
    }
    
    public function updateBillGetting($billGetting, $data)
    {
        $billGetting->setName($data['name']);
        $billGetting->setEmail($data['email']);
        $billGetting->setEmailPassword($data['emailPassword']);
        $billGetting->setStatus($data['status']);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($billGetting);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }
    
    public function removeBillGetting($billGetting)
    {
        $this->entityManager->remove($billGetting);
        $this->entityManager->flush();
    }
    
    /**
     * Добавить настройки чтерия накладных
     * 
     * @param Supplier $supplier
     * @param array $data
     * @return BillSetting
     */
    public function addBillSetting($supplier, $data)
    {
        $billSetting = new BillSetting();
        $billSetting->setName($data['name']);
        $billSetting->setStatus($data['status']);
        $billSetting->setDescription($data['description']);
        $billSetting->setSupplier($supplier);
        
        $this->entityManager->persist();
        $this->entityManager->flush();
        
        return $BillSetting;
    }
    
    /**
     * Обновить настройки чтерия накладных
     * 
     * @param BillSetting $billSetting
     * @param array $data
     * @return BillSetting
     */
    public function updateBillSetting($billSetting, $data)
    {
        $billSetting->setName($data['name']);
        $billSetting->setStatus($data['status']);
        $billSetting->setDescription($data['description']);
        
        $this->entityManager->persist();
        $this->entityManager->flush();
        
        return $BillSetting;
    }
    
    /**
     * Удалить настройки чтерия накладных
     * 
     * @param BillSetting $billSetting
     */
    public function removeBillSetting($billSetting)
    {
        
        $this->entityManager->remove($billSetting);
        $this->entityManager->flush();
        
        return;
    }
            
    public function addNewRequestSetting($supplier, $data)
    {
        $requestSetting = new RequestSetting();
        $requestSetting->setName($data['name']);
        $requestSetting->setDescription($data['description']);
        $requestSetting->setSite($data['site']);
        $requestSetting->setLogin($data['login']);
        $requestSetting->setPassword($data['password']);
        $requestSetting->setMode($data['mode']);
        $requestSetting->setStatus($data['status']);
        
        $currentDate = date('Y-m-d H:i:s');
        $requestSetting->setDateCreated($currentDate);        
        
        $requestSetting->setSupplier($supplier);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($requestSetting);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }
    
    public function updateRequestSetting($requestSetting, $data)
    {
        $requestSetting->setName($data['name']);
        $requestSetting->setDescription($data['description']);
        $requestSetting->setSite($data['site']);
        $requestSetting->setLogin($data['login']);
        $requestSetting->setPassword($data['password']);
        $requestSetting->setMode($data['mode']);
        $requestSetting->setStatus($data['status']);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($requestSetting);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }
    
    public function removeRequestSetting($requestSetting)
    {
        $this->entityManager->remove($requestSetting);
        $this->entityManager->flush();
    }
    
    public function addNewSupplySetting($supplier, $data)
    {
        $supplySetting = new SupplySetting();
        $supplySetting->setOrderBefore($data['orderBefore']);
        $supplySetting->setSupplyTime($data['supplyTime']);
        $supplySetting->setSupplySat($data['supplySat']);
        $supplySetting->setStatus($data['status']);
        
        $currentDate = date('Y-m-d H:i:s');
        $supplySetting->setDateCreated($currentDate); 
        
        $office = $this->entityManager->getRepository(Office::class)
                ->findOneById($data['office']);
        $supplySetting->setOffice($office);
        
        $supplySetting->setSupplier($supplier);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($supplySetting);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }
    
    public function updateSupplySetting($supplySetting, $data)
    {
        $supplySetting->setOrderBefore($data['orderBefore']);
        $supplySetting->setSupplyTime($data['supplyTime']);
        $supplySetting->setSupplySat($data['supplySat']);
        $supplySetting->setStatus($data['status']);

        $office = $this->entityManager->getRepository(Office::class)
                ->findOneById($data['office']);
        $supplySetting->setOffice($office);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($supplySetting);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }
    
    public function removeSupplySetting($supplySetting)
    {
        $this->entityManager->remove($supplySetting);
        $this->entityManager->flush();
    }
    
    /**
     * Создать новый SupplierApiSetting
     * 
     * @param Supplier $supplier
     * @param array $data
     */
    public function addNewSupplierApiSetting($supplier, $data)
    {
        $supplierApiSetting = new SupplierApiSetting();
        $supplierApiSetting->setName($data['name']);
        $supplierApiSetting->setLogin($data['login']);
        $supplierApiSetting->setPassword($data['password']);
        $supplierApiSetting->setUserId($data['userId']);
        $supplierApiSetting->setStatus($data['status']);
        
        $currentDate = date('Y-m-d H:i:s');
        $supplierApiSetting->setDateCreated($currentDate); 
        
        $supplierApiSetting->setSupplier($supplier);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($supplierApiSetting);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }
    
    /**
     * Обновить SupplierApiSetting
     * 
     * @param SupplierApiSetting $supplierApiSetting
     * @param array $data
     */    
    public function updateSupplierApiSetting($supplierApiSetting, $data)
    {
        $supplierApiSetting->setName($data['name']);
        $supplierApiSetting->setLogin($data['login']);
        $supplierApiSetting->setPassword($data['password']);
        $supplierApiSetting->setUserId($data['userId']);
        $supplierApiSetting->setStatus($data['status']);

        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($supplierApiSetting);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    
    
    /**
     * Удалить SupplierApiSetting
     * @param SupplierApiSetting $supplierApiSetting
     */
    public function removeSupplierApiSetting($supplierApiSetting)
    {
        $this->entityManager->remove($supplierApiSetting);
        $this->entityManager->flush();
    }    
    
    public function checkPriceFolder($supplier)
    {
        $this->rawManager->checkSupplierPrice($supplier);
    }
    
    /**
     * Обновить сумму поставок поставщика
     * 
     * @param Supplier $supplier
     */
    public function updateAmount($supplier)
    {
        $amount = $this->entityManager->getRepository(Mutual::class)
                ->supplierAmount($supplier);
        $this->entityManager->getConnection()->update('supplier', ['amount' => $amount], ['id' => $supplier->getId()]);
    }

    /**
     * Обновить сумму поставок поставщиков
     * 
     */
    public function updateAmounts()
    {
        ini_set('memory_limit', '512M');
        
        $suppliers = $this->entityManager->getRepository(Supplier::class)
                ->findAll();
        foreach ($suppliers as $supplier){
            $this->updateAmount($supplier);
        }
    }
}
