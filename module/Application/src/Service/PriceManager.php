<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Supplier;
use Application\Entity\Tax;
use Application\Entity\Country;
use Application\Entity\Producer;
use Application\Entity\Raw;
use Application\Entity\Rawprice;
use Application\Entity\PriceGetting;
use MvlabsPHPExcel\Service;
use Zend\Json\Json;

/**
 * Description of PriceManager
 *
 * @author Daddy
 */
class PriceManager {
    
    const PRICE_FOLDER       = './data/prices'; // папка с прайсами
    const PRICE_FOLDER_ARX   = './data/prices/arx'; // папка с архивами прайсов

    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /*
     * @var Admin\Service\PostManager
     */
    private $postManager;
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $postManager)
    {
        $this->entityManager = $entityManager;
        $this->postManager = $postManager;
    }
    
    public function getPriceFolder()
    {
        return self::PRICE_FOLDER;
    }        

    public function getPriceArxFolder()
    {
        return self::PRICE_FOLDER_ARX;
    }      
    
    
    /*
     * Очистить содержимое папки
     * 
     * @var Application\Entity\Supplier $supplier
     * @var string $folderName
     * 
     */
    public function clearPriceFolder($supplier, $folderName)
    {
        if (is_dir($folderName)){
            if ($dh = opendir($folderName)) {
                while (($file = readdir($dh)) !== false) {
                    if($file != "." && $file != ".."){ // если это не папка
                        if(is_file($folderName."/".$file)){ // если файл
                            unlink($folderName."/".$file);                            
                        }                        
                        // если папка, то рекурсивно вызываем
                        if(is_dir($folderName."/".$file)){
                            $this->clearPriceFolder($supplier, $folderName."/".$file);
                        }
                    }           
                }
                closedir($dh);
                
                if ($folderName != self::PRICE_FOLDER.'/'.$supplier->getId()){
                    rmdir($folderName);
                }
            }
        }
        
    }
    
    /**
     * Загрузка сырого прайса
     * @var Application\Entity\Supplier
     * @var string $filename
     */
    
    public function uploadRawprice($supplier, $filename){
                
        if (file_exists($filename)){
            $mvexcel = new Service\PhpExcelService();    
            $excel = $mvexcel->createPHPExcelObject($filename);
            
            $raw = new Raw();
            $raw->setSupplier($supplier);
            $raw->setFilename($filename);
            $raw->setStatus($raw->getStatusActive());

            $currentDate = date('Y-m-d H:i:s');
            $raw->setDateCreated($currentDate);
                        
            $sheets = $excel->getAllSheets();
            foreach ($sheets as $sheet) { // PHPExcel_Worksheet

                $excel_sheet_content = $sheet->toArray();

                if (count($sheet)){
                    foreach ($excel_sheet_content as $row){
                        $rawprice = new Rawprice();

                        $rawprice->setRawdata(Json::encode($row));

                        $rawprice->setRaw($raw);

                        $currentDate = date('Y-m-d H:i:s');
                        $rawprice->setDateCreated($currentDate);

                        // Добавляем сущность в менеджер сущностей.
                        $this->entityManager->persist($rawprice);
                        
                        $raw->addRawprice($rawprice);

                    }
                    // Применяем изменения к базе данных.
                }	
            }

            $this->entityManager->persist($raw);
            
            $this->entityManager->flush();                    
            
            unset($excel);
            unset($mvexcel);
        }
    }
    
    /*
     * 
     * Проверка папки с прайсами. Если в папке есть прайс то загружаем его
     * 
     * @var Application\Entity\Supplier $supplier
     * @var string $folderName
     * 
     */
    public function checkPriceFolder($supplier, $folderName)
    {
    
        if (is_dir($folderName)){
            if ($dh = opendir($folderName)) {
                while (($file = readdir($dh)) !== false) {
                    if($file != "." && $file != ".."){ // если это не папка
                        if(is_file($folderName."/".$file)){ // если файл
                            
                            if ($supplier->getStatus() == $supplier->getStatusActive()){
                                $this->uploadRawprice($supplier, $folderName."/".$file);
                            }
                            
                            $arx_folder = self::PRICE_FOLDER_ARX.'/'.$supplier->getId();
                            if (is_dir($arx_folder)){
                                if (!rename($folderName."/".$file, $arx_folder."/".$file)){
                                    unlink($folderName."/".$file);
                                }
                            }
                            
                        }                        
                        // если папка, то рекурсивно вызываем
                        if(is_dir($folderName."/".$file)){
                            $this->checkPriceFolder($supplier, $folderName."/".$file);
                        }
                    }           
                }
                closedir($dh);
            }
        }
    }
    
    /*
     * Проход по всем поставщикам - поиск файлов с прайсам в папках
     */
    public function checkSupplierPrice()
    {
        
        $suppliers = $this->entityManager->getRepository(Supplier::class)->findAll();
        
        foreach ($suppliers as $supplier){
            $this->checkPriceFolder($supplier, self::PRICE_FOLDER.'/'.$supplier->getId());
            $this->clearPriceFolder($supplier, self::PRICE_FOLDER.'/'.$supplier->getId());
        }
    }
    
    /**
     * Проверка почты в ящике поставщика
     * @var Application\Entity\PriceGettting $priceGetting
     */
    public function getPriceByMail($priceGetting)
    {
        $box = [
            'host' => 'imap.yandex.ru',
            'server' => '{imap.yandex.ru:993/imap/ssl}INBOX',
            'user' => $priceGetting->getEmail(),
            'password' => $priceGetting->getEmailPassword(),
            'leave_message' => true,
        ];
        
        $mailList = $this->postManager->readImap($box);
        
        if (count($mailList)){
            foreach ($mailList as $mail){
                if (count($mail['attachment'])){
                    foreach($mail['attachment'] as $attachment){
                        if ($attachment['filename'] && file_exists($attachment['temp_file'])){
                            $target = self::PRICE_FOLDER.'/'.$priceGetting->getSupplier()->getId().'/'.$attachment['filename'];
                            if (copy($attachment['temp_file'], $target)){
                                
                                if ($priceGetting->getOrderToApl == PriceGetting::ORDER_PRICE_FILE_TO_APL){
                                    
                                }
                                
                                unlink($attachment['temp_file']);
                            }
                        }
                    }
                }
            }
        }
        
        return;
    }
        
}
