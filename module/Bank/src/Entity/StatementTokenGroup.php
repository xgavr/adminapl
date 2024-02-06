<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bank\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of Statement Token Group
 * @ORM\Entity(repositoryClass="\Bank\Repository\BankRepository")
 * @ORM\Table(name="statement_token_group")
 * @author Daddy
 */
class StatementTokenGroup {
    
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
     * @ORM\Column(name="statement_count")   
     */
    protected $statementCount = 0;

   /**
    * @ORM\OneToMany(targetEntity="\Bank\Entity\Statement", mappedBy="statementTokenGroup")
    * @ORM\JoinColumn(name="id", referencedColumnName="statement_token_group_id")
   */
   private $statements;


    public function __construct() {
        $this->statements = new ArrayCollection();
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
    
    public function getStatementCount() 
    {
        return $this->statementCount;
    }

    public function setStatementCount($statementCount) 
    {
        $this->statementCount = $statementCount;
    }  
        
    /**
     * Возвращает statements для этого tokenGroup.
     * @return array
     */   
   public function getStatements() {
      return $this->statements;
   }    
   
    /**
     * Добавляет новый statement к этому tokenGroup.
     * @param Statement $statement
     */   
    public function addGood($statement) 
    {
        $this->statements[] = $statement;
    }   

}
