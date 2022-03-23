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
use Application\Filter\RawToStr;

/**
 * Description of BillManager
 *
 * @author Daddy
 */
class BillManager
{
    
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
    
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $postManager)
    {
        $this->entityManager = $entityManager;
        $this->postManager = $postManager;
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
                                    
            $filter = new RawToStr();
                    
            try{
                $reader = IOFactory::createReaderForFile($filename);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e){
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
                            $result[] = explode(';', $str);                              
                        }                               
                    }
                }
                    
            }                
            unset($spreadsheet);
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
                'leave_message' => true,
            ];
            
            $mailList = $this->postManager->readImap($box);
            
            if (count($mailList)){
                foreach ($mailList as $mail){
                    if (isset($mail['attachment'])){
                        foreach($mail['attachment'] as $attachment){
                            if ($attachment['filename'] && file_exists($attachment['temp_file'])){                                
                                $this->addIdoc($billGetting->getSupplier(), [
                                    'status' => Idoc::STATUS_ACTIVE,
                                    'name' => $attachment['filename'],
                                    'description' => Encoder::encode($this->_filedata2array($attachment['filename'], $attachment['temp_file'])),
                                    'docKey' => null,
                                ]);
                                
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
