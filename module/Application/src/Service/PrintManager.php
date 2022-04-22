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


/**
 * Description of PrintManager
 *
 * @author Daddy
 */
class PrintManager {
    
    const TEMPLATE_FOLDER       = './data/template'; // папка с шаблонами
    const TEMPLATE_TORG2        = './data/template/torg2/torg-2.xls'; 

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
        $torg2_folder_name = self::TORG2_FOLDER;
        if (!is_dir($torg2_folder_name)){
            mkdir($torg2_folder_name);
        }        
    }        
        
  // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
        
        $this->_addTemplateFolder();
    }
    
    /**
     * Представление юрлица
     * @param Legal $legal
     * @param array $options
     * @return string
     */
    private function _legalPreset($legal, $options = null)
    {
        $result = '';
        $result .= $legal->getName();
        $result .= ', ';
        $result .= "ИНН {$legal->getInn()}";
        $result .= ', ';
        $result .= "КПП {$legal->getKpp()}";
        $result .= ', ';
        $result .= "адрес: {$legal->getAddress()}";
//        $result .= ', ';
//        $result .= "тел.: {$legal->get()}";
        
        return trim($result);
    }
    
    /**
     * Торг2
     * @param Vtp $vtp
     */
    public function torg2($vtp)
    {
        $inputFileType = 'Xls';
        $reader = IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load(self::TEMPLATE_TORG2);
        
        $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A7', $this->_legalPreset($vtp->getPtu()->getContract()->getCompany()))
                ;

        $writer = IOFactory::createWriter($spreadsheet, "Xls");
        $newFilename = self::TORG2_FOLDER.'/vtp_'.$vtp->getId().'.xls';
        $writer->save($newFilename);
    }
}
