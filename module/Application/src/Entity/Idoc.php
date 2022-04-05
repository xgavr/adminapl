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

/**
 * Description of idoc
 * @ORM\Entity(repositoryClass="\Application\Repository\BillRepository")
 * @ORM\Table(name="idoc")
 * @author Daddy
 */
class Idoc {
    
     // Supplier status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
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
            self::STATUS_ACTIVE => 'Используется',
            self::STATUS_RETIRED => 'Не используется'
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
    
    public function getDescriptionAsArray()
    {
        return Decoder::decode($this->description, Json::TYPE_ARRAY);
    }
    
    public function getDescriptionAsHtmlTable()
    {
        $data = $this->getDescriptionAsArray();
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

    public function setDescription($description)
    {
        $this->description = $description;
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
        $supplier->addBillSettings($this);
    }    
        
    /**
     * Прочитать номер документа
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
     * Преобразование даты
     * @param string $excelDate
     */
    private function _excelDateToDate($excelDate)
    {
        if (is_numeric($excelDate) && $excelDate < 60000){
            return date('Y-m-d', ($excelDate - 25569) * 86400);
        }
        setlocale(LC_ALL,'ru_RU.UTF-8');
        $ru_month = ['январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'];
        $ru_month1 = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        $en_month = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        
        $date = str_replace($ru_month1, $en_month, mb_strtolower($excelDate));
        $date = str_replace($ru_month, $en_month, mb_strtolower($date));
        $date = trim(preg_replace('/[^a-zA-Z0-9 ]/ui', '',$date));
        //var_dump($date);

        return date('Y-m-d', strtotime($date));
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
                $date = $this->_excelDateToDate($result);
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
        if (isset($idocData[$row])){
            if (isset($idocData[$row][$col])){
                $result = (float) str_replace(',', '.', preg_replace('/\s+/', '', $idocData[$row][$col]));
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
        $idocData = $this->getDescriptionAsArray();
        if (!empty($billSettingData['docNumRow'])){
            $result['doc_no'] = $this->readText($billSettingData['docNumRow']-1, $billSettingData['docNumCol']-1, $idocData);
        }    
        if (!empty($billSettingData['docDateRow'])){
            $result['doc_date'] = $this->readDate($billSettingData['docDateRow']-1, $billSettingData['docDateCol']-1, $idocData);
        }    
        if (!empty($billSettingData['corNumRow'])){
            $result['cor_no'] = $this->readText($billSettingData['corNumRow']-1, $billSettingData['corNumCol']-1, $idocData);
        }    
        if (!empty($billSettingData['corDateRow'])){
            $result['cor_date'] = $this->readDate($billSettingData['corDateRow']-1, $billSettingData['corDateCol']-1, $idocData);
        }    
        if (!empty($billSettingData['idNumRow'])){
            $result['id_no'] = $this->readText($billSettingData['idNumRow']-1, $billSettingData['idNumCol']-1, $idocData);
        }    
        if (!empty($billSettingData['idDateRow'])){
            $result['id_date'] = $this->readDate($billSettingData['idDateRow']-1, $billSettingData['idDateCol']-1, $idocData);
        }    
        if (!empty($billSettingData['contractRow'])){
            $result['contract'] = $this->readText($billSettingData['contractRow']-1, $billSettingData['contractCol']-1, $idocData);
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
            while(true){
                $tab = [];
                $continue = false;
                if (!empty($billSettingData['articleCol'])){
                    $tab['article'] = $this->readText($initRow-1, $billSettingData['articleCol']-1, $idocData);
                    if ($tab['article']){
                        $continue = true;
                    }            
                }        
                if (!empty($billSettingData['supplierIdCol'])){
                    $tab['supplier_article'] = $this->readText($initRow-1, $billSettingData['supplierIdCol']-1, $idocData);
                    if ($tab['supplier_article']){
                        $continue = true;
                    }            
                }        
                if (!empty($billSettingData['goodNameCol'])){
                    $tab['good_name'] = $this->readGoodName($initRow-1, $billSettingData['goodNameCol']-1, $idocData);
                    if ($tab['good_name']){
                        $continue = true;
                    }            
                }        
                if (!empty($billSettingData['producerCol'])){
                    $tab['producer'] = $this->readText($initRow-1, $billSettingData['producerCol']-1, $idocData);
                }        
                if (!empty($billSettingData['quantityCol'])){
                    $tab['quantity'] = $this->readNumeric($initRow-1, $billSettingData['quantityCol']-1, $idocData);
                    if ($tab['quantity']){
                        $continue = true;
                    }            
                }        
                if (!empty($billSettingData['priceCol'])){
                    $tab['price'] = $this->readNumeric($initRow-1, $billSettingData['priceCol']-1, $idocData);
                    if ($tab['price']){
                        $continue = true;
                    }            
                }        
                if (!empty($billSettingData['amountCol'])){
                    $tab['amount'] = $this->readNumeric($initRow-1, $billSettingData['amountCol']-1, $idocData);
                    if ($tab['amount']){
                        $continue = true;
                        if (!empty($tab['quantity']) && !empty($tab['good_name'])){
                            $total += $tab['amount'];
                        }   
                    }            
                }        
                if (!empty($billSettingData['packageCodeCol'])){
                    $tab['package_code'] = $this->readText($initRow-1, $billSettingData['packageCodeCol']-1, $idocData);
                    if ($tab['package_code']){
                        $continue = true;
                    }            
                }        
                if (!empty($billSettingData['packcageCol'])){
                    $tab['packcage'] = $this->readText($initRow-1, $billSettingData['packcageCol']-1, $idocData);
                    if ($tab['packcage']){
                        $continue = true;
                    }            
                }        
                if (!empty($billSettingData['countryCodeCol'])){
                    $tab['country_code'] = $this->readText($initRow-1, $billSettingData['countryCodeCol']-1, $idocData);
                    if ($tab['country_code']){
                        $continue = true;
                    }            
                }        
                if (!empty($billSettingData['countryCol'])){
                    $tab['country'] = $this->readText($initRow-1, $billSettingData['countryCol']-1, $idocData);
                    if ($tab['country']){
                        $continue = true;
                    }            
                }        
                if (!empty($billSettingData['ntdCol'])){
                    $tab['ntd'] = $this->readText($initRow-1, $billSettingData['ntdCol']-1, $idocData);
                    if ($tab['ntd']){
                        $continue = true;
                    }            
                }      
                
                if (!$continue){
                    break;
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
}
