<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bank\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Email
 * @ORM\Entity(repositoryClass="\Bank\Repository\BankRepository")
 * @ORM\Table(name="bank_balance")
 * @author Daddy
 */
class Balance {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="bic")   
     */
    protected $bik;
   
    /**
     * @ORM\Column(name="account")   
     */
    protected $account;
   
    /** 
     * @ORM\Column(name="date_balance")  
     */
    protected $dateBalance;
    
    /** 
     * @ORM\Column(name="balance")  
     */
    protected $balance;
        
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="balances") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;    
    
    /**
     * Возвращает Id
     * @return int
     */    
    public function getId() 
    {
        return $this->id;
    }

    /**
     * Устанавливает Id
     * @param int $id
     */
    public function setId($id) 
    {
        $this->id = $id;
    }     

    /**
     * Возвращает БИК
     * @return string
     */
    public function getBik() 
    {
        return $this->bik;
    }
    
    /**
     * Устанвливает БИК
     * @param string $bik
     */
    public function setBik($bik) 
    {
        $this->bik = $bik;
    }     

    /**
     * Возвращает расчетный счет.
     * @return string
     */
    public function getAccount() 
    {
        return $this->account;
    }

    /**
     * Устанавливает расчетный счет
     * @param string $account
     */
    public function setAccount($account) 
    {
        $this->account = $account;
    }     

    /**
     * Returns the date of balance.
     * @return string     
     */
    public function getDateBalance() 
    {
        return $this->dateBalance;
    }
    
    /**
     * Sets the date balance.
     * @param string $dateBalance     
     */
    public function setDateBalance($dateBalance) 
    {
        $this->dateBalance = date('Y-m-d', strtotime($dateBalance));
    }             

    /**
     * Возвращает остаток на начало дня.
     * @return float
     */
    public function getBalance() 
    {
        return $this->balance;
    }

    /**
     * Устанавливает остаток на начало дня
     * @param float $balance
     */
    public function setBalance($balance) 
    {
        $this->balance = $balance;
    }     

    public function getCompany() {
        return $this->company;
    }

    public function setCompany($company) {
        $this->company = $company;
        return $this;
    }
}
