<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Entity;

use Doctrine\ORM\Mapping as ORM;
use Stock\Entity\Movement;
use Stock\Entity\Ot;
use Stock\Entity\Ptu;
use Stock\Entity\Pt;
use Stock\Entity\Vt;
use Application\Entity\Order;
use Stock\Entity\St;
use Stock\Entity\Vtp;
use Stock\Entity\Revise;


/**
 * Description of Comiss
 * @ORM\Entity(repositoryClass="\Stock\Repository\RegisterRepository")
 * @ORM\Table(name="register")
 * @author Daddy
 */
class Register {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    const STATUS_COMMISSION   = 3; // commission.
    const STATUS_TAKE_NO      = 4; // не проведено.
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /** 
     * @ORM\Column(name="date_oper")  
     */
    protected $dateOper;    

    /**
     * @ORM\Column(name="doc_type")   
     */
    protected $docType;
    
    /**
     * @ORM\Column(name="doc_id")   
     */
    protected $docId;
        
    /**
     * @ORM\Column(name="doc_stamp")   
     */
    protected $docStamp;
    
    /**
     * @ORM\OneToOne(targetEntity="Stock\Entity\Ptu") 
     * @ORM\JoinColumn(name="doc_id", referencedColumnName="id")
     */
    private $ptu;

    /**
     * @ORM\OneToOne(targetEntity="Stock\Entity\Ot") 
     * @ORM\JoinColumn(name="doc_id", referencedColumnName="id")
     */
    private $ot;

    /**
     * @ORM\OneToOne(targetEntity="Stock\Entity\Pt") 
     * @ORM\JoinColumn(name="doc_id", referencedColumnName="id")
     */
    private $pt;

    /**
     * @ORM\OneToOne(targetEntity="Stock\Entity\Vt") 
     * @ORM\JoinColumn(name="doc_id", referencedColumnName="id")
     */
    private $vt;

    /**
     * @ORM\OneToOne(targetEntity="Application\Entity\Order") 
     * @ORM\JoinColumn(name="doc_id", referencedColumnName="id")
     */
    private $order;

    /**
     * @ORM\OneToOne(targetEntity="Stock\Entity\St") 
     * @ORM\JoinColumn(name="doc_id", referencedColumnName="id")
     */
    private $st;

    /**
     * @ORM\OneToOne(targetEntity="Stock\Entity\Vtp") 
     * @ORM\JoinColumn(name="doc_id", referencedColumnName="id")
     */
    private $vtp;

    /**
     * @ORM\OneToOne(targetEntity="Stock\Entity\Revise") 
     * @ORM\JoinColumn(name="doc_id", referencedColumnName="id")
     */
    private $revise;

    public function __construct() {
    }
   
    public function getId() 
    {
        return $this->id;
    }

    public function getDocType() 
    {
        return $this->docType;
    }

    public function setDocType($docType) 
    {
        $this->docType = $docType;
    }     

    public function getDocId() 
    {
        return $this->docId;
    }

    public function setDocId($docId) 
    {
        $this->docId = $docId;
    }     

    public function getDocStamp() 
    {
        return round($this->docStamp, 3);
    }

    public function setDocStamp($docStamp) 
    {
        $this->docStamp = $docStamp;
    }     

    /**
     * Returns the date of oper.
     * @return string     
     */
    public function getDateOper() 
    {
        return $this->dateOper;
    }
    
    /**
     * Returns the date of oper.
     * @return string     
     */
    public function getDateVar() 
    {
        return $this->dateOper;
    }
    
    /**
     * Sets the date when oper.
     * @param date $dateOper     
     */
    public function setDateOper($dateOper) 
    {
        $this->dateOper = $dateOper;
    }         
    
    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusList() 
    {
        return [
            self::STATUS_ACTIVE => 'Активный',
            self::STATUS_RETIRED => 'Удален',
            self::STATUS_COMMISSION => 'На комиссии',
            self::STATUS_TAKE_NO => 'Не проведено',
        ];
    }    
    
    /**
     * return Ot
     */
    public function getOt()
    {
        if ($this->docType == Movement::DOC_OT){
            return $this->ot;
        }
        
        return;
    }
    
    /**
     * return Ptu
     */
    public function getPtu()
    {
        if ($this->docType == Movement::DOC_PTU){
            return $this->ptu;
        }
        
        return;
    }

    /**
     * return Pt
     */
    public function getPt()
    {
        if ($this->docType == Movement::DOC_PT){
            return $this->pt;
        }
        
        return;
    }

    /**
     * return Order
     */
    public function getOrder()
    {
        if ($this->docType == Movement::DOC_ORDER){
            return $this->order;
        }
        
        return;
    }

    /**
     * return Vt
     */
    public function getVt()
    {
        if ($this->docType == Movement::DOC_VT){
            return $this->vt;
        }
        
        return;
    }

    /**
     * return St
     */
    public function getSt()
    {
        if ($this->docType == Movement::DOC_ST){
            return $this->st;
        }
        
        return;
    }

    /**
     * return Vtp
     */
    public function getVtp()
    {
        if ($this->docType == Movement::DOC_VTP){
            return $this->vtp;
        }
        
        return;
    }

    /**
     * return Revise
     */
    public function getRevise()
    {
        if ($this->docType == Movement::DOC_REVISE){
            return $this->revise;
        }
        
        return;
    }

    /**
     * Представление документа
     * @param integer $docType
     * @param integer $docId
     * @return string
     */
    public static function getDocLink($docType, $docId)
    {
        switch($docType){
            case Movement::DOC_ORDER:
                return "<a href='/order/intro/{$docId}' target='_blank'>Заказ №{$docId}</a>";
            case Movement::DOC_OT:
                return "<a href='#' class='ot-modal-show' modal-url='/ot/edit-form/{$docId}'>Оприходование №{$docId}</a>";
            case Movement::DOC_PT:
                return "<a href='#' class='pt-modal-show' modal-url='/pt/edit-form/{$docId}'>Перемещение №{$docId}</a>";
            case Movement::DOC_PTU:
                return "<a href='#' class='ptu-modal-show' modal-url='/ptu/edit-form/{$docId}'>Поступление №{$docId}</a>";
            case Movement::DOC_ST:
                return "<a href='#' class='st-modal-show' modal-url='/st/edit-form/{$docId}'>Списание №{$docId}</a>";
            case Movement::DOC_VT:
                return "<a href='#' class='vt-modal-show' modal-url='/vt/edit-form/{$docId}'>Возврат покупателя №{$docId}</a>";
            case Movement::DOC_VTP:
                return "<a href='#' class='vtp-modal-show' modal-url='/vtp/edit-form/{$docId}'>Возврат поставщику №{$docId}</a>";
            case Movement::DOC_REVISE:
                return "Корректировка №{$docId}";
            case Movement::DOC_CASH:
                return "Оплата №{$docId}";
        }
        return;
    }
    
    public function getDoc()
    {
        return $this->getDocLink($this->docType, $this->docId);
    }
}
