<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Entity;

use Doctrine\ORM\Mapping as ORM;
use Company\Entity\Legal;
use Company\Entity\Office;
use Application\Entity\Contact;
use User\Entity\User;
use Laminas\Json\Encoder;


/**
 * Description of Comiss
 * @ORM\Entity(repositoryClass="\Stock\Repository\RegisterRepository")
 * @ORM\Table(name="register")
 * @author Daddy
 */
class Register {
    
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

    /**
     * Returns the date of oper.
     * @return string     
     */
    public function getDateOper() 
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
     * Представление документа
     * @param integer $docType
     * @param integer $docId
     * @return string
     */
    public static function getDocLink($docType, $docId)
    {
        switch($docType){
            case Movement::DOC_ORDER:
                return "<a href='/order/intro/{$docId}'>Заказ №{$docId}</a>";
            case Movement::DOC_OT:
                return "<a href='#' class='ot-modal-show' modal-url='/ot/edit-form/{$docId}'>Оприходование №{$docId}</a>";
            case Movement::DOC_PT:
                return "<a href='#' class='pt-modal-show' modal-url='/pt/edit-form/{$docId}'>Перемещение №{$docId}</a>";
            case Movement::DOC_PTU:
                return "<a href='#' class='ptu-modal-show' modal-url='/ptu/edit-form/{$docId}'>Поступление №{$docId}</a>";
            case Movement::DOC_ST:
                return "<a href='#' class='st-modal-show' modal-url='/st/edit-form/{$docId}'>Списание №{$docId}</a>";
            case Movement::DOC_VT:
                return "<a href='#' class='vt-modal-show' modal-url='/vt/edit-form/{$docId}'>Взврат покупателя №{$docId}</a>";
            case Movement::DOC_VTP:
                return "<a href='#' class='vtp-modal-show' modal-url='/vtp/edit-form/{$docId}'>Взврат поставщику №{$docId}</a>";
        }
        return;
    }
    
    public function getDoc()
    {
        return $this->getDocLink($this->docType, $this->docId);
    }
}
