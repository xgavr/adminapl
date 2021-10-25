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

use Bukashk0zzz\YmlGenerator\Model\Offer\OfferSimple;
use Bukashk0zzz\YmlGenerator\Model\Category;
use Bukashk0zzz\YmlGenerator\Model\Currency;
use Bukashk0zzz\YmlGenerator\Model\Delivery;
use Bukashk0zzz\YmlGenerator\Model\ShopInfo;
use Bukashk0zzz\YmlGenerator\Settings;
use Bukashk0zzz\YmlGenerator\Generator;

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
                if (!$rawprice->getComment() && $rawprice->getRealRest() && $rawprice->getCode()){
                    $good = $rawprice->getCode()->getGood();
                    if ($good){
                        $opts = $good->getOpts();
                        $sheet->setCellValue("A$k", $good->getCode());
                        $sheet->setCellValue("B$k", $good->getProducer()->getName());
                        $sheet->setCellValue("C$k", $good->getName());
                        $sheet->setCellValue("D$k", $rawprice->getRealRest());
//                        $sheet->setCellValue("E$k", $opts[5]);
                        $sheet->setCellValue("E$k", $rawprice->getRealPrice());
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
    
    /**
     * Выгрузка в формаt YML
     * 
     * @param array $params
     */
    public function toYml($params)
    {
        $filename = 'market.yml';
        $path = self::MARKET_FOLDER.'/'.$filename;        
        
//        $file = tempnam(sys_get_temp_dir(), 'YMLGenerator');
        $settings = (new Settings())
            ->setOutputFile($path)
            ->setEncoding('UTF-8')
        ;

        // Creating ShopInfo object (https://yandex.ru/support/webmaster/goods-prices/technical-requirements.xml#shop)
        $shopInfo = (new ShopInfo())
            ->setName('APL')
            ->setCompany('АПЛ Сервис')
            ->setUrl('https://autopartslist.ru/')
        ;

        // Creating currencies array (https://yandex.ru/support/webmaster/goods-prices/technical-requirements.xml#currencies)
        $currencies = [];
        $currencies[] = (new Currency())
            ->setId('RUR')
            ->setRate(1)
        ;

        // Creating categories array (https://yandex.ru/support/webmaster/goods-prices/technical-requirements.xml#categories)
        $categories = [];
        $categories[] = (new Category())
            ->setId(1)
            ->setName($this->faker->name)
        ;        
        
        // Creating offers array (https://yandex.ru/support/webmaster/goods-prices/technical-requirements.xml#offers)
        $offers = [];
        $offers[] = (new OfferSimple())
            ->setId(12346)
            ->setAvailable(true)
            ->setUrl('http://www.best.seller.com/product_page.php?pid=12348')
            ->setPrice($this->faker->numberBetween(1, 9999))
            ->setCurrencyId('USD')
            ->setCategoryId(1)
            ->setDelivery(false)
            ->setName('Best product ever')
        ;

        // Optional creating deliveries array (https://yandex.ru/support/partnermarket/elements/delivery-options.xml)
        $deliveries = [];
        $deliveries[] = (new Delivery())
            ->setCost(2)
            ->setDays(1)
            ->setOrderBefore(14)
        ;

        (new Generator($settings))->generate(
            $shopInfo,
            $currencies,
            $categories,
            $offers,
            $deliveries
        );        
    }    
}
