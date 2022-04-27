<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Laminas\ServiceManager\ServiceManager;
use Application\Entity\Supplier;
use Application\Entity\Raw;
use Application\Entity\Rawprice;
use Application\Filter\RawToStr;
use Application\Filter\CsvDetectDelimiterFilter;
use MvlabsPHPExcel\Service;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Laminas\Validator\File\IsCompressed;
use Laminas\Filter\Decompress;
use Application\Filter\Basename;
use Stock\Entity\Vtp;
use Company\Entity\Legal;
use Mpdf\Mpdf;
use Company\Entity\Office;
use Stock\Entity\Ptu;
use Stock\Entity\VtpGood;
use Stock\Entity\PtuGood;
use Company\Entity\Commission;


/**
 * Description of PrintManager
 *
 * @author Daddy
 */
class PrintManager {
    
    const TEMPLATE_FOLDER       = './data/template'; // папка с шаблонами
     

    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    private function _getTemplateFolder()
    {
        return self::TEMPLATE_FOLDER;
    }        
    
    private function _addTemplatesFolder()
    {
        //Создать папку для шаблонов
        $template_folder_name = $this->_getTemplateFolder();
        if (!is_dir($template_folder_name)){
            mkdir($template_folder_name);
        }        
    }        
        
  // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
        
        $this->_addTemplatesFolder();
    }
    
    /**
     * Торг2
     * @param Vtp $vtp
     * @param string $writerType
     * @return string 
     */
    public function torg2($vtp, $writerType = 'Html')
    {
        ini_set("pcre.backtrack_limit", "5000000");
        setlocale(LC_ALL, 'ru_RU', 'ru_RU.UTF-8', 'ru', 'russian');
//        echo strftime("%B %d, %Y", time()); exit;
        
        $torg2_folder_name = Vtp::PRINT_FOLDER;
        if (!is_dir($torg2_folder_name)){
            mkdir($torg2_folder_name);
        }        
        
        $inputFileType = 'Xls';
        $reader = IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load(Vtp::TEMPLATE_TORG2);
        
        $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A6', $vtp->getPtu()->getContract()->getCompany()->getLegalPresent())
                ->setCellValue('A9', $vtp->getPtu()->getOffice()->getName())
                ->setCellValue('CI7', $vtp->getPtu()->getContract()->getCompany()->getOkpo())
                ->setCellValue('AY17', $vtp->getId())
                ->setCellValue('BK17', date('d.m.y', strtotime($vtp->getDocDate())))
                ->setCellValue('CK18', $vtp->getPtu()->getContract()->getCompany()->getHead())
                ->setCellValue('CD20', date('d', strtotime($vtp->getDocDate())))
                ->setCellValue('CI20', date('m', strtotime($vtp->getDocDate())))
                ->setCellValue('CS20', date('Y', strtotime($vtp->getDocDate())))
                ->setCellValue('AE23', $vtp->getPtu()->getContract()->getOffice()->getLegalContact()->getAddressForDoc())
                ->setCellValue('BL24', date('d', strtotime($vtp->getDocDate())))
                ->setCellValue('BS24', date('m', strtotime($vtp->getDocDate())))
                ->setCellValue('CS24', date('Y', strtotime($vtp->getDocDate())))
                ->setCellValue('AS25', $vtp->getPtu()->getDocPresent())
                ->setCellValue('U33', $vtp->getPtu()->getLegal()->getLegalPresent())
                ->setCellValue('N37', $vtp->getPtu()->getLegal()->getLegalPresent())
                ->setCellValue('BB42', $vtp->getPtu()->getContract()->getAct())
                ->setCellValue('BV42', date('d', strtotime($vtp->getPtu()->getContract()->getDateStart())))
                ->setCellValue('CC42', date('m', strtotime($vtp->getPtu()->getContract()->getDateStart())))
                ->setCellValue('CO42', date('Y', strtotime($vtp->getPtu()->getContract()->getDateStart())))
                ->setCellValue('U43', $vtp->getPtu()->getDocNo())
                ->setCellValue('AO43', date('d', strtotime($vtp->getPtu()->getDocDate())))
                ->setCellValue('AV43', date('m', strtotime($vtp->getPtu()->getDocDate())))
                ->setCellValue('BH43', date('Y', strtotime($vtp->getPtu()->getDocDate())))
                ;
        
        $sheet2 = $spreadsheet->setActiveSheetIndex(1);
        $sheet3 = $spreadsheet->setActiveSheetIndex(2);
        
        $vtpGoods = $this->entityManager->getRepository(VtpGood::class)
                ->findByVtp($vtp->getId());
        $row2 = 40;
        $row3 = 40;
        foreach ($vtpGoods as $vtpGood){
            $ptuGood = $this->entityManager->getRepository(PtuGood::class)
                    ->findOneBy(['good' => $vtpGood->getGood()->getId()]);
            if ($ptuGood){
                $sheet2->setCellValue("A$row2", $vtpGood->getGood()->getName());                
                $sheet2->setCellValue("AL$row2", $ptuGood->getUnit()->getName());                
                $sheet2->setCellValue("AS$row2", $ptuGood->getUnit()->getCode());                
                $sheet2->setCellValue("BA$row2", $vtpGood->getGood()->getCode());                
                $sheet2->setCellValue("BS$row2", $vtpGood->getQuantity());                
                $sheet2->setCellValue("CD$row2", $vtpGood->getPrice());                
                $sheet2->setCellValue("CQ$row2", $vtpGood->getAmount());                

                $sheet3->mergeCells("A$row3:N$row3");
                $sheet3->setCellValue("A$row3", $vtpGood->getGood()->getCode());                
                $sheet3->setCellValue("O$row3", $vtpGood->getQuantity());                
                $sheet3->setCellValue("V$row3", $vtpGood->getPrice());                
                $sheet3->setCellValue("AC$row3", $vtpGood->getAmount());                
                $sheet3->setCellValue("AJ$row3", $vtpGood->getQuantity());                
                $sheet3->setCellValue("AQ$row3", $vtpGood->getAmount());                
            } else {
                $sheet1->setCellValue("A$row2", '!Не найдено в приходе!');
            }
            $row2++;
            $row3++;
        }
        
        $sheet4 = $spreadsheet->setActiveSheetIndex(3);
        $sheet4->setCellValue("A21", $vtp->getInfo());
        $commission = $vtp->getPtu()->getOffice()->getCommission();
        $memberRow = 37;
        foreach ($commission as $commissar){
            if ($commissar->getStatus() == Commission::STATUS_HEAD){
                $sheet4->setCellValue("AC34", $commissar->getPosition());
                $sheet4->setCellValue("CA34", $commissar->getName());
            }
            if ($commissar->getStatus() == Commission::STATUS_MEMBER){
                $sheet4->setCellValue("AC$memberRow", $commissar->getPosition());
                $sheet4->setCellValue("CA$memberRow", $commissar->getName());  
                $memberRow += 2;
            }
        }
                
        $writer = IOFactory::createWriter($spreadsheet, $writerType);
        $htmlFilename = $vtp->getPrintName('html');
        $writer->writeAllSheets();
        $writer->save($htmlFilename);
        
        $mpdf = new Mpdf([
            'margin_header' => 10,
            'margin_footer' => 10,
            'margin_top' => 5,
            'margin_bottom' => 5,
        ]);
        $mpdf->WriteHTML(\file_get_contents($htmlFilename));
        $pdfFilename = $vtp->getPrintName('pdf');
        $mpdf->Output($pdfFilename,'F');
        
        
        return $pdfFilename;
    }
}
