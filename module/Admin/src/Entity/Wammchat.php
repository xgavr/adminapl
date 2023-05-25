<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Application\Entity\Order;
use Application\Entity\Comment;

/**
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Admin\Repository\SmsRepository")
 * @ORM\Table(name="wammchat")
 * @author Daddy
 */
class Wammchat {
    
    const STATUS_ACTIVE = 1; //новая запись
    const STATUS_OK = 2; //учтено
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="msg_id")  
     */
    protected $msgId;        

    /**
     * @ORM\Column(name="from_me")  
     */
    protected $fromMe;        

    /**
     * @ORM\Column(name="phone")  
     */
    protected $phone;        

    /**
     * @ORM\Column(name="chat_name")  
     */
    protected $chatName;        

    /**
     * @ORM\Column(name="tip_msg")  
     */
    protected $tipMsg;        

    /**
     * @ORM\Column(name="msg_text")  
     */
    protected $msgText;        

    /**
     * @ORM\Column(name="msg_link")  
     */
    protected $msgLink;        

    /**
     * @ORM\Column(name="date_ins")  
     */
    protected $dateIns;        

    /**
     * @ORM\Column(name="date_upd")  
     */
    protected $dateUpd;        

    /**
     * @ORM\Column(name="state")  
     */
    protected $state;        

    /**
     * @ORM\Column(name="status")  
     */
    protected $status;        

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Order", inversedBy="wammchats") 
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    protected $order;    
    
    /**
     * @ORM\OneToOne(targetEntity="Application\Entity\Comment", inversedBy="wammchat") 
     * @ORM\JoinColumn(name="comment_id", referencedColumnName="id")
     */
    private $comment;    

    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getMsgId()
    {
        return $this->msgId;
    }
    
    public function setMsgId($msgId) 
    {
        $this->msgId = $msgId;
    }     

    public function getFromMe()
    {
        return $this->fromMe;
    }
    
    public function getFromMeAsSting()
    {
        if ($this->fromMe){
            return $this->getStateAsString().'=>';
        }
        
        return $this->getStateAsString().'<=';
    }

    public function setFromMe($fromMe) 
    {
        $this->fromMe = $fromMe;
    }     

    public function getPhone() 
    {
        return $this->phone;
    }

    public function setPhone($phone) 
    {
        $this->phone = $phone;
    }     

    public function getChatName() 
    {
        return $this->chatName;
    }

    public function setChatName($chatName) 
    {
        $this->chatName = $chatName;
    }     

    public function getTipMsg() 
    {
        return $this->tipMsg;
    }

    public function setTipMsg($tipMsg) 
    {
        $this->tipMsg = $tipMsg;
    }     

    public function getMsgText() 
    {
        return $this->msgText;
    }

    public function getMsgTextAsHtml() 
    {
        if ($this->msgLink){
            return '<a href="'.$this->msgLink.'" target="_blank">'.$this->msgText.'</a>';
        }
        return $this->msgText;
    }

    public function setMsgText($msgText) 
    {
        $this->msgText = $msgText;
    }     

    public function getMsgLink() 
    {
        return $this->msgLink;
    }

    public function setMsgLink($msgLink) 
    {
        $this->msgLink = $msgLink;
    }     

    public function getDateIns() 
    {
        return $this->dateIns;
    }

    public function setDateIns($dateIns) 
    {
        $this->dateIns = $dateIns;
    }     

    public function getDateUpd() 
    {
        return $this->dateUpd;
    }

    public function setDateUpd($dateUpd) 
    {
        $this->dateUpd = $dateUpd;
    }     

    public function getState() 
    {
        return $this->state;
    }

    public function getStateAsString() 
    {
        switch ($this->state){
            case 'viewed': return 'W';
            case 'received': return 'w';
            case 'sent': return 'w';
            case 'delivered': return 'W';
            default: return '';     
        }
        
        return;
    }

    public function setState($state) 
    {
        $this->state = $state;
    }     

    public function getStatus() 
    {
        return $this->status;
    }

    public function setStatus($status) 
    {
        $this->status = $status;
    }     

    /**
     * Возвращает связанный order.
     * @return Order
     */    
    public function getOrder() 
    {
        return $this->order;
    }

    /**
     * Задает связанный order.
     * @param Order $order
     */    
    public function setOrder($order) 
    {
        $this->order = $order;
        $order->addWammchat($this);
    }               

    /**
     * Возвращает связанный comment.
     * @return Comment
     */    
    public function getComment() 
    {
        return $this->comment;
    }

    public function getCommentMsg() 
    {
        return '['.$this->getFromMeAsSting().'] '.$this->getMsgTextAsHtml();
    }

    /**
     * Задает связанный comment.
     * @param Comment $comment
     */    
    public function setComment($comment) 
    {
        $this->comment = $comment;
    }               
}
