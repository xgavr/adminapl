<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Laminas\Json\Json;
use Admin\Filter\EmailFromStr;
use Admin\Filter\NameFromEmailStr;
use Admin\Entity\MailPostToken;


/**
 * Description of PostLog
 * @ORM\Entity(repositoryClass="\Admin\Repository\PostLogRepository")
 * @ORM\Table(name="post_log")
 * @author Daddy
 */
class PostLog {
    
    const STATUS_ACTIVE       = 1; // Active user.
    const STATUS_RETIRED      = 2; // Retired user.
    
    const ACT_NO       = 1; // no action.
    
    const MAIL_DICT = './data/dict/mail_token.php';
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="to_email")   
     */
    protected $to;
    
    /**
     * @ORM\Column(name="from_email")   
     */
    protected $from;
    
    /**
     * @ORM\Column(name="from_str")   
     */
    protected $fromStr;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;
    
    /**
     * @ORM\Column(name="subject")   
     */
    protected $subject;

    /**
     * @ORM\Column(name="body")   
     */
    protected $body;

    /**
     * @ORM\Column(name="attachment")   
     */
    protected $attachment;

    /**
     * @ORM\Column(name="status")   
     */
    protected $status;

    /**
     * @ORM\Column(name="act")   
     */
    protected $act;

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

    public function getTo() 
    {
        return $this->to;
    }

    public function getToName() 
    {
        $filter = new NameFromEmailStr();
        return $filter->filter($this->to);
    }

    public function getToEmail() 
    {
        $filter = new EmailFromStr();
        return $filter->filter($this->to);
    }

    public function setTo($to) 
    {
        $this->to = $to;
    }     

    public function getFrom()
    {
        return $this->from;
    }

    public function getFromEmail()
    {
        return $this->from;
    }

    public function setFrom($from)
    {
        $this->from = $from;
    }
    
    public function getFromStr()
    {
        return $this->fromStr;
    }
    
    public function getFromStrName() 
    {
        $filter = new NameFromEmailStr();
        return $filter->filter($this->fromStr);
    }
    
    public function setFromStr($fromStr)
    {
        $this->fromStr = $fromStr;
    }
    
    public function getSubject()
    {
        return $this->subject;
    }
    
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }
    
    /**
     * Returns the date of user creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * Sets the date when this user was created.
     * @param string $dateCreated     
     */
    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
    }    
            
    public function getBody()
    {
        return $this->body;
    }
    
    public function getAllowedBody()
    {
        try{
            $bodies = Json::decode($this->body);
            foreach($bodies as $body){
                return $body;
            }
        } catch (\Laminas\Json\Exception\RuntimeException $ex){
            return $this->body;
        }    
        
        return;
    }
    
    public function getBodyAsArray()
    {
        try{
            $bodies = Json::decode($this->body, Json::TYPE_ARRAY);
            var_dump($bodies); exit;
            return $bodies;
        } catch (\Laminas\Json\Exception\RuntimeException $ex){
            return [MailPostToken::PART_BODY => $this->body];
        }    
        
        return [];
    }
    
    public function setBody($body)
    {
        $this->body = $body;
    }
    
    public function getAttachment()
    {
        return $this->attachment;
    }
    
    public function getAttachmentFileName()
    {
        try{
            $attachments = Json::decode($this->attachment);
            foreach ($attachments as $part){
                return $part->filename;
            }    
        } catch (\Laminas\Json\Exception\RuntimeException $ex){
            return;
        }    
    }
    
    public function getAttachmentFileNames()
    {
        $result = [];
        try{
            $attachments = Json::decode($this->attachment);
            foreach ($attachments as $part){
                $result[] = $part->filename;
            }    
        } catch (\Laminas\Json\Exception\RuntimeException $ex){
            return $result;
        } 
        
        return $result;
    }
    
    public function getAttachmentTmpName()
    {
        try{
            $attachments = Json::decode($this->attachment); 
            foreach ($attachments as $part){
                return $part->temp_file;
            }    
        } catch (\Laminas\Json\Exception\RuntimeException $ex){
            return;
        }    
    }
    
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;
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
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_RETIRED => 'Retired'
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
     * Returns act.
     * @return int     
     */
    public function getAct() 
    {
        return $this->act;
    }

    /**
     * Returns possible acts as array.
     * @return array
     */
    public static function getActList() 
    {
        return [
            self::ACT_NO => 'Нет действий',
        ];
    }    
    
    /**
     * Returns act as string.
     * @return string
     */
    public function getActAsString()
    {
        $list = self::getActList();
        if (isset($list[$this->status]))
            return $list[$this->status];
        
        return 'Unknown';
    }    
    
    /**
     * Sets act.
     * @param int $act     
     */
    public function setAct($act) 
    {
        $this->act = $act;
    }   
    
}
