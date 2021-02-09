<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Admin\Repository\PostLogRepository")
 * @ORM\Table(name="mail_post_token")
 * @author Daddy
 */
class MailPostToken {
    
    const STATUS_TAKE_NEW = 1; //новая запись
    const STATUS_TAKE_OLD = 2; //учтено
    
    const PART_FROM = 1; //от кого
    const PART_SUBLECT = 2; //тема
    const PART_BODY = 3; //тело
    const PART_FILENAME = 4; //имя файла
    const PART_UNKNOWN = 9; // неизвестно

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="display_lemma")  
     */
    protected $displayLemma;        

    /**
     * @ORM\Column(name="status")  
     */
    protected $status;        

    /**
     * @ORM\Column(name="mail_part")  
     */
    protected $mailPart;        

    /**
     * @ORM\Column(name="frequency_part")  
     */
    protected $frequencyPart;        

    /**
     * @ORM\ManyToOne(targetEntity="Admin\Entity\MailToken", inversedBy="mailPostTokens") 
     * @ORM\JoinColumn(name="mail_token_id", referencedColumnName="id")
     */
    protected $mailToken;    

    /**
     * @ORM\ManyToOne(targetEntity="Admin\Entity\PostLog", inversedBy="mailPostTokens") 
     * @ORM\JoinColumn(name="post_log_id", referencedColumnName="id")
     */
    protected $postLog;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Admin\Entity\MailTokenGroup", inversedBy="mailPostTokens") 
     * @ORM\JoinColumn(name="mail_token_group_id", referencedColumnName="id")
     */
    protected $mailTokenGroup;    

    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     


    public function getDisplayLemma()
    {
        return $this->displayLemma;
    }
    
    public function setDisplayLemma($displayLemma) 
    {
        $this->displayLemma = $displayLemma;
    }     

    public function getStatus() 
    {
        return $this->status;
    }

    public function setStatus($status) 
    {
        $this->status = $status;
    }     

    public function getMailPart() 
    {
        return $this->mailPart;
    }

    public function setMailPart($mailPart) 
    {
        $this->mailPart = $mailPart;
    }     

    public function getFrequencyPart() 
    {
        return $this->frequencyPart;
    }

    public function setFrequencyPart($frequencyPart) 
    {
        $this->frequencyPart = $frequencyPart;
    }     

    /**
     * Возвращает связанный mailToken.
     * @return \Admin\Entity\MailToken
     */    
    public function getMailToken() 
    {
        return $this->mailToken;
    }

    /**
     * Задает связанный mailToken.
     * @param \Admin\Entity\MailToken $mailToken
     */    
    public function setMailToken($mailToken) 
    {
        $this->mailToken = $mailToken;
    }           
    
    /**
     * Возвращает связанный postLog.
     * @return \Admin\Entity\PostLog
     */    
    public function getPostLog() 
    {
        return $this->postLog;
    }

    /**
     * Задает связанный postLog.
     * @param \Admin\Entity\PostLog $postLog
     */    
    public function setPostLog($postLog) 
    {
        $this->postLog = $postLog;
    }           
    
    /**
     * Возвращает связанный mailTokenGroup.
     * @return \Admin\Entity\MailTokenGroup
     */    
    public function getMailTokenGroup() 
    {
        return $this->mailTokenGroup;
    }

    /**
     * Задает связанный mailTokenGroup.
     * @param \Admin\Entity\MailTokenGroup $mailTokenGroup
     */    
    public function setMailTokenGroup($mailTokenGroup) 
    {
        $this->mailTokenGroup = $mailTokenGroup;
    }           
}
