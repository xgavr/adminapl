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
use Application\Entity\Rawprice;
use Application\Filter\ProducerName;
use Application\Entity\UnknownProducer;
use Application\Entity\Goods;

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
     * Ptu manager.
     * @var \Stock\Service\PtuManager
     */
    private $ptuManager;
    
    /**
     * Assembly manager.
     * @var \Application\Service\AssemblyManager
     */
    private $assemblyManager;
    
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $postManager, $ptuManager, $assemblyManager)
    {
        $this->entityManager = $entityManager;
        $this->postManager = $postManager;
        $this->ptuManager = $ptuManager;
        $this->assemblyManager = $assemblyManager;
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
        $idoc = new Idoc();
        $idoc->setName($data['name']);
        $idoc->setStatus($data['status']);
        $idoc->setDescription($data['description']);
        $idoc->setDateCreated(date('Y-m-d H:i:s'));
        $idoc->setDocKey(null);
        $idoc->setSupplier($supplier);
        
        $this->entityManager->persist($idoc);
        $this->entityManager->flush($idoc);
        
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
        $idoc->setDocKey($data['docKey']);
        
        $this->entityManager->persist($idoc);
        $this->entityManager->flush($idoc);
        
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
//        $billSetting->setName(empty($data['name']) ? null:$data['name']);
        $billSetting->setStatus($data['status']);
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
    
    /**
     * Преобразовать xls в array
     * @param string $filename
     */
    protected function _xls2array($filename)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0); 
        $result = [];
        
        if (file_exists($filename)){
            
            if (!filesize($filename)){
                return;
            }
                                    
//            $filter = new RawToStr();
                    
            try{
                $reader = IOFactory::createReaderForFile($filename);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e){
            }    

//            $filterSubset = new \Application\Filter\ExcelColumn();
//            $reader->setReadFilter($filterSubset);
//            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filename);

            $sheets = $spreadsheet->getAllSheets();
            foreach ($sheets as $sheet) { // PHPExcel_Worksheet
                foreach ($sheet->getRowIterator() as $row) { 
                    $cellIterator = $row->getCellIterator();
                    $resultRow = [];
                    foreach ($cellIterator as $cell) {   
//                        $testOut=[
//                            $cell->getCoordinate(),
//                            $cell->getStyle()->getNumberFormat()->getFormatCode(),
//                            $cell->getDataType(),
//                            $cell->getFormattedValue(),
//                            $cell->getValue()
//                        ];
//                        var_dump($testOut);                            
                        if (Date::isDateTime($cell) && $cell->getValue()) {
                            $value = date('Y-m-d', Date::excelToTimestamp($cell->getValue()));
                        } elseif (Date::isDateTimeFormat($cell->getStyle()->getNumberFormat()) && $cell->getValue()) {
                            $value = date('Y-m-d', Date::excelToTimestamp($cell->getValue()));
                        } else {
                            $value = mb_substr(trim($cell->getCalculatedValue()), 0, 50);
                        }                        
                        $resultRow[] = $value;
                    }
                    $result[] = $resultRow;                              
                }    
            }                
            unset($spreadsheet);
//            exit;
        }        
        return $result;        
    }
    
    /**
     * Преобразовать csv в array
     * @param string $filename
     * @return array
     */
    
    protected function _csv2array($filename)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        $result = [];
        
        if (file_exists($filename)){
            
            if (!filesize($filename)){
                return;
            }

            $lines = fopen($filename, 'r');

            if($lines) {

                $detector = new CsvDetectDelimiterFilter();
                $delimiter = $detector->filter($filename);
                
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
        return $result;
    }    

    /**
     * Преобразование данных файла в массив
     * @param string $filename
     * @param string $filepath
     * @return array
     */
    protected function _filedata2array($filename, $filepath)
    {

        $result = [];
        $pathinfo = pathinfo($filename);
        //var_dump($pathinfo); exit;
        if (in_array(strtolower($pathinfo['extension']), ['xls', 'xlsx'])){
            return $this->_xls2array($filepath);            
        }
        if (in_array(strtolower($pathinfo['extension']), ['txt', 'csv'])){
            return $this->_csv2array($filepath);            
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
                    $this->clearPriceFolder($supplier, $fileInfo->getPathname());
                    
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
     * 
     */
    protected function _checkBillFolder($supplier, $folderName)
    {    
        setlocale(LC_ALL,'ru_RU.UTF-8');
        if (is_dir($folderName)){
            foreach (new \DirectoryIterator($folderName) as $fileInfo) {
                if ($fileInfo->isDot()) continue;
                if ($fileInfo->isFile()){
                    $this->addIdoc($supplier, [
                        'status' => Idoc::STATUS_ACTIVE,
                        'name' => $fileInfo->getFilename(),
                        'description' => Encoder::encode($this->_filedata2array($fileInfo->getFilename(), $fileInfo->getPathname())),
                        'docKey' => null,
                    ]);
                }
                if ($fileInfo->isDir()){
                    $this->_checkBillFolder($supplier, $fileInfo->getPathname());                    
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
     * @return null
     */
    protected function _decompressAttachment($supplier, $filename, $filepath)
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
            $this->_checkBillFolder($supplier, $bill_supplier_folder_name);
        }
        
        $this->_clearBillFolder($supplier, $bill_supplier_folder_name);
        
        return;
    }
    
    /**
     * Проверка почты в ящике поставщика
     * @param BillGetting $billGetting
     */
    public function getBillByMail($billGetting)
    {
        if ($billGetting->getEmail() && $billGetting->getEmailPassword()){
            $box = [
                'host' => 'imap.yandex.ru',
                'server' => '{imap.yandex.ru:993/imap/ssl}',
                'user' => $billGetting->getEmail(),
                'password' => $billGetting->getEmailPassword(),
                'leave_message' => false,
            ];
            
            $mailList = $this->postManager->readImap($box);
            $validator = new IsCompressed();
            if (count($mailList)){
                foreach ($mailList as $mail){
                    if (isset($mail['attachment'])){
                        foreach($mail['attachment'] as $attachment){
                            if ($attachment['filename'] && file_exists($attachment['temp_file'])){
                                $pathinfo = pathinfo($attachment['filename']);
                                if ($validator->isValid($attachment['temp_file']) && $pathinfo['extension'] != 'xlsx'){
                                    $this->_decompressAttachment($billGetting->getSupplier(), $attachment['filename'], $attachment['temp_file']);
                                } else {
                                    $this->addIdoc($billGetting->getSupplier(), [
                                        'status' => Idoc::STATUS_ACTIVE,
                                        'name' => $attachment['filename'],
                                        'description' => Encoder::encode($this->_filedata2array($attachment['filename'], $attachment['temp_file'])),
                                        'docKey' => null,
                                    ]);
                                }    
                                unlink($attachment['temp_file']);
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
     * Прочитать ящики с накладными
     */
    public function billsByMail()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(900);
        $startTime = time();
        
        $billGettings = $this->entityManager->getRepository(BillGetting::class)
                ->findBy(['status' => BillSetting::STATUS_ACTIVE]);
        foreach ($billGettings as $billGetting){
            $this->getBillByMail($billGetting);
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
            $name = $code;
        }
        if (!$producer){
            $producer = $this->entityManager->getRepository(Producer::class)
                    ->findOneByAplId(0);
        }
        $articleFilter = new ArticleCode();
        return $this->assemblyManager->addNewGood($articleFilter->filter($article), $producer, NULL, 0, mb_substr($name, 0, 255));        
    }
    
    /**
     * Получить товар
     * @param Idoc $idoc
     * @param array $data
     * @return Goods
     */
    public function findGood($idoc, $data)
    {
        $articleStr = empty($data['article']) ? null:$data['article'];
        $producerStr = empty($data['producer']) ? null:$data['producer'];
        $goodName = empty($data['good_name']) ? null:$data['good_name'];
        $iid = empty($data['supplier_article']) ? null:$data['supplier_article'];
        
        $articleFilter = new ArticleCode();
        if ($articleStr && !$producer){
            $code = $articleFilter->filter($articleStr);
            $good = $this->entityManager->getRepository(Goods::class)
                    ->findOneByCode($code);
            if ($good){
                return $good;
            }
            $articles = $this->entityManager->getRepository(Article::class)
                    ->findByCode($code);
            foreach ($articles as $article){
                if ($article->getGood()){
                    return $article->getGood();
                }    
            }
        }
        if ($iid){
            $good = $this->entityManager->getRepository(BillSetting::class)
                    ->findGoodFromRawprice($idoc->getSupplier(), $iid);
            if (is_array($good)){
                return $this->_newGood($good['article'], $good['producer'], $good['goodName']);
            }
            if ($good){
                return $good;
            }
        }
        $producerProducer = null;
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
        $idocData = $idoc->idocToPtu($billSetting->toArray());
        if ($idocData['total'] && $idocData['doc_no'] && $idocData['doc_date']){
            
            $dataPtu = [
                'apl_id' => 0,
                'doc_no' => $idocData['doc_no'],
                'doc_date' => $idocData['doc_date'],
                'status_ex' => Ptu::STATUS_EX_UPL,
                'status' => Ptu::STATUS_ACTIVE,
            ];
            
            $defaultSupplySetting = $this->entityManager->getRepository(SupplySetting::class)
                    ->findOneBy(['supplier' => $idoc->getSupplier()->getId()]);
            $office = $defaultSupplySetting->getOffice();
            if (!$office){
                $office = $this->entityManager->getRepository(Office::class)
                        ->findDefaultOffice();
            }
            $legal = $this->entityManager->getRepository(Supplier::class)
                    ->findDefaultSupplierLegal($idoc->getSupplier(), $idocData['doc_date']);
            $contract = $this->entityManager->getRepository(Office::class)
                    ->findDefaultContract($office, $legal, $idocData['doc_date'], $this->_idocContractPay($idocData));
            
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
            
            if ($ptu && isset($idocData['tab'])){
                $rowNo = 1;
                foreach ($idocData['tab'] as $tp){
                    if (!empty($tp['quantity']) && !empty($tp['good_name'])){
                        $good = $this->findGood($idoc, $tp);   
                        if (empty($good)){
                            throw new \Exception("Не удалось создать карточку товара для документа {$tp['good_name']}");
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
                $idoc->setDocKey($ptu->getLogKey());
                $idoc->setStatus(Idoc::STATUS_RETIRED);
                $this->entityManager->persist($idoc);
                
                $ptu->setStatusEx(Ptu::STATUS_EX_NEW);
                $this->entityManager->persist($ptu);
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
        $billSettings = $this->entityManager->getRepository(BillSetting::class)
                ->findBy(['supplier' => $idoc->getSupplier()->getId(), 'status' => Idoc::STATUS_ACTIVE]);
        foreach ($billSettings as $billSetting){
            $idocData = $idoc->idocToPtu($billSetting->toArray());
            if ($idocData['doc_no'] && $idocData['doc_date'] > '1970-01-01' && $idocData['total']){
                if ($this->idocToPtu($idoc, $billSetting)){
                    return;
                }                
            }
        }
        
        foreach ($billSettings as $billSetting){
            $idocData = $idoc->idocToPtu($billSetting->toArray());
            if ($idocData['doc_no'] && $idocData['doc_date'] > '1970-01-01'){
                if ($this->idocToPtu($idoc, $billSetting)){
                    return;
                }
                
            }
        }
        
        if (count($billSettings)){
            $idoc->setStatus(Idoc::STATUS_ERROR);
            $this->entityManager->persist($idoc);
            $this->entityManager->flush($idoc);
        }
        
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
}
