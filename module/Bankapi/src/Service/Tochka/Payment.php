<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bankapi\Service\Tochka;

use Zend\Http\Client;
use Zend\Json\Decoder;
use Zend\Json\Encoder;

/**
 * Подготовка платежных поручений для банка Точка
 *
 * @author Daddy
 */
class Payment {

    /**
     * @var Bankapi\Service\Tochka\Authenticate
     */
    private $auth;
    
    public function __construct($auth) 
    {
        $this->auth = $auth;
    }
    
    /**
     * Отправляет платежку в банк
     * 
     * @param array $data_payment Описание полей:
     *   bank_code                      БИК банка отправителя	9, цифры
     *   counterparty_account_number	Счёт получателя	20, цифры
     *   counterparty_bank_bic          БИК банка получателя	9, цифры
     *   counterparty_inn               ИНН получателя	10, 12 цифры
     *   counterparty_kpp               КПП получателя	9, цифры
     *   counterparty_name              Получатель платежа	до 160, русские буквы, цифры, символы
     *   payment_amount                 Сумма платежа	до 18, цифры
     *   payment_date                   Дата платежа	ДД.ММ.ГГГГ от -10 до +1 дней от текущей даты
     *   payment_number                 Номер платежа	6, цифры
     *   payment_priority               Очередность платежа	1, цифры
     *   payment_purpose                Назначение платежа	до 210
     *   supplier_bill_id               Код УИН	1, 20, 25 русские буквы, цифры
     *   tax_info_document_date         Дата бюджетного документа	1, 10 цифры
     *   tax_info_document_number	Номер документа	до 15
     *   tax_info_kbk                   КБК	1, 20, цифры
     *   tax_info_okato                 Код ОКАТО/ОКТМО	1, 8 цифры
     *   tax_info_period                Налоговый период/Код таможенного органа	1, 8, 10 русские буквы, цифры, символы
     *   tax_info_reason_code           Основание платежа	2, русские буквы
     *   tax_info_status                Статус плательщика	2, цифры
     * 
     * @return string|Exception
     */
    public function payment($data_payment)
    {
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri().'/payment');
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('POST');
        $client->setRawBody(Encoder::encode($data_payment));
        $client->setOptions(['timeout' => 30]);
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->auth->readCode($this->auth::TOKEN_ACCESS),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isOk()){
            $result = Decoder::decode($response->getBody()); 
            return $result->request_id;
        }
        
        return $this->auth->exception($response);
        
    }
    
    /**
     * Возвращает статус платежного поручения в банке
     * 
     * @param string $request_id
     * @return string|Exception
     */
    public function paymentStatus($request_id)
    {
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri().'/payment/status/'.$request_id);
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('GET');
        $client->setOptions(['timeout' => 30]);
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->auth->readCode($this->auth::TOKEN_ACCESS),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isOk()){
            $result = Decoder::decode($response->getBody()); 
            return $result->status;
        }
        
        return $this->auth->exception($response);
        
    }
}
