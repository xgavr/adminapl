<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Stock\Entity\Vtp;
use Company\Entity\Legal;
use Company\Entity\Contract;
use Mpdf\Mpdf;
use Stock\Entity\VtpGood;
use Stock\Entity\PtuGood;
use Company\Entity\Commission;
use Application\Entity\Order;
use Application\Entity\Bid;
use Application\Filter\NumToStr;
use Application\Entity\Shipping;
use Company\Entity\LegalLocation;
use Bank\Entity\QrCode;
use Stock\Entity\Mutual;
use Stock\Entity\Movement;


/**
 * Description of PrintManager
 *
 * @author Daddy
 */
class PrintManager {
    
    const TEMPLATE_FOLDER       = './data/template'; // папка с шаблонами
    const PUBLIC_DOC_FOLDER       = './public/doc'; // папка с документами
     

    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    /**
     * Менеджер СБП.
     * @var \Bank\Service\SbpManager 
     */
    private $sbpManager;    
    
    private function _getTemplateFolder()
    {
        return self::TEMPLATE_FOLDER;
    }        
    
    /**
     * Переименовать файл в папке doc
     * @param sting $oldFileName
     * @param string $newFileName
     */
    public function renameIfNeed($oldFileName, $newFileName)
    {
        return rename(self::PUBLIC_DOC_FOLDER.'/'.$oldFileName, self::PUBLIC_DOC_FOLDER.'/'.$newFileName);        
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
    public function __construct($entityManager, $sbpManager)
    {
        $this->entityManager = $entityManager;
        $this->sbpManager = $sbpManager;
        
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
                ->setCellValue('A6', $vtp->getPtu()->getContract()->getCompany()->getLegalPresent(['onDate' => $vtp->getPtu()->getDocDate()]))
                ->setCellValue('A9', $vtp->getPtu()->getOffice()->getName())
                ->setCellValue('CI7', $vtp->getPtu()->getContract()->getCompany()->getOkpo())
                ->setCellValue('AY17', $vtp->getDocNo())
                ->setCellValue('BK17', date('d.m.y', strtotime($vtp->getDocDate())))
                ->setCellValue('CK18', $vtp->getPtu()->getContract()->getCompany()->getHeadFio())
                ->setCellValue('CD20', date('d', strtotime($vtp->getDocDate())))
                ->setCellValue('CI20', date('m', strtotime($vtp->getDocDate())))
                ->setCellValue('CS20', date('Y', strtotime($vtp->getDocDate())))
                ->setCellValue('AE23', $vtp->getPtu()->getContract()->getOffice()->getLegalContact()->getAddressForDoc())
                ->setCellValue('BL24', date('d', strtotime($vtp->getDocDate())))
                ->setCellValue('BS24', date('m', strtotime($vtp->getDocDate())))
                ->setCellValue('CS24', date('Y', strtotime($vtp->getDocDate())))
                ->setCellValue('AS25', $vtp->getPtu()->getDocPresent())
                ->setCellValue('U33', $vtp->getPtu()->getLegal()->getLegalPresent(['onDate' => $vtp->getDocDate()]))
                ->setCellValue('N37', $vtp->getPtu()->getLegal()->getLegalPresent(['onDate' => $vtp->getDocDate()]))
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
                $sheet2->setCellValue("CD$row2", number_format($vtpGood->getPrice(), 2, ',', ''));                
                $sheet2->setCellValue("CQ$row2", number_format($vtpGood->getAmount(), 2, ',', ''));                

                $sheet3->mergeCells("A$row3:N$row3");
                $sheet3->setCellValue("A$row3", $vtpGood->getGood()->getCode());                
                $sheet3->setCellValue("O$row3", $vtpGood->getQuantity());                
                $sheet3->setCellValue("V$row3", number_format($vtpGood->getPrice(), 2, ',', ''));                
                $sheet3->setCellValue("AC$row3", number_format($vtpGood->getAmount(), 2, ',', ''));                
                $sheet3->setCellValue("AJ$row3", $vtpGood->getQuantity());                
                $sheet3->setCellValue("AQ$row3", number_format($vtpGood->getAmount(), 2, ',', ''));                
            } else {
                $sheet1->setCellValue("A$row2", '!Не найдено в приходе!');
            }
            $row2++;
            $row3++;
        }
        
        $sheet4 = $spreadsheet->setActiveSheetIndex(3);
        $sheet4->setCellValue("A21", $vtp->getCause());
        $commission = $vtp->getPtu()->getOffice()->getCommission();
        $memberRow = 37;
        foreach ($commission as $commissar){
            if ($commissar->getStatus() == Commission::STATUS_HEAD){
                $sheet4->setCellValue("AC34", $commissar->getPosition());
                $sheet4->setCellValue("CA34", $commissar->getName());
            }
            if ($commissar->getStatus() != Commission::STATUS_HEAD){
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
                ->setCellValue('D6', 2)
                ->setCellValue('L2', $vtp->getDocNo())
                ->setCellValue('T2', date('d.m.Y', strtotime($vtp->getDocDate())))
                ->setCellValue('O5', $vtp->getPtu()->getContract()->getCompany()->getName())
                ->setCellValue('O6', $vtp->getPtu()->getContract()->getCompany()->getAddress())
                ->setCellValue('O7', $vtp->getPtu()->getContract()->getCompany()->getInnKpp())
                ->setCellValue('O8', trim($vtp->getPtu()->getContract()->getCompany()->getName().' '.$vtp->getPtu()->getContract()->getCompany()->getAddress()))
                ->setCellValue('O9', trim($vtp->getPtu()->getLegal()->getName().' '.$vtp->getPtu()->getLegal()->getAddress()))
                ->setCellValue('O12', $vtp->getPtu()->getLegal()->getName())
                ->setCellValue('O13', $vtp->getPtu()->getLegal()->getAddress())
                ->setCellValue('O14', $vtp->getPtu()->getLegal()->getInnKpp())
                ->setCellValue('O15', 'Российский рубль, 643')
                ->setCellValue('M28', $vtp->getPtu()->getContract()->getContractPresent('Договор'))
                
                ->setCellValue('AH22', number_format($vtp->getAmount(), 2, ',', ''))
                ->setCellValue('AS22', number_format($vtp->getAmount(), 2, ',', ''))

                ->setCellValue('W24', $vtp->getPtu()->getContract()->getCompany()->getHead())
                ->setCellValue('AU24', $vtp->getPtu()->getContract()->getCompany()->getChiefAccount())                
                ->setCellValue('K37', date('d.m.Y', strtotime($vtp->getDocDate())))
                ;
        
        $signatory = $this->entityManager->getRepository(Commission::class)
                ->findOneBy(['status' => Commission::STATUS_SIGN]);
        if ($signatory){
            $sheet->setCellValue('B35', $signatory->getPosition());
            $sheet->setCellValue('S35', $signatory->getName());
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
                    $sheet->setCellValue("B$row", $i);                
                    $sheet->setCellValue("F$row", $i);                
                    $sheet->setCellValue("C$row", $vtpGood->getGood()->getCode());                
                    $sheet->setCellValue("H$row", $vtpGood->getGood()->getNameShort());                
                    $sheet->setCellValue("U$row", $ptuGood->getUnit()->getCode());                
                    $sheet->setCellValue("W$row", $ptuGood->getUnit()->getName());                
                    $sheet->setCellValue("X$row", number_format($vtpGood->getQuantity(), 0, ',', ''));                
                    $sheet->setCellValue("AD$row", number_format($vtpGood->getPrice(), 2, ',', ''));                              
                    $sheet->setCellValue("AH$row", number_format($vtpGood->getAmount(), 2, ',', ''));                
                    $sheet->setCellValue("AK$row", 'Без акциза');                
                    $sheet->setCellValue("AM$row", 'Без НДС');                
                    $sheet->setCellValue("AS$row", number_format($vtpGood->getAmount(), 2, ',', ''));                
                    $sheet->setCellValue("AV$row", $ptuGood->getCountry()->getCode());                
                    $sheet->setCellValue("AW$row", $ptuGood->getCountry()->getName());                
                    $sheet->setCellValue("BG$row", $ptuGood->getNtd()->getNtd());                
                } else {
                    $sheet->setCellValue("H$row", '!Не найдено в приходе!');
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

    /**
     * УПД возврат
     * @param Vtp $vtp
     * @param string $writerType
     * @return string 
     */
    public function updVtp2($vtp, $writerType = 'Pdf')
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
                ->setCellValue('X2', $vtp->getDocNo())
                ->setCellValue('AF2', date('d.m.Y', strtotime($vtp->getDocDate())))
                ->setCellValue('AB5', $vtp->getPtu()->getContract()->getCompany()->getName())
                ->setCellValue('AB6', $vtp->getPtu()->getContract()->getCompany()->getAddress())
                ->setCellValue('AB7', $vtp->getPtu()->getContract()->getCompany()->getInnKpp())
                ->setCellValue('AI8', 'он же')
                ->setCellValue('AI9', trim($vtp->getPtu()->getLegal()->getName().' '.$vtp->getPtu()->getLegal()->getAddress()))
                ->setCellValue('AB12', $vtp->getPtu()->getLegal()->getName())
                ->setCellValue('AB13', $vtp->getPtu()->getLegal()->getAddress())
                ->setCellValue('AB14', $vtp->getPtu()->getLegal()->getInnKpp())
                ->setCellValue('AB15', 'Российский рубль, 643')
                ->setCellValue('AS16', $vtp->getPtu()->getContract()->getContractPresent(''))
                
                ->setCellValue('AR22', number_format($vtp->getAmount(), 2, ',', ' '))
                ->setCellValue('BM22', number_format($vtp->getAmount(), 2, ',', ' '))

                ->setCellValue('AJ24', $vtp->getPtu()->getContract()->getCompany()->getHead())
                ->setCellValue('BV24', $vtp->getPtu()->getContract()->getCompany()->getChiefAccount())                
                ->setCellValue('Z36', date('d', strtotime($vtp->getDocDate())))
                ->setCellValue('AC36', date('m', strtotime($vtp->getDocDate())))
                ->setCellValue('AN36', date('y', strtotime($vtp->getDocDate())))                
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

    /**
     * Торг12 возврат
     * @param Vtp $vtp
     * @param string $writerType
     * @param bool $code
     * @return string 
     */
    public function vtpTorg12($vtp, $writerType = 'Pdf', $code = true)
    {
        ini_set("pcre.backtrack_limit", "5000000");
        setlocale(LC_ALL, 'ru_RU', 'ru_RU.UTF-8', 'ru', 'russian');
//        echo strftime("%B %d, %Y", time()); exit;

        $folder_name = Vtp::PRINT_FOLDER;
        if (!is_dir($folder_name)){
            mkdir($folder_name);
        }        
        
        $rubToStrFilter = new NumToStr();
        $numToStrFilter = new NumToStr(['format' => NumToStr::FORMAT_NUM]);
        
        $inputFileType = 'Xls';
        $reader = IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load(Vtp::TEMPLATE_TORG12);
        $spreadsheet->getProperties()
                ->setTitle($vtp->getDocPresent('Накладная'))
                ;
        
        $recipient = $vtp->getPtu()->getLegal();
        $company = $vtp->getPtu()->getContract()->getCompany();
        
        $sheet = $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('B3', $company->getCompanyBankAccountPresent($vtp->getPtu()->getOffice()))
                ->setCellValue('AL4', $company->getOkpo())
                ->setCellValue('D8', ($recipient) ? $recipient->getLegalBankAccountPresent(['locationStatus' => LegalLocation::STATUS_CONSIGNEE, 'onDate' => $vtp->getPtu()->getDocDate()]):'')
                ->setCellValue('AL9', $company->getOkpo())
                ->setCellValue('D10', $company->getCompanyBankAccountPresent($vtp->getPtu()->getOffice()))
                ->setCellValue('D12', ($recipient) ? $recipient->getLegalBankAccountPresent(['locationStatus' => LegalLocation::STATUS_ACTIVE, 'onDate' => $vtp->getPtu()->getDocDate()]):'')
//                ->setCellValue('D14', ($order->getContract()) ? $order->getContract()->getContractPresent():'')
                ->setCellValue('K17', $vtp->getDocNo())
                ->setCellValue('O17', date('d.m.Y', strtotime($vtp->getDocDate())))
                ->setCellValue('Z25', number_format($vtp->getTotal(), 2, ',', ' '))
                ->setCellValue('AK25', number_format($vtp->getTotal(), 2, ',', ' '))
                
                ->setCellValue('F27', $numToStrFilter->filter($vtp->getVtpGoods()->count()))
                ->setCellValue('B37', $rubToStrFilter->filter($vtp->getTotal()))                
                ->setCellValue('J39', $company->getHead())
                ->setCellValue('J41', $company->getChiefAccount())

                ->setCellValue('F46', '"'.date('d', strtotime($vtp->getDocDate())).'"')
                ->setCellValue('G46', date('m', strtotime($vtp->getDocDate())))
                ->setCellValue('I46', date('Y', strtotime($vtp->getDocDate())).' года')                
                ;
        
        
        $bids = $this->entityManager->getRepository(VtpGood::class)
                ->findBy(['vtp' => $vtp->getId()]);
        if ($bids){
            $i = 1;
            $row = 23;
            $totalNum = $pageNum = $pageTotal = 0;
            $bidsCount = count($bids);
            $srcRow = $row + $bidsCount - 1;
            if ($bidsCount > 1){
                $sheet->insertNewRowBefore($row, $bidsCount - 1);            
            }
            foreach ($bids as $bid){
                if ($bidsCount > 1){
                    $this->_copyRows($sheet, "A$srcRow:AN$srcRow", "A$row");
                } 
                $sheet->setCellValue("B$row", $i);       
                $sheet->setCellValue("C$row", $bid->getGood()->getNameShort());                
                if ($code){
                    $sheet->setCellValue("G$row", $bid->getGood()->getCode());                
                } else {
                    $sheet->setCellValue("G$row", $bid->getGood()->getId());                                    
                }    
                $sheet->setCellValue("V$row", number_format($bid->getQuantity(), 0, ',', ' '));                
                $sheet->setCellValue("X$row", number_format($bid->getPrice(), 2, ',', ' '));                              
                $sheet->setCellValue("Z$row", number_format($bid->getTotal(), 2, ',', ' '));                
                $sheet->setCellValue("AK$row", number_format($bid->getTotal(), 2, ',', ' '));                
                $i++;
                $row++;
                $totalNum += $bid->getQuantity();
                $pageNum += $bid->getQuantity();
                $pageTotal += $bid->getTotal();
            }
            $sheet->setCellValue('V'.(24+$bidsCount-1), $pageNum);
            $sheet->setCellValue('V'.(25+$bidsCount-1), $totalNum);
            $sheet->setCellValue('Z'.(24+$bidsCount-1), number_format($pageTotal, 2, ',', ' '));
            $sheet->setCellValue('AK'.(24+$bidsCount-1), number_format($pageTotal, 2, ',', ' '));
        }
        
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageMargins()
            ->setLeft(0.5)
            ->setRight(0.5)
            ->setTop(0.2)
            ->setBottom(0.2)
            ->setHeader(0);
        
        switch ($writerType){
            case 'Pdf':
                $writer = IOFactory::createWriter($spreadsheet, 'Mpdf');
                $outFilename = $vtp->getPrintName($writerType, 'Накладная');
                $writer->save($outFilename);
                break;
            case 'Xls':
            case 'Xlsx':
                $writer = IOFactory::createWriter($spreadsheet, $writerType);
                $outFilename = $vtp->getPrintName($writerType, 'Накладная');
//                $writer->writeAllSheets();
                $writer->save($outFilename);
                break;
            default: 
                $outFilename = null;
        } 
        
        
        return $outFilename;
    }        
    
    /**
     * Счет на оплату
     * @param Order $order
     * @param string $writerType
     * @param bool $stamp
     * @param bool $code
     * @param bool $edo
     * @return string 
     */
    public function bill($order, $writerType = 'Pdf', $stamp = false, $code = true, $edo = false)
    {
        ini_set("pcre.backtrack_limit", "5000000");
        setlocale(LC_ALL, 'ru_RU', 'ru_RU.UTF-8', 'ru', 'russian');
//        echo strftime("%B %d, %Y", time()); exit;

        $folder_name = Order::PRINT_FOLDER;
        if (!is_dir($folder_name)){
            mkdir($folder_name);
        }        
        
        $numToStrFilter = new NumToStr();
        
        $inputFileType = 'Xls';
        $reader = IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load(Order::TEMPLATE_BILL);
        $spreadsheet->getProperties()
                ->setTitle($order->getDocPresent('Счет'))
                ;
        $sheet = $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('B3', $order->getCompany()->getLastActiveBankAccount()->getName())
                ->setCellValue('W3', $order->getCompany()->getLastActiveBankAccount()->getBik())
                ->setCellValue('W4', $order->getCompany()->getLastActiveBankAccount()->getRs())
                ->setCellValue('D6', $order->getCompany()->getInn())
                ->setCellValue('M6', $order->getCompany()->getKpp())
                ->setCellValue('W6', $order->getCompany()->getLastActiveBankAccount()->getKs())
                ->setCellValue('B7', $order->getCompany()->getName())
                ->setCellValue('B11', 'Счет на оплату №'.$order->getDocNo().' от '.date('d.m.Y', strtotime($order->getDocDate())))
                ->setCellValue('H13', $order->getCompany()->getName())
                ->setCellValue('H15', ($order->getLegal()) ? $order->getLegal()->getName():$order->getContact()->getName())
                ->setCellValue('H17', $order->getInvoiceInfo())
                ->setCellValue('AH22', number_format($order->getTotal(), 2, ',', ' '))
                ->setCellValue('AH23', 'Без НДС')
                ->setCellValue('AH24', number_format($order->getTotal(), 2, ',', ' '))
                
                ->setCellValue('B25', 'Всего наименований '.$order->getBids()->count().', на сумму '.number_format($order->getTotal(), 2, ',', ' ').' руб.')
                ->setCellValue('B26', $numToStrFilter->filter($order->getTotal()))               
                ;
        
        if (!$stamp){
            $sheet
                ->setCellValue('G30', $order->getCompany()->getHead())
                ->setCellValue('Y30', $order->getCompany()->getChiefAccount())
                ;
            $sheet->removeRow(31);
        }
        if ($stamp){            

            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Stamp');
            $drawing->setDescription('Stamp');
            $drawing->setPath(Order::STAMP_IMG, false); // put your path and image here
            $drawing->setCoordinates('B31');
            $drawing->getShadow()->setVisible(true);
            $drawing->setWorksheet($sheet);            

            $sheet->removeRow(30);
        }
        
        if (!$code){
            $sheet->setCellValue('D19', 'Код');
        }
        
        $bids = $this->entityManager->getRepository(Bid::class)
                ->findByOrder($order->getId());
        if ($bids){
            $i = 1;
            $row = 20;
            $bidsShipmentCount = ($order->getShipmentTotal()) ? count($bids)+1:count($bids);
            $srcRow = $row + $bidsShipmentCount - 1;
            if ($bidsShipmentCount > 1){
                $sheet->insertNewRowBefore($row, $bidsShipmentCount - 1);            
            }
            foreach ($bids as $bid){
                if ($bidsShipmentCount > 1){
                    $this->_copyRows($sheet, "A$srcRow:AH$srcRow", "A$row");
                } 
                $sheet->setCellValue("B$row", $i);       
                if ($code){
                    $sheet->setCellValue("D$row", $bid->getGood()->getCode());                
                } else {
                    $sheet->setCellValue("D$row", $bid->getGood()->getId());                                    
                }    
                $sheet->setCellValue("H$row", ($bid->getDisplayName()) ? $bid->getDisplayName():$bid->getGood()->getNameShort());                
                $sheet->setCellValue("Y$row", number_format($bid->getNum(), 0, ',', ' '));                
                $sheet->setCellValue("AD$row", number_format($bid->getPrice(), 2, ',', ' '));                              
                $sheet->setCellValue("AH$row", number_format($bid->getTotal(), 2, ',', ' '));                
                $i++;
                $row++;
            }
        }
        
        if ($order->getShipmentTotal()){
            $this->_copyRows($sheet, "A$srcRow:AH$srcRow", "A$row");
            $sheet->setCellValue("B$row", $i);       
            $sheet->setCellValue("D$row", '');                                    
            $sheet->setCellValue("H$row", 'Организация доставки груза');                
            $sheet->setCellValue("Y$row", '');                
            $sheet->setCellValue("AD$row", '');                              
            $sheet->setCellValue("AH$row", number_format($order->getShipmentTotal(), 2, ',', ' '));                            
        }
                
        switch ($writerType){
            case 'Pdf':
                $writer = IOFactory::createWriter($spreadsheet, 'Mpdf');
                $outFilename = $order->getPrintName($writerType, 'Счет');
                if ($edo){
                    $outFilename = $order->getEdoName('СЧОП', 'pdf');                    
                }
                $writer->save($outFilename);
                break;
            case 'Xls':
            case 'Xlsx':
                $writer = IOFactory::createWriter($spreadsheet, $writerType);
                $outFilename = $order->getPrintName($writerType, 'Счет');
//                $writer->writeAllSheets();
                $writer->save($outFilename);
                break;
            default: 
                $outFilename = null;
        } 
        
        
        return $outFilename;
    }    
    
    /**
     * Торг12
     * @param Order $order
     * @param string $writerType
     * @param bool $code
     * @return string 
     */
    public function torg12($order, $writerType = 'Pdf', $code = true)
    {
        ini_set("pcre.backtrack_limit", "5000000");
        setlocale(LC_ALL, 'ru_RU', 'ru_RU.UTF-8', 'ru', 'russian');
//        echo strftime("%B %d, %Y", time()); exit;

        $folder_name = Order::PRINT_FOLDER;
        if (!is_dir($folder_name)){
            mkdir($folder_name);
        }        
        
        $rubToStrFilter = new NumToStr();
        $numToStrFilter = new NumToStr(['format' => NumToStr::FORMAT_NUM]);
        
        $inputFileType = 'Xls';
        $reader = IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load(Order::TEMPLATE_TORG12);
        $spreadsheet->getProperties()
                ->setTitle($order->getDocPresent('Накладная'))
                ;
        
        $recipient = ($order->getRecipient()) ? $order->getRecipient():$order->getLegal();
        
        $sheet = $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('B3', $order->getCompany()->getCompanyBankAccountPresent($order->getOffice()))
                ->setCellValue('AL4', $order->getCompany()->getOkpo())
                ->setCellValue('D8', ($recipient) ? $recipient->getLegalBankAccountPresent():'')
                ->setCellValue('AL9', $order->getCompany()->getOkpo())
                ->setCellValue('D10', $order->getCompany()->getCompanyBankAccountPresent($order->getOffice()))
                ->setCellValue('D12', ($order->getLegal()) ? $order->getLegal()->getLegalBankAccountPresent():'')
//                ->setCellValue('D14', ($order->getContract()) ? $order->getContract()->getContractPresent():'')
                ->setCellValue('K17', $order->getDocNo())
                ->setCellValue('O17', date('d.m.Y', strtotime($order->getDocDate())))
                ->setCellValue('Z25', number_format($order->getBidTotal(), 2, ',', ' '))
                ->setCellValue('AK25', number_format($order->getBidTotal(), 2, ',', ' '))
                
                ->setCellValue('F27', $numToStrFilter->filter($order->getBids()->count()))
                ->setCellValue('B37', $rubToStrFilter->filter($order->getBidTotal()))                
                ->setCellValue('J39', $order->getCompany()->getHead())
                ->setCellValue('J41', $order->getCompany()->getChiefAccount())

                ->setCellValue('F46', '"'.date('d', strtotime($order->getDocDate())).'"')
                ->setCellValue('G46', date('m', strtotime($order->getDocDate())))
                ->setCellValue('I46', date('Y', strtotime($order->getDocDate())).' года')                
                ;
        
        
        $bids = $this->entityManager->getRepository(Bid::class)
                ->findByOrder($order->getId());
        if ($bids){
            $i = 1;
            $row = 23;
            $totalNum = $pageNum = $pageTotal = 0;
            $bidsCount = count($bids);
            $srcRow = $row + $bidsCount - 1;
            if ($bidsCount > 1){
                $sheet->insertNewRowBefore($row, $bidsCount - 1);            
            }
            foreach ($bids as $bid){
                if ($bidsCount > 1){
                    $this->_copyRows($sheet, "A$srcRow:AN$srcRow", "A$row");
                } 
                $sheet->setCellValue("B$row", $i);       
                $sheet->setCellValue("C$row", ($bid->getDisplayName()) ? $bid->getDisplayName():$bid->getGood()->getNameShort());                
                if ($code){
                    $sheet->setCellValue("G$row", $bid->getGood()->getCode());                
                } else {
                    $sheet->setCellValue("G$row", $bid->getGood()->getId());                                    
                }    
                $sheet->setCellValue("V$row", number_format($bid->getNum(), 0, ',', ' '));                
                $sheet->setCellValue("X$row", number_format($bid->getPrice(), 2, ',', ' '));                              
                $sheet->setCellValue("Z$row", number_format($bid->getTotal(), 2, ',', ' '));                
                $sheet->setCellValue("AK$row", number_format($bid->getTotal(), 2, ',', ' '));                
                $i++;
                $row++;
                $totalNum += $bid->getNum();
                $pageNum += $bid->getNum();
                $pageTotal += $bid->getTotal();
            }
            $sheet->setCellValue('V'.(24+$bidsCount-1), $pageNum);
            $sheet->setCellValue('V'.(25+$bidsCount-1), $totalNum);
            $sheet->setCellValue('Z'.(24+$bidsCount-1), number_format($pageTotal, 2, ',', ' '));
            $sheet->setCellValue('AK'.(24+$bidsCount-1), number_format($pageTotal, 2, ',', ' '));
        }
        
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageMargins()
            ->setLeft(0.5)
            ->setRight(0.5)
            ->setTop(0.2)
            ->setBottom(0.2)
            ->setHeader(0);
        
        switch ($writerType){
            case 'Pdf':
                $writer = IOFactory::createWriter($spreadsheet, 'Mpdf');
                $outFilename = $order->getPrintName($writerType, 'Накладная');
                $writer->save($outFilename);
                break;
            case 'Xls':
            case 'Xlsx':
                $writer = IOFactory::createWriter($spreadsheet, $writerType);
                $outFilename = $order->getPrintName($writerType, 'Накладная');
//                $writer->writeAllSheets();
                $writer->save($outFilename);
                break;
            default: 
                $outFilename = null;
        } 
        
        
        return $outFilename;
    }        
    
    /**
     * Акт на услуги доставки
     * @param Order $order
     * @param string $writerType
     * @return string 
     */
    public function act($order, $writerType = 'Pdf')
    {
        ini_set("pcre.backtrack_limit", "5000000");
        setlocale(LC_ALL, 'ru_RU', 'ru_RU.UTF-8', 'ru', 'russian');
//        echo strftime("%B %d, %Y", time()); exit;

        $folder_name = Order::PRINT_FOLDER;
        if (!is_dir($folder_name)){
            mkdir($folder_name);
        }        
        
        $numToStrFilter = new NumToStr();
        
        $inputFileType = 'Xls';
        $reader = IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load(Order::TEMPLATE_ACT);
        $spreadsheet->getProperties()
                ->setTitle($order->getDocPresent('Акт'))
                ;
        $sheet = $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('B3', $order->getDocPresent('Акт'))
                ->setCellValue('F5', $order->getCompany()->getName())
                ->setCellValue('F7', ($order->getLegal()) ? $order->getLegal()->getName():$order->getContact()->getName())
                ->setCellValue('Z11', number_format($order->getShipmentTotal(), 2, ',', ' '))
                ->setCellValue('AD11', number_format($order->getShipmentTotal(), 2, ',', ' '))
                ->setCellValue('AD13', number_format($order->getShipmentTotal(), 2, ',', ' '))
                
                ->setCellValue('B16', 'Всего оказано услуг 1, на сумму '.number_format($order->getShipmentTotal(), 2, ',', ' ').' руб.')
                ->setCellValue('B17', $numToStrFilter->filter($order->getShipmentTotal()))               
                ->setCellValue('B22', 'Генеральный директор '.$order->getCompany()->getName())
                ->setCellValue('B24', $order->getCompany()->getHead())
                ;
        
        switch ($writerType){
            case 'Pdf':
                $writer = IOFactory::createWriter($spreadsheet, 'Mpdf');
                $outFilename = $order->getPrintName($writerType, 'Акт');
                $writer->save($outFilename);
                break;
            case 'Xls':
            case 'Xlsx':
                $writer = IOFactory::createWriter($spreadsheet, $writerType);
                $outFilename = $order->getPrintName($writerType, 'Акт');
//                $writer->writeAllSheets();
                $writer->save($outFilename);
                break;
            default: 
                $outFilename = null;
        } 
        
        
        return $outFilename;
    }    

    /**
     * Предварительный заказ
     * @param Order $order
     * @param string $writerType
     * @param bool $code
     * @param bool $public
     * @return string 
     */
    public function preorder($order, $writerType = 'Pdf', $code = false, $public = false, $reportName = null)
    {
        ini_set("pcre.backtrack_limit", "5000000");
        setlocale(LC_ALL, 'ru_RU', 'ru_RU.UTF-8', 'ru', 'russian');
//        echo strftime("%B %d, %Y", time()); exit;
        
        if (empty($reportName)){
            $reportName = 'Предварительный заказ';
        }

        $folder_name = Order::PRINT_FOLDER;
        if (!is_dir($folder_name)){
            mkdir($folder_name);
        }        
        
        $numToStrFilter = new NumToStr();
        
        $inputFileType = 'Xls';
        $reader = IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load(Order::TEMPLATE_PREORDER);
        $spreadsheet->getProperties()
                ->setTitle($order->getDocPresent($reportName))
                ;
        $sheet = $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('B3', $order->getDocPresent($reportName))
                ->setCellValue('B5', $order->getCompany()->getName())
                ->setCellValue('B7', $order->getOffice()->getLegalContactSmsAddress())
                ->setCellValue('AD13', number_format($order->getTotal(), 2, ',', ' '))
                ->setCellValue('B17', $order->getOffice()->getLegalContactPhones())
                
                ;
        $bids = $this->entityManager->getRepository(Bid::class)
                ->findByOrder($order->getId());
        if ($bids){
            $i = 1;
            $row = 11;
            $bidsShipmentCount = ($order->getShipmentTotal()) ? count($bids)+1:count($bids);
            $srcRow = $row + $bidsShipmentCount - 1;
            if ($bidsShipmentCount > 1){
                $sheet->insertNewRowBefore($row, $bidsShipmentCount - 1);            
            }
            foreach ($bids as $bid){
                if ($bidsShipmentCount > 1){
                    $this->_copyRows($sheet, "A$srcRow:AD$srcRow", "A$row");
                } 
                $sheet->setCellValue("B$row", $i);       
                if ($code){
                    $sheet->setCellValue("D$row", $bid->getDisplayNameProducerCode());                
                } else {
                    $sheet->setCellValue("D$row", $bid->getDisplayNameProducer());                
                }    
                $sheet->setCellValue("U$row", number_format($bid->getNum(), 0, ',', ' '));                
                $sheet->setCellValue("Z$row", number_format($bid->getPrice(), 2, ',', ' '));                              
                $sheet->setCellValue("AD$row", number_format($bid->getTotal(), 2, ',', ' '));                

                $i++;
                $row++;
            }
        }

        if ($order->getShipmentTotal()){
            $this->_copyRows($sheet, "A$srcRow:AD$srcRow", "A$row");
            $sheet->setCellValue("B$row", $i);       
            $sheet->setCellValue("D$row", 'Организация доставки груза');                
            $sheet->setCellValue("U$row", '');                
            $sheet->setCellValue("Z$row", '');                              
            $sheet->setCellValue("AD$row", number_format($order->getShipmentTotal(), 2, ',', ' '));                            
        }
        
        switch ($writerType){
            case 'Pdf':
                $writer = IOFactory::createWriter($spreadsheet, 'Mpdf');
                $outFilename = $order->getPrintName($writerType, $reportName);
                $writer->save($outFilename);
                break;
            case 'Xls':
            case 'Xlsx':
                $writer = IOFactory::createWriter($spreadsheet, $writerType);
                $outFilename = $order->getPrintName($writerType, $reportName);
//                $writer->writeAllSheets();
                $writer->save($outFilename);
                break;
            default: 
                $outFilename = null;
        } 
        
        if ($public && $outFilename){
            $publicFilename = realpath(self::PUBLIC_DOC_FOLDER).'/'. basename($outFilename);
//            var_dump(realpath($outFilename));
//            var_dump($publicFilename);
            if (copy(realpath($outFilename), $publicFilename)){
                return basename($outFilename);
            }
            return false;
        }
        
        return $outFilename;
    }    
    
    /**
     * Коммерческое предложение
     * @param Order $order
     * @param string $writerType
     * @param bool $stamp
     * @param bool $code
     * @return string 
     */
    public function offer($order, $writerType = 'Pdf', $stamp = true, $code = true)
    {
        ini_set('memory_limit', '512M');
        ini_set("pcre.backtrack_limit", "5000000");
        setlocale(LC_ALL, 'ru_RU', 'ru_RU.UTF-8', 'ru', 'russian');
//        echo strftime("%B %d, %Y", time()); exit;

        $folder_name = Order::PRINT_FOLDER;
        if (!is_dir($folder_name)){
            mkdir($folder_name);
        }        
        
        $numToStrFilter = new NumToStr();
        
        $inputFileType = 'Xls';
        $reader = IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load(Order::TEMPLATE_OFFER);
        $spreadsheet->getProperties()
                ->setTitle($order->getDocPresent('Коммерческое предложение'))
                ;
        $sheet = $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('B1', 'Коммерческое предложение №'.$order->getDocNo().' от '.date('d.m.Y', strtotime($order->getDocDate())))
                ->setCellValue('H3', date('d.m.Y', strtotime($order->getDocDate())))
                ->setCellValue('H5', $order->getCompany()->getName())
                ->setCellValue('H7', ($order->getLegal()) ? $order->getLegal()->getName():$order->getContact()->getName())
                ->setCellValue('H9', $order->getInvoiceInfo())
                ->setCellValue('AH14', number_format($order->getTotal(), 2, ',', ' '))
                ->setCellValue('AH15', 'Без НДС')
                ->setCellValue('AH16', number_format($order->getTotal(), 2, ',', ' '))
                
                ->setCellValue('B17', 'Всего наименований '.$order->getBids()->count().', на сумму '.number_format($order->getTotal(), 2, ',', ' ').' руб.')
                ;
        
        if (!$stamp){
            $sheet
                ->setCellValue('G21', $order->getCompany()->getHead())
                ->setCellValue('Y21', $order->getCompany()->getChiefAccount())
                ;
            $sheet->removeRow(22);
        }
        if ($stamp){            

            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Stamp');
            $drawing->setDescription('Stamp');
            $drawing->setPath(Order::STAMP_IMG, false); // put your path and image here
            $drawing->setCoordinates('B22');
            $drawing->getShadow()->setVisible(true);
            $drawing->setWorksheet($sheet);            

            $sheet->removeRow(21);
        }
        
        if (!$code){
            $sheet->setCellValue('D11', 'Код');
        }
        
        $bids = $this->entityManager->getRepository(Bid::class)
                ->findByOrder($order->getId());
        if ($bids){
            $i = 1;
            $row = 12;
            $bidsShipmentCount = ($order->getShipmentTotal()) ? count($bids)+1:count($bids);
            $srcRow = $row + $bidsShipmentCount - 1;
            if ($bidsShipmentCount > 1){
                $sheet->insertNewRowBefore($row, $bidsShipmentCount - 1);            
            }
            foreach ($bids as $bid){
                if ($bidsShipmentCount > 1){
                    $this->_copyRows($sheet, "A$srcRow:AH$srcRow", "A$row");
                } 
                $sheet->setCellValue("B$row", $i);       
                if ($code){
                    $sheet->setCellValue("D$row", $bid->getGood()->getCode());                
                } else {
                    $sheet->setCellValue("D$row", $bid->getGood()->getId());                                    
                }    
                $sheet->setCellValue("H$row", $bid->getDisplayNameProducer());                
                $sheet->setCellValue("Y$row", number_format($bid->getNum(), 0, ',', ' '));                
                $sheet->setCellValue("AD$row", number_format($bid->getPrice(), 2, ',', ' '));                              
                $sheet->setCellValue("AH$row", number_format($bid->getTotal(), 2, ',', ' '));                
                $i++;
                $row++;
            }
        }
        
        if ($order->getShipmentTotal()){
            $this->_copyRows($sheet, "A$srcRow:AH$srcRow", "A$row");
            $sheet->setCellValue("B$row", $i);       
            $sheet->setCellValue("D$row", '');                                    
            $sheet->setCellValue("H$row", 'Организация доставки груза');                
            $sheet->setCellValue("Y$row", '');                
            $sheet->setCellValue("AD$row", '');                              
            $sheet->setCellValue("AH$row", number_format($order->getShipmentTotal(), 2, ',', ' '));                            
        }
                
        switch ($writerType){
            case 'Pdf':
                $writer = IOFactory::createWriter($spreadsheet, 'Mpdf');
                $outFilename = $order->getPrintName($writerType, 'Коммерческое предложение');
                $writer->save($outFilename);
                break;
            case 'Xls':
            case 'Xlsx':
                $writer = IOFactory::createWriter($spreadsheet, $writerType);
                $outFilename = $order->getPrintName($writerType, 'Коммерческое предложение');
//                $writer->writeAllSheets();
                $writer->save($outFilename);
                break;
            default: 
                $outFilename = null;
        } 
        
        
        return $outFilename;
    }    
    
    /**
     * Получить номер Апл
     * @param Mutual $mutual
     */
    private function getAplDocNum($mutual)
    {        
        switch($mutual->getDocType()){
            case Movement::DOC_ORDER:
                $order = $this->entityManager->getRepository(Order::class)
                    ->find($mutual->getDocId());
                if ($order){
                    return $order->getAplId();
                }    
            default:
                return $mutual->getDocId();
        }

        return $mutual->getDocId();
    }
    
    /**
     * Акт сверки
     * @param date $dateStart
     * @param date $dateEnd
     * @param Legal $company
     * @param Legal $legal
     * @param Contract $contract
     * @param string $writerType
     * @param bool $stamp
     * @param bool $edo
     * @return string 
     */
    public function revise($dateStart, $dateEnd, $company, $legal, $contract = null, $writerType = 'Pdf', $stamp = false, $edo = true)
    {
        ini_set('memory_limit', '512M');
        ini_set("pcre.backtrack_limit", "5000000");
        setlocale(LC_ALL, 'ru_RU', 'ru_RU.UTF-8', 'ru', 'russian');
//        echo strftime("%B %d, %Y", time()); exit;

        $folder_name = Order::PRINT_FOLDER;
        if (!is_dir($folder_name)){
            mkdir($folder_name);
        }        
        
        $numToStrFilter = new NumToStr();
        
        $inputFileType = 'Xls';
        $reader = IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load(Order::TEMPLATE_REVISE);
        $spreadsheet->getProperties()
                ->setTitle('Акт сверки')
                ;
        $sheet = $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('B3', "взатимных расчетов за период ".
                        date('d.m.Y', strtotime($dateStart))." - ".date('d.m.Y', strtotime($dateEnd)).
                        " между ".$company->getName()." и ".$legal->getName())
                ->setCellValue('B5', "Мы, нижеподписавшиеся, Генеральный директор ".
                        $company->getName()." ".$company->getHead().
                        ", с одной стороны, и ____________________________ ".
                        $legal->getName()." ______________________________,".
                        "с другой стороны, составили настоящий акт сверки в том,".
                        "что состояние взаимных расчетов по данным учета следующее:")
                ->setCellValue('B7', "По данным ".$company->getName().", руб.")
                ->setCellValue('J7', "По данным ".$legal->getName().", руб.")                
                ->setCellValue('B14', "По данным ".$company->getName())
                ->setCellValue('J14', "По данным ".$legal->getName())                
                ->setCellValue('B17', "От ".$company->getName())
                ->setCellValue('J17', "От ".$legal->getName())                
                ->setCellValue('D21', "(".$company->getHead().")")                
                ;
                
        $params = [
            'sort' => 'dateOper', 'order' => 'asc', 'status' => Mutual::STATUS_ACTIVE,
            'startDate' => $dateStart, 'endDate' => $dateEnd,
            'companyId' => $company->getId(), 'legalId' => $legal->getId(),
        ];
        
        if ($contract){
            $params['contractId'] = $contract->getId();
        }
        
        $startTotal = 0;
//        var_dump(strtotime($dateStart.' -1 day')); exit;
        $startBalance = $this->entityManager->getRepository(Mutual::class)
                ->mutualBalance([
                    'companyId' => $company->getId(), 
                    'legalId' => $legal->getId(), 
                    'contractId' => ($contract) ? $contract->getId():null, 
                    'endDate' => date('Y-m-d 23:59:59', strtotime($dateStart.' -1 day')),
                    'endBalance' => true,
                ])->getOneOrNullResult();
        
        if (!empty($startBalance['total'])){
            $startTotal = $startBalance['total'];
            if ($startTotal>0){
                $sheet->setCellValue("E9", number_format(abs($startTotal), 2, ',', ' ')); 
            }    
            if ($startTotal<0){
                $sheet->setCellValue("G9", number_format(abs($startTotal), 2, ',', ' ')); 
            }    
        }
                
        $query = $this->entityManager->getRepository(Mutual::class)
                        ->mutuals($params);
        
        $result = $query->getResult();
        
        $row = 10;
        
        $sheet->setCellValue("B$row", "");                
        $sheet->setCellValue("C$row", "");                
        $sheet->setCellValue("E$row", "");                              
        $sheet->setCellValue("G$row", "");    
        
        if (count($result) - 1 > 0){
            $sheet->insertNewRowBefore($row+1, count($result) - 1);            
        }    
        
        $dTotal = $cTotal = 0;
        foreach ($result as $data){
            
            $sheet->mergeCells("C$row:D$row");
            $sheet->mergeCells("E$row:F$row");
            $sheet->mergeCells("G$row:H$row");
            
            $sheet->setCellValue("B$row", date('d.m.Y', strtotime($data->getDateOper())));                
            $sheet->setCellValue("C$row", Movement::getReviseDocList()[$data->getDocType()]." №".$this->getAplDocNum($data).' от '.date('d.m.Y', strtotime($data->getDateOper())));                
            $sheet->setCellValue("E$row", "");                              
            $sheet->setCellValue("G$row", "");
            switch($data->getDocType()){
                case Movement::DOC_ORDER:
                case Movement::DOC_MSR:
                    $sheet->setCellValue("E$row", number_format(abs($data->getAmount()), 2, ',', ' '));
                    $dTotal += abs($data->getAmount());
                    break;
                case Movement::DOC_VT:
                    $sheet->setCellValue("E$row", number_format($data->getAmount(), 2, ',', ' '));
                    $dTotal += $data->getAmount();
                    break;
                case Movement::DOC_REVISE:
                    $sheet->setCellValue("E$row", number_format($data->getAmount(), 2, ',', ' '));
                    $dTotal += $data->getAmount();
                    break;
                default:    
                    $sheet->setCellValue("G$row", number_format(abs($data->getAmount()), 2, ',', ' '));
                    $cTotal += abs($data->getAmount());
                    break;
            }
            $row++;
        }

        $sheet->setCellValue("E$row", number_format($dTotal, 2, ',', ' '));
        $sheet->setCellValue("G$row", number_format($cTotal, 2, ',', ' '));
        
        $endTotal = round($startTotal + $dTotal - $cTotal, 2);
        
        $row++;
        $resumeRow = $row+3;
        $sheet->setCellValue("B$resumeRow", "На ".date('d.m.Y', strtotime($dateEnd))." задолженность отсутствует.");
        
        if ($endTotal > 0){
            $sheet->setCellValue("E$row", number_format(abs($endTotal), 2, ',', ' '));
            $sheet->setCellValue("B$resumeRow", "На ".date('d.m.Y', strtotime($dateEnd))." задолженность в пользу ".
                    $company->getName()." ".number_format(abs($endTotal), 2, ',', ' ').
                    "(".$numToStrFilter->filter(abs($endTotal)).")");
        }
        if ($endTotal < 0){
            $sheet->setCellValue("G$row", number_format(abs($endTotal), 2, ',', ' '));
            $sheet->setCellValue("B$resumeRow", "На ".date('d.m.Y', strtotime($dateEnd))." задолженность в пользу ".
                    $legal->getName()." ".number_format(abs($endTotal), 2, ',', ' ').
                    "(".$numToStrFilter->filter(abs($endTotal)).")");
        }    
        
        switch ($writerType){
            case 'Pdf':
                $writer = IOFactory::createWriter($spreadsheet, 'Mpdf');
                $outFilename = Order::getReviseEdoName($company->getInn(), $company->getKpp(), date('dmY', strtotime($dateEnd)), 'АКТС', $writerType);
                $writer->save($outFilename);
                break;
            case 'Xls':
            case 'Xlsx':
                $writer = IOFactory::createWriter($spreadsheet, $writerType);
                $outFilename = Order::getReviseEdoName($company->getInn(), $company->getKpp(), date('dmY', strtotime($dateEnd)), 'АКТС', $writerType);
                $writer->save($outFilename);
                break;
            default: 
                $outFilename = null;
        } 
        
        
        return $outFilename;
    }    
    
    /**
     * Получить QrCode для оплаты по СБП
     * @param Order $order
     * @return string
     */
    private function getQrCode($order)
    {
        if ($order->getAplId()){
            $qrCode = $this->sbpManager->registerQrCode([
                'amount' => $order->getTotal(),
                'orderAplId' => $order->getAplId(),
            ]);
            if ($qrCode instanceof QrCode){
                return $qrCode->getImg();
            }
        }    
        
        return;
    }
    
    /**
     * Товарный чек
     * @param Order $order
     * @param string $writerType
     * @return string 
     */
    public function check($order, $writerType = 'Pdf')
    {
        ini_set("pcre.backtrack_limit", "5000000");
        setlocale(LC_ALL, 'ru_RU', 'ru_RU.UTF-8', 'ru', 'russian');
//        echo strftime("%B %d, %Y", time()); exit;

        $folder_name = Order::PRINT_FOLDER;
        if (!is_dir($folder_name)){
            mkdir($folder_name);
        }        
        
        $content = \file_get_contents(Order::TEMPLATE_CHECK);
        
        $out = []; $outShip = [];
        preg_match('|<tabparts>(.*)</tabparts>|Uis', $content, $out);
        $tabTmpl = $out[1];

        preg_match('|<shipparts>(.*)</shipparts>|Uis', $content, $outShip);
        $shipTmpl = $outShip[1];
        
        $replace = [
            '[ПредставлениеДокумента]' => $order->getDocPresent('Товарный чек'),
            '[ТелефонОфиса]' => $order->getOffice()->getLegalContactPhones(),
            '[Продавец]' => $order->getCompany()->getName(),
            '[ИНН]' => $order->getCompany()->getInn(),
            '[ОГРН]' => $order->getCompany()->getOgrn(),
            '[ФактическийАдрес]' => $order->getOffice()->getLegalContactSmsAddress(),
            '[Покупатель]' => ($order->getLegal()) ? $order->getLegal()->getName():$order->getContact()->getName(),
            '[Телефон]' => $order->_getContactPhone(),
            '[АдресДоставки]' => $order->getAddress(),
            '[ИтогоКоличество]' => $order->getBids()->count(),
            '[Всего]' => number_format($order->getTotal(), 2, ',', ' '),
            '[Предоплата]' => 0,
            '[КОплате]' => number_format($order->getTotal(), 2, ',', ' '),
            '[КомментарийКЗаказу]' => $order->getInfoShipping(),
            '[qrCode]' => $this->getQrCode($order),
        ];
        
        $tabData = [];
        $bids = $this->entityManager->getRepository(Bid::class)
                ->findByOrder($order->getId());
        if ($bids){
            $i = 1;
            foreach ($bids as $bid){                
                $tabReplace = [
                    '[НомерСтроки]' => $i,
                    '[Артикул]' => $bid->getGood()->getCode(),
                    '[Производитель]' => $bid->getGood()->getProducer()->getName(),
                    '[Наименование]' => $bid->getDisplayName(),
                    '[Количество]' => $bid->getNum(),
                    '[ЕИ]' => 'шт.',
                    '[Цена]' => number_format($bid->getPrice(), 2, ',', ' '),
                    '[Сумма]' => number_format($bid->getTotal(), 2, ',', ' '),
                ];                
                $i++;
                $tabData[] = str_replace(array_keys($tabReplace), array_values($tabReplace), $tabTmpl);
            }
        }
        if ($order->getShipping()->getRate() != Shipping::RATE_PICKUP){
            $tabReplace = [
                '[НомерСтроки]' => $i,
                '[Артикул]' => '',
                '[Производитель]' => '',
                '[Наименование]' => 'Организация доставки груза',
                '[Количество]' => '',
                '[ЕИ]' => '',
                '[Цена]' => '',
                '[Сумма]' => number_format($order->getShipmentTotal(), 2, ',', ' '),
            ];                
            $tabData[] = str_replace(array_keys($tabReplace), array_values($tabReplace), $tabTmpl);
        } else {
            $content = str_replace($shipTmpl, "", $content);
        }
                
        $content = str_replace($tabTmpl, implode("",$tabData), $content);
        $result = str_replace(array_keys($replace), array_values($replace), $content);
        
        switch ($writerType){
            case 'Pdf':
                $mpdf = new Mpdf([
                    'margin_top' => 10,
                    'margin_bottom' => 10,
                    'margin_left' => 10,
                    'margin_right' => 10,                    
                ]);
                $mpdf->WriteHTML($result);
                $outFilename = $order->getPrintName($writerType);
                $mpdf->Output($outFilename,'F');
                break;
            case 'Html':
//                $outFilename = $order->getPrintName($writerType);
//                file_put_contents($outFilename, $result);
                return $result;
                break;
            default: 
                $outFilename = null;
        }         
        
        return $outFilename;
    }    
}
