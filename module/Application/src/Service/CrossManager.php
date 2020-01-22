<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\UnknownProducer;
use Application\Entity\Article;
use Application\Entity\OemRaw;
use Application\Entity\Oem;
use Application\Entity\Cross;
use Application\Entity\CrossList;
use Application\Filter\RawToStr;
use Application\Filter\CsvDetectDelimiterFilter;
use MvlabsPHPExcel\Service;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Zend\Validator\File\IsCompressed;
use Zend\Filter\Decompress;
use Application\Filter\Basename;
use Application\Filter\ProducerName;
use Application\Filter\ArticleCode;
use Application\Validator\IsRU;


/**
 * Description of CrossManager
 *
 * @author Daddy
 */
class CrossManager {
        
    const CROSS_FILE_EXTENSIONS   = 'xls, xlsx, csv, txt'; //допустимые расширения файлов c кроссами

    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    /**
     *
     * @var \Application\Service\ProducerManager 
     */
    private $producerManager;
  
    /**
     *
     * @var \Application\Service\PostManager 
     */
    private $postManager;
  
    /*
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;

    /**
     *
     * @var \Applicaion\Entity\GoodsManager 
     */
    private $goodManager;
    
  // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $producerManager, $goodManager, $postManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->producerManager = $producerManager;
        $this->goodManager = $goodManager;
        $this->postManager = $postManager;
        $this->adminManager = $adminManager;
    }
    
    /**
     * Распаковать файл архива
     * 
     * @param string $filename
     * @return null
     */
    public function decompress($filename)
    {
        $pathinfo = pathinfo($filename);
        $validator = new IsCompressed();
        if ($validator->isValid($filename)){
            setlocale(LC_ALL,'ru_RU.UTF-8');
            $filter = new Decompress([
                'adapter' => $pathinfo['extension'],
                'options' => [
                    'target' => $pathinfo['dirname'],
                ],
            ]);
            if ($filter->filter($filename)){
                unlink($filename);
            }
        }
        return;
    }
    
    /**
     * Проверка на файл с кроссами
     * 
     * @param string $filename
     * @return bool
     */
    public function isCrossFile($filename)
    {
        $validator = new FileExtensionValidator(self::CROSS_FILE_EXTENSIONS);
        
        return $validator->isValid($filename);
    }
    
    /**
     * Проверка на файл с архивом
     * 
     * @param string $filename
     * @return bool
     */
    public function isCompressFile($filename)
    {
        $validator = new IsCompressed();
        return $validator->isValid($filename);
    }
    
    
    /**
     * Проверка почты в ящике для кроссов
     * 
     */
    public function getCrossByMail()
    {
        
        $priceSettings = $this->adminManager->getPriceSettings();
        
        if ($priceSettings['cross_mail_box'] && $priceSettings['cross_mail_box_password']){
            $box = [
                'host' => 'imap.yandex.ru',
                'server' => '{imap.yandex.ru:993/imap/ssl}',
                'user' => $priceSettings['cross_mail_box'],
                'password' => $priceSettings['cross_mail_box_password'],
                'leave_message' => false,
            ];
//            var_dump($box); exit;

            $mailList = $this->postManager->readImap($box);
            
            if (count($mailList)){
                foreach ($mailList as $mail){
                    if (isset($mail['attachment'])){
                        foreach($mail['attachment'] as $attachment){
                            if ($attachment['filename'] && file_exists($attachment['temp_file'])){
                                if (file_exists($attachment['temp_file'])){ 
                                    $targetFolder = $this->entityManager->getRepository(Cross::class)
                                            ->getTmpCrossFolder();
                                    
                                    $filename = $targetFolder.'/'.$attachment['filename'];

                                    if (copy($attachment['temp_file'], $filename)){
                                        unlink($attachment['temp_file']);  
                                        
                                        $this->decompress($filename);
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
     * 
     * Проверка папки с кроссами. Если в папке есть кросс то загружаем его
     * 
     * @param string $folderName
     * 
     */
    public function checkCrossFolder($folderName)
    {    
        setlocale(LC_ALL,'ru_RU.UTF-8');
        if (is_dir($folderName)){
            foreach (new \DirectoryIterator($folderName) as $fileInfo) {
                if ($fileInfo->isDot()) continue;
                if ($fileInfo->isFile()){
                    $this->uploadCross($fileInfo->getPathname());
                }
                if ($fileInfo->isDir()){
                    $this->checkCrossFolder($fileInfo->getPathname());                    
                }
            }
        }
        return;
    }    
        
    /**
     * Загрузка сырого кросса csv, txt
     * @var string $filename
     */
    
    public function uploadCrossCsv($filename)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        $i = 0;
        
        if (file_exists($filename)){
            
            if (!filesize($filename)){
                return;
            }

            $basenameFilter = new Basename();

            $lines = fopen($filename, 'r');

            if($lines) {

                $detector = new CsvDetectDelimiterFilter();
                $delimiter = $detector->filter($filename);

                $filter = new RawToStr();

                $rows = 0;
                $cross = new Cross();
//                $cross->setSupplier($supplier);
                $cross->setFilename($basenameFilter->filter($filename));
                $cross->setStatus(Cross::STATUS_LOAD);
                $cross->setRowCount($rows);                    

                $currentDate = date('Y-m-d H:i:s');
                $cross->setDateCreated($currentDate);

                $this->entityManager->persist($cross);
                $this->entityManager->flush($cross);

                while (($row = fgetcsv($lines, 4096, $delimiter)) !== false) {

                    $str = $filter->filter($row);

                    if ($str){
                        $data = [
                            'rawdata' => $filter->filter($row),
                            'status'  => CrossList::STATUS_NEW,
                            'cross_id' => $cross->getId(),
                            'article_id' => null,
                        ];

                        $this->entityManager->getRepository(CrossList::class)
                                ->insertLine($data);
                        $rows ++;
                    }                            
                }

                if ($rows > 1){
//                    $cross->setStatus(Cross::STATUS_ACTIVE);
                    $status = Cross::STATUS_ACTIVE;
                } else {
//                    $cross->setStatus(Cross::STATUS_RETIRED);                    
                    $status = Cross::STATUS_RETIRED;
                }    
//                $cross->setRowCount($rows);                    
//                $this->entityManager->persist($cross);
//                $this->entityManager->flush($cross);                    
                $this->entityManager->getRepository(Cross::class)
                        ->updateCross($cross, ['status'=> $status, 'row_count' => $rows]);

                fclose($lines);
            }                                
            
            $this->entityManager->getRepository(Cross::class)->renameToArchive($filename);

        }
        
        return;
    }
    
    /**
     * Загрузка сырого кросса xls, xlsx
     * @var string $filename
     */
    
    public function uploadCrossXls($filename)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0); 
        
        if (file_exists($filename)){
            
            if (!filesize($filename)){
                return;
            }

            $basenameFilter = new Basename();

            $rows = 0;
            $cross = new Cross();
//            $cross->setSupplier($supplier);
            $cross->setFilename($basenameFilter->filter($filename));
            $cross->setStatus(Cross::STATUS_LOAD);
            $cross->setRowCount($rows);

            $currentDate = date('Y-m-d H:i:s');
            $cross->setDateCreated($currentDate);

            $this->entityManager->persist($cross);
            $this->entityManager->flush($cross);

            $filter = new RawToStr();

            try{
                $reader = IOFactory::createReaderForFile($filename);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e){
                //попытка прочитать файл старым способом
                $cross->setFilename($e->getMessage());
                $cross->setStatus(Cross::STATUS_FAILED);
                $this->entityManager->persist($cross);
                $this->entityManager->flush($cross);                    
                return $this->uploadCrossXls2($filename);
            }    
            $filterSubset = new \Application\Filter\ExcelColumn();
            $reader->setReadFilter($filterSubset);
            $spreadsheet = $reader->load($filename);

            $sheets = $spreadsheet->getAllSheets();
            foreach ($sheets as $sheet) { // PHPExcel_Worksheet

                $excel_sheet_content = $sheet->toArray();

                if (count($excel_sheet_content)){

                    foreach ($excel_sheet_content as $row){

                        $str = $filter->filter($row);

                        if ($str){

                            $data = [
                                'rawdata' => $filter->filter($row),
                                'status'  => CrossList::STATUS_NEW,
                                'cross_id' => $cross->getId(),
                                'article_id' => null,
                            ];

                            $this->entityManager->getRepository(CrossList::class)
                                    ->insertLine($data);
                            $rows ++;
                        }                               
                    }
                }

            }

            if ($rows > 1){
                $cross->setStatus(Cross::STATUS_ACTIVE);
            } else {
                $cross->setStatus(Cross::STATUS_RETIRED);                    
            }    
            $cross->setRowCount($rows);
            $this->entityManager->persist($cross);
            $this->entityManager->flush($cross);                    

            unset($spreadsheet);

        }    

        $this->entityManager->getRepository(Cross::class)->renameToArchive($filename);
        return;
    }    
    
    /**
     * Загрузка сырого кросса xls, xlsx
     * @var string $filename
     */
    
    public function uploadCrossXls2($filename)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0); 
        
        if (file_exists($filename)){
            
            if (!filesize($filename)){
                return;
            }
            
            $basenameFilter = new Basename();

            $mvexcel = new Service\PhpExcelService();
            try {
                $excel = $mvexcel->createPHPExcelObject($filename);
            } catch (\PHPExcel_Reader_Exception $e){
                //попытка прочитать файл не удалась
                $cross = new Cross();
                $cross->setFilename($e->getMessage());
                $cross->setStatus(Cross::STATUS_FAILED);
                $this->entityManager->persist($cross);
                $this->entityManager->flush($cross);                    
                $this->entityManager->getRepository(Cross::class)->renameToArchive($filename);
                return;
            }    

            $rows = 0;
            $cross = new Cross();
//            $cross->setSupplier($supplier);
            $cross->setFilename($basenameFilter->filter($filename));
            $cross->setStatus(Cross::STATUS_LOAD);
            $cross->setRowCount($rows);

            $currentDate = date('Y-m-d H:i:s');
            $cross->setDateCreated($currentDate);

            $this->entityManager->persist($cross);
            $this->entityManager->flush($cross);

            $filter = new RawToStr();

            $sheets = $excel->getAllSheets();
            foreach ($sheets as $sheet) { // PHPExcel_Worksheet
                $excel_sheet_content = $sheet->toArray();

                if (count($excel_sheet_content)){
                    foreach ($excel_sheet_content as $row){
                        $str = $filter->filter($row);

                        if ($str){

                            $data = [
                                'rawdata' => $filter->filter($row),
                                'status'  => CrossList::STATUS_NEW,
                                'cross_id' => $cross->getId(),
                                'article_id' => null,
                            ];

                            $this->entityManager->getRepository(CrossList::class)
                                    ->insertLine($data);
                            $rows ++;
                        }                                
                    }
                }

            }

            if ($rows > 1){
                $cross->setStatus(Cross::STATUS_ACTIVE);
            } else {
                $cross->setStatus(Cross::STATUS_RETIRED);                    
            }    
            $cross->setRowCount($rows);
            $this->entityManager->persist($cross);
            $this->entityManager->flush($cross);                    

            unset($excel);
            unset($mvexcel);

            $this->entityManager->getRepository(Cross::class)->renameToArchive($filename);
        }
        
        return;
    }
    
    /*
     * Загрузка сырого кросса
     * @var string $filename
     */
    
    public function uploadCross($filename)
    {
        
        if (file_exists($filename)){
            
            $pathinfo = pathinfo($filename);

            $validator = new IsCompressed();

            if ($validator->isValid($filename) && $pathinfo['extension'] != 'xlsx'){
                setlocale(LC_ALL,'ru_RU.UTF-8');
                $filter = new Decompress([
                    'adapter' => $pathinfo['extension'],
                    'options' => [
                        'target' => $pathinfo['dirname'],
                    ],
                ]);
                if ($filter->filter($filename)){
                    unlink($filename);
                    return $this->checkCrossFolder(self::CROSS_FOLDER);
                }
            }

            if (in_array(strtolower($pathinfo['extension']), ['xls', 'xlsx'])){
                return $this->uploadCrossXls($filename);
            }
            if (in_array(strtolower($pathinfo['extension']), ['txt', 'csv'])){
                return $this->uploadCrossCsv($filename);
            }
            
            $this->entityManager->getRepository(Cross::class)->renameToArchive($filename);
        }
        
        return;
    }
        
            
    /**
     * Удаление строки кросса
     * $param CrossList $line
     */
    public function removeLine($line)
    {
        $this->entityManager->remove($line);
        $this->entityManager->flush($line);        
    }
    
    /**
     * Удаление кросса
     * 
     * @param Cross $cross
     * 
     */
    public function removeCross($cross)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);
                
        $this->entityManager->getRepository(Cross::class)->deleteCrossList($cross);
        
        $crossListCount = $this->entityManager->getRepository(CrossList::class)
                ->count(['cross' => $cross->getId()]);
        
        if ($crossListCount == 0){
            $this->entityManager->remove($cross);
            $this->entityManager->flush($cross);
        }
        
        return;
    }  

    /**
     * Исследовать строку
     * 
     * @param CrossList $line
     */
    public function exploreLine($line)
    {
        $row = $line->getRawdataAsArray();
        $producerNameFilter = new ProducerName();
        $articleFilter = new ArticleCode();
        $isRuValidator = new IsRU();
        
        $articleCode = $brandArticleCode = $producer = $brandProducer = $name = $brandName = $articles = $description = null;

        foreach ($row as $key => $value){
            if (!$value){
                continue;
            }
//            var_dump($value);
            if (!$name || !$brandName){
                if ($isRuValidator->isValid(mb_strtoupper($value, 'utf-8'))){
                    if (!$name){
                        $name = $value;
                        $description['producerArticleName'] = $key;
                    } else {
                        $brandName = $value;
                        $description['brandArticleName'] = $key;
                    }
                    continue;
                }
            }    
            
            if (!$articleCode || !$brandArticleCode){
                $code = $articleFilter->filter($value);
                if ($code && $code != OemRaw::LONG_CODE){
                    $oes = $this->entityManager->getRepository(Oem::class)
                            ->findBy(['oe' => $code, 'status' => Oem::STATUS_ACTIVE]);
                    if (count($oes)){
                        if (!$articleCode){
                            $articleCode = $code;
                            $description['producerArticle'] = $key;
                        } else {
                            $brandArticleCode = $code;
                            $description['brandArticle'] = $key;
                        }                        
//                        continue;
                    }
                }
            }    

            if (!$producer || !$brandProducer){
                $producerName = $producerNameFilter->filter($value);
//                var_dump($producerName);
                if ($producerName){
                    $unknownProducers = $this->entityManager->getRepository(UnknownProducer::class)
                            ->findUnknownProducerByName($producerName);
                    if (count($unknownProducers)){
                        if (!$producer){
                            $producer = $producerName;
                            $description['producerName'] = $key;
                            if ($articleCode == $producerName){
                                unset($articleCode);
                                unset($description['producerArticle']);
                            }
                        } else {
                            $brandProducer = $producerName;
                            $description['brandName'] = $key;
                            if ($brandArticleCode == $producerName){
                                unset($brandArticleCode);
                                unset($description['brandArticle']);
                            }
                        }                        
                        continue;
                    }
                }
            }    
        }
        
        if ($articleCode){
            $articles = $this->entityManager->getRepository(Article::class)
                    ->findBy(['code' => $articleCode]);
            foreach ($articles as $article){
                $unknownProducerName = $article->getUnknownProducer()->getName();
                $unknownProducerNameTd = $article->getUnknownProducer()->getNameTd();
                foreach ($row as $key => $value){
                    if ($producerNameFilter->filter($unknownProducerName) == $producerNameFilter->filter($value) ||
                            $producerNameFilter->filter($unknownProducerNameTd) == $producerNameFilter->filter($value)){
                        $producer = $unknownProducerName;
                        $description['producerName'] = $key;
                        $description['articleBy'] = 'producer';
                        break;
                    }
                }
                
                if ($producer){
                    break;
                }
            }        
        }

        if ($brandArticleCode){
            $articles = $this->entityManager->getRepository(Article::class)
                    ->findBy(['code' => $brandArticleCode]);
            foreach ($articles as $article){
                $unknownProducerName = $article->getUnknownProducer()->getName();
                $unknownProducerNameTd = $article->getUnknownProducer()->getNameTd();
                foreach ($row as $key => $value){
                    if ($producerNameFilter->filter($unknownProducerName) == $producerNameFilter->filter($value) ||
                            $producerNameFilter->filter($unknownProducerNameTd) == $producerNameFilter->filter($value)){
                        $brandProducer = $unknownProducerName;
                        $description['brandName'] = $key;
                        if (!isset($description['articleBy'])){
                            $description['articleBy'] = 'brand';
                        }
                        break;
                    }
                }
                
                if ($brandProducer){
                    break;
                }
            }        
        }
        
//        var_dump($row);
//        var_dump($description);
        if (isset($description['articleBy'])){
            if ($description['articleBy'] == 'producer' && isset($description['brandArticle'])){
                return $description;
            }
            if ($description['articleBy'] == 'brand' && isset($description['producerArticle'])){
                return $description;
            }            
        }
        
        return;
    }
    
    /**
     * Исследовать кросс
     * 
     * @param Cross $cross
     */
    public function exploreCross($cross)
    {
        $cross->setDescription(null);
        $cross->setStatus(Cross::STATUS_ACTIVE);
        $this->entityManager->persist($cross);
        $this->entityManager->flush($cross);                

        $lines = $this->entityManager->getRepository(CrossList::class)
                ->findBy(['cross' => $cross->getId()], null, 100);
        
        foreach ($lines as $line){
            $description = $this->exploreLine($line);
            if (is_array($description)){
                $cross->setDescription($description);
                $cross->setStatus(Cross::STATUS_EXPLORED);
                $this->entityManager->persist($cross);
                $this->entityManager->flush($cross);                
                return;
            }
        }
        
        return;
    }
    
    /**
     * Разобрать строку кросса
     * 
     * @param CrossList $line
     * @param array $description
     */
    public function parseLine($line, $description)
    {
        $data = [];
        $rawData = $line->getRawdataAsArray();
        if (isset($description['producerName'])){
            if (isset($rawData[$description['producerName']])){
                $data['producer_name'] = $rawData[$description['producerName']];
            }    
        }
        if (isset($description['producerArticle'])){
            if (isset($rawData[$description['producerArticle']])){
                $data['producer_article'] = $rawData[$description['producerArticle']];
            }    
        }
        if (isset($description['producerArticleName'])){
            if (isset($rawData[$description['producerArticleName']])){
                $data['producer_article_name'] = $rawData[$description['producerArticleName']];
            }    
        }
        if (isset($description['brandName'])){
            if (isset($rawData[$description['brandName']])){
                $data['brand_name'] = $rawData[$description['brandName']];
            }    
        }
        if (isset($description['brandArticle'])){
            if (isset($rawData[$description['brandArticle']])){
                $data['brand_article'] = $rawData[$description['brandArticle']];
            }    
        }
        if (isset($description['brandArticleName'])){
            if (isset($rawData[$description['brandArticleName']])){
                $data['brand_article_name'] = $rawData[$description['brandArticleName']];
            }    
        }

        if (count($data)){
            $data['status'] = CrossList::STATUS_PARSED;
            $this->entityManager->getRepository(CrossList::class)
                    ->updateCrossList($line, $data);
        }
        
        return;
    }
    
    /**
     * Разобрать кросс
     * 
     * @param Cross $cross
     */
    public function parseCross($cross)
    {
        $cross->setStatus(Cross::STATUS_PARSE);
        $this->entityManager->persist($cross);
        $this->entityManager->flush($cross);

        $lineQuery = $this->entityManager->getRepository(Cross::class)
                ->crossList($cross, ['status' => CrossList::STATUS_NEW]);

        $iterator = $lineQuery->iterate();

        foreach ($iterator as $item){
            foreach ($item as $row){
                $this->parseLine($row, $cross->getDescription());
                $this->entityManager->detach($row);
            }
        }

        if (count($lineQuery->getResult()) == 0){
            $cross->setStatus(Cross::STATUS_PARSED);
            $this->entityManager->persist($cross);
            $this->entityManager->flush($cross);
        }

        unset($iterator);
        return;
    }
    
    /**
     * Привязать строку кросса к артикулу
     * 
     * @param CrossList $line
     * @param array $description
     */
    public function bindLine($line, $description)
    {
        $data = [];

        if (isset($description['articleBy'])){

            $producerNameFilter = new ProducerName();
            $articleFilter = new ArticleCode();

            if ($description['articleBy'] == 'producer'){
                $code = $articleFilter->filter($line->getProducerArticle());
                $unknownProducerName = $producerNameFilter->filter($line->getProducerName());
                $data['oe'] = $line->getBrandArticle();
                $data['oe_brand'] = $line->getBrandName();
            }
            if ($description['articleBy'] == 'brand'){
                $code = $articleFilter->filter($line->getBrandArticle());
                $unknownProducerName = $producerNameFilter->filter($line->getBrandName());
                $data['oe'] = $line->getProducerArticle();
                $data['oe_brand'] = $line->getProducerName();
            }

            if (isset($code) && isset($unknownProducerName)){
                $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                        ->findOneByName($unknownProducerName);
                
                if ($unknownProducer){
                    $article = $this->entityManager->getRepository(Article::class)
                           ->findOneBy(['code' => $code, 'unknownProducer' => $unknownProducer->getId()]);
                    if ($article){
                        $data['article_id'] = $article->getId();
                        if ($article->getGood()){
                            $data['code_id'] = $article->getGood()->getId();
                        }    
                    }
                }    
            }    
        }

        $data['status'] = CrossList::STATUS_BIND;
        $this->entityManager->getRepository(CrossList::class)
                ->updateCrossList($line, $data);
        
        return;
    }       

    /**
     * Привязать кросс
     * 
     * @param Cross $cross
     */
    public function bindCross($cross)
    {

        $lineQuery = $this->entityManager->getRepository(Cross::class)
                ->crossList($cross, ['status' => CrossList::STATUS_PARSED]);

        $iterator = $lineQuery->iterate();

        foreach ($iterator as $item){
            foreach ($item as $row){
                $this->bindLine($row, $cross->getDescription());
                $this->entityManager->detach($row);
            }
        }

        if (count($lineQuery->getResult()) == 0){
            $cross->setStatus(Cross::STATUS_BIND);
            $this->entityManager->persist($cross);
            $this->entityManager->flush($cross);
        }

        unset($iterator);
        return;
    }
    
    /**
     * Сбросить привязки кросса
     * 
     * @param Cross $cross
     */
    public function resetCross($cross)
    {

        $lineQuery = $this->entityManager->getRepository(Cross::class)
                ->crossList($cross);

        $iterator = $lineQuery->iterate();

        foreach ($iterator as $item){
            foreach ($item as $row){
                $this->entityManager->getRepository(CrossList::class)
                        ->updateCrossList($row, [
                            'status' => CrossList::STATUS_NEW, 
                            'code_id' => null, 
                            'oe' => null, 
                            'oe_brand' => NULL,
                         ]);
                $this->entityManager->detach($row);
            }
        }

        $cross->setStatus(Cross::STATUS_ACTIVE);
        $this->entityManager->persist($cross);
        $this->entityManager->flush($cross);

        unset($iterator);
        return;
    }
    
}
