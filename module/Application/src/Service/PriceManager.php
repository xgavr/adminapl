<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Application\Entity\Supplier;
use Application\Entity\PriceGetting;
use Laminas\Http\Client;
use Application\Validator\FileExtensionValidator;
use Application\Validator\PriceNameValidator;
use Application\Filter\Basename;

/**
 * Description of PriceManager
 *
 * @author Daddy
 */
class PriceManager {
    
    const PRICE_FOLDER       = './data/prices'; // папка с прайсами
    const PRICE_FOLDER_ARX   = './data/prices/arx'; // папка с архивами прайсов
    const PRICE_FILE_EXTENSIONS   = 'zip, rar, xls, xlsx, csv, txt'; //допустимые расширения файлов прайсов

    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /*
     * @var \Admin\Service\PostManager
     */
    private $postManager;
  
    /*
     * @var \Admin\Service\FtpManager
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
            foreach (new \DirectoryIterator($folderName) as $fileInfo) {
                if ($fileInfo->isDot()) {
                    continue;
                }
                if ($fileInfo->isFile()){
                    unlink($fileInfo->getFilename());                            
                }
                if ($fileInfo->isDir()){
                    $this->clearPriceFolder($supplier, $fileInfo->getFilename());
                    
                }
            }
            if ($folderName != self::PRICE_FOLDER.'/'.$supplier->getId()){
                rmdir($folderName);
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
            $filter = new Basename();
            $destfile = '/'.$supplier->getAplId().'/'.$filter->filter($filename);
            return $this->ftpManager->putPriceToApl(['source_file' => realpath($filename), 'dest_file' => $destfile]);
        }
        
        return;
    }
    
    
    /**
     * Проверка почты в ящике поставщика
     * @param Application\Entity\PriceGettting $priceGetting
     */
    public function getPriceByMail($priceGetting)
    {
        if ($priceGetting->getEmail() && $priceGetting->getEmailPassword()){
            $box = [
                'host' => 'imap.yandex.ru',
                'server' => '{imap.yandex.ru:993/imap/ssl}',
                'user' => $priceGetting->getEmail(),
                'password' => $priceGetting->getEmailPassword(),
                'leave_message' => false,
            ];

            $mailList = $this->postManager->readImap($box);

            $priceNameValidator = new PriceNameValidator();
            
            if (count($mailList)){
                foreach ($mailList as $mail){
                    if (isset($mail['attachment'])){
                        foreach($mail['attachment'] as $attachment){
                            if ($attachment['filename'] && file_exists($attachment['temp_file'])){
                                //Проверка наименования файла
                                if (!$priceNameValidator->isValid($attachment['filename'], $priceGetting)){
                                    unlink($attachment['temp_file']);                                    
                                }
                                if (file_exists($attachment['temp_file'])){ 
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
        }    
        
        return;
    }
    
    /**
     * Прочитать очередной ящик
     */
    public function readQueyeMailBox()
    {
        set_time_limit(300);
                
        $priceGetting = $this->entityManager->getRepository(PriceGetting::class)
                ->findOneBy(['status' => PriceGetting::STATUS_ACTIVE, 'mailBoxCheck' => PriceGetting::MAILBOX_TO_CHECK]);
        
        if ($priceGetting){
            $this->getPriceByMail($priceGetting);
            
            $this->entityManager->getConnection()->update('price_gettings', 
                    ['mailbox_check' => PriceGetting::MAILBOX_CHECKED], 
                    ['id' => $priceGetting->getId()]);
            
        } else {
            $priceGettings = $this->entityManager->getRepository(PriceGetting::class)
                    ->findBy(['status' => PriceGetting::STATUS_ACTIVE, 'mailBoxCheck' => PriceGetting::MAILBOX_CHECKED]);
            
            foreach ($priceGettings as $priceGetting){
                $this->entityManager->getConnection()->update('price_gettings', 
                        ['mailbox_check' => PriceGetting::MAILBOX_TO_CHECK], 
                        ['id' => $priceGetting->getId()]);
            }
            
            $this->readQueyeMailBox();
        }     
        return;
    }
    

    /**
     * Получить прайс по ссылке
     * @param Application\Entity\PriceGettting $priceGetting
     */
    public function getPriceByLink($priceGetting)
    {
        if ($priceGetting->getLink()){
            $pathinfo = pathinfo($priceGetting->getLink());
            
            $client = new Client($priceGetting->getLink());
            $client->setMethod('GET');
            $client->setOptions(['timeout' => 60]);
            
            $response = $client->send();
            
            $filename = '';
            if ($response->isSuccess()){
                
                $validator = new FileExtensionValidator(self::PRICE_FILE_EXTENSIONS);
                
                if ($validator->isValid($pathinfo['basename'])){
                    $filename = $pathinfo['basename'];
                } else {
                    
                    if ($response->getHeaders()->get('Content-Disposition')){
                    
                        preg_match_all("/\w+\.\w+/", $response->getHeaders()->get('Content-Disposition')->getFieldValue(), $output);

                        if ($validator->isValid($output[0][0])){
                            $filename = $output[0][0];
                        }    
                    }    
                }
                
                if ($filename){
                    //Проверка наименования файла
                    $priceNameValidator = new PriceNameValidator();
                    if (!$priceNameValidator->isValid($filename, $priceGetting)){
                        return;
                    }

                    $target = self::PRICE_FOLDER.'/'.$priceGetting->getSupplier()->getId().'/'.$filename;
                    $result = file_put_contents($target, $response->getBody());

                    if ($result === false){

                    } else {
                        if ($priceGetting->getOrderToApl() == PriceGetting::ORDER_PRICE_FILE_TO_APL){    
                            $destfile = '/'.$priceGetting->getSupplier()->getAplId().'/'.$filename;
                            $this->ftpManager->putPriceToApl(['source_file' => $target, 'dest_file' => $destfile]);
                        }                      
                    }
                }    
            }            
        }
        
        return;
    }
    
    /**
     * Получить прайсы по ссылке
     */
    public function getPricesByLink()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(900);
        $startTime = time();
        
        $priceGettings = $this->entityManager->getRepository(PriceGetting::class)
                ->findBy(['status' => PriceGetting::STATUS_ACTIVE]);

        foreach ($priceGettings as $priceGetting){
            $this->getPriceByLink($priceGetting);
            if (time() > $startTime + 840){
                return;
            }            
        }        
    }
}
