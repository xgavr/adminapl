<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\Contact;
use Application\Entity\ContactCar;
use Application\Entity\Courier;
use Application\Entity\Shipping;
use ApiMarketPlace\Entity\MarketplaceUpdate;
use Stock\Entity\Vt;
use Admin\Filter\ClickFilter;


/**
 * Description of App
 * @ORM\Entity(repositoryClass="\Application\Repository\OrderRepository")
 * @ORM\Table(name="orders")
 * @author Daddy
 */
class Order {
    
    // Константы.
    const STATUS_NEW    = 10; // Новый.
    const STATUS_PROCESSED   = 20; // Обработан.
    const STATUS_CONFIRMED   = 30; // Подтвержден.
    const STATUS_DELIVERY   = 40; // Доставка.
    const STATUS_SHIPPED   = 50; // Отгружен.
    const STATUS_CANCELED  = -10; // Отменен.
    const STATUS_UNKNOWN  = -100; // Неизвестно.
        
    const MODE_MAN    = 1; // Звонок
    const MODE_VIN    = 2; // Запрос по вин
    const MODE_ORDER  = 3; // Заказ с сайта
    const MODE_FAST  = 4; // Быстрый заказ
    const MODE_INNER  = 5; // Внутренний заказ
        
    const PRINT_FOLDER         = './data/template/order'; 
    const TEMPLATE_TORG12      = './data/template/torg-12.xls';
    const TEMPLATE_BILL        = './data/template/bill.xls';
    const STAMP_IMG            = '../data/template/stamp.png';
//    const STAMP_IMG            = '/img/stamp.png';
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="apl_id")   
     */
    protected $aplId;

    /**
     * @ORM\Column(name="geo")   
     */
    protected $geo;

    /**
     * Для печати
     * @ORM\Column(name="invoice_info")  
     */
    protected $invoiceInfo;    

    /**
     * 
     * @ORM\Column(name="info")  
     */
    protected $info;    

    /**
     * 
     * @ORM\Column(name="address")  
     */
    protected $address;    

    /**
     * Тариф
     * @ORM\Column(name="shipment_rate")  
     */
    protected $shipmentRate;    

    /**
     * Км
     * @ORM\Column(name="shipment_distance")  
     */
    protected $shipmentDistance;    

    /**
     * Доп тариф
     * @ORM\Column(name="shipment_add_rate")  
     */
    protected $shipmentAddRate;    

    /**
     * Всего за доставку
     * @ORM\Column(name="shipment_total")  
     */
    protected $shipmentTotal;    

    /**
     * Накладная ТК
     * @ORM\Column(name="track_number")  
     */
    protected $trackNumber;    

    /**
     * Дата заказа
     * @ORM\Column(name="date_oper")  
     */
    protected $dateOper;    

    /**
     * Дата доставки/отгрузки
     * @ORM\Column(name="date_shipment")  
     */
    protected $dateShipment;    

    /**
     * Дата модификации
     * @ORM\Column(name="date_mod")  
     */
    protected $dateMod;    
    
    /**
     * Дата создания
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;    
    
    /**
     * @ORM\Column(name="total")  
     */
    protected $total;    
        
    /**
     * @ORM\Column(name="status")  
     */
    protected $status;    

    /**
     * @ORM\Column(name="mode")  
     */
    protected $mode;    

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\ContactCar", inversedBy="orders") 
     * @ORM\JoinColumn(name="contact_car_id", referencedColumnName="id")
     */
    protected $contactCar;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Courier", inversedBy="orders") 
     * @ORM\JoinColumn(name="courier_id", referencedColumnName="id")
     */
    protected $courier;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Shipping", inversedBy="orders") 
     * @ORM\JoinColumn(name="shipping_id", referencedColumnName="id")
     */
    protected $shipping;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Contact", inversedBy="orders") 
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    protected $contact;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="orders") 
     * @ORM\JoinColumn(name="legal_id", referencedColumnName="id")
     */
    protected $legal;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\BankAccount", inversedBy="orders") 
     * @ORM\JoinColumn(name="bank_account_id", referencedColumnName="id")
     */
    protected $bankAccount;
    
    /**
     * Грузополучатель
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="orders") 
     * @ORM\JoinColumn(name="recipient_id", referencedColumnName="id")
     */
    protected $recipient;
    
    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="orders") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
        
    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="orders") 
     * @ORM\JoinColumn(name="skiper_id", referencedColumnName="id")
     */
    private $skiper;
        
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="orders") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;
        
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="orders") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;
        
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Bid", mappedBy="order")
    * @ORM\JoinColumn(name="id", referencedColumnName="order_id")
     */
    private $bids;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Selection", mappedBy="order")
    * @ORM\JoinColumn(name="id", referencedColumnName="order_id")
     */
    private $selections;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Comment", mappedBy="order")
    * @ORM\JoinColumn(name="id", referencedColumnName="order_id")
     */
    private $comments;
    
    /**
    * @ORM\OneToMany(targetEntity="ApiMarketPlace\Entity\MarketplaceUpdate", mappedBy="order")
    * @ORM\JoinColumn(name="id", referencedColumnName="order_id")
     */
    private $marketplaceUpdates;
    
    /**
    * @ORM\OneToMany(targetEntity="Stock\Entity\Vt", mappedBy="order")
    * @ORM\JoinColumn(name="id", referencedColumnName="order_id")
     */
    private $vt;
    
    private $ciphering = "AES-128-CTR"; //Метод шифрования
    
    private $iv = "1234567891011121"; // Non-NULL Initialization Vector for encryption
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->bids = new ArrayCollection();
        $this->selections = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->marketplaceUpdates = new ArrayCollection();
        $this->vt = new ArrayCollection();
    }
    
    protected function _encrypt($unencryptedText, $passphrase)
    { 
        $options = 0; 
        //$iv_length = openssl_cipher_iv_length($this->ciphering);
        
        return openssl_encrypt($unencryptedText, $this->ciphering, $passphrase, $options, $this->iv);
    }

    protected function _decrypt($unencryptedText, $passphrase) 
    {
        $options = 0; 
        return openssl_decrypt($unencryptedText, $this->ciphering, $passphrase, $options, $this->iv);
    }	
    
    public function getId() 
    {
        return $this->id;
    }
    
    public function getDocNo()
    {
        if ($this->aplId){
            return $this->aplId;
        }
        return $this->id;        
    }

    public function getIdLink() 
    {
        return "<a href='/order/view/{$this->id}' target=_blank>{$this->id}</a>";
    }

    /**
     * Returns the namefile.
     * @param string $docName
     * @return string     
     */
    public function getPrintName($ext, $docName = 'ТОРГ12') 
    {
        return self::PRINT_FOLDER.'/'.$this->getDocPresent($docName).'.'.strtolower($ext);
    }

    /**
     * Returns the present of doc.
     * @param string $docName
     * @return string     
     */
    public function getDocPresent($docName = 'ТОРГ12') 
    {
        $docDate = date('d-m-Y', strtotime($this->getDocDate()));
        $docNo = $this->getDocNo();
        return "$docName №{$docNo} от {$docDate}";
    }

    public function getLogKey() 
    {
        return 'ord:'.$this->id;
    }
    
    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getAplId() 
    {
        return $this->aplId;
    }
    
    public function getAplTurboId($passphrase)
    {
        return "https://autopartslist.ru/index/turbo?order=".base64_encode($this->_encrypt($this->aplId, $passphrase));
    }

    public function getAplTurboClick($passphrase)
    {
        $filter = new ClickFilter();
        return $filter->filter($this->getAplTurboId($passphrase));
    }
    
    /**
     * Сыылка на платежную форму
     * @param float $prepay
     * @return string
     */
    public function getAplPaymentLink($prepay = 0)
    {
        $sum = ($prepay) ? $prepay:$this->total;
        return 'https://autopartslist.ru/payments/sb-register/amount/'.$sum.'/id/'.$this->aplId;
    }

    public function getAplPaymentLinkClick($prepay = 0)
    {
        $filter = new ClickFilter();
        return $filter->filter($this->getAplPaymentLink($prepay));
    }

    public function getAplIdLink() 
    {
        return "<a href='https://autopartslist.ru/admin/orders/view/id/{$this->aplId}' target=_blank>{$this->aplId}</a>";
    }

    public function setAplId($aplId) 
    {
        $this->aplId = $aplId;
    }     
    
    public function getGeo() 
    {
        return $this->geo;
    }

    public function setGeo($geo) 
    {
        $this->geo = $geo;
    }     
    
    public function getInvoiceInfo() 
    {
        return $this->invoiceInfo;
    }

    public function setInvoiceInfo($invoiceInfo) 
    {
        $this->invoiceInfo = $invoiceInfo;
    }     
    
    public function getInfo() 
    {
        return $this->info;
    }

    public function setInfo($info) 
    {
        $this->info = $info;
    }     
    
    public function getAddress() 
    {
        return $this->address;
    }

    public function setAddress($address) 
    {
        $this->address = $address;
    }     
    
    public function getShipmentRate() 
    {
        return $this->shipmentRate;
    }

    public function setShipmentRate($shipmentRate) 
    {
        $this->shipmentRate = $shipmentRate;
    }     
    
    public function getShipmentDistance() 
    {
        return $this->shipmentDistance;
    }

    public function setShipmentDistance($shipmentDistance) 
    {
        $this->shipmentDistance = $shipmentDistance;
    }     
    
    public function getShipmentAddRate() 
    {
        return $this->shipmentAddRate;
    }

    public function setShipmetAddRate($shipmentAddRate) 
    {
        $this->shipmentAddRate = $shipmentAddRate;
    }     
    
    public function getShipmentTotal() 
    {
        return $this->shipmentTotal;
    }

    public function setShipmetTotal($shipmentTotal) 
    {
        $this->shipmentTotal = $shipmentTotal;
    }     
    
    public function getTrackNumber() 
    {
        return $this->trackNumber;
    }

    public function setTrackNumber($trackNumber) 
    {
        $this->trackNumber = $trackNumber;
    }     
    
    public function getDateOper() 
    {
        return $this->dateOper;
    }

    public function setDateOper($dateOper) 
    {
        $this->dateOper = $dateOper;
    }     

    public function getDateShipment() 
    {
        return $this->dateShipment;
    }

    public function setDateShipment($dateShipment) 
    {
        $this->dateShipment = $dateShipment;
    }     

    public function getDateMod() 
    {
        return $this->dateMod;
    }

    public function setDateMod($dateMod) 
    {
        $this->dateMod = $dateMod;
    }     

    public function getDateCreated() 
    {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
    }     
    
    public function getDocDate()
    {
        if ($this->dateOper){
            return $this->dateOper;
        }
        return $this->dateCreated;
    }

    public function getTotal() 
    {
        return $this->total;
    }

    public function getPrepay() 
    {
        return round($this->total/10, -2);
    }

    public function setTotal($total) 
    {
        $this->total = $total;
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
            self::STATUS_NEW => 'Новый',
            self::STATUS_PROCESSED => 'Обработан',
            self::STATUS_CONFIRMED => 'Подтвержден',
            self::STATUS_DELIVERY => 'Доставка',
            self::STATUS_SHIPPED => 'Отгружен',
            self::STATUS_CANCELED => 'Отменен',
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
     * Returns mode.
     * @return int     
     */
    public function getMode() 
    {
        return $this->mode;
    }

    /**
     * Returns possible modes as array.
     * @return array
     */
    public static function getModesList() 
    {
        return [
            self::MODE_MAN => 'Звонок',
            self::MODE_ORDER => 'Заказ с сайта',
            self::MODE_VIN => 'Запрос по VIN',
            self::MODE_FAST => 'Быстрый заказ',
            self::MODE_INNER => 'Внутренний заказ',
        ];
    }    
    
    /**
     * Returns user mode as string.
     * @return string
     */
    public function getModeAsString()
    {
        $list = self::getModesList();
        if (isset($list[$this->mode]))
            return $list[$this->mode];
        
        return 'Unknown';
    }    
        
    /**
     * Sets mode.
     * @param int $mode     
     */
    public function setMode($mode) 
    {
        $this->mode = $mode;
    }   

    /*
     * Возвращает связанный contact.
     * @return Contact
     */
    
    public function getContact() 
    {
        return $this->contact;
    }

    /**
     * Задает связанный contact.
     * @param Contact $contact
     */    
    public function setContact($contact) 
    {
        $this->contact = $contact;
        $contact->addOrder($this);
    }     
    
    /*
     * Возвращает связанный legal.
     * @return \Company\Entity\Legal
     */    
    public function getLegal() 
    {
        return $this->legal;
    }

    /**
     * Задает связанный legal.
     * @param \Company\Entity\Legal $legal
     */    
    public function setLegal($legal) 
    {
        $this->legal = $legal;
    }         
 
    /*
     * Возвращает связанный bank account.
     * @return \Company\Entity\BankAccount
     */    
    public function getBankAccount() 
    {
        return $this->bankAccount;
    }

    /**
     * Задает связанный bank account.
     * @param \Company\Entity\BankAccount $bankAccount
     */    
    public function setBankAccount($bankAccount) 
    {
        $this->bankAccount = $bankAccount;
    }         
 
    /*
     * Возвращает связанный recipient.
     * @return \Company\Entity\Legal
     */    
    public function getRecipient() 
    {
        return $this->recipient;
    }

    /**
     * Задает связанный recipient.
     * @param \Company\Entity\Legal $recipient
     */    
    public function setRecipient($recipient) 
    {
        $this->recipient = $recipient;
    }         
     
    /*
     * Возвращает связанный user.
     * @return \User\Entity\User
     */
    
    public function getUser() 
    {
        return $this->user;
    }

    /**
     * Задает связанный user.
     * @param \User\Entity\User $user
     */    
    public function setUser($user) 
    {
        $this->user = $user;
    }         
 
    /*
     * Возвращает связанный skiper.
     * @return \User\Entity\User
     */
    
    public function getSkiper() 
    {
        return $this->skiper;
    }

    public function getSkiperPhone() 
    {
        if ($this->skiper){
            if ($this->skiper->getLegalContact()->getPhone()){
                return $this->skiper->getLegalContact()->getPhone()->getName();
            }
        }
        return;
    }

    public function getSkiperName() 
    {
        if ($this->skiper){
            return $this->skiper->getFullName();
        }
        return;
    }

    /**
     * Задает связанный skiper.
     * @param \User\Entity\User $skiper
     */    
    public function setSkiper($skiper) 
    {
        $this->skiper = $skiper;
    }         
 
    /*
     * Возвращает связанный office.
     * @return \Company\Entity\Office
     */    
    public function getOffice() 
    {
        return $this->office;
    }

    /**
     * Задает связанный user.
     * @param \Company\Entity\Office $office
     */    
    public function setOffice($office) 
    {
        $this->office = $office;
    }         
 
    /*
     * Возвращает связанный company.
     * @return \Company\Entity\Legal
     */    
    public function getCompany() 
    {
        return $this->company;
    }

    /**
     * Задает связанный company.
     * @param \Company\Entity\Legal $company
     */    
    public function setCompany($company) 
    {
        $this->company = $company;
    }         
 
    /*
     * Возвращает связанный contactCar.
     * @return ContactCar
     */    
    public function getContactCar() 
    {
        return $this->contactCar;
    }

    /*
     * Возвращает VIN.
     * @return string
     */    
    public function getContactCarVin() 
    {
        if ($this->contactCar){
            return $this->contactCar->getVin();
        }
        return;
    }

    /**
     * Задает связанный contactCar.
     * @param ContactCar $contactCar
     */    
    public function setContactCar($contactCar) 
    {
        $this->contactCar = $contactCar;
    }         
 
    /*
     * Возвращает связанный courier.
     * @return Courier
     */    
    
    public function getCourier() 
    {
        return $this->courier;
    }

    public function getCourierName() 
    {
        if ($this->courier){
            return $this->courier->getName();
        }
        return;
    }

    public function getCourierNameLink() 
    {
        if ($this->courier){
            return $this->courier->getNameLink();
        }
        return;
    }

    public function getTrackLink() 
    {
        if ($this->courier && $this->trackNumber){
            return $this->courier->getTrackLink($this->trackNumber);
        }
        return;
    }

    /**
     * Задает связанный counrier.
     * @param Courier $courier
     */    
    public function setCourier($courier) 
    {
        $this->courier = $courier;
    }         
 
    /*
     * Возвращает связанный shipping.
     * @return Shipping
     */    
    
    public function getShipping() 
    {
        return $this->shipping;
    }

    /**
     * Задает связанный shipping.
     * @param Shipping $shipping
     */    
    public function setShipping($shipping) 
    {
        $this->shipping = $shipping;
    }         
 
    /**
     * Returns the array of bid assigned to this.
     * @return array
     */
    public function getBids()
    {
        return $this->bids;
    }
    
    /**
     * Для вставки в письмо заголовок таблицы
     * @param bool $showCode //показывать артикли
     * @return string
     */
    private function getBidsAsHtmlHeader($showCode = false)
    {
        $result .= "<tr>";

        $result .= "<th class='article-code' align='center'>";
        if ($showCode){
            $result .= "Артикул";
        } else {
            $result .= "N";
        }   
        $result .= "</th>";

        $result .= "<th align='center'>";
        $result .= "Производитель";
        $result .= "</th>";

        $result .= "<th align='center'>";
        $result .= "Наименование";
        $result .= "</th>";

        $result .= "<th align='center'>";
        $result .= "Цена";
        $result .= "</th>";

        $result .= "<th align='center'>";
        $result .= "Количество";
        $result .= "</th>";

        $result .= "<th align='center'>";
        $result .= "Сумма";
        $result .= "</th>";

        $result .= "</tr>";
        
        return $result;
    }

    /**
     * Для вставки в письмо тело таблицы
     * @param bool $showCode //показывать артикли
     * @return string
     */
    private function getBidsAsHtmlBody($showCode = false)
    {
        $result = '';
        $i = 0;
        foreach ($this->bids as $bid){
            $i++;
            
            $result .= "<tr>";

            $result .= "<td class='article-code'>";
            if ($showCode){
                $result .= $bid->getGood()->getAplIdLinkCode();
            } else {
                $result .= $i;
            }    
            $result .= "</td>";

            $result .= "<td>";
            $result .= $bid->getGood()->getProducer()->getName();
            $result .= "</td>";

            $result .= "<td>";
            $result .= ($bid->getDisplayName()) ? $bid->getDisplayName():$bid->getGood()->getNameShort();
            $result .= "</td>";

            $result .= "<td align='right'>";
            $result .= $bid->getPrice();
            $result .= "</td>";

            $result .= "<td align='right'>";
            $result .= $bid->getNum();
            $result .= "</td>";

            $result .= "<td align='right'>";
            $result .= $bid->getTotal();
            $result .= "</td>";

            $result .= "</tr>";
        }
        
        return $result;
    }

    /**
     * Для вставки в письмо подвал таблицы
     * @return string
     */
    private function getBidsAsHtmlFooter()
    {
        $result .= "<tr>";

        $result .= "<td colspan='5' align='right'>";
        $result .= "<strong>Итого:</strong>";
        $result .= "</td>";

        $result .= "<td align='right'><strong>";
        $result .= $this->total;
        $result .= "</strong></td>";

        $result .= "</tr>";
        
        $result .= "<tr>";

        $result .= "<td colspan='5' align='right'>";
        $result .= "В том числе НДС:";
        $result .= "</td>";

        $result .= "<td align='right'>";
        $result .= "Без НДС";
        $result .= "</td>";

        $result .= "</tr>";

        return $result;
    }

    /**
     * Для вставки в письмо
     * @param bool $showCode //показывать артикли
     * @return string
     */
    public function getBidsAsHtml($showCode = false)
    {
        $result = "<table width='50%' border='1'>";
        $result .= $this->getBidsAsHtmlHeader($showCode);
        $result .= $this->getBidsAsHtmlBody($showCode);
        $result .= $this->getBidsAsHtmlFooter();
        $result .= "</table>";
        
        return $result;
    }
        
    /**
     * Assigns.
     * @param Application\Entity\Bid $bid
     */
    public function addBid($bid)
    {
        $this->bids[] = $bid;
    }
            
    /**
     * Returns the array of selection assigned to this.
     * @return array
     */
    public function getSelections()
    {
        return $this->selections;
    }
        
    /**
     * Assigns.
     * @param \Application\Entity\Selection $selection
     */
    public function addSelection($selection)
    {
        $this->selections[] = $selection;
    }
            
    /**
     * Returns the array of comments assigned to this.
     * @return array
     */
    public function getComments()
    {
        return $this->comments;
    }
        
    /**
     * Assigns.
     * @param \Application\Entity\Comment $comment
     */
    public function addComment($comment)
    {
        $this->comments[] = $comment;
    }
        
    /**
     * Returns the array of vt assigned to this.
     * @return array
     */
    public function getVt()
    {
        return $this->vt;
    }
        
    /**
     * Assigns.
     * @param Vt $vt
     */
    public function addVt($vt)
    {
        $this->vt[] = $vt;
    }
                
    /**
     * Returns the array of marketplaceUpdates assigned to this.
     * @return array
     */
    public function getMarketplaceUpdates()
    {
        return $this->marketplaceUpdates;
    }
        
    /**
     * Assigns.
     * @param MarketplaceUpdate $marketplaceupdate
     */
    public function addMarketplaceUpdate($marketplaceUpdate)
    {
        $this->marketplaceUpdates[] = $marketplaceUpdate;
    }
           
    private function _getContactEmail()
    {
        if ($this->contact){
            if ($this->contact->getEmail()){
                return  $this->contact->getEmail()->getName();
            }            
        }
        return;    
    }
    
    private function _getContactPhone()
    {
        if ($this->contact){
            if ($this->contact->getPhone()){
                return  $this->contact->getPhone()->getName();
            }            
        }
        return;    
    }
    
    private function _getContactCarMake()
    {
        if ($this->contactCar){
            if ($this->contactCar->getMake()){
                return  $this->contactCar->getMake()->getName();
            }            
        }
        return;    
    }
    
    /**
     * Лог
     * @return array
     */
    public function toArray()
    {
        return [
            'orderId' => $this->getId(),
            'aplId' => $this->getAplId(),
            'phone' => $this->_getContactPhone(),
            'email' => $this->_getContactEmail(),
            'name' => ($this->getContact()) ? $this->getContact()->getName():null,
            'vin' => ($this->getContactCar()) ? $this->getContactCar()->getVin():null,
            'make' => $this->_getContactCarMake(),
            'makeComment' => ($this->getContactCar()) ? $this->getContactCar()->getComment():null,
            'address' => $this->getAddress(),
            'company' => $this->getCompany()->getId(),
            'contact' => $this->getContact()->getId(),
            'operDate' => (string) $this->getDateOper(),
            'dateShipment' => date('Y-m-d', strtotime($this->getDateShipment())),
            'timeShipment' => date('H', strtotime($this->getDateShipment())),
            'courier' => ($this->getCourier()) ? $this->getCourier()->getId():null,
            'office' => $this->getOffice()->getId(),
            'status' => $this->getStatus(),
            'info' => $this->getInfo(),
            'invoiceInfo' => $this->getInvoiceInfo(),
            'mode' =>$this->getMode(),
            'trackNumber' => $this->getTrackNumber(),
            'user' => ($this->getUser()) ? $this->getUser()->getId():null,
            'skiper' => ($this->getSkiper()) ? $this->getSkiper()->getId():null,
            'shipping' => ($this->getShipping()) ? $this->getShipping()->getId():null,
            'shipmentTotal' => $this->getShipmentTotal(),
            'shipmentRate' => $this->getShipmentRate(),
            'rate' => ($this->getShipping()) ? $this->getShipping()->getRate():null,
            'shipmentRate1' => ($this->getShipping()) ? $this->getShipping()->getRateTrip1():null,
            'shipmentRate2' => ($this->getShipping()) ? $this->getShipping()->getRateTrip2():null,
            'shippingLimit1' => $this->getOffice()->getShippingLimit1(),
            'shippingLimit2' => $this->getOffice()->getShippingLimit2(),
            'shipmentDistance' => $this->getShipmentDistance(),
            'rateDistance' => ($this->getShipping()) ? $this->getShipping()->getRateDistance():null,
            'legal' => ($this->getLegal()) ? $this->getLegal()->getId():null,
            'legalName' => ($this->getLegal()) ? $this->getLegal()->getName():null,
            'legalInn' => ($this->getLegal()) ? $this->getLegal()->getInn():null,
            'legalKpp' => ($this->getLegal()) ? $this->getLegal()->getKpp():null,
            'legalOgrn' => ($this->getLegal()) ? $this->getLegal()->getOgrn():null,
            'legalAddress' => ($this->getLegal()) ? $this->getLegal()->getAddress():null,
            'recipient' => ($this->getRecipient()) ? $this->getRecipient()->getId():null,
            'recipientName' => ($this->getRecipient()) ? $this->getRecipient()->getName():null,
            'recipientInn' => ($this->getRecipient()) ? $this->getRecipient()->getInn():null,
            'recipientKpp' => ($this->getRecipient()) ? $this->getRecipient()->getKpp():null,
            'recipientOgrn' => ($this->getRecipient()) ? $this->getRecipient()->getOgrn():null,
            'recipientAddress' => ($this->getRecipient()) ? $this->getRecipient()->getAddress():null,
            'bankAccount' => ($this->getBankAccount()) ? $this->getBankAccount()->getId():null,
            'bankName' => ($this->getBankAccount()) ? $this->getBankAccount()->getName():null,
            'rs' => ($this->getBankAccount()) ? $this->getBankAccount()->getRs():null,
            'ks' => ($this->getBankAccount()) ? $this->getBankAccount()->getKs():null,
            'bik' => ($this->getBankAccount()) ? $this->getBankAccount()->getBik():null,
            'bankCity' => ($this->getBankAccount()) ? $this->getBankAccount()->getCity():null,
            'goods' => [],
        ];
    }    

    /**
     * Лог
     * @return array
     */
    public function toLog()
    {
        return [
            'amount' => $this->getTotal(),
            'aplId' => $this->getAplId(),
            'contact' => $this->getContact()->getId(),
            'operDate' => (string) $this->getDateOper(),
            'shipmentDate' => (string) $this->getDateShipment(),
            'aplId' => $this->getAplId(),
            'info' => $this->getInfo(),
            'office' => $this->getOffice()->getId(),
            'status' => $this->getStatus(),
            'goods' => [],
        ];
    }        
}
