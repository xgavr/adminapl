<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Laminas\ServiceManager\ServiceManager;
use Application\Entity\Raw;
use Application\Entity\Rawprice;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Description of MarketService
 *
 * @author Daddy
 */
class MarketManager
{
    const MARKET_FOLDER       = './data/market'; // папка с прайсами
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /*
     * @var \Admin\Service\FtpManager
     */
    private $ftpManager;    
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $ftpManager)
    {
        $this->entityManager = $entityManager;
        $this->ftpManager = $ftpManager;
    }
    
    /**
     * Выгрузка в zzap только апл
     * 
     * @param array $params
     */
    public function aplToZzap($params = null)
    {
        $aplSupplierId = 7;
        $currentRaw = $this->entityManager->getRepository(Raw::class)
                ->findOneBy(['supplier' => $aplSupplierId, 'status' => Raw::STATUS_PARSED], ['id' => 'DESC']);
        
        if ($currentRaw){
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $rawprices = $this->entityManager->getRepository(Rawprice::class)
                    ->findBy(['raw' => $currentRaw, 'status' => Rawprice::STATUS_PARSED]);
            
            $sheet->setCellValue("A1", 'Артикул');
            $sheet->setCellValue("B1", 'Производитель');
            $sheet->setCellValue("C1", 'Наименование');
            $sheet->setCellValue("D1", 'Наличие');
            $sheet->setCellValue("E1", 'Цена');

            $k = 2;
            foreach ($rawprices as $rawprice){
                if (!$rawprice->getComment() && $rawprice->getRealRest()){
                    $good = $rawprice->getGood();
                    if ($good){
                        $opts = $good->getOpts();
                        $sheet->setCellValue("A$k", $good->getCode());
                        $sheet->setCellValue("B$k", $good->getProducer()->getName());
                        $sheet->setCellValue("C$k", $good->getName());
                        $sheet->setCellValue("D$k", $rawprice->getRealRest());
                        $sheet->setCellValue("E$k", $opts[5]);
                        $k++;
                    }    
                }    
            }
            
            $filename = 'apl2zzap.xlsx';
            $path = self::MARKET_FOLDER.'/'.$filename;
            
            $writer = new Xlsx($spreadsheet);
            $writer->save($path);
            
            $this->ftpManager->putMarketPriceToApl(['source_file' => $path, 'dest_file' => $filename]);            
        }
        
        return;
    }
    
}
