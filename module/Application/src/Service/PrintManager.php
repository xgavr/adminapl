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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

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
    public function torg2($vtp, $writerType = 'Pdf')
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
        $spreadsheet->getProperties()
                ->setTitle($vtp->getDocPresent())
                ;
        
        $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A6', $vtp->getPtu()->getContract()->getCompany()->getLegalPresent())
                ->setCellValue('A9', $vtp->getPtu()->getOffice()->getName())
                ->setCellValue('CI7', $vtp->getPtu()->getContract()->getCompany()->getOkpo())
                ->setCellValue('AY17', $vtp->getDocNo())
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
                    ->findOneBy(['ptu' => $vtp->getPtu()->getId(), 'good' => $vtpGood->getGood()->getId()]);
            if ($ptuGood){
                $sheet2->setCellValue("A$row2", $vtpGood->getGood()->getNameShort());                
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
        
        switch ($writerType){
            case 'Pdf':
                $writer = IOFactory::createWriter($spreadsheet, 'Html');
                $htmlFilename = $vtp->getPrintName('html');
                $writer->writeAllSheets();
                $writer->save($htmlFilename);

                $mpdf = new Mpdf();
                $mpdf->WriteHTML(\file_get_contents($htmlFilename));
                $outFilename = $vtp->getPrintName($writerType);
                $mpdf->Output($outFilename,'F');
                break;
            case 'Xls':
            case 'Xlsx':
                $writer = IOFactory::createWriter($spreadsheet, $writerType);
                $outFilename = $vtp->getPrintName($writerType);
//                $writer->writeAllSheets();
                $writer->save($outFilename);
                break;
            default: 
                $outFilename = null;
        } 
        
        
        return $outFilename;
    }
    
    /**
     * Копирование строк
     * @param Worksheet $sheet
     * @param integer $srcRow
     * @param integer $dstRow
     * @param integer $maxRow
     * @param integer $maxCol
     */
    private function _copyRows(Worksheet $sheet, $srcRange, $dstCell, Worksheet $destSheet = null) 
    {
        if( !isset($destSheet)) {
            $destSheet = $sheet;
        }

        if( !preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', $srcRange, $srcRangeMatch) ) {
            // Invalid src range
            return;
        }

        if( !preg_match('/^([A-Z]+)(\d+)$/', $dstCell, $destCellMatch) ) {
            // Invalid dest cell
            return;
        }

        $srcColumnStart = $srcRangeMatch[1];
        $srcRowStart = $srcRangeMatch[2];
        $srcColumnEnd = $srcRangeMatch[3];
        $srcRowEnd = $srcRangeMatch[4];

        $destColumnStart = $destCellMatch[1];
        $destRowStart = $destCellMatch[2];

        $srcColumnStart = Coordinate::columnIndexFromString($srcColumnStart);
        $srcColumnEnd = Coordinate::columnIndexFromString($srcColumnEnd);
        $destColumnStart = Coordinate::columnIndexFromString($destColumnStart);

        $rowCount = 0;
        for ($row = $srcRowStart; $row <= $srcRowEnd; $row++) {
            $colCount = 0;
            for ($col = $srcColumnStart; $col <= $srcColumnEnd; $col++) {
                $cell = $sheet->getCellByColumnAndRow($col, $row);
                $style = $sheet->getStyleByColumnAndRow($col, $row);
                $dstCell = Coordinate::stringFromColumnIndex($destColumnStart + $colCount) . (string)($destRowStart + $rowCount);
                $destSheet->setCellValue($dstCell, $cell->getValue());
                $destSheet->duplicateStyle($style, $dstCell);

                // Set width of column, but only once per column
                if ($rowCount === 0) {
                    $w = $sheet->getColumnDimensionByColumn($col)->getWidth();
                    $destSheet->getColumnDimensionByColumn ($destColumnStart + $colCount)->setAutoSize(false);
                    $destSheet->getColumnDimensionByColumn ($destColumnStart + $colCount)->setWidth($w);
                }

                $colCount++;
            }

            $h = $sheet->getRowDimension($row)->getRowHeight();
            $destSheet->getRowDimension($destRowStart + $rowCount)->setRowHeight($h);

            $rowCount++;
        }

        foreach ($sheet->getMergeCells() as $mergeCell) {
            $mc = explode(":", $mergeCell);
            $mergeColSrcStart = Coordinate::columnIndexFromString(preg_replace("/[0-9]*/", "", $mc[0]));
            $mergeColSrcEnd = Coordinate::columnIndexFromString(preg_replace("/[0-9]*/", "", $mc[1]));
            $mergeRowSrcStart = ((int)preg_replace("/[A-Z]*/", "", $mc[0]));
            $mergeRowSrcEnd = ((int)preg_replace("/[A-Z]*/", "", $mc[1]));

            $relativeColStart = $mergeColSrcStart - $srcColumnStart;
            $relativeColEnd = $mergeColSrcEnd - $srcColumnStart;
            $relativeRowStart = $mergeRowSrcStart - $srcRowStart;
            $relativeRowEnd = $mergeRowSrcEnd - $srcRowStart;

            if (0 <= $mergeRowSrcStart && $mergeRowSrcStart >= $srcRowStart && $mergeRowSrcEnd <= $srcRowEnd) {
                $targetColStart = Coordinate::stringFromColumnIndex($destColumnStart + $relativeColStart);
                $targetColEnd = Coordinate::stringFromColumnIndex($destColumnStart + $relativeColEnd);
                $targetRowStart = $destRowStart + $relativeRowStart;
                $targetRowEnd = $destRowStart + $relativeRowEnd;

                $merge = (string)$targetColStart . (string)($targetRowStart) . ":" . (string)$targetColEnd . (string)($targetRowEnd);
                //Merge target cells
                $destSheet->mergeCells($merge);
            }
        }
    }

    private function _copyStyleXFCollection(Spreadsheet $sourceSheet, Spreadsheet $destSheet) 
    {
        $collection = $sourceSheet->getCellXfCollection();

        foreach ($collection as $key => $item) {
            $destSheet->addCellXf($item);
        }
    }
    
    
    /**
     * УПД возврат
     * @param Vtp $vtp
     * @param string $writerType
     * @return string 
     */
    public function updVtp($vtp, $writerType = 'Pdf')
    {
        ini_set("pcre.backtrack_limit", "5000000");
        setlocale(LC_ALL, 'ru_RU', 'ru_RU.UTF-8', 'ru', 'russian');
//        echo strftime("%B %d, %Y", time()); exit;
        
        $upd_folder_name = Vtp::PRINT_FOLDER;
        if (!is_dir($upd_folder_name)){
            mkdir($upd_folder_name);
        }        
        
        $inputFileType = 'Xls';
        $reader = IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load(Vtp::TEMPLATE_UPD);
        $spreadsheet->getProperties()
                ->setTitle($vtp->getDocPresent('УПД'))
                ;
        $sheet = $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('E6', 1)
                ->setCellValue('V2', $vtp->getDocNo())
                ->setCellValue('AF2', date('d.m.Y', strtotime($vtp->getDocDate())))
                ->setCellValue('AB5', $vtp->getPtu()->getContract()->getCompany()->getName())
                ->setCellValue('AB6', $vtp->getPtu()->getContract()->getCompany()->getAddress())
                ->setCellValue('AB7', $vtp->getPtu()->getContract()->getCompany()->getInnKpp())
                ->setCellValue('AD8', 'он же')
                ->setCellValue('AD9', trim($vtp->getPtu()->getLegal()->getName().' '.$vtp->getPtu()->getLegal()->getAddress()))
                ->setCellValue('AB12', $vtp->getPtu()->getLegal()->getName())
                ->setCellValue('AB13', $vtp->getPtu()->getLegal()->getAddress())
                ->setCellValue('AB14', $vtp->getPtu()->getLegal()->getInnKpp())
                ->setCellValue('AB15', 'Российский рубль, 643')
                ->setCellValue('AS16', $vtp->getPtu()->getContract()->getContractPresent(''))
                
                ->setCellValue('AR22', number_format($vtp->getAmount(), 2, ',', ' '))
                ->setCellValue('BM22', number_format($vtp->getAmount(), 2, ',', ' '))

                ->setCellValue('AJ24', $vtp->getPtu()->getContract()->getCompany()->getHead())
                ->setCellValue('BV24', $vtp->getPtu()->getContract()->getCompany()->getChiefAccount())                
                ->setCellValue('X36', date('d', strtotime($vtp->getDocDate())))
                ->setCellValue('AA36', date('m', strtotime($vtp->getDocDate())))
                ->setCellValue('AL36', date('y', strtotime($vtp->getDocDate())))                
                ;
        
        $signatory = $this->entityManager->getRepository(Commission::class)
                ->findOneBy(['status' => Commission::STATUS_SIGN]);
        if ($signatory){
            $sheet->setCellValue('A34', $signatory->getPosition());
            $sheet->setCellValue('AC34', $signatory->getName());
        }
        
        $vtpGoods = $this->entityManager->getRepository(VtpGood::class)
                ->findByVtp($vtp->getId());
        if ($vtpGoods){
            $i = 1;
            $row = 21;
            $srcRow = $row + count($vtpGoods)-1;
            if (count($vtpGoods) > 1){
                $sheet->insertNewRowBefore($row, count($vtpGoods) - 1);            
            }
            foreach ($vtpGoods as $vtpGood){
                if (count($vtpGoods) > 1){
                    $this->_copyRows($sheet, "A$srcRow:CJ$srcRow", "A$row");
                } 
                $ptuGood = $this->entityManager->getRepository(PtuGood::class)
                        ->findOneBy(['ptu' => $vtp->getPtu()->getId(), 'good' => $vtpGood->getGood()->getId()]);
                if ($ptuGood){
                    $sheet->setCellValue("A$row", $i);                
                    $sheet->setCellValue("C$row", $vtpGood->getGood()->getCode());                
                    $sheet->setCellValue("I$row", $vtpGood->getGood()->getNameShort());                
                    $sheet->setCellValue("Z$row", $ptuGood->getUnit()->getCode());                
                    $sheet->setCellValue("AB$row", $ptuGood->getUnit()->getName());                
                    $sheet->setCellValue("AI$row", number_format($vtpGood->getQuantity(), 0, ',', ' '));                
                    $sheet->setCellValue("AM$row", number_format($vtpGood->getPrice(), 2, ',', ' '));                              
                    $sheet->setCellValue("AR$row", number_format($vtpGood->getAmount(), 2, ',', ' '));                
                    $sheet->setCellValue("AY$row", 'Без акциза');                
                    $sheet->setCellValue("BC$row", 'Без налога');                
                    $sheet->setCellValue("BM$row", number_format($vtpGood->getAmount(), 2, ',', ' '));                
                    $sheet->setCellValue("BT$row", $ptuGood->getCountry()->getCode());                
                    $sheet->setCellValue("BX$row", $ptuGood->getCountry()->getName());                
                    $sheet->setCellValue("CE$row", $ptuGood->getNtd()->getNtd());                
                } else {
                    $sheet->setCellValue("I$row", '!Не найдено в приходе!');
                }
                $i++;
                $row++;
            }
        }
                
        switch ($writerType){
            case 'Pdf':
                $writer = IOFactory::createWriter($spreadsheet, 'Html');
                $htmlFilename = $vtp->getPrintName('html', 'УПД');
                $writer->writeAllSheets();
                $writer->save($htmlFilename);

                $mpdf = new Mpdf();
                $mpdf->WriteHTML(\file_get_contents($htmlFilename));
                $outFilename = $vtp->getPrintName($writerType, 'УПД');
                $mpdf->Output($outFilename,'F');
                break;
            case 'Xls':
            case 'Xlsx':
                $writer = IOFactory::createWriter($spreadsheet, $writerType);
                $outFilename = $vtp->getPrintName($writerType, 'УПД');
//                $writer->writeAllSheets();
                $writer->save($outFilename);
                break;
            default: 
                $outFilename = null;
        } 
        
        
        return $outFilename;
    }    
}
