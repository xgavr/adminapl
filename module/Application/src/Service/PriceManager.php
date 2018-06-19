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
  
    /*
     * @var Admin\Service\FtpManager
     */
    private $ftpManager;
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $postManager, $ftpManager)
    {
        $this->entityManager = $entityManager;
        $this->postManager = $postManager;
        $this->ftpManager = $ftpManager;
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
    
    public function putPriceFileToApl($supplier, $filename)
    {
        if (file_exists($filename)){
            $pathinfo = pathinfo($filename);
            $destfile = '/'.$supplier->getAplId().'/'.$pathinfo['basename'];
            return $this->ftpManager->putPriceToApl(['source_file' => realpath($filename), 'dest_file' => $destfile]);
        }
        
        return;
    }
    
    
    /**
     * Проверка почты в ящике поставщика
     * @var Application\Entity\PriceGettting $priceGetting
     */
    public function getPriceByMail($priceGetting)
    {
        if ($priceGetting->getEmail() && $priceGetting->getEmailPassword()){
            $box = [
                'host' => 'imap.yandex.ru',
                'server' => '{imap.yandex.ru:993/imap/ssl}INBOX',
                'user' => $priceGetting->getEmail(),
                'password' => $priceGetting->getEmailPassword(),
                'leave_message' => false,
            ];

            $mailList = $this->postManager->readImap($box);

            if (count($mailList)){
                foreach ($mailList as $mail){
                    if (count($mail['attachment'])){
                        foreach($mail['attachment'] as $attachment){
                            if ($attachment['filename'] && file_exists($attachment['temp_file'])){
                                $target = self::PRICE_FOLDER.'/'.$priceGetting->getSupplier()->getId().'/'.$attachment['filename'];
                                if (copy($attachment['temp_file'], $target)){
                                    if ($priceGetting->getOrderToApl() == PriceGetting::ORDER_PRICE_FILE_TO_APL){    
                                        $destfile = '/'.$priceGetting->getSupplier()->getAplId().'/'.$attachment['filename'];
                                        $this->ftpManager->putPriceToApl(['source_file' => $attachment['temp_file'], 'dest_file' => $destfile]);
                                    }    
                                    unlink($attachment['temp_file']);
                                }
                            }
                        }
                    }
                }
            }
        }    
        
        return;
    }

    
    public function getPriceByLink($priceGetting)
    {
        if ($priceGetting->getLink()){
            $pathinfo = pathinfo($priceGetting->getLink());
            $target = self::PRICE_FOLDER.'/'.$priceGetting->getSupplier()->getId().'/'.$pathinfo['basename'];
            if ($result = copy($priceGetting->getLink(), $target)){
                if ($priceGetting->getOrderToApl() == PriceGetting::ORDER_PRICE_FILE_TO_APL){    
                    $destfile = '/'.$priceGetting->getSupplier()->getAplId().'/'.$pathinfo['basename'];
                    $this->ftpManager->putPriceToApl(['source_file' => $target, 'dest_file' => $destfile]);
                }  
                return $result;
            }
        }
        
        return;
    }
    
}
