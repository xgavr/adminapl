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
    protected $tagNonCashValue;    

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
    
    public function setDescription($description)
    {
        $this->description = $description;
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
            'name' => $this->getName(),
            'description' => $this->getDescription(),
        ];
        
        return $result;
    }    
    
}
