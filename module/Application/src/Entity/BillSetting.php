<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of BillSetting
 * @ORM\Entity(repositoryClass="\Application\Repository\BillRepository")
 * @ORM\Table(name="bill_setting")
 * @author Daddy
 */
class BillSetting {
    
     // Bill setting status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    const RULE_CELL_ALL  = 1; //  искать значение во всех колноках строки.
    const RULE_CELL_VALUE  = 2; //  искать значение в колнках с данными .
        
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
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
     * @ORM\Column(name="rule_cell")  
     */
    protected $ruleCell;    
        
    /**
     * @ORM\Column(name="doc_num_col")  
     */
    protected $docNumCol;    

    /**
     * @ORM\Column(name="doc_num_row")  
     */
    protected $docNumRow;    

    /**
     * @ORM\Column(name="doc_date_col")  
     */
    protected $docDateCol;    

    /**
     * @ORM\Column(name="doc_date_row")  
     */
    protected $docDateRow;    

    /**
     * @ORM\Column(name="cor_num_col")  
     */
    protected $corNumCol;    

    /**
     * @ORM\Column(name="cor_num_row")  
     */
    protected $corNumRow;    

    /**
     * @ORM\Column(name="cor_date_col")  
     */
    protected $corDateCol;    

    /**
     * @ORM\Column(name="cor_date_row")  
     */
    protected $corDateRow;    

    /**
     * @ORM\Column(name="id_num_col")  
     */
    protected $idNumCol;    

    /**
     * @ORM\Column(name="id_num_row")  
     */
    protected $idNumRow;    

    /**
     * @ORM\Column(name="id_date_col")  
     */
    protected $idDateCol;    

    /**
     * @ORM\Column(name="id_date_row")  
     */
    protected $idDateRow;    

    /**
     * @ORM\Column(name="contract_col")  
     */
    protected $contractCol;    

    /**
     * @ORM\Column(name="contract_row")  
     */
    protected $contractRow;    

    /**
     * @ORM\Column(name="tag_non_cash_col")  
     */
    protected $tagNonCashCol;    

    /**
     * @ORM\Column(name="tag_non_cash_row")  
     */
    protected $tagNonCashRow;    

    /**
     * @ORM\Column(name="tag_non_cash_value")  
     */
    protected $tagNonCashValue;    

    /**
     * @ORM\Column(name="init_tab_row")  
     */
    protected $initTabRow;    

    /**
     * @ORM\Column(name="article_col")  
     */
    protected $articleCol;    

    /**
     * @ORM\Column(name="supplier_id_col")  
     */
    protected $supplierIdCol;    

    /**
     * @ORM\Column(name="good_name_col")  
     */
    protected $goodNameCol;    

    /**
     * @ORM\Column(name="producer_col")  
     */
    protected $producerCol;    

    /**
     * @ORM\Column(name="quantity_col")  
     */
    protected $quantityCol;    

    /**
     * @ORM\Column(name="price_col")  
     */
    protected $priceCol;    

    /**
     * @ORM\Column(name="amount_col")  
     */
    protected $amountCol;    

    /**
     * @ORM\Column(name="package_code_col")  
     */
    protected $packageCodeCol;    

    /**
     * @ORM\Column(name="package_col")  
     */
    protected $packageCol;    

    /**
     * @ORM\Column(name="country_code_col")  
     */
    protected $countryCodeCol;    

    /**
     * @ORM\Column(name="country_col")  
     */
    protected $countryCol;    

    /**
     * @ORM\Column(name="ntd_col")  
     */
    protected $ntdCol;    

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Supplier", inversedBy="billSettings") 
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

    public function getName() 
    {
        return $this->name;
    }
    
    /**
     * Получить какоето имя
     * @param string $name
     */
    public static function gname($name)
    {
        $blackListRu = [
            'января',
            'февраля',
            'марта',
            'апреля',
            'мая',
            'июня',
            'июля',
            'августа',
            'сентября',
            'октября',
            'ноября',
            'декабря',
        ];
        
        $result = preg_replace('/[^а-яА-Я]/ui', '', mb_str_replace($blackListRu, '', $name));
        if (empty($result)){
            $result = preg_replace('/[^a-zA-Z]/ui', '',$name );            
        }
                
        if (empty($result)){
            $result = $name;   
        }
        
        return mb_strtoupper($result);
    }

    public function setName($name) 
    {
        $this->name = $this->gname($name);
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
    
    /**
     * Returns rule cell.
     * @return int     
     */
    public function getRuleCell() 
    {
        return $this->ruleCell;
    }
    
    /**
     * Returns possible cell rules as array.
     * @return array
     */
    public static function getRuleCellList() 
    {
        return [
            self::RULE_CELL_ALL => 'Во всей строке',
            self::RULE_CELL_VALUE => 'В ячейках с данными',
        ];
    }    
    
    /**
     * Returns rule cell as string.
     * @return string
     */
    public function getRuleCellAsString()
    {
        $list = self::getRuleCellList();
        if (isset($list[$this->ruleCell]))
            return $list[$this->ruleCell];
        
        return 'Unknown';
    }    
    
    /**
     * Returns possible cell rules as array.
     * @return array
     */
    public static function getRuleCellBools() 
    {
        return [
            self::RULE_CELL_ALL => true,
            self::RULE_CELL_VALUE => false,
        ];
    }    
    
    /**
     * Returns rule cell as bool.
     * @return string
     */
    public function getRuleCellAsBool()
    {
        $list = self::getRuleCellBools();
        if (isset($list[$this->ruleCell]))
            return $list[$this->ruleCell];
        
        return 'Unknown';
    }    

    /**
     * Sets rule cell.
     * @param int $ruleCell     
     */
    public function setRuleCell($ruleCell) 
    {
        $this->ruleCell = $ruleCell;
    }   
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function getDescriptionAsArray()
    {
        return Decoder::decode($this->description, Json::TYPE_ARRAY);
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
    }    
    
    public function getDocNumCol()
    {
        return $this->docNumCol;
    }
    
    public function setDocNumCol($docNumCol)
    {
        $this->docNumCol = $docNumCol;
    }    

    public function getDocNumRow()
    {
        return $this->docNumRow;
    }
    
    public function setDocNumRow($docNumRow)
    {
        $this->docNumRow = $docNumRow;
    }    

    public function getDocDateCol()
    {
        return $this->docDateCol;
    }
    
    public function setDocDateCol($docDateCol)
    {
        $this->docDateCol = $docDateCol;
    }    

    public function getDocDateRow()
    {
        return $this->docDateRow;
    }
    
    public function setDocDateRow($docDateRow)
    {
        $this->docDateRow = $docDateRow;
    }    

    public function getCorNumCol()
    {
        return $this->corNumCol;
    }
    
    public function setCorNumCol($corNumCol)
    {
        $this->corNumCol = $corNumCol;
    }    

    public function getCorNumRow()
    {
        return $this->corNumRow;
    }
    
    public function setCorNumRow($corNumRow)
    {
        $this->corNumRow = $corNumRow;
    }    
    
    public function getCorDateCol()
    {
        return $this->corDateCol;
    }
    
    public function setCorDateCol($corDateCol)
    {
        $this->corDateCol = $corDateCol;
    }    

    public function getCorDateRow()
    {
        return $this->corDateRow;
    }
    
    public function setCorDateRow($corDateRow)
    {
        $this->corDateRow = $corDateRow;
    }    

    public function getIdNumCol()
    {
        return $this->idNumCol;
    }
    
    public function setIdNumCol($idNumCol)
    {
        $this->idNumCol = $idNumCol;
    }    

    public function getIdNumRow()
    {
        return $this->idNumRow;
    }
    
    public function setIdNumRow($idNumRow)
    {
        $this->idNumRow = $idNumRow;
    }    

    public function getIdDateCol()
    {
        return $this->idDateCol;
    }
    
    public function setIdDateCol($idDateCol)
    {
        $this->idDateCol = $idDateCol;
    }    

    public function getIdDateRow()
    {
        return $this->idDateRow;
    }
    
    public function setIdDateRow($idDateRow)
    {
        $this->idDateRow = $idDateRow;
    }    

    public function getContractCol()
    {
        return $this->contractCol;
    }
    
    public function setContractCol($contractCol)
    {
        $this->contractCol = $contractCol;
    }    

    public function getContractRow()
    {
        return $this->contractRow;
    }
    
    public function setContractRow($contractRow)
    {
        $this->contractRow = $contractRow;
    }    

    public function getTagNoCashCol()
    {
        return $this->tagNonCashCol;
    }
    
    public function setTagNoCashCol($tagNoCashCol)
    {
        $this->tagNonCashCol = $tagNoCashCol;
    }    

    public function getTagNoCashRow()
    {
        return $this->tagNonCashRow;
    }
    
    public function setTagNoCashRow($tagNoCashRow)
    {
        $this->tagNonCashRow = $tagNoCashRow;
    }    

    public function getTagNoCashValue()
    {
        return $this->tagNonCashValue;
    }
    
    public function setTagNoCashValue($tagNoCashValue)
    {
        $this->tagNonCashValue = $tagNoCashValue;
    }    

    public function getInitTabRow()
    {
        return $this->initTabRow;
    }
    
    public function setInitTabRow($initTabRow)
    {
        $this->initTabRow = $initTabRow;
    }    

    public function getArticleCol()
    {
        return $this->articleCol;
    }
    
    public function setArticleCol($articleCol)
    {
        $this->articleCol = $articleCol;
    }    

    public function getSupplierIdCol()
    {
        return $this->supplierIdCol;
    }
    
    public function setSupplierIdCol($supplierIdCol)
    {
        $this->supplierIdCol = $supplierIdCol;
    }    
    
    public function getGoodNameCol()
    {
        return $this->goodNameCol;
    }
    
    public function setGoodNameCol($goodNameCol)
    {
        $this->goodNameCol = $goodNameCol;
    }    
    
    public function getProducerCol()
    {
        return $this->producerCol;
    }
    
    public function setProducerCol($producerCol)
    {
        $this->producerCol = $producerCol;
    }    
    
    public function getQuantityCol()
    {
        return $this->quantityCol;
    }
    
    public function setQuantityCol($quantityCol)
    {
        $this->quantityCol = $quantityCol;
    }    
    
    public function getPriceCol()
    {
        return $this->priceCol;
    }
    
    public function setPriceCol($priceCol)
    {
        $this->priceCol = $priceCol;
    }        
    
    public function getAmountCol()
    {
        return $this->amountCol;
    }
    
    public function setAmountCol($amountCol)
    {
        $this->amountCol = $amountCol;
    }    
    
    public function getPackageCodeCol()
    {
        return $this->packageCodeCol;
    }
    
    public function setPackageCodeCol($packageCodeCol)
    {
        $this->packageCodeCol = $packageCodeCol;
    }    
    
    public function getPackageCol()
    {
        return $this->packageCol;
    }
    
    public function setPackageCol($packageCol)
    {
        $this->packageCol = $packageCol;
    }    
    
    public function getCountryCodeCol()
    {
        return $this->countryCodeCol;
    }
    
    public function setCountryCodeCol($countryCodeCol)
    {
        $this->countryCodeCol = $countryCodeCol;
    }    
    
    public function getCountryCol()
    {
        return $this->countryCol;
    }
    
    public function setCountryCol($countryCol)
    {
        $this->countryCol = $countryCol;
    }    
    
    public function getNtdCol()
    {
        return $this->ntdCol;
    }
    
    public function setNtdCol($ntdCol)
    {
        $this->ntdCol = $ntdCol;
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
     * Массив для формы
     * @return array 
     */
    public function toArray()
    {
        $result = [
            'status' => $this->getStatus(),
            'ruleCell' => $this->getRuleCell(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'docNumCol' => $this->getDocNumCol(),
            'docNumRow' => $this->getDocNumRow(),
            'docDateCol' => $this->getDocDateCol(),
            'docDateRow' => $this->getDocDateRow(),
            'corNumCol' => $this->getCorNumCol(),
            'corNumRow' => $this->getCorNumRow(),
            'corDateCol' => $this->getCorDateCol(),
            'corDateRow' => $this->getCorDateRow(),
            'idNumCol' => $this->getIdNumCol(),
            'idNumRow' => $this->getIdNumRow(),
            'idDateCol' => $this->getIdDateCol(),
            'idDateRow' => $this->getIdDateRow(),
            'contractCol' => $this->getContractCol(),
            'contractRow' => $this->getContractRow(),
            'tagNoCashCol' => $this->getTagNoCashCol(),
            'tagNoCashRow' => $this->getTagNoCashRow(),
            'tagNoCashValue' => $this->getTagNoCashValue(),
            'initTabRow' => $this->getInitTabRow(),
            'articleCol' => $this->getArticleCol(),
            'supplierIdCol' => $this->getSupplierIdCol(),
            'goodNameCol' => $this->getGoodNameCol(),
            'producerCol' => $this->getProducerCol(),
            'quantityCol' => $this->getQuantityCol(),
            'priceCol' => $this->getPriceCol(),
            'amountCol' => $this->getAmountCol(),
            'packageCodeCol' => $this->getPackageCodeCol(),
            'packcageCol' => $this->getPackageCol(),
            'countryCodeCol' => $this->getCountryCodeCol(),
            'countryCol' => $this->getCountryCol(),
            'ntdCol' => $this->getNtdCol(),            
        ];
        
        return $result;
    }    
    
}
