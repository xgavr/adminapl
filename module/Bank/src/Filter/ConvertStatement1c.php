<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bank\Filter;

use Zend\Filter\AbstractFilter;

/**
 * Возвращает массив полей Bank\Entity\Statement преобразованный из полей документа выписки 1с
 * 
 * @author Daddy
 */
class ConvertStatement1c extends AbstractFilter
{
    
    /**
     * 
     * @var Bank\Entity\BankAccount
     */
    private $bankAccount;

    
    // Доступные опции фильтра.
    protected $options = [
    ];    

    // Конструктор.
    public function __construct($bankAccount, $options = null) 
    {     
        
        $this->bankAccount = $bankAccount;
        // Задает опции фильтра (если они предоставлены).
        if(is_array($options)) {
            $this->options = $options;
        }    
    }

    /**
     * Возвращает массив полей Bank\Entity\Statement преобразованный из полей выписки 1с
     * 
     * @param array $value Массив документа из выписки 1с
     * 
     * @return array поля Bank\Entity\Statement
     */
    public function filter($doc)
    {

        if (isset($doc['Номер'])) $data['payment_number']   = $doc['Номер'];
        if (isset($doc['Дата']))  $data['payment_date']     = date('Y-m-d', strtotime($doc['Дата']));

        if (isset($doc['ДатаСписано'])){

            if (isset($doc['Сумма']))               $data['payment_amount']         = -$doc['Сумма'];                    
            if (isset($doc['ДатаСписано']))         $data['payment_charge_date']    = date('Y-m-d', strtotime($doc['ДатаСписано']));
            if (isset($doc['ПлательщикРасчСчет']))  $data['account']                = $doc['ПлательщикРасчСчет'];
                                                    $data['bik']                    = $this->bankAccount->getBik();

            if (isset($doc['ПолучательРасчСчет']))  $data['counterparty_account_number'] = $doc['ПолучательРасчСчет'];
            if (isset($doc['Получатель']))          $data['counterparty_name']           = $doc['Получатель'];
            if (isset($doc['Получатель1']))         $data['counterparty_name']           = $doc['Получатель1'];
            if (isset($doc['ПолучательИНН']))       $data['counterparty_inn']            = $doc['ПолучательИНН'];
            if (isset($doc['ПолучательКПП']))       $data['counterparty_kpp']            = $doc['ПолучательКПП'];
            if (isset($doc['ПолучательБанк1']))     $data['counterparty_bank_name']      = $doc['ПолучательБанк1'];
            if (isset($doc['ПолучательБИК']))       $data['counterparty_bank_bic']       = $doc['ПолучательБИК'];

        } else {

            if (isset($doc['Сумма']))               $data['payment_amount']         = $doc['Сумма'];                    
            if (isset($doc['ДатаПоступило']))       $data['payment_charge_date']    = date('Y-m-d', strtotime($doc['ДатаПоступило']));
            if (isset($doc['ПолучательРасчСчет']))  $data['account']                = $doc['ПолучательРасчСчет'];
                                                    $data['bik']                    = $this->bankAccount->getBik();

            if (isset($doc['ПлательщикРасчСчет']))  $data['counterparty_account_number'] = $doc['ПлательщикРасчСчет'];
            if (isset($doc['Плательщик']))          $data['counterparty_name']           = $doc['Плательщик'];
            if (isset($doc['Плательщик1']))         $data['counterparty_name']           = $doc['Плательщик1'];
            if (isset($doc['ПлательщикИНН']))       $data['counterparty_inn']            = $doc['ПлательщикИНН'];
            if (isset($doc['ПлательщикКПП']))       $data['counterparty_kpp']            = $doc['ПлательщикКПП'];
            if (isset($doc['ПлательщикБанк1']))     $data['counterparty_bank_name']      = $doc['ПлательщикБанк1'];
            if (isset($doc['ПолучательБИК']))       $data['counterparty_bank_bic']       = $doc['ПолучательБИК'];
        }

        if (isset($doc['ВидОплаты']))           $data['operation_type']           = $doc['ВидОплаты'];
        if (isset($doc['СтатусСоставителя']))   $data['tax_info_status']          = $doc['СтатусСоставителя'];
        if (isset($doc['ПоказательКБК']))       $data['tax_info_kbk']             = $doc['ПоказательКБК'];
        if (isset($doc['ОКАТО']))               $data['tax_info_okato']           = $doc['ОКАТО'];
        if (isset($doc['ПоказательОснования'])) $data['tax_info_reason_code']     = $doc['ПоказательОснования'];
        if (isset($doc['ПоказательПериода']))   $data['tax_info_period']          = $doc['ПоказательПериода'];
        if (isset($doc['ПоказательНомера']))    $data['tax_info_document_number'] = $doc['ПоказательНомера'];
        if (isset($doc['ПоказательДаты']))      $data['tax_info_document_date']   = $doc['ПоказательДаты'];
        if (isset($doc['НазначениеПлатежа']))   $data['payment_purpose']          = $doc['НазначениеПлатежа'];
        
        $data['x_payment_id'] = 'none';

        return $data;        
    }
    
}
