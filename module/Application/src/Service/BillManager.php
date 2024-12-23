<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Application\Entity\Supplier;
use Application\Entity\BillSetting;
use Application\Entity\Idoc;
use Application\Entity\BillGetting;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Laminas\Json\Encoder;
use Application\Filter\CsvDetectDelimiterFilter;
use Laminas\Validator\File\IsCompressed;
use Laminas\Filter\Decompress;
use Application\Filter\RawToStr;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Stock\Entity\Ptu;
use Application\Entity\SupplySetting;
use Company\Entity\Office;
use Company\Entity\Contract;
use Application\Entity\Producer;
use Application\Entity\Article;
use Application\Filter\ArticleCode;
use Application\Filter\ProducerName;
use Application\Entity\UnknownProducer;
use Application\Entity\Goods;
use Application\Entity\GoodSupplier;
use Application\Entity\Oem;
use Laminas\Json\Decoder;

/**
 * Description of BillManager
 *
 * @author Daddy
 */
class BillManager
{
    const BILL_FOLDER       = './data/bills'; // папка с файлами
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Post manager.
     * @var \Admin\Service\PostManager
     */
    private $postManager;
    
    /**
     * Admin manager.
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;
    
    /**
     * Ptu manager.
     * @var \Stock\Service\PtuManager
     */
    private $ptuManager;
    
    /**
     * Assembly manager.
     * @var \Application\Service\AssemblyManager
     */
    private $assemblyManager;
    
    /**
     * Giga manager.
     * @var \Ai\Service\GigaManager
     */
    private $gigaManager;
    
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $postManager, $ptuManager, 
            $assemblyManager, $gigaManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->postManager = $postManager;
        $this->ptuManager = $ptuManager;
        $this->assemblyManager = $assemblyManager;
        $this->gigaManager = $gigaManager;
        $this->adminManager = $adminManager;

        if (!is_dir($this::BILL_FOLDER)){
            mkdir($this::BILL_FOLDER);
        }
    }
    
    public function getBillFolder()
    {
        return $this::BILL_FOLDER;
    }


    /**
     * Добавить документ
     * 
     * @param Supplier $supplier
     * @param array $data
     * @return Idoc
     */
    public function addIdoc($supplier, $data)
    {
        if ($supplier && !empty($data['tmpfile'])){
            $idoc = $this->entityManager->getRepository(Idoc::class)
                    ->findOneBy([
                        'supplier' => $supplier->getId(),
                        'status' => Idoc::STATUS_NEW,
                        'tmpfile' => $data['tmpfile'],
                    ]);
            if ($idoc){
                return $this->updateIdoc($idoc, $data);
            }
        }
        
        $idoc = new Idoc();
        $idoc->setName($data['name']);
        $idoc->setStatus($data['status']);
        $idoc->setDescription($data['description']);
        $idoc->setInfo(empty($data['info']) ? null:$data['info']);
        $idoc->setDateCreated(date('Y-m-d H:i:s'));
        $idoc->setDocKey(null);
        $idoc->setSupplier($supplier);
        $idoc->setSender(empty($data['sender']) ? null:$data['sender']);
        $idoc->setSubject(empty($data['subject']) ? null:$data['subject']);
        $idoc->setTmpfile(empty($data['tmpfile']) ? null:$data['tmpfile']);
        
        $this->entityManager->persist($idoc);
        $this->entityManager->flush();
        
        if ($supplier && !empty($data['sender'])){
            $this->postManager->addEmailToContact($supplier->getLegalContact(), $data['sender']);
        }
        
        return $idoc;
    }
    
    /**
     * Обновить документ
     * 
     * @param Idoc $idoc
     * @param array $data
     * @return idoc
     */
    public function updateIdoc($idoc, $data)
    {
        $idoc->setName($data['name']);
        $idoc->setStatus($data['status']);
        $idoc->setDescription($data['description']);
        $idoc->setInfo(empty($data['info']) ? null:$data['info']);
        $idoc->setDocKey(empty($data['docKey']) ? null:$data['docKey']);
        $idoc->setSender(empty($data['sender']) ? null:$data['sender']);
        $idoc->setSubject(empty($data['subject']) ? null:$data['subject']);
        $idoc->setTmpfile(empty($data['tmpfile']) ? null:$data['tmpfile']);
        
        $this->entityManager->persist($idoc);
        $this->entityManager->flush($idoc);
        
        if ($idoc->getSupplier() && $idoc->getSender()){
            $this->postManager->addEmailToContact($idoc->getSupplier()->getLegalContact(), $idoc->getSender());
        }
        return $idoc;
    }
    
    /**
     * Обновить документ
     * 
     * @param Idoc $idoc
     * @param Supplier $supplier
     * @return idoc
     */
    public function updateIdocSupplier($idoc, $supplier)
    {
        $idoc->setSupplier($supplier);
//        $idoc->setStatus(Idoc::STATUS_ACTIVE);
        $this->entityManager->persist($idoc);
        $this->entityManager->flush();
        
        return $idoc;
    }
    
    /**
     * Обновить статус документа
     * 
     * @param Idoc $idoc
     * @param array $data
     * @return idoc
     */
    public function updateIdocStatus($idoc, $data)
    {
        $idoc->setStatus($data['status']);
        
        $this->entityManager->persist($idoc);
        $this->entityManager->flush($idoc);
        
        return $idoc;
    }

    /**
     * Удалить документ
     * 
     * @param Idoc $idoc
     */
    public function removeIdoc($idoc)
    {
        $ptus = $this->entityManager->getRepository(Ptu::class)
                ->findBy(['idoc' => $idoc->getId()]);
        
        foreach ($ptus as $ptu){
            $ptu->setIdoc(null);
            $this->entityManager->persist($ptu);
            $this->entityManager->flush();
        }
        
        if (file_exists($idoc->getTmpfile())){
            unlink($idoc->getTmpfile());
        }    
        
        $this->entityManager->remove($idoc);
        $this->entityManager->flush();
        
        return;
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
        $billSetting->setName(empty($data['name']) ? null:$data['name']);
        $billSetting->setStatus($data['status']);
        $billSetting->setRuleCell($data['ruleCell']);
        $billSetting->setDescription(empty($data['description']) ? null:$data['description']);
        $billSetting->setDocNumCol(empty($data['docNumCol']) ? null:$data['docNumCol']);
        $billSetting->setDocNumRow(empty($data['docNumRow']) ? null:$data['docNumRow']);
        $billSetting->setDocDateCol(empty($data['docDateCol']) ? null:$data['docDateCol']);
        $billSetting->setDocDateRow(empty($data['docDateRow']) ? null:$data['docDateRow']);
        $billSetting->setCorNumCol(empty($data['corNumCol']) ? null:$data['corNumCol']);
        $billSetting->setCorNumRow(empty($data['corNumRow']) ? null:$data['corNumRow']);
        $billSetting->setCorDateCol(empty($data['corDateCol']) ? null:$data['corDateCol']);
        $billSetting->setCorDateRow(empty($data['corDateRow']) ? null:$data['corDateRow']);
        $billSetting->setIdNumCol(empty($data['idNumCol']) ? null:$data['idNumCol']);
        $billSetting->setIdNumRow(empty($data['idNumRow']) ? null:$data['idNumRow']);
        $billSetting->setIdDateCol(empty($data['idDateCol']) ? null:$data['idDateCol']);
        $billSetting->setIdDateRow(empty($data['idDateRow']) ? null:$data['idDateRow']);
        $billSetting->setContractCol(empty($data['contractCol']) ? null:$data['contractCol']);
        $billSetting->setContractRow(empty($data['contractRow']) ? null:$data['contractRow']);
        $billSetting->setTagNoCashCol(empty($data['tagNoCashCol']) ? null:$data['tagNoCashCol']);
        $billSetting->setTagNoCashRow(empty($data['tagNoCashRow']) ? null:$data['tagNoCashRow']);
        $billSetting->setTagNoCashValue(empty($data['tagNoCashValue']) ? null:$data['tagNoCashValue']);
        $billSetting->setInitTabRow(empty($data['initTabRow']) ? null:$data['initTabRow']);
        $billSetting->setArticleCol(empty($data['articleCol']) ? null:$data['articleCol']);
        $billSetting->setSupplierIdCol(empty($data['supplierIdCol']) ? null:$data['supplierIdCol']);
        $billSetting->setGoodNameCol(empty($data['goodNameCol']) ? null:$data['goodNameCol']);
        $billSetting->setProducerCol(empty($data['producerCol']) ? null:$data['producerCol']);
        $billSetting->setQuantityCol(empty($data['quantityCol']) ? null:$data['quantityCol']);
        $billSetting->setPriceCol(empty($data['priceCol']) ? null:$data['priceCol']);
        $billSetting->setAmountCol(empty($data['amountCol']) ? null:$data['amountCol']);
        $billSetting->setPackageCodeCol(empty($data['packageCodeCol']) ? null:$data['packageCodeCol']);
        $billSetting->setPackageCol(empty($data['packcageCol']) ? null:$data['packcageCol']);
        $billSetting->setCountryCodeCol(empty($data['countryCodeCol']) ? null:$data['countryCodeCol']);
        $billSetting->setCountryCol(empty($data['countryCol']) ? null:$data['countryCol']);
        $billSetting->setNtdCol(empty($data['ntdCol']) ? null:$data['ntdCol']);
        
        $billSetting->setSupplier($supplier);
        
        $this->entityManager->persist($billSetting);
        $this->entityManager->flush($billSetting);
        
        return $billSetting;
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
//        $billSetting->setName(empty($data['name']) ? null:$data['name']);
        $billSetting->setStatus($data['status']);
        $billSetting->setRuleCell($data['ruleCell']);
        $billSetting->setDescription(empty($data['description']) ? null:$data['description']);
        $billSetting->setDocNumCol(empty($data['docNumCol']) ? null:$data['docNumCol']);
        $billSetting->setDocNumRow(empty($data['docNumRow']) ? null:$data['docNumRow']);
        $billSetting->setDocDateCol(empty($data['docDateCol']) ? null:$data['docDateCol']);
        $billSetting->setDocDateRow(empty($data['docDateRow']) ? null:$data['docDateRow']);
        $billSetting->setCorNumCol(empty($data['corNumCol']) ? null:$data['corNumCol']);
        $billSetting->setCorNumRow(empty($data['corNumRow']) ? null:$data['corNumRow']);
        $billSetting->setCorDateCol(empty($data['corDateCol']) ? null:$data['corDateCol']);
        $billSetting->setCorDateRow(empty($data['corDateRow']) ? null:$data['corDateRow']);
        $billSetting->setIdNumCol(empty($data['idNumCol']) ? null:$data['idNumCol']);
        $billSetting->setIdNumRow(empty($data['idNumRow']) ? null:$data['idNumRow']);
        $billSetting->setIdDateCol(empty($data['idDateCol']) ? null:$data['idDateCol']);
        $billSetting->setIdDateRow(empty($data['idDateRow']) ? null:$data['idDateRow']);
        $billSetting->setContractCol(empty($data['contractCol']) ? null:$data['contractCol']);
        $billSetting->setContractRow(empty($data['contractRow']) ? null:$data['contractRow']);
        $billSetting->setTagNoCashCol(empty($data['tagNoCashCol']) ? null:$data['tagNoCashCol']);
        $billSetting->setTagNoCashRow(empty($data['tagNoCashRow']) ? null:$data['tagNoCashRow']);
        $billSetting->setTagNoCashValue(empty($data['tagNoCashValue']) ? null:$data['tagNoCashValue']);
        $billSetting->setInitTabRow(empty($data['initTabRow']) ? null:$data['initTabRow']);
        $billSetting->setArticleCol(empty($data['articleCol']) ? null:$data['articleCol']);
        $billSetting->setSupplierIdCol(empty($data['supplierIdCol']) ? null:$data['supplierIdCol']);
        $billSetting->setGoodNameCol(empty($data['goodNameCol']) ? null:$data['goodNameCol']);
        $billSetting->setProducerCol(empty($data['producerCol']) ? null:$data['producerCol']);
        $billSetting->setQuantityCol(empty($data['quantityCol']) ? null:$data['quantityCol']);
        $billSetting->setPriceCol(empty($data['priceCol']) ? null:$data['priceCol']);
        $billSetting->setAmountCol(empty($data['amountCol']) ? null:$data['amountCol']);
        $billSetting->setPackageCodeCol(empty($data['packageCodeCol']) ? null:$data['packageCodeCol']);
        $billSetting->setPackageCol(empty($data['packcageCol']) ? null:$data['packcageCol']);
        $billSetting->setCountryCodeCol(empty($data['countryCodeCol']) ? null:$data['countryCodeCol']);
        $billSetting->setCountryCol(empty($data['countryCol']) ? null:$data['countryCol']);
        $billSetting->setNtdCol(empty($data['ntdCol']) ? null:$data['ntdCol']);
        
        $this->entityManager->persist($billSetting);
        $this->entityManager->flush($billSetting);
        
        return $billSetting;
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
    
    /**
     * Преобразовать xls в array
     * @param Supplier $supplier
     * @param string $filename
     * @param string $filepath
     * @param string $sender
     * @param string $subject
     * 
     */
    protected function _xls2array($supplier, $filename, $filepath, $sender, $subject)
    {        
        setlocale(LC_ALL,'ru_RU.UTF-8');
        ini_set('memory_limit', '512M');
        set_time_limit(0); 
        
        if (file_exists($filepath)){
            
            if (!filesize($filepath)){
                return;
            }
                                    
            try{
                libxml_use_internal_errors(true);
                $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($filepath); 
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                $spreadsheet = $reader->load($filepath);
                libxml_use_internal_errors(false);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e){
//                var_dump($e->getMessage()); 
                return;
            }    

            $sheets = $spreadsheet->getAllSheets();
            foreach ($sheets as $sheet) { // PHPExcel_Worksheet
                $result = [];
                foreach ($sheet->getRowIterator() as $row) { 
                    $cellIterator = $row->getCellIterator();
                    $resultRow = [];
                    foreach ($cellIterator as $cell) {  
//                        var_dump($cell->getValue());
//                        var_dump($cell->getCalculatedValue());
//                        var_dump($cell->getFormattedValue());
                        if (Date::isDateTime($cell) && $cell->getValue()) {
                            $value = date('Y-m-d', Date::excelToTimestamp(floatval($cell->getValue())));
                        } elseif (Date::isDateTimeFormat($cell->getStyle()->getNumberFormat()) && $cell->getValue()) {
                            $value = date('Y-m-d', Date::excelToTimestamp(floatval($cell->getValue())));
                        } else {
                            if (!empty($cell->getCalculatedValue())){
                                $value = mb_substr(trim($cell->getCalculatedValue()), 0, 50);
                            } else {
                                $value = '';
                            }    
                        }                        
                        $resultRow[] = str_replace('#NULL!', '', $value);
                    }
                    $result[] = $resultRow;                              
                }              
                $this->addIdoc($supplier, [
                    'status' => Idoc::STATUS_ACTIVE,
                    'name' => $filename,
                    'description' => Encoder::encode($result),
                    'docKey' => null,
                    'sender' => $sender,
                    'subject' => $subject,
                    'tmpfile' => $filepath,
                ]);                
            }                
            unset($spreadsheet);
//            exit;
        }        
        return $result;        
    }
        
    /**
     * Преобразовать html в array
     * @param Supplier $supplier
     * @param string $filename
     * @param string $content
     * @param string $sender
     * @param string $subject
     * @param string $filepath
     */
    protected function _html2array($supplier, $filename, $content, $sender, $subject, $filepath)
    {
        libxml_use_internal_errors(true);
        ini_set('memory_limit', '512M');
        set_time_limit(0);         
        
        if ($content){
            
            $dom = new \DOMDocument();
            $dom->loadHTML($content); 
            $row = [];
            foreach($dom->getElementsByTagName('*') as $element ){
                if ($element->tagName == 'b'){
                    $row[] = $element->nodeValue;
                    $result[] = $row;
                }
//                if ($element->tagName == 'br'){
//                    $result[] = $row;
//                    $row = [];
//                }
                if ($element->tagName == 'tr'){
                    $row = [];
                    foreach ($element->childNodes as $node) {
                        $row[] = $node->nodeValue;
                    }
                    $result[] = $row;
                }
            }
            
//            var_dump($result); exit;
            $this->addIdoc($supplier, [
                'status' => Idoc::STATUS_ACTIVE,
                'name' => $filename,
                'description' => Encoder::encode($result),
                'docKey' => null,
                'sender' => $sender,
                'subject' => $subject,
                'tmpfile' => $filepath,
            ]);                
        }    
                                    
        return;        
    }

    /**
     * Преобразовать csv в array
     * @param Supplier $supplier
     * @param string $filename
     * @param string $filepath
     * @param string $sender
     * @param string $subject
     * @return array
     */
    
    protected function _csv2array($supplier, $filename, $filepath, $sender, $subject)
    {
        ini_set('memory_limit', '512M');
        $result = [];
        
        if (file_exists($filepath)){
            
            if (!filesize($filepath)){
                return;
            }

            $lines = fopen($filepath, 'r');

            if($lines) {

                $detector = new CsvDetectDelimiterFilter();
                $delimiter = $detector->filter($filepath);
                
                $filter = new RawToStr();

                while (($row = fgetcsv($lines, 4096, $delimiter)) !== false) {

                    $str = $filter->filter($row);

                    if ($str){
                        $result[] = explode(';', $str);                              
                    }                            
                }
                    
                fclose($lines);
            }                                
        }                
        
        $this->addIdoc($supplier, [
            'status' => Idoc::STATUS_ACTIVE,
            'name' => $filename,
            'description' => Encoder::encode($result),
            'docKey' => null,
            'sender' => $sender,
            'subject' => $subject,
            'tmpfile' => $filepath,
        ]);                
        
        return $result;
    }    

    /**
     * Преобразование данных файла в массив
     * @param Supplier $supplier
     * @param string $filename
     * @param string $filepath
     * @param string $sender
     * @param string $subject
     * @return array
     */
    protected function _filedata2array($supplier, $filename, $filepath, $sender, $subject)
    {
        $result = [];
        $pathinfo = pathinfo($filename);
        
//        $stripContent = strip_tags($content);
        if ($supplier){
            if($supplier->getId() == 65) { //микадо злбчее
    //            // contains HTML
                $content = file_get_contents($filepath);
                return $this->_html2array($supplier, $filename, $content, $sender, $subject, $filepath);
            }       
        }    
        if (!empty($pathinfo['extension'])){
            
            if (in_array(strtolower($pathinfo['extension']), ['xls', 'xlsx'])){
                return $this->_xls2array($supplier, $filename, $filepath, $sender, $subject);            
            }
            
            if (in_array(strtolower($pathinfo['extension']), ['txt', 'csv'])){
                return $this->_csv2array($supplier, $filename, $filepath, $sender, $subject);            
            } else {
                return $this->_xls2array($supplier, $filename, $filepath, $sender, $subject);                                
            }       
        }
        return $result;
    }    
    
    /**
     * Добавить папку для временных файло
     * @param Supplier $supplier
     */
    protected function _addBillFolder($supplier)
    {
        //Создать папку для файлов
        if (!is_dir(self::BILL_FOLDER)){
            mkdir(self::BILL_FOLDER);
        }
        
        $bill_supplier_folder_name = self::BILL_FOLDER.'/'.$supplier->getId();
        if (!is_dir($bill_supplier_folder_name)){
            mkdir($bill_supplier_folder_name);
        }
    }        
    
    
    /*
     * Очистить содержимое папки
     * 
     * @var Supplier $supplier
     * @var string $folderName
     * 
     */
    protected function _clearBillFolder($supplier, $folderName)
    {
        if (is_dir($folderName)){
            foreach (new \DirectoryIterator($folderName) as $fileInfo) {
                if ($fileInfo->isDot()) continue;
                if ($fileInfo->isFile()){
                    unlink($fileInfo->getPathname());                            
                }
                if ($fileInfo->isDir()){
                    $this->_clearBillFolder($supplier, $fileInfo->getPathname());
                    
                }
            }
            if ($folderName != self::BILL_FOLDER.'/'.$supplier->getId()){
                rmdir($folderName);
            }
        }        
    }
    
    /**
     * 
     * Проверка папки с файлами. Если в папке есть файл то загружаем его
     * 
     * @param Supplier $supplier
     * @param string $folderName
     * @param string $sender
     * @param string $subject
     * 
     */
    protected function _checkBillFolder($supplier, $folderName, $sender, $subject)
    {    
        setlocale(LC_ALL,'ru_RU.UTF-8');

        $validator = new IsCompressed();

        if (is_dir($folderName)){
            foreach (new \DirectoryIterator($folderName) as $fileInfo) {
                if ($fileInfo->isDot()) continue;
                if ($fileInfo->isFile()){
                    $pathinfo = pathinfo($fileInfo->getFilename());
                    if ($validator->isValid($fileInfo->getPathname()) && strtolower($pathinfo['extension']) != 'xlsx'){                    
                        $this->_decompressAttachment($supplier, $fileInfo->getFilename(), $fileInfo->getPathname(), $sender, $subject);
                    } else {
                        $this->_filedata2array($supplier, $fileInfo->getFilename(), $fileInfo->getPathname(), $sender, $subject);
                    }    
                }
                if ($fileInfo->isDir()){
                    $this->_checkBillFolder($supplier, $fileInfo->getPathname(), $sender, $subject);                    
                }
            }
        }
        return;
    }    
    
    /**
     * Распаковать вложение и загрузить
     * @param Supplier $supplier
     * @param string $filename
     * @param string $filepath
     * @param string $filepath
     * @param string $sender
     * @param string $subject
     * @return null
     */
    protected function _decompressAttachment($supplier, $filename, $filepath, $sender, $subject)
    {
        $this->_addBillFolder($supplier);
        $bill_supplier_folder_name = self::BILL_FOLDER.'/'.$supplier->getId();
        $pathinfo = pathinfo($filename);
        setlocale(LC_ALL,'ru_RU.UTF-8');
        $filter = new Decompress([
            'adapter' => $pathinfo['extension'],
            'options' => [
                'target' => $bill_supplier_folder_name,
            ],
        ]);
        if ($filter->filter($filepath)){
            unlink($filepath);
            $this->_checkBillFolder($supplier, $bill_supplier_folder_name, $sender, $subject);
        }
        
        $this->_clearBillFolder($supplier, $bill_supplier_folder_name);
        
        return;
    }
    
   /**
    * Записатьь данные
    * @param Supplier $supplier
    * @param array $mail
    * @param array $attachment
    */
    private function saveAttachment($supplier, $mail, $attachment)
    {
        $result = false;
        $validator = new IsCompressed();

//        var_dump($attachment);
        if ($attachment['filename'] && file_exists($attachment['temp_file'])){
            $pathinfo = pathinfo($attachment['filename']);
            if ($validator->isValid($attachment['temp_file']) && strtolower($pathinfo['extension']) != 'xlsx'){
                $this->_decompressAttachment($supplier, $attachment['filename'], $attachment['temp_file'], $mail['fromEmail'], $mail['subject']);
            } else {
                $this->_filedata2array($supplier, $attachment['filename'], $attachment['temp_file'], $mail['fromEmail'], $mail['subject']);
            }    
            if (file_exists($attachment['temp_file'])){
                unlink($attachment['temp_file']);
                $result = true;
            }    
        }
//        var_dump($mail['to'], $mail['toEmail'], $supplier->getId());
        if ($supplier && (!empty($mail['to']) || !empty($mail['toEmail']))){
            $supplier->setToBillEmail(!empty($mail['toEmail']) ? implode(';', $mail['toEmail']):$mail['to']);
            $this->entityManager->persist($supplier);
            $this->entityManager->flush();
        }                    
        
        return $result;
    }
    
    /**
     * Проверка почты в ящике поставщика
     * @param BillGetting $billGetting
     */
    public function getBillByMail($billGetting)
    {
        if ($billGetting->getEmail() && $billGetting->getEmailPassword()){
            $box = [
                'user' => $billGetting->getEmail(),
                'password' => $billGetting->getAppPassword(),
                'leave_message' => false,
            ];
            
            $mailList = $this->postManager->readImap($box);
//            var_dump($mailList);
            if (count($mailList)){
                foreach ($mailList as $mail){
                    if (isset($mail['attachment'])){
                        foreach($mail['attachment'] as $attachment){
                            $this->saveAttachment($billGetting->getRealSupplier(), $mail, $attachment);
                        }
                    }
//                    exit;
                }
            }
        }    
        
        return;
    }   
    
    /**
     * Проверка почты в общем ящике
     * 
     */
    public function getNewBillByMail()
    {
        $setting = $this->adminManager->getPriceSettings();
        
        if (!empty($setting['b_email']) && !empty($setting['b_app_password'])){
            $box = [
                'user' => $setting['b_email'],
                'password' => $setting['b_app_password'],
                'leave_message' => false,
            ];
            
            $mailList = $this->postManager->readImap($box);
//            var_dump($mailList);
            if (count($mailList)){
                foreach ($mailList as $mail){
                    if (isset($mail['attachment'])){
                        $supplier = null;
                        if (!empty($mail['fromEmail'])){
                            $supplier = $this->entityManager->getRepository(Supplier::class)
                                        ->suplierByFromEmail($mail['fromEmail']);
                        }    
                        foreach($mail['attachment'] as $attachment){
                            if ($supplier){
                                $this->saveAttachment($supplier, $mail, $attachment);
                            } else {     
                                $this->addIdoc(null, [
                                    'status' => Idoc::STATUS_NEW,
                                    'name' => $attachment['filename'],
                                    'description' => null,
                                    'docKey' => null,
                                    'sender' => $mail['fromEmail'],
                                    'subject' => $mail['subject'],
                                    'tmpfile' => $attachment['temp_file'],
                                ]);                                                    
                            }
                        }
                    }
//                    exit;
                }
            }
        }    
        
        return;
    }   
    
    /**
     * 
     * @param Idoc $idoc
     */
    public function rereadIdoc($idoc)
    {
        if ($idoc->getSupplier()){
            $this->saveAttachment($idoc->getSupplier(), [
                'fromEmail' => $idoc->getSender(),
                'subject' => $idoc->getSubject(),
            ], [
                'filename' => $idoc->getName(),
                'temp_file' => $idoc->getTmpfile(),
            ]);
        }
        
        return;
    }
    
    /**
     * Прочитать ящики с накладными
     */
    public function billsByMail()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(900);
        $startTime = time();
        
        $this->getNewBillByMail();
        
        $billGettings = $this->entityManager->getRepository(BillGetting::class)
                ->findBy(['status' => BillSetting::STATUS_ACTIVE]);
        foreach ($billGettings as $billGetting){
            if ($billGetting->getSupplier()->getStatus() == Supplier::STATUS_ACTIVE){
                $this->getBillByMail($billGetting);
            }    
            if (time() > $startTime + 840){
                break;
            }
            usleep(100);
        }
        return;
    }
    
    /**
     * Получить тип оплаты контракта
     * @param array $idocData
     * @return int
     */
    private function _idocContractPay($idocData)
    {
        $result = Contract::PAY_CASH;
        $noCashValue = false;
        if (!empty($idocData['tag_no_cash_value'])){
            $noCashValue = $idocData['tag_no_cash_value'];
        }
        if (!empty($idocData['tag_no_cash'])){
            if ($noCashValue == $idocData['tag_no_cash'] || $idocData['tag_no_cash']){
                $result = Contract::PAY_CASHLESS;
            }
        }
        return $result;
    }
    
    /**
     * Новый товар
     * @param string $article
     * @param Producer $producer
     * @param string $name
     * @return Goods
     */
    private function _newGood($article, $producer = null, $name = null)
    {
        if (!$name){
            $name = $article;
        }
        if (!$producer){
            $producer = $this->entityManager->getRepository(Producer::class)
                    ->findOneByAplId(3078); //no name
        }
        $articleFilter = new ArticleCode();
        $code = $articleFilter->filter($article);
        if ($code){
            $good = $this->entityManager->getRepository(Goods::class)
                    ->findOneBy(['code' => $code, 'producer' => $producer->getId()]);
            if ($good){
                return $good;
            }
            return $this->assemblyManager->addNewGood($code, $producer, NULL, 0, mb_substr($name, 0, 255));        
        }
        
        return;
    }
    
    /**
     * Попробовать получить артикул из кода поставщика
     * @param string $iid
     * @param string $goodName
     * @return Goods
     */
    protected function _parseIid($iid, $goodName = null)
    {
        $delimeters = ['^', '_'];
        $articleFilter = new ArticleCode();
        $producerNameFilter = new ProducerName();
        foreach ($delimeters as $delimetr){

            $articleStr = $producer = null;
            $art_pro = explode($delimetr, $iid);

            if (count($art_pro) > 1){
                $producer = null;
                foreach ($art_pro as $value){
                    $producerName = $producerNameFilter->filter($value);
                    if ($producerName){
                        $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                                ->findOneByName($producerName);
                        if ($unknownProducer){
                            if ($unknownProducer->getProducer()){
                                $producer = $unknownProducer->getProducer();
                                continue;
                            }    
                        }
                    }
                    if (!$articleStr){
                        $articleStr = $value;
                    }
                }
                if ($articleStr && $producer){
    //                var_dump($articleStr, $producer);
                    return $this->_newGood($articleStr, $producer, $goodName);                
                }
                if ($articleStr){
                    $code = $articleFilter->filter($articleStr);
                    $oem = $this->entityManager->getRepository(Oem::class)
                            ->findOneBy(['oe' => $code, 'source' => Oem::SOURCE_MY_CODE]); 
                    if ($oem){
                        if ($oem->getGood()){
                            return $oem->getGood();
                        }                
                    }    
                    $good = $this->entityManager->getRepository(Goods::class)
                            ->findOneByCode($code);
                    if ($good){
                        return $good;
                    }                
                }    
            }    
        }
        return;
    }
    
    /**
     * Разборка внутреннего кода микадо
     * xbr-s23530;xfrk-238901;xnk-751516;xnk-929906;xpm-phb-007
     * 
     * @param string $iid
     * @param Supplier $supplier
     * @param float $price
     */
    protected function _goodFromMikadoIid($iid, $supplier, $price)
    {
        $delimeters = ['-'];
        $articleFilter = new ArticleCode();
        $articleStr = null;
        foreach ($delimeters as $delimetr){
            $art_pro = explode($delimetr, $iid);
            if (count($art_pro) > 1){
                unset($art_pro[0]);
                $articleStr = implode('', $art_pro);
                if ($articleStr){
                    $code = $articleFilter->filter($articleStr);
                    $good = $this->_findGoodByCode($supplier, $articleStr, $price);
                    if ($good){
                        return $good;
                    }                
                }    
            }
        }        
        return;
    }
    
    /**
     * Поиск товара по внутреннему коду поставщика
     * @param string $iid
     * @param Supplier $supplier
     * @return type
     */
    protected function _goodFromIid($iid, $supplier)
    {
        $articleFilter = new ArticleCode();

        $oe = $supplier->getId().'@'.$articleFilter->filter($iid);
        
        $oem = $this->entityManager->getRepository(Oem::class)
                ->findOneBy(['oe' => $oe, 'status' => Oem::STATUS_ACTIVE, 'source' => Oem::SOURCE_IID]);
        
        if ($oem){
            return $oem->getGood();
        }
        
        return;
    }
    
    /**
     * Поиск товара по внутренним кодам поставщика
     * @param string $iid
     * @param Supplier $supplier
     */
    protected function _goodFromSupplierIid($iid, $supplier)
    {
        $parentSupplier = $supplier->getParent();
        if (!$parentSupplier){
            $parentSupplier = $supplier;
        }

        $good = $this->_goodFromIid($iid, $parentSupplier);
        if ($good){
            return $good;
        }        
        
        foreach ($parentSupplier->getChildren() as $chldSupplier){
            $good = $this->_goodFromIid($iid, $chldSupplier);
            if ($good){
                return $good;
            }        
        }
        return;
    }

    /**
     * Найти подходящий товар по коду
     * @param Supplier $supplier
     * @param string $articleStr
     * @param float $price
     */
    protected function _findGoodByCode($supplier, $articleStr, $price)
    {
        
        $articleFilter = new ArticleCode();
        $code = $articleFilter->filter($articleStr);
        if ($code){
            $goods = $this->entityManager->getRepository(Goods::class)
                    ->findByCode($code);
            if ($goods){
                if (count($goods) == 1){
                    foreach ($goods as $good){
                        return $good;
                    }
                } else {
                    $good = $this->entityManager->getRepository(GoodSupplier::class)
                            ->findGoodChildSupplierByCode($code, $price, $supplier);
                    if ($good){
                        return $good;
                    }
                }
            }    
            $articles = $this->entityManager->getRepository(Article::class)
                    ->findByCode($code);
            foreach ($articles as $article){
                if ($article->getGood()){
                    return $article->getGood();
                }    
            }
        }
        
        return;
    }
    
    /**
     * Найти подходящий товар по коду
     * 
     * @param Producer $producer
     * @param string $goodName
     */
    protected function _findGoodByGoodName($producer, $goodName)
    {
        if ($producer && $goodName){
            $messages = [];
            $messages[] = [
                'role' => 'system',
                'content' => 'Ты специалист по логистике. Определи артикул товара в описании. Ответь в формате JSON {"article":"xxxxx"}',
            ];
            $messages[] = [
                'role' => 'user',
                'content' => $goodName,
            ];

            $result = $this->gigaManager->completions($messages);
            
//            var_dump($result);
            if (!empty($result['choices'])){
                foreach ($result['choices'] as $choice){
                    if (!empty($choice['message']['content'])){                        
                        $articleResult = Decoder::decode($choice['message']['content'], \Laminas\Json\Json::TYPE_ARRAY);
                        if (!empty($articleResult['article'])){
                            $articleFilter = new ArticleCode();
                            $code = $articleFilter->filter($articleResult['article']);
                            return $this->entityManager->getRepository(Goods::class)
                                    ->findOneBy(['code' => $code, 'producer' => $producer->getId()]);
                        }
                    }    
                }
            }            
        }       
        
        return;
    }
    
    /**
     * Получить товар
     * @param Supplier $supplier
     * @param array $data
     * @return Goods
     */
    public function findGood($supplier, $data)
    {
        $articleStr = empty($data['article']) ? null:$data['article'];
        $producerStr = empty($data['producer']) ? null:$data['producer'];
        $goodName = empty($data['good_name']) ? null:$data['good_name'];
        $iid = empty($data['supplier_article']) ? null:$data['supplier_article'];
        $price = empty($data['price']) ? 0:(int) $data['price'];
        $producer = null;
        
        $articleFilter = new ArticleCode();
        $code = $articleFilter->filter($articleStr);
        if ($code && !$producerStr){
            $good = $this->_findGoodByCode($supplier, $articleStr, $price);
            if ($good){
                return $good;
            }
        }
        
        if ($producerStr){
            $producerNameFilter = new ProducerName();
            $producerName = $producerNameFilter->filter($producerStr);
            $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                    ->findOneByName($producerName);
            if ($unknownProducer){
                if ($unknownProducer->getProducer()){
                    $producer = $unknownProducer->getProducer();
                }
            }    
        }
            
        if ($code && $producer){
            if ($producer){
                $good = $this->entityManager->getRepository(Goods::class)
                        ->findOneBy(['code' => $code, 'producer' => $producer->getId()]);
                if ($good){
                    return $good;
                }
            }    
        }
        
        if ($iid){
            
            $good = $this->_goodFromSupplierIid($iid, $supplier);
            if ($good){
                return $good;
            }
            
            if ($supplier->getAplId() == 69){ //mikado
                $good = $this->_goodFromMikadoIid($iid, $supplier, $price);
            } else {
                $good = $this->_parseIid($iid, $goodName);                
            }
            
            if ($good){
                return $good;
            }

            $good = $this->entityManager->getRepository(BillSetting::class)
                    ->findGoodFromRawprice($supplier, $iid);
            if (is_array($good)){
                return $this->_newGood($good['article'], $good['producer'], $good['goodName']);
            }
            if ($good){
                return $good;
            }
            
        }
        
        if (empty($articleStr) && $goodName && $producer){
            $good = $this->_findGoodByGoodName($producer, $goodName);
            if ($good){
                return $good;
            }
        }

        return $this->_newGood($articleStr, $producer, $goodName);
    }

    /**
     * Получить ПТУ
     * 
     * @param Idoc $idoc
     * @param BillSetting $billSetting
     * 
     * @return Ptu 
     */
    public function idocToPtu($idoc, $billSetting)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(900);

        $idocData = $idoc->idocToPtu($billSetting->toArray());
        if ($idocData['total'] && $idocData['doc_no'] && $idocData['doc_date']){
            
            $dataPtu = [
                'apl_id' => 0,
                'doc_no' => $idocData['doc_no'],
                'doc_date' => $idocData['doc_date'],
                'status_ex' => Ptu::STATUS_EX_UPL,
                'status' => Ptu::STATUS_ACTIVE,
            ];
            
            $office = $idoc->getSupplier()->getOffice();
            if (!$office){
                $office = $this->entityManager->getRepository(Office::class)
                        ->findDefaultOffice();
            }
            $legal = $this->entityManager->getRepository(Supplier::class)
                    ->findDefaultSupplierLegal($idoc->getSupplier(), $idocData['doc_date']);
            $contract = $this->entityManager->getRepository(Office::class)
                    ->findDefaultContract($office, $legal, $idocData['doc_date'], $this->_idocContractPay($idocData));
            
            $dataPtu['supplier'] = $idoc->getSupplier();            
            $dataPtu['office'] = $office;
            $dataPtu['legal'] = $legal;
            $dataPtu['contract'] = $contract; 
            
            $ptu = $this->entityManager->getRepository(Ptu::class)
                    ->findOneBy(['docNo' => $idocData['doc_no'], 'docDate' => $idocData['doc_date']]);
            
            if ($ptu){
                $dataPtu['apl_id'] = $ptu->getAplId();
                $this->ptuManager->updatePtu($ptu, $dataPtu);
                $this->ptuManager->removePtuGood($ptu); 
            } else {        
                $ptu = $this->ptuManager->addPtu($dataPtu);
            }    
            
            $notFoundArticle = [];
            if ($ptu && isset($idocData['tab'])){
                $rowNo = 1;                
                foreach ($idocData['tab'] as $tp){
                    if (!empty($tp['quantity']) && !empty($tp['good_name'])&& !empty($tp['amount'])){
                        $good = $this->findGood($idoc->getSupplier(), $tp);   
                        if (empty($good)){
                            if (!empty($tp['article'])){
                                $notFoundArticle[] = $tp['article'];
                            } elseif (!empty($tp['supplier_article'])) {
                                $notFoundArticle[] = $tp['supplier_article'];
                            } else {
                                $notFoundArticle[] = $tp['good_name'];                                
                            }    
//                            throw new \Exception("Не удалось создать карточку товара для документа {$tp['good_name']}");
                        } else {

                            $this->ptuManager->addPtuGood($ptu->getId(), [
                                'status' => $ptu->getStatus(),
                                'statusDoc' => $ptu->getStatusDoc(),
                                'quantity' => $tp['quantity'],                    
                                'amount' => $tp['amount'],
                                'good_id' => $good->getId(),
                                'comment' => '',
                                'info' => '',
                                'countryName' => (isset($tp['country'])) ? $tp['country']:'',
                                'countryCode' => (isset($tp['country_code'])) ? $tp['country_code']:'',
                                'unitName' => (isset($tp['packcage'])) ? $tp['packcage']:'',
                                'unitCode' => (isset($tp['package_code'])) ? $tp['package_code']:'',
                                'ntd' => (isset($tp['ntd'])) ? $tp['ntd']:'',
                            ], $rowNo);
                            $rowNo++;
                        }    
                    }    
                }
            }  

            if ($ptu){
                $this->ptuManager->updatePtuAmount($ptu);
                $ptu->setIdoc($idoc);
                
                if (count($notFoundArticle) > 0){
                    $articles = implode(';', $notFoundArticle);
                    $idoc->setInfo($articles);
                    //throw new \Exception("Не удалось создать карточку товара для документа {$articles}");
                }

                $idoc->setDocKey($ptu->getLogKey());
                $idoc->setStatus(Idoc::STATUS_ACTIVE);
                if (count($notFoundArticle) == 0){
                    $idoc->setStatus(Idoc::STATUS_RETIRED);
                    $idoc->setInfo($ptu->getAmount());

                    $ptu->setStatusEx(Ptu::STATUS_EX_NEW);
                    $this->entityManager->persist($ptu);
                }    
                $this->entityManager->persist($idoc);
                $this->entityManager->flush();
                
                return true;
            }            
        }       

        return false;
    }
    
    /**
     * Создать ПТУ
     * @param Idoc $idoc
     */
    public function tryPtu($idoc)
    {
        if (empty($idoc->getSupplier())){
            return;
        }
        
        if ($idoc->getSupplier() && $idoc->getSender()){
            $this->postManager->addEmailToContact($idoc->getSupplier()->getLegalContact(), $idoc->getSender());
        }
        
        if ($idoc->getStatus() === Idoc::STATUS_NEW){
            $this->rereadIdoc($idoc);
        }
        
        $oldstatus = $idoc->getStatus();
        $idoc->setStatus(Idoc::STATUS_PROC);
        $this->entityManager->persist($idoc);
        $this->entityManager->flush($idoc);
        
        $billSetting = $this->entityManager->getRepository(BillSetting::class)
                ->billSettingForIdoc($idoc);
        $flag = true;
        if ($billSetting){
            $idocData = $idoc->idocToPtu($billSetting->toArray());
            if ($idocData['doc_no'] && $idocData['doc_date'] > '1970-01-01' && $idocData['total']){
                if ($flag = $this->idocToPtu($idoc, $billSetting)){
                    return;
                }                
            }
        }
        
        if ($billSetting && $flag){
            $idoc->setStatus(Idoc::STATUS_ERROR);
            $this->entityManager->persist($idoc);
            $this->entityManager->flush($idoc);
            return;
        }

        $idoc->setStatus($oldstatus);
        $this->entityManager->persist($idoc);
        $this->entityManager->flush($idoc);
                
        return;
    }
    
    /**
     * Проверить и создать ПТУ из эл.докуметов
     * @return null
     */
    public function tryIdocs()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(900);
        $startTime = time();
        
        $idocs = $this->entityManager->getRepository(Idoc::class)
                ->findBy(['status' => Idoc::STATUS_ACTIVE]);
        foreach ($idocs as $idoc){
            $this->tryPtu($idoc);
            if (time() > $startTime + 840){
                break;
            }
        }
        return;
    }
    
    /**
     * Перезагрузка испорченных накладных
     */
    public function correction()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(900);
        $startTime = time();

        $idocs = $this->entityManager->getRepository(Idoc::class)
                ->findForCorrection();
        foreach ($idocs as $idoc){
            $idoc = $this->entityManager->getRepository(Idoc::class)
                    ->find($idoc['iid']);
            if ($idoc){
                $this->tryPtu($idoc);
            }    
            if (time() > $startTime + 840){
                break;
            }
        }   
        
        return;
    }
}
