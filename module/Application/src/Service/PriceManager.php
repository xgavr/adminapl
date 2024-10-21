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
use Application\Entity\Raw;

/**
 * Description of PriceManager
 *
 * @author Daddy
 */
class PriceManager {
    
    const PRICE_FOLDER       = './data/prices'; // папка с прайсами
    const PRICE_FOLDER_ARX   = './data/prices/arx'; // папка с архивами прайсов
    const PRICE_FOLDER_NEW   = './data/prices/new'; // папка с новыми прайсами
    const PRICE_FILE_EXTENSIONS   = 'zip, rar, xls, xlsx, csv, txt'; //допустимые расширения файлов прайсов

    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * @var \Admin\Service\PostManager
     */
    private $postManager;
  
    /**
     * @var \Admin\Service\FtpManager
     */
    private $ftpManager;
  
    /**
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $postManager, $ftpManager,
            $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->postManager = $postManager;
        $this->ftpManager = $ftpManager;
        $this->adminManager = $adminManager;
    }
    
    public function getPriceFolder()
    {
        return self::PRICE_FOLDER;
    }        

    public function getPriceArxFolder()
    {
        return self::PRICE_FOLDER_ARX;
    }      
    
    public function getPriceNewFolder()
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
     * Закинуть прайс в папку поставщика с таким же прайсом
     * 
     * @param Supplier $supplier
     * @param string $filename
     * @return null
     */
    public function putPriceFileToPriceSupplier($supplier, $filename)
    {
        $priceNameValidator = new PriceNameValidator();
        if (file_exists($filename)){
            $filter = new Basename();
            $priceGettings = $this->entityManager->getRepository(PriceGetting::class)
                    ->findBy(['priceSupplier' => $supplier->getId(), 'status' => PriceGetting::STATUS_ACTIVE]);
            foreach ($priceGettings as $priceGetting){
                $target = self::PRICE_FOLDER.'/'.$priceGetting->getSupplier()->getId().'/'.$filter->filter($filename);
                if (copy($filename, $target)){
                    if (!$priceNameValidator->isValid($target, $priceGetting)){
                        unlink($target);                                    
                    }
                    continue;
                }
            }    
        }
        
        return;
    }
    
    /**
     * 
     * @param Raw $newRaw
     */
    public function checkNewPriceFile($newRaw)
    {
        $supplier = $newRaw->getSupplier();        
        if ($supplier){
            $priceNameValidator = new PriceNameValidator();
                    
            $target = self::PRICE_FOLDER.'/'.$supplier->getId().'/'.$newRaw->getFilename();
            if (copy($newRaw->getTmpfile(), $target)){
                //Закинуть прайс в папку поставщика с таким же прайсом
                $this->putPriceFileToPriceSupplier($supplier, $target);
                
                //Проверка наименования файла
                $priceGettings = $this->entityManager->getRepository(PriceGetting::class)
                        ->findBy(['priceSupplier' => $supplier->getId(), 'status' => PriceGetting::STATUS_ACTIVE]);
                
                foreach ($priceGettings as $priceGetting){
                    if (!$priceNameValidator->isValid($newRaw->getFilename(), $priceGetting)){
                        if (file_exists($target)){
                            unlink($target);                                    
                        }    
                    }
                }    

//                if ($priceGetting->getOrderToApl() == PriceGetting::ORDER_PRICE_FILE_TO_APL){    
//                    $destfile = '/'.$priceGetting->getSupplier()->getAplId().'/'.$attachment['filename'];
//                    $this->ftpManager->putPriceToApl(['source_file' => $attachment['temp_file'], 'dest_file' => $destfile]);
//                }    
                
                if (file_exists($newRaw->getTmpfile())){
                    unlink($newRaw->getTmpfile());
                }    
            }
        }           
        return;        
    }
    
    /**
     * 
     * @param Supplier|null $supplier
     * @param array $mailList
     */
    private function saveAttachement($supplier, $mailList)
    {
        if (count($mailList)){
            foreach ($mailList as $mail){
                if (isset($mail['attachment'])){
                    foreach($mail['attachment'] as $attachment){
                        if ($attachment['filename'] && file_exists($attachment['temp_file'])){
                            if (file_exists($attachment['temp_file'])){ 

                                $newRaw = $this->addNewRaw($supplier, [
                                    'filename' => $attachment['filename'],
                                    'fromEmail' => $mail['fromEmail'],
                                    'subject' => $mail['subject'],
                                    'tmpfile' => $attachment['temp_file'],
                                    ]);

                                if ($supplier){
                                    $this->checkNewPriceFile($newRaw);
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
     * Проверка почты в ящике поставщика
     * @param PriceGettting $priceGetting
     */
    public function getPriceByMail($priceGetting)
    {
        if ($priceGetting->getEmail() && $priceGetting->getAppPassword()){
            $box = [
                'host' => 'imap.yandex.ru',
                'server' => '{imap.yandex.ru:993/imap/ssl}',
                'user' => $priceGetting->getEmail(),
                'password' => $priceGetting->getAppPassword(),
                'leave_message' => false,
            ];
            
            $mailList = $this->postManager->readImap($box);
            
            $this->saveAttachement($priceGetting->getSupplier(), $mailList);
        }    
        
        return;
    }
    
    /**
     * Добавить upload прайс
     * 
     * @param Supplier|null $supplier
     * @param array $data
     * 
     * @return Raw 
     */
    public function addNewRaw($supplier, $data)
    {
        $raw = new Raw();
        $raw->setFilename($data['filename']);
        $raw->setParseStage(Raw::STAGE_NOT);
        $raw->setRows(0);
        $raw->setSender(empty($data['fromEmail']) ? null:$data['fromEmail']);
        $raw->setStatus(Raw::STATUS_NEW);
        $raw->setStatusEx(Raw::EX_NEW);
        $raw->setSubject(empty($data['subject']) ? null:$data['subject']);
        $currentDate = date('Y-m-d H:i:s');
        $raw->setDateCreated($currentDate);
        $raw->setTmpfile(empty($data['tmpfile']) ? null:$data['tmpfile']);
        
        $raw->setSupplier($supplier);

        $this->entityManager->persist($raw);
        $this->entityManager->flush();
        
        if (!empty($data['fromEmail']) && $supplier){
            $this->postManager->addEmailToContact($supplier->getLegalContact(), $data['fromEmail']);
        }
        
        return $raw;
    }
    
    /**
     * 
     * @param Raw $raw
     * @param Supplier $supplier
     */
    public function updateRawSupplier($raw, $supplier)
    {
        $raw->setSupplier($supplier);
        $this->entityManager->persist($raw);
        $this->entityManager->flush();
        
        if ($supplier){
            $this->checkNewPriceFile($raw);
        }    
        
        return;
    }
    
    /**
     * Проверка почты в ящике прайсов
     * 
     */
    public function getNewPriceByMail()
    {
        $setting = $this->adminManager->getPriceSettings();
                
        if (!empty($setting['sup_email']) && !empty($setting['sup_app_password'])){
            $box = [
                'user' => $setting['sup_email'],
                'password' => $setting['sup_app_password'],
                'leave_message' => false,
            ];
            
            $mailList = $this->postManager->readImap($box);
            $this->saveAttachement(null, $mailList);
        }    
        
        return;
    }
    
    /**
     * Прочитать очередной ящик
     */
    public function readQueyeMailBox()
    {
        set_time_limit(300);
        
        $this->getNewPriceByMail();
                
        $priceGetting = $this->entityManager->getRepository(PriceGetting::class)
                ->findOneBy(['status' => PriceGetting::STATUS_ACTIVE, 'mailBoxCheck' => PriceGetting::MAILBOX_TO_CHECK]);
        
        if ($priceGetting){
            if ($priceGetting->getSupplier()->getStatus() == Supplier::STATUS_ACTIVE){
                $this->getPriceByMail($priceGetting);
            }    
            
            $this->entityManager->getConnection()->update('price_gettings', 
                    ['mailbox_check' => PriceGetting::MAILBOX_CHECKED], 
                    ['id' => $priceGetting->getId()]);
            
        } else {
            //обнуление для новой проверки
            $priceGettings = $this->entityManager->getRepository(PriceGetting::class)
                    ->findBy(['status' => PriceGetting::STATUS_ACTIVE, 'mailBoxCheck' => PriceGetting::MAILBOX_CHECKED]);
            
            foreach ($priceGettings as $priceGetting){
                $this->entityManager->getConnection()->update('price_gettings', 
                        ['mailbox_check' => PriceGetting::MAILBOX_TO_CHECK], 
                        ['id' => $priceGetting->getId()]);
            }
            //рекурсивно запускаем снова 
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
            try{
                $response = $client->send();
            } catch(\Laminas\Http\Client\Adapter\Exception\RuntimeException $e){
                return false;
            } catch(\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                return false;
            }    
            
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
                        $this->putPriceFileToPriceSupplier($priceGetting->getSupplier(), $target);
                        
                        if ($priceGetting->getOrderToApl() == PriceGetting::ORDER_PRICE_FILE_TO_APL){    
                            $destfile = '/'.$priceGetting->getSupplier()->getAplId().'/'.$filename;
                            $this->ftpManager->putPriceToApl(['source_file' => $target, 'dest_file' => $destfile]);
                        }                      
                    }
                }    
            }            
        }
        
        return true;
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
            if (!$this->getPriceByLink($priceGetting)){
                sleep(10);
                $this->getPriceByLink($priceGetting);
            }
            if (time() > $startTime + 840){
                return;
            }            
        }        
    }
}
