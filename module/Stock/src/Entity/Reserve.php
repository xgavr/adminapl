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
use Application\Entity\Goods;
use User\Entity\User;


/**
 * Description of Mutual
 * @ORM\Entity(repositoryClass="\Stock\Repository\MovementRepository")
 * @ORM\Table(name="reserve")
 * @author Daddy
 */
class Reserve {
    
    const STATUS_RESERVE      = 1; // резерв.
    const STATUS_DELIVERY     = 2; // доставка.
    const STATUS_VOZVRAT      = 3; // возврат.
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="rest")   
     */
    protected $rest;
    
    /**
     * @ORM\Column(name="status")   
     */
    protected $status;
        
    /**
     * @ORM\Column(name="doc_key")   
     */
    protected $docKey;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="reserves") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    private $good;
            
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="reserves") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="reserves") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;
    
    /**
     * @ORM\ManyToOne(targetEntity="Stock\Entity\Register", inversedBy="reserves") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    public function __construct() {
    }
   
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getRest() 
    {
        return $this->rest;
    }

    public function setRest($rest) 
    {
        $this->rest = $rest;
    }     

    public function getDockey() 
    {
        return $this->docKey;
    }

    public function setDocKey($dockey) 
    {
        $this->docKey = $dockey;
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
            self::STATUS_RESERVE => 'Резерв',
            self::STATUS_DELIVERY => 'Доставка',
            self::STATUS_VOZVRAT => 'Возврат',
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
     * Returns the good.
     * @return Goods     
     */
    public function getGood() 
    {
        return $this->good;
    }

    /**
     * Returns the office.
     * @return Office     
     */
    public function getOffice() 
    {
        return $this->office;
    }

    /**
     * Returns the company.
     * @return Legal     
     */
    public function getCompany() 
    {
        return $this->company;
    }

    /**
     * Returns the user.
     * @return User     
     */
    public function getUser() 
    {
        return $this->user;
    }

}
