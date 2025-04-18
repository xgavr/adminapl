<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Laminas\Json\Decoder;
use Laminas\Json\Json;
use Application\Entity\BillSetting;
use Stock\Entity\Ptu;
use Phpml\Math\Matrix;

/**
 * Description of idoc
 * @ORM\Entity(repositoryClass="\Application\Repository\BillRepository")
 * @ORM\Table(name="idoc")
 * @author Daddy
 */
class Idoc {
    
     // Supplier status constants.
    const STATUS_ACTIVE       = 1; // Новый.
    const STATUS_RETIRED      = 2; // Создан документ.
    const STATUS_ERROR      = 3; // Не прочитано.
    const STATUS_PROC      = 4; // Читается.
    const STATUS_TO_CORRECT      = 9; // Читается.
    const STATUS_NEW      = 10; // Не определено.
        
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="doc_key")   
     */
    protected $docKey;    
    
    /**
     * @ORM\Column(name="name")   
     */
    protected $name;
        
    /**
     * @ORM\Column(name="description")   
     */
    protected $description;
        
    /**
     * @ORM\Column(name="info")   
     */
    protected $info;

    /**
     * @ORM\Column(name="sender")   
     */
    protected $sender;

    /**
     * @ORM\Column(name="subject")   
     */
    protected $subject;
    
    /**
     * @ORM\Column(name="tmp_file")   
     */
    protected $tmpfile;
    
    /**
     * @ORM\Column(name="status")  
     */
    protected $status;    
    
    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;        
       
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Supplier", inversedBy="idocs") 
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    private $supplier;    
    
    /**
     * @ORM\OneToOne(targetEntity="Stock\Entity\Mutual", inversedBy="idoc") 
     * @ORM\JoinColumn(name="doc_key", referencedColumnName="doc_key")
     */
//    private $mutual;    

    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     
    
    public function setDocKey($docKey) 
    {
        $this->docKey = $docKey;
    }     

    public function getDocKey() 
    {
        return $this->docKey;
    }    

    public function getName() 
    {
        return $this->name;
    }

    public function setName($name) 
    {
        $this->name = $name;
    }     
 
    public function getSender() {
        return $this->sender;
    }

    public function setSender($sender) {
        $this->sender = $sender;
        return $this;
    }

    public function getSubject() {
        return $this->subject;
    }

    public function setSubject($subject) {
        $this->subject = $subject;
        return $this;
    }
    
    public function getTmpfile() {
        return $this->tmpfile;
    }

    public function setTmpfile($tmpfile) {
        $this->tmpfile = $tmpfile;
        return $this;
    }

    /**
     * Returns status.
     * @return int     
     */
    public function getStatus() 
    {
        return $this->status;
    }

    
    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusList() 
    {
        return [
            self::STATUS_ACTIVE => 'Новый',
            self::STATUS_RETIRED => 'Есть документ',
            self::STATUS_ERROR => 'Нет документа',
            self::STATUS_PROC => 'Обработка',
            self::STATUS_TO_CORRECT => 'Требуется исправление',
            self::STATUS_NEW => 'Не определено',
        ];
    }    
    
    /**
     * Returns user status as string.
     * @return string
     */
    public function getStatusAsString()
    {
        $list = self::getStatusList();
        if (isset($list[$this->status]))
            return $list[$this->status];
        
        return 'Unknown';
    }    
    
    /**
     * Sets status.
     * @param int $status     
     */
    public function setStatus($status) 
    {
        $this->status = $status;
    }   
    
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Очистить пустые колонки из данных
     * @param array $data
     * @return array
     */
    private function _getDataValueColumns($data)
    {
        if (empty($data)){
            return $data;
        }
        
        $matrix = new Matrix($data);
        $transpose = $matrix->transpose()->toArray();
//        return $transpose;
        
        $result = [];
        foreach ($transpose as $row){
            $filterRow = array_filter($row);
            if (count($filterRow)){
                $result[] = $row;
            }
        }
        $matrix2 = new Matrix($result);        
        return $matrix2->transpose()->toArray();
    }

    /**
     * Данные документа
     * @param bool $allColumn
     * @return array
     */
    public function getDescriptionAsArray($allColumn = true)
    {
        $data = Decoder::decode($this->description, Json::TYPE_ARRAY);
        if ($allColumn){
            return $data;
        } else {
            return $this->_getDataValueColumns($data);
        }    
    }
    
    /**
     * Данные для HTML
     * @param bool $allColumn
     * @return string
     */
    public function getDescriptionAsHtmlTable($allColumn = true)
    {
        ini_set('memory_limit', '512M');
        
        $data = $this->getDescriptionAsArray($allColumn);
        if (is_array($data)){
            $maxH = count($data)*5;
            $maxCol = 0;
            foreach ($data as $row){
                if (count($row) > $maxCol){
                    $maxCol = count($row);
                }
            }
            $w = 20;
            $maxW = $maxCol*$w;
            $result = "<table style=\"width: {$maxW}px; height: {$maxH}px\">";
            $c = 1;
            $result .= '<tr>'; 
            $result .= "<td align=\"center\" style=\"border:1px solid black; width: {$w}px\">"; 
            $result .= 0;
            $result .= '</td>';                    
            while (true){
                if ($c > $maxCol) break;
                $result .= "<td align=\"center\" style=\"border:1px solid black; width: {$w}px\">"; 
                $result .= $c;
                $result .= '</td>';                    
                $c++;
            }
            $result .= '</tr>';            
            $r = 1;
            foreach ($data as $row){
                $result .= '<tr>'; 
                $result .= "<td align=\"center\" style=\"border:1px solid black; width: {$w}px\">"; 
                $result .= $r;
                $result .= '</td>';                    
                $c = 1;
                foreach ($row as $key=>$value){
                    $result .= "<td style=\"border:1px solid black; width: {$w}px\" class=\"dataCell\" data-row=\"{$r}\" data-col=\"{$c}\">"; 
                    $result .= $value;
                    $result .= '</td>';                    
                    $c++;
                }
                $result .= '</tr>';
                $r++;
            }
            $result .= '</table>';
            return $result;
        }    
        return;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }    
    
    public function getInfo()
    {
        return $this->info;
    }

    public function setInfo($info)
    {
        return $this->info = $info;
    }
    
    /**
     * Returns the date of doc creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * Sets the date when this doc was created.
     * @param string $dateCreated     
     */
    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
    }    
    
    /*
     * Возвращает связанный supplier.
     * @return \Application\Entity\Supplier
     */    
    public function getSupplier() 
    {
        return $this->supplier;
    }

    /**
     * Задает связанный supplier.
     * @param \Application\Entity\Supplier $supplier
     */    
    public function setSupplier($supplier) 
    {
        $this->supplier = $supplier;
        if ($supplier){
            $supplier->addIdoc($this);
        }    
    }    
        
        
    /**
     * Прочитать значение в ячейке
     * @param int $row
     * @param int $col
     * @param array $idocData
     * @return string
     */
    public function readText($row, $col, $idocData)
    {
        if (isset($idocData[$row])){
            if (isset($idocData[$row][$col])){
                $result = trim($idocData[$row][$col]);
                return $result;
            }
        }
        return false;
    }
    
    /**
     * Прочитать наименование товара документа
     * @param int $row
     * @param int $col
     * @param array $idocData
     * @return string
     */
    public function readGoodName($row, $col, $idocData)
    {
        $result = $this->readText($row, $col, $idocData);
        if (mb_strlen($result)>3){
            return $result;
        }
        return false;
    }
    
    /**
     * Строка в дату Старая
     * @param string $str
     * @return date
     */
    private function _strToDateOld($str)
    {
        setlocale(LC_ALL,'ru_RU.UTF-8');
        $ru_month = ['январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'];
        $ru_month1 = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        $en_month = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $en_month_ = [' January ', ' February ', ' March ', ' April ', ' May ', ' June ', ' July ', ' August ', ' September ', ' October ', ' November ', ' December '];
        
        $date = str_replace($ru_month1, $en_month, mb_strtolower($str));
        $date = str_replace($ru_month, $en_month, mb_strtolower($date));
        $date = str_replace($ru_month_, $en_month, mb_strtolower($date));
        
        $date = trim(preg_replace('/[а-яА-Я]/ui', '',$dateEn));

        return date('Y-m-d', strtotime($date));        
    }
    
    /**
     * Строка в дату
     * @param string $str
     * @return date
     */
    private function _strToDate($str)
    {
        setlocale(LC_ALL,'ru_RU.UTF-8');
        $ru_month = ['январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'];
        $ru_month1 = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        $en_month = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];
        $num_month = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        
        $date = str_replace($ru_month1, $num_month, mb_strtolower($str));
        $date = str_replace($ru_month, $num_month, mb_strtolower($date));
        $date = str_replace($en_month, $num_month, mb_strtolower($date));
        
        $delimetrs = [' ','.','-'];        
        foreach ($delimetrs as $delimetr){
            $matches = [];
            \preg_match_all("/\d{1,4}\\$delimetr\d{1,2}\\$delimetr\d{2,4}/", $date, $matches);
            foreach ($matches as $match){
                foreach ($match as $strdate){
                    $dateFromFormat = \DateTime::createFromFormat("d{$delimetr}m{$delimetr}Y", $strdate);
                    if ($dateFromFormat){
                        return $dateFromFormat->format('Y-m-d');
                    }    
                    $dateFromFormat = \DateTime::createFromFormat("d{$delimetr}m{$delimetr}y", $strdate);
                    if ($dateFromFormat){
                        return $dateFromFormat->format('Y-m-d');
                    }    
                    $dateFromFormat = \DateTime::createFromFormat("Y{$delimetr}m{$delimetr}d", $strdate);
                    if ($dateFromFormat){
                        return $dateFromFormat->format('Y-m-d');
                    }    
                }
            }
        }    
        return false;
    }
    
    /**
     * Преобразование даты НЕ ИСПОЛЬЗУЕТСЯ
     * @param string $excelDate
     */
    private function _excelDateToDate($excelDate)
    {
        if (is_numeric($excelDate) && $excelDate < 60000){
            return date('Y-m-d', ($excelDate - 25569) * 86400);
        }
        
        return $this->_strToDate($excelDate);
    }
    
    /**
     * Проверить дату
     * @param str $date
     * @return bool
     */
    private function _isDate($date)
    {
        if (empty($date)){
            return false;
        }
        if ($date == '1970-01-01'){
            return false;
        }
        if(strtotime($date)){
            return true;
        }        
        return false; 
    }
    
    /**
     * Номер и дата в одной строке
     * @param string $datastr
     * @param string $expec num|date
     */
    private function _docnumAndDate($datastr, $expec = 'num')
    {        
        $str = str_replace(['/', '\\'], '', $datastr);
        if ($expec == 'date'){
            $dateFromFormat = \DateTime::createFromFormat('d.m.y', $str);
            if ($dateFromFormat){
                return $dateFromFormat->format('Y-m-d');
            }
            $dateFromFormat = \DateTime::createFromFormat('Y-m-d', $str);
            if ($dateFromFormat){
                return $dateFromFormat->format('Y-m-d');
            }
            $date = $this->_strToDate($str);
            if ($this->_isDate($date)){
                return $date;
            }
        }
        
        $strs = explode(' ', $str);
        if (count($strs) == 1 && $expec == 'num'){
            return $str;
        }
        foreach ($strs as $value){
            if ($expec == 'num'){
                $posNN = \stripos(trim($value), '№');
                $strlen = mb_strlen($value);
                if ($posNN !== false && $strlen > 1){
                    return trim($value, '№');
                }
                $posN = \stripos(trim($value), 'N');
                if ($posN !== false  && $strlen > 1){
                    return trim($value, 'N');
                }
                
                $dig = preg_replace("/[^0-9]/", '', $value);
                if ($dig){
                    $date = $this->_strToDate($value);
                    if (!$this->_isDate($date)){
                        return $value;
                    }                    
                }
            }
            if ($expec == 'date'){
                $date = $this->_strToDate($value);
                if ($this->_isDate($date)){
                    return $date;
                }
            }    
        }
        return;
    }
    
    /**
     * Прочитать наименование товара документа
     * @param int $row
     * @param int $col
     * @param array $idocData
     * @param string $expec num|date
     * @return string
     */    
    private function _readDocnumAndDate($row, $col, $idocData, $expec = 'num')
    {
       $str = $this->readText($row, $col, $idocData);
       return $this->_docnumAndDate($str, $expec);
    }

    /**
     * Прочитать дату документа
     * @param int $row
     * @param int $col
     * @param array $idocData
     * @return string
     */
    public function readDate($row, $col, $idocData)
    {
        if (isset($idocData[$row])){
            if (isset($idocData[$row][$col])){
                $result = trim($idocData[$row][$col]);
                $date = $this->_strToDate($result);
                return $date;
            }
        }
        
        return false;
    }
    
    /**
     * Прочитать число из документа
     * @param int $row
     * @param int $col
     * @param array $idocData
     * @return string
     */
    public function readNumeric($row, $col, $idocData)
    {
        setlocale(LC_ALL,'ru_RU.UTF-8');
        if (isset($idocData[$row])){
            if (isset($idocData[$row][$col])){
//                var_dump($idocData[$row][$col]);
                $converted = trim($idocData[$row][$col],chr(0xC2).chr(0xA0)); //&nbsp;
                $result = (float) str_replace(',', '.', preg_replace('/\s+/', '', $converted));
//                var_dump($result);
                if (is_numeric($result)){
                    return $result; 
                }
            }    
        }    
        return FALSE;
    }
    
    /**
     * Данные для ПТУ
     * @param array $billSettingData
     * @return array 
     */
    public function idocToPtu($billSettingData)
    {
        $idocData = $this->getDescriptionAsArray($billSettingData['ruleCell'] == BillSetting::RULE_CELL_ALL);
        $result = [];
        if (!empty($billSettingData['docNumRow'])){
            $result['doc_no'] = $this->_readDocnumAndDate($billSettingData['docNumRow']-1, $billSettingData['docNumCol']-1, $idocData);
        }    
        if (!empty($billSettingData['docDateRow'])){
            $result['doc_date'] = $this->_readDocnumAndDate($billSettingData['docDateRow']-1, $billSettingData['docDateCol']-1, $idocData, 'date');
        }    
        if (!empty($billSettingData['corNumRow'])){
            $result['cor_no'] = $this->_readDocnumAndDate($billSettingData['corNumRow']-1, $billSettingData['corNumCol']-1, $idocData);
        }    
        if (!empty($billSettingData['corDateRow'])){
            $result['cor_date'] = $this->_readDocnumAndDate($billSettingData['corDateRow']-1, $billSettingData['corDateCol']-1, $idocData, 'date');
        }    
        if (!empty($billSettingData['idNumRow'])){
            $result['id_no'] = $this->_readDocnumAndDate($billSettingData['idNumRow']-1, $billSettingData['idNumCol']-1, $idocData);
        }    
        if (!empty($billSettingData['idDateRow'])){
            $result['id_date'] = $this->_readDocnumAndDate($billSettingData['idDateRow']-1, $billSettingData['idDateCol']-1, $idocData, 'date');
        }    
        if (!empty($billSettingData['contractRow'])){
            $result['contract'] = $this->readText($billSettingData['contractRow']-1, $billSettingData['contractCol']-1, $idocData);
        }    
        if (!empty($billSettingData['innRow'])){
            $result['inn'] = $this->readText($billSettingData['innRow']-1, $billSettingData['innCol']-1, $idocData);
        }    
        if (!empty($billSettingData['tagNoCashRow'])){
            $result['tag_no_cash'] = $this->readText($billSettingData['tagNoCashRow']-1, $billSettingData['tagNoCashCol']-1, $idocData);
        }    
        if (!empty($billSettingData['tagNoCashValue'])){
            $result['tag_no_cash_value'] = $billSettingData['tagNoCashValue'];
        }    
        
        $initRow = null;
        if (!empty($billSettingData['initTabRow'])){
            $initRow = $billSettingData['initTabRow'];
        }  
        if ($initRow){
            $total = 0;
            while($initRow <= count($idocData)){
                $tab = [];
                if (!empty($billSettingData['articleCol'])){
                    $tab['article'] = $this->readText($initRow-1, $billSettingData['articleCol']-1, $idocData);
                }        
                if (!empty($billSettingData['supplierIdCol'])){
                    $tab['supplier_article'] = $this->readText($initRow-1, $billSettingData['supplierIdCol']-1, $idocData);
                }        
                if (!empty($billSettingData['goodNameCol'])){
                    $tab['good_name'] = $this->readGoodName($initRow-1, $billSettingData['goodNameCol']-1, $idocData);
                }        
                if (!empty($billSettingData['producerCol'])){
                    $tab['producer'] = $this->readText($initRow-1, $billSettingData['producerCol']-1, $idocData);
                }        
                if (!empty($billSettingData['quantityCol'])){
                    $tab['quantity'] = $this->readNumeric($initRow-1, $billSettingData['quantityCol']-1, $idocData);
                }        
                if (!empty($billSettingData['priceCol'])){
                    $tab['price'] = $this->readNumeric($initRow-1, $billSettingData['priceCol']-1, $idocData);
                    if ($tab['price']){
                        if (!empty($tab['quantity']) && !empty($tab['good_name'])){
                            $total += $tab['price']*$tab['quantity'];
                            $tab['amount'] = $tab['price']*$tab['quantity'];
                        }   
                    }            
                }        
                if (!empty($billSettingData['amountCol'])){
                    $tab['amount'] = $this->readNumeric($initRow-1, $billSettingData['amountCol']-1, $idocData);
                    if ($tab['amount']){
                        if (!empty($tab['quantity']) && !empty($tab['good_name'])){
                            $total += $tab['amount'];
                            $tab['price'] = $tab['amount']/$tab['quantity'];
                        }   
                    }            
                }        
                if (!empty($billSettingData['packageCodeCol'])){
                    $tab['package_code'] = $this->readText($initRow-1, $billSettingData['packageCodeCol']-1, $idocData);
                }        
                if (!empty($billSettingData['packcageCol'])){
                    $tab['packcage'] = $this->readText($initRow-1, $billSettingData['packcageCol']-1, $idocData);
                }        
                if (!empty($billSettingData['countryCodeCol'])){
                    $tab['country_code'] = $this->readText($initRow-1, $billSettingData['countryCodeCol']-1, $idocData);
                }        
                if (!empty($billSettingData['countryCol'])){
                    $tab['country'] = $this->readText($initRow-1, $billSettingData['countryCol']-1, $idocData);
                }        
                if (!empty($billSettingData['ntdCol'])){
                    $tab['ntd'] = $this->readText($initRow-1, $billSettingData['ntdCol']-1, $idocData);
                }      
                
//                if ($initRow > 100){
//                    break;
//                }
                $result['tab'][] = $tab;
                $initRow++;
            }
            $result['total'] = $total;
        }                
        return $result;
    }
    
//    public function getMutual() {
//        return $this->mutual;
//    }
}
