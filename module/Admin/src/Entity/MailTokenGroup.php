<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Filter\IdsFormat;

/**
 * Description of NameGroup
 * @ORM\Entity(repositoryClass="\Admin\Repository\PostLogRepository")
 * @ORM\Table(name="mail_token_group")
 * @author Daddy
 */
class MailTokenGroup {
    
    const FREQUENCY_MIN   = 5000; // минимальная чатота токена
    const MIN_GOODCOUNT = 10; // минимальное количество товаров в группе

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
     * @ORM\Column(name="lemms")   
     */
    protected $lemms;

    /**
     * @ORM\Column(name="ids")   
     */
    protected $ids;

    /**
     * @ORM\Column(name="post_count")   
     */
    protected $postCount = 0;

    
   /**
    * @ORM\OneToMany(targetEntity="\Admin\Entity\PostLog", mappedBy="mailTokenGroup")
    * @ORM\JoinColumn(name="id", referencedColumnName="mail_token_group_id")
   */
   private $postLogs;

     /**
     * @ORM\ManyToMany(targetEntity="\Admin\Entity\MailToken")
     * @ORM\JoinTable(name="mail_token_group_token",
     *      joinColumns={@ORM\JoinColumn(name="mail_token_group_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="mail_token_id", referencedColumnName="id")}
     *      )
     */
    private $mailTokens;
    

    public function __construct() {
        $this->postLogs = new ArrayCollection();
        $this->mailTokens = new ArrayCollection();
    }

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
        $fGn = mb_strtoupper(mb_substr(trim($name), 0, 1));
        $Gn = $fGn.mb_substr(trim($name), 1);
        $this->name = $Gn;
    }  
    
    public function getLemms() 
    {
        return $this->lemms;
    }

    public function setLemms($lemms) 
    {
        $filter = new IdsFormat(['separator' => ' ']);
        $this->lemms = $filter->filter($lemms);
    }  
    
    public function getPostCount() 
    {
        return $this->postCount;
    }

    public function setPostCount($postCount) 
    {
        $this->postCount = $postCount;
    }  
    
    public function getIds() 
    {
        return $this->ids;
    }

    /**
     * 
     * @param array $ids
     */
    public function setIds($ids) 
    {        
        $filter = new IdsFormat();
        $this->ids = md5($filter->filter($ids));
    }  
    
    /**
     * Возвращает postLog для этого tokenGroup.
     * @return array
     */   
   public function getPostLogs() {
      return $this->postLogs;
   }    
   
    /**
     * Добавляет новый postLog к этому tokenGroup.
     * @param \Admin\Entity\Goods $postLog
     */   
    public function addPostLog($postLog) 
    {
        $this->postLogs[] = $postLog;
    }   

    public function getMailTokens() {
       return $this->mailTokens;
    }    
   
    /**
     * 
     * @param \Admin\Entity\MailToken $mailToken
     */
    public function addMailToken($mailToken)
    {
        $this->mailTokens->add($mailToken);
    }        
}
