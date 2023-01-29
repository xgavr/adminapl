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
use Application\Entity\Order;
use Application\Entity\Bid;
use Application\Filter\NumToStr;
use Application\Entity\Shipping;
use Laminas\Filter\Compress;


/**
 * Description of EdoManager
 *
 * @author Daddy
 */
class EdoManager {
    
    const PUBLIC_DOC_FOLDER       = './public/doc'; // папка с документами
    
    const VERSION = 'APL0';
     

    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
  // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;        
    }
    
    
    /**
     * Счет на оплату
     * @param Order $order
     * @param string $writerType
     * @param bool $stamp
     * @param bool $code
     * @return string 
     */
    public function bill($order, $code = true)
    {
        ini_set("pcre.backtrack_limit", "5000000");
        setlocale(LC_ALL, 'ru_RU', 'ru_RU.UTF-8', 'ru', 'russian');
//        echo strftime("%B %d, %Y", time()); exit;

        $folder_name = Order::PRINT_FOLDER;
        if (!is_dir($folder_name)){
            mkdir($folder_name);
        }        
        
        $outFile = $order->getEdoName('СЧОП');
        $outFileZip = $order->getEdoName('СЧОП', 'zip');
//        var_dump($outFile); exit;
        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'windows-1251');
        
        $xml->startElement('Файл');
            $xml->writeAttribute('ИдФайл', $order->getEdoPresent('СЧОП'));
            $xml->writeAttribute('ВерсПрог', self::VERSION);
            $xml->writeAttribute('ВерсФорм', '5.1');
        
            $xml->startElement('СвУчДокОбор');
                $xml->writeAttribute('ИдОтпр', $order->getCompany()->getEdoAddress());
                if ($order->getLegal()){
                    $xml->writeAttribute('ИдПол', $order->getLegal()->getEdoAddress());
                }    
                $xml->startElement('СвОЭДОтпр');
                    if ($order->getCompany()->getEdoOperator()){
                        $xml->writeAttribute('НаимОрг', $order->getCompany()->getEdoOperator()->getName());
                        $xml->writeAttribute('ИННЮЛ', $order->getCompany()->getEdoOperator()->getInn());
                        $xml->writeAttribute('ИдЭДО', $order->getCompany()->getEdoOperator()->getCode());
                    }    
                $xml->endElement(); //СвОЭДОтпр
            $xml->endElement(); //СвУчДокОбор

            $xml->startElement('Документ');
        
                $xml->startElement('СвСчет');

                    $xml->startElement('СвПрод');
                        $xml->startElement('ИдСв');
                            $xml->startElement('СвЮЛ');
                                $xml->writeAttribute('НаимОрг', $order->getCompany()->getName());
                                $xml->writeAttribute('ИННЮЛ', $order->getCompany()->getInn());
                                $xml->writeAttribute('КПП', $order->getCompany()->getKpp());
                            $xml->endElement(); //СвЮЛ
                        $xml->endElement(); //ИдСв
                        $xml->startElement('Адрес');
                            $xml->startElement('АдрИнф');
                                $xml->writeAttribute('АдрТекст', $order->getCompany()->getAddress());
                            $xml->endElement(); //АдрИнф
                        $xml->endElement(); //Адрес
                        $xml->startElement('БанкРекв');
                            $xml->writeAttribute('НомерСчета', $order->getCompany()->getLastActiveBankAccount()->getRs());
                            $xml->startElement('СвБанк');
                                $xml->writeAttribute('НаимБанк', $order->getCompany()->getLastActiveBankAccount()->getName());
                                $xml->writeAttribute('БИК', $order->getCompany()->getLastActiveBankAccount()->getBik());
                                $xml->writeAttribute('КорСчет', $order->getCompany()->getLastActiveBankAccount()->getKs());
                            $xml->endElement(); //СвБанк
                        $xml->endElement(); //БанкРекв
                    $xml->endElement(); //СвПрод

                    if ($order->getLegal()){
                        $xml->startElement('СвПокуп');
                            $xml->startElement('ИдСв');
                                $xml->startElement('СвЮЛ');
                                    $xml->writeAttribute('НаимОрг', $order->getLegal()->getName());
                                    $xml->writeAttribute('ИННЮЛ', $order->getLegal()->getInn());
                                    $xml->writeAttribute('КПП', $order->getLegal()->getKpp());
                                $xml->endElement(); //СвЮЛ
                            $xml->endElement(); //ИдСв
                            $xml->startElement('Адрес');
                                $xml->startElement('АдрИнф');
                                    $xml->writeAttribute('АдрТекст', $order->getLegal()->getAddress());
                                $xml->endElement(); //АдрИнф
                            $xml->endElement(); //Адрес
                            $xml->startElement('Контакт');
                                $xml->writeAttribute('Тлф', $order->getContact()->getPhone());
                            $xml->endElement(); //Контакт
                            $xml->startElement('БанкРекв');
                                $xml->writeAttribute('НомерСчета', $order->getCompany()->getLastActiveBankAccount()->getRs());
                                $xml->startElement('СвБанк');
                                    $xml->writeAttribute('НаимБанк', $order->getLegal()->getLastActiveBankAccount()->getName());
                                    $xml->writeAttribute('БИК', $order->getLegal()->getLastActiveBankAccount()->getBik());
                                    $xml->writeAttribute('КрСчет', $order->getLegal()->getLastActiveBankAccount()->getKs());
                                $xml->endElement(); //СвБанк
                            $xml->endElement(); //БанкРекв
                        $xml->endElement(); //СвПокуп
                    }    

                    $xml->startElement('ИнфПол');
                        $xml->writeAttribute('ТекстИнф', '<Данные>'
                                . '<Реквизит Имя="НазначениеПлатежа" Значение="'.$order->getDocPresent('Оплата по счету').'"/>'
                                . '<Реквизит Имя="ИмяГлавБух" Значение="/'.$order->getCompany()->getChiefAccount().'/"/>'
                                . '<Реквизит Имя="ИмяРуковод" Значение="/'.$order->getCompany()->getHead().'/"/>'
                                . '</Данные>"');
                    $xml->endElement(); //ИнфПол

                $xml->endElement(); //СвСчФакт
        
                $xml->startElement('ТаблСчет');

                    $bids = $this->entityManager->getRepository(Bid::class)
                            ->findByOrder($order->getId());
                    if ($bids){
                        $i = 1;
                        foreach ($bids as $bid){

                            $xml->startElement('СведТов');
                                $xml->writeAttribute('НомСтр', $i);
                                $xml->writeAttribute('НаимТов', ($bid->getDisplayName()) ? $bid->getDisplayName():$bid->getGood()->getNameShort());
                                $xml->writeAttribute('ОКЕИ_Тов', '796');
                                $xml->writeAttribute('КолТов', $bid->getNum());
                                $xml->writeAttribute('ЦенаТов', $bid->getPrice());
                                $xml->writeAttribute('СтТовБезНДС', $bid->getPrice());
                                $xml->writeAttribute('СтТовУчНал', $bid->getTotal());
                                $xml->startElement('Акциз');
                                    $xml->writeAttribute('СумАкциз', 'Без акциза');
                                $xml->endElement(); //Акциз
                                $xml->startElement('НалСт');
                                    $xml->writeAttribute('НалСтВел', 0);
                                    $xml->writeAttribute('НалСтТип', 'процент');
                                $xml->endElement(); //НалСт
                                $xml->startElement('СумНал');
                                    $xml->writeAttribute('СумНДС', 'Без НДС');
                                $xml->endElement(); //СумНал
                            $xml->endElement(); //СведТов

                            $i++;
                        }
                    }
            
                    if ($order->getShipmentTotal()){
                        $xml->startElement('СведТов');
                            $xml->writeAttribute('НомСтр', $i);
                            $xml->writeAttribute('НаимТов', 'Организация доставки груза');
                            $xml->writeAttribute('ОКЕИ_Тов', '');
                            $xml->writeAttribute('КолТов', '');
                            $xml->writeAttribute('ЦенаТов', $order->getShipmentTotal());
                            $xml->writeAttribute('СтТовБезНДС', $order->getShipmentTotal());
                            $xml->writeAttribute('СтТовУчНал', $order->getShipmentTotal());
                            $xml->startElement('Акциз');
                                $xml->writeAttribute('СумАкциз', 'Без акциза');
                            $xml->endElement(); //Акциз
                            $xml->startElement('НалСт');
                                $xml->writeAttribute('НалСтВел', 0);
                                $xml->writeAttribute('НалСтТип', 'процент');
                            $xml->endElement(); //НалСт
                            $xml->startElement('СумНал');
                                $xml->writeAttribute('СумНДС', 'Без НДС');
                            $xml->endElement(); //СумНал
                        $xml->endElement(); //СведТов
                    }
        
                    $xml->startElement('ВсегоОпл');
                        $xml->writeAttribute('СтТовБезНДСВсего', $order->getTotal());
                        $xml->writeAttribute('СтТовУчНалВсего', $order->getTotal());
                        $xml->startElement('СумНалВсего');
                            $xml->writeAttribute('СумНДС', 'Без НДС');
                        $xml->endElement(); //СумНалВсего
                    $xml->endElement(); //ВсегоОпл
                $xml->endElement(); //ТаблСчет
        
            $xml->endElement(); //Документ
        $xml->endElement(); //Файл
        $xml->endDocument();

//        echo $xml->flush();
//        exit;
        
        $fp = fopen($outFile,'w');
        fwrite($fp, $xml->flush());
        fclose($fp);        
        
        $filter = new Compress([
            'adapter' => 'Zip',
            'options' => [
                'archive' => $outFileZip,
            ],
        ]);
        $filter->filter($outFile);
        
        return $outFileZip;
    }    
    
    /**
     * Торг12
     * @param Order $order
     * @param string $writerType
     * @param bool $code
     * @return string 
     */
    public function torg12($order, $code = true)
    {
        ini_set("pcre.backtrack_limit", "5000000");
        setlocale(LC_ALL, 'ru_RU', 'ru_RU.UTF-8', 'ru', 'russian');
//        echo strftime("%B %d, %Y", time()); exit;

        $folder_name = Order::PRINT_FOLDER;
        if (!is_dir($folder_name)){
            mkdir($folder_name);
        }        
        
        $outFile = $order->getEdoName('ТОРГ');
        $outFileZip = $order->getEdoName('ТОРГ', 'zip');
//        var_dump($outFile); exit;
        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'windows-1251');
        
        $xml->startElement('Файл');
            $xml->writeAttribute('ИдФайл', $order->getEdoPresent('ТОРГ'));
            $xml->writeAttribute('ВерсПрог', self::VERSION);
            $xml->writeAttribute('ВерсФорм', '5.01');
            
            $xml->startElement('СвУчДокОбор');
                $xml->writeAttribute('ИдОтпр', $order->getCompany()->getEdoAddress());
                if ($order->getLegal()){
                    $xml->writeAttribute('ИдПол', $order->getLegal()->getEdoAddress());
                }    
                $xml->startElement('СвОЭДОтпр');
                    if ($order->getCompany()->getEdoOperator()){
                        $xml->writeAttribute('НаимОрг', $order->getCompany()->getEdoOperator()->getName());
                        $xml->writeAttribute('ИННЮЛ', $order->getCompany()->getEdoOperator()->getInn());
                        $xml->writeAttribute('ИдЭДО', $order->getCompany()->getEdoOperator()->getCode());
                    }    
                $xml->endElement(); //СвОЭДОтпр
            $xml->endElement(); //СвУчДокОбор
        
            $xml->startElement('Документ');
                $xml->writeAttribute('КНД', '1115131');
                $xml->writeAttribute('Функция', 'СЧФ');
                $xml->writeAttribute('ДатаИнфПр', date('d.m.Y'));
                $xml->writeAttribute('ВремИнфПр', date('H.i.s'));
                $xml->writeAttribute('НаимЭконСубСост', $order->getCompany()->getName());
                $xml->writeAttribute('ПоФактХЖ', 'Документ об отгрузке товаров (выполнении работ), передаче имущественных прав (документ об оказании услуг)');
                $xml->writeAttribute('НаимДокОпр', 'Счет-фактура и документ об отгрузке товаров (выполнении работ), передаче имущественных прав (документ об оказании услуг)');
        
                $xml->startElement('СвСчФакт');
                    $xml->writeAttribute('НомерСчФ', $order->getAplId());
                    $xml->writeAttribute('ДатаСчФ', date('d.m.Y', strtotime($order->getDocDate())));
                    $xml->writeAttribute('КодОКВ', '643');

                    $xml->startElement('СвПрод');
                        $xml->startElement('ИдСв');
                            $xml->startElement('СвЮЛУч');
                                $xml->writeAttribute('НаимОрг', $order->getCompany()->getName());
                                $xml->writeAttribute('ИННЮЛ', $order->getCompany()->getInn());
                                $xml->writeAttribute('КПП', $order->getCompany()->getKpp());
                            $xml->endElement(); //СвЮЛУч
                        $xml->endElement(); //ИдСв
                        $xml->startElement('Адрес');
                            $xml->startElement('АдрИнф');
                                $xml->writeAttribute('КодСтр', '643');
                                $xml->writeAttribute('АдрТекст', $order->getCompany()->getAddress());
                            $xml->endElement(); //АдрИнф
                        $xml->endElement(); //Адрес
                    $xml->endElement(); //СвПрод

                    $xml->startElement('ГрузОт');
                        $xml->startElement('ОнЖе');
                            $xml->text('он же');
                        $xml->endElement(); //ОнЖе
                    $xml->endElement(); //ГрузОт

                    if ($order->getLegal()){
                        $xml->startElement('ГрузПолуч');
                            $xml->startElement('ИдСв');
                                $xml->startElement('СвЮЛУч');
                                    $xml->writeAttribute('НаимОрг', $order->getRecipient()->getName());
                                    $xml->writeAttribute('ИННЮЛ', $order->getRecipient()->getInn());
                                    $xml->writeAttribute('КПП', $order->getRecipient()->getKpp());
                                $xml->endElement(); //СвЮЛУч
                            $xml->endElement(); //ИдСв
                            $xml->startElement('Адрес');
                                $xml->startElement('АдрИнф');
                                    $xml->writeAttribute('КодСтр', '643');
                                    $xml->writeAttribute('АдрТекст', $order->getRecipient()->getAddress());
                                $xml->endElement(); //АдрИнф
                            $xml->endElement(); //Адрес
                        $xml->endElement(); //ГрузПолуч
                    
                        $xml->startElement('СвПокуп');
                            $xml->startElement('ИдСв');
                                $xml->startElement('СвЮЛУч');
                                    $xml->writeAttribute('НаимОрг', $order->getLegal()->getName());
                                    $xml->writeAttribute('ИННЮЛ', $order->getLegal()->getInn());
                                    $xml->writeAttribute('КПП', $order->getLegal()->getKpp());
                                $xml->endElement(); //СвЮЛУч
                            $xml->endElement(); //ИдСв
                            $xml->startElement('Адрес');
                                $xml->startElement('АдрИнф');
                                    $xml->writeAttribute('КодСтр', '643');
                                    $xml->writeAttribute('АдрТекст', $order->getLegal()->getAddress());
                                $xml->endElement(); //АдрИнф
                            $xml->endElement(); //Адрес
                        $xml->endElement(); //СвПокуп
                    }    
                    
                $xml->endElement(); //СвСчФакт
        
                $xml->startElement('ТаблСчФакт');

                    $bids = $this->entityManager->getRepository(Bid::class)
                            ->findByOrder($order->getId());
                    if ($bids){
                        $i = 1;
                        foreach ($bids as $bid){

                            $xml->startElement('СведТов');
                                $xml->writeAttribute('НомСтр', $i);
                                $xml->writeAttribute('НаимТов', ($bid->getDisplayName()) ? $bid->getDisplayName():$bid->getGood()->getNameShort());
                                $xml->writeAttribute('ОКЕИ_Тов', '796');
                                $xml->writeAttribute('КолТов', $bid->getNum());
                                $xml->writeAttribute('ЦенаТов', $bid->getPrice());
                                $xml->writeAttribute('СтТовБезНДС', $bid->getPrice());
                                $xml->writeAttribute('НалСт', 'без НДС');
                                $xml->writeAttribute('СтТовУчНал', $bid->getTotal());
                                $xml->startElement('Акциз');
                                    $xml->startElement('СумАкциз');
                                        $xml->text('Без акциза');
                                    $xml->endElement(); //СумАкциз
                                $xml->endElement(); //Акциз
                                $xml->startElement('СумНал');
                                    $xml->startElement('СумНал');
                                        $xml->text('без НДС');
                                    $xml->endElement(); //СумНал
                                $xml->endElement(); //СумНал
                                $xml->startElement('СвТД');
                                    $xml->writeAttribute('КодПроисх', '-');
                                    $xml->writeAttribute('НомерТД', '-');
                                $xml->endElement(); //СвТД
                            $xml->endElement(); //СведТов

                            $i++;
                        }
                    }
            
                    if ($order->getShipmentTotal()){
                        $xml->startElement('СведТов');
                            $xml->writeAttribute('НомСтр', $i);
                            $xml->writeAttribute('НаимТов', 'Организация доставки груза');
                            $xml->writeAttribute('ОКЕИ_Тов', '');
                            $xml->writeAttribute('КолТов', '');
                            $xml->writeAttribute('ЦенаТов', $order->getShipmentTotal());
                            $xml->writeAttribute('СтТовБезНДС', $order->getShipmentTotal());
                            $xml->writeAttribute('НалСт', 'без НДС');
                            $xml->writeAttribute('СтТовУчНал', $order->getShipmentTotal());
                            $xml->startElement('Акциз');
                                $xml->startElement('СумАкциз');
                                    $xml->text('Без акциза');
                                $xml->endElement(); //СумАкциз
                            $xml->endElement(); //Акциз
                            $xml->startElement('СумНал');
                                $xml->startElement('СумНал');
                                    $xml->text('без НДС');
                                $xml->endElement(); //СумНал
                            $xml->endElement(); //СумНал
                            $xml->startElement('СвТД');
                                $xml->writeAttribute('КодПроисх', '-');
                                $xml->writeAttribute('НомерТД', '-');
                            $xml->endElement(); //СвТД
                        $xml->endElement(); //СведТов
                    }
        
                    $xml->startElement('ВсегоОпл');
                        $xml->writeAttribute('СтТовУчНалВсего', $order->getTotal());
                        $xml->startElement('СумНалВсего');
                            $xml->startElement('СумНал');
                                $xml->text('Без НДС');
                            $xml->endElement(); //СумНал
                        $xml->endElement(); //СумНалВсего
                    $xml->endElement(); //ВсегоОпл
                $xml->endElement(); //ТаблСчФакт
                
                $xml->startElement('СвПродПер');
                    $xml->startElement('СвПер');
                        $xml->writeAttribute('СодОпер', 'Поступление товаров и услуг');
                        if ($order->getLegal()){
                            $contract = $order->getLegal()->getLastContract();
                            if ($contract){
                                $xml->startElement('ОснПер');
                                    $xml->writeAttribute('НаимОсн', $contract->getName());            
                                    $xml->writeAttribute('НомОсн', $contract->getAct());            
                                    $xml->writeAttribute('ДатаОсн', date('d.m.Y', strtotime($contract->getDateStart())));            
                                $xml->endElement(); //ОснПер
                            }    
                        }    
                    $xml->endElement(); //СвПер
                $xml->endElement(); //СвПродПер
                
                $xml->startElement('Подписант');
                    $xml->writeAttribute('ОснПолн', 'Должностные обязанности');            
                    $xml->writeAttribute('ОблПолн', '1');            
                    $xml->writeAttribute('Статус', '1');            
                    $xml->startElement('ЮЛ');
                        $xml->writeAttribute('ИННЮЛ', $order->getCompany()->getInn());            
                        $xml->writeAttribute('Должн', 'Генеральный директор');            
                        $xml->startElement('ФИО');
                            $xml->writeAttribute('Фамилия', $order->getCompany()->getHeadLastName());            
                            $xml->writeAttribute('Имя', $order->getCompany()->getHeadFirstName());            
                            $xml->writeAttribute('Отчество', $order->getCompany()->getHeadSecondName());            
                        $xml->endElement(); //ФИО
                    $xml->endElement(); //ЮЛ
                $xml->endElement(); //Подписант
        
            $xml->endElement(); //Документ
        $xml->endElement(); //Файл
        $xml->endDocument();

//        echo $xml->flush();
//        exit;
        
        $fp = fopen($outFile,'w');
        fwrite($fp, $xml->flush());
        fclose($fp);        
        
        $filter = new Compress([
            'adapter' => 'Zip',
            'options' => [
                'archive' => $outFileZip,
            ],
        ]);
        $filter->filter($outFile);
        
        return $outFileZip;
    }            
}
