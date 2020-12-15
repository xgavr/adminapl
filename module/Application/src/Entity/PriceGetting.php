<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Pricelist
 * @ORM\Entity(repositoryClass="\Application\Repository\SupplierRepository")
 * @ORM\Table(name="price_gettings")
 * @author Daddy
 */
class PriceGetting {
    
     // Supplier status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    const STATUS_FILENAME_NONE       = 1; //Игнорировать filename, принимать файлы с любым наименованием
    const STATUS_FILENAME_IN         = 2; //Принимать файлы, содержащие filename
    const STATUS_FILENAME_EX         = 3; //Исключить файлы с наименованием, содержащие filename
    
    const ORDER_PRICE_FILE_TO_APL       = 1; // Закачивать полученый прайс на сервер АПЛ.
    const NO_ORDER_PRICE_FILE_TO_APL    = 2; // Не закачивать полученый прайс на сервер АПЛ.
    
    const MAILBOX_CHECKED = 1; //Почтовый ящик проверен
    const MAILBOX_TO_CHECK = 2; //Почтовый ящик не проверен
    
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
     * @ORM\Column(name="ftp")   
     */
    protected $ftp;
    
    /**
     * @ORM\Column(name="ftp_dir")   
     */
    protected $ftpDir;
        
    /**
     * @ORM\Column(name="ftp_login")   
     */
    protected $ftpLogin;
    
    /**
     * @ORM\Column(name="ftp_password")   
     */
    protected $ftpPassword;
    
    /**
     * @ORM\Column(name="email")   
     */
    protected $email;
    
    /**
     * @ORM\Column(name="email_password")   
     */
    protected $emailPassword;
    
    /**
     * @ORM\Column(name="link")   
     */
    protected $link;
    
    /**
     * @ORM\Column(name="filename")  
     */
    protected $filename;    

    /**
     * @ORM\Column(name="status_filename")  
     */
    protected $statusFilename;    

    /**
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;    

    /**
     * @ORM\Column(name="status")  
     */
    protected $status;    
       
    /**
     * @ORM\Column(name="order_to_apl")  
     */
    protected $orderToApl;    
       
    /**
     * @ORM\Column(name="mailbox_check")  
     */
    protected $mailBoxCheck = self::MAILBOX_TO_CHECK;    
       
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Supplier", inversedBy="priceGettings") 
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    private $supplier;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Supplier", inversedBy="priceGettingSupplier") 
     * @ORM\JoinColumn(name="price_supplier", referencedColumnName="id")
     */
    private $priceSupplier;    

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

    public function getFtp() 
    {
        return $this->ftp;
    }

    public function setFtp($ftp) 
    {
        $this->ftp = $ftp;
    }     

    public function getFtpDir() 
    {
        return $this->ftpDir;
    }

    public function setFtpDir($ftpDir) 
    {
        $this->ftpDir = $ftpDir;
    }     


    public function getFtpLogin() 
    {
        return $this->ftpLogin;
    }

    public function setFtpLogin($ftpLogin) 
    {
        $this->ftpLogin = $ftpLogin;
    }     

    public function getFtpPassword() 
    {
        return $this->ftpPassword;
    }

    public function setFtpPassword($ftpPassword) 
    {
        $this->ftpPassword = $ftpPassword;
    }     

    public function getEmail() 
    {
        return $this->email;
    }

    public function setEmail($email) 
    {
        $this->email = $email;
    }     

    public function getEmailPassword() 
    {
        return $this->emailPassword;
    }

    public function setEmailPassword($emailPassword) 
    {
        $this->emailPassword = $emailPassword;
    }     

    public function getLink() 
    {
        return $this->link;
    }

    public function setLink($link) 
    {
        $this->link = $link;
    }     

    public function getFilename() 
    {
        return $this->filename;
    }

    public function setFilename($filename) 
    {
        $this->filename = $filename;
    }     

    public function getDateCreated() 
    {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
    }     
    
    public function getPriceSupplier() 
    {
        return $this->priceSupplier;
    }

    public function setPriceSupplier($priceSupplier) 
    {
        $this->priceSupplier = $priceSupplier;
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
        if (isset($list[$this->status])) {
            return $list[$this->status];
        }

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
     * Returns status.
     * @return int     
     */
    public function getStatusFilename() 
    {
        return $this->statusFilename;
    }

    
    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusFilenameList() 
    {
        return [
            self::STATUS_FILENAME_NONE => 'Принимать файлы с любым наименованием',
            self::STATUS_FILENAME_IN => 'Принимать файлы с наименованием, содержащие строку ...',
            self::STATUS_FILENAME_EX => 'Исключить файлы с наименованием, сожержащие строку ...'
        ];
    }    
    
    /**
     * Returns status as string.
     * @return string
     */
    public function getStatusFilenameAsString()
    {
        $list = self::getStatusList();
        if (isset($list[$this->statusFilename]))
            return $list[$this->statusFilename];
        
        return 'Unknown';
    }    
    
    /*
     * Возврат статуса фильтра имени файлов
     */
    public function getStatusFilenameRuleAsString()
    {
        switch ($this->getStatusFilename()){
            case self::STATUS_FILENAME_IN :
                if ($this->getFilename()){
                    return "Принимаем файлы, содержащие в наименовании фразу <q>".$this->getFilename()."</q>";
                }
                break;
            case self::STATUS_FILENAME_EX :
                if ($this->getFilename()){
                    return "Принимаем файлы, не содержащие в наименовании фразу <q>".$this->getFilename()."</q>";
                }
                break;
            default: 
        }
        
        return 'Принимаем файлы с любым наименованием';
    }
    
    /**
     * Sets status.
     * @param int $statusFilename     
     */
    public function setStatusFilename($statusFilename) 
    {
        $this->statusFilename = $statusFilename;
    }   

    /**
     * Returns orderToApl.
     * @return int     
     */
    public function getOrderToApl() 
    {
        return $this->orderToApl;
    }

    
    /**
     * Returns possible orders as array.
     * @return array
     */
    public static function getOrderToAplList() 
    {
        return [
            self::ORDER_PRICE_FILE_TO_APL => 'Закачивать на сервер АПЛ',
            self::NO_ORDER_PRICE_FILE_TO_APL => 'Не закачивать на сервер АПЛ'
        ];
    }    
    
    /**
     * Returns user orders as string.
     * @return string
     */
    public function getOrderToAplAsString()
    {
        $list = self::getOrderToAplList();
        if (isset($list[$this->orderToApl]))
            return $list[$this->orderToApl];
        
        return 'Unknown';
    }    
    
    /**
     * Sets order.
     * @param int $order     
     */
    public function setOrderToApl($order) 
    {
        $this->orderToApl = $order;
    }   

    /**
     * Returns MailBoxCheck.
     * @return int     
     */
    public function getMailBoxCheck() 
    {
        return $this->mailBoxCheck;
    }

    
    /**
     * Returns possible mailBoxCheck as array.
     * @return array
     */
    public static function getMailBoxCheckList() 
    {
        return [
            self::MAILBOX_CHECKED => 'Почтовый ящик проверен',
            self::MAILBOX_TO_CHECK => 'Надо проверить почтовый ящик'
        ];
    }    
    
    /**
     * Returns mailBoxCheck as string.
     * @return string
     */
    public function getMailBoxCheckAsString()
    {
        $list = self::getMailBoxCheckList();
        if (isset($list[$this->mailBoxCheck])) {
            return $list[$this->mailBoxCheck];
        }

        return 'Unknown';
    }    
    
    /**
     * Sets mailBoxCheck.
     * @param int $mailBoxCheck     
     */
    public function setMailBoxCheck($mailBoxCheck) 
    {
        $this->mailBoxCheck = $mailBoxCheck;
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
        $supplier->addPriceGettings($this);
    }    
        
}
