<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bankapi\Service\Tochka;

use Laminas\Http\Client;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;

/**
 * Подготовка платежных поручений для банка Точка
 *
 * @author Daddy
 */
class Payment {

    /**
     * @var \Bankapi\Service\Tochka\Authenticate
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
     * @return array|Exception
     */
    public function payment($data_payment)
    {
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri().'/payment');
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('POST');
        $client->setRawBody(Encoder::encode($data_payment));
        $client->setOptions(['timeout' => 60]);
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->auth->readCode($this->auth::TOKEN_ACCESS),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isOk()){
            return Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);            
        }
        
        return $this->auth->exception($response);
        
    }
    
    /**
     * Возвращает статус платежного поручения в банке
     * 
     * @param string $request_id
     * @return array|Exception
     */
    public function paymentStatus($request_id)
    {
//        var_dump($request_id); exit; 
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri().'/payment/status/'.$request_id);
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('GET');
        $client->setOptions(['timeout' => 60]);
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->auth->readCode($this->auth::TOKEN_ACCESS),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isOk()){
            return Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);            
        }
        
        return $this->auth->exception($response);        
    }
    
    
    /**
     * Отправляет платежку в банк 2
     * 
     * @param array $data_payment Описание полей:
            "accountCode": "40702810840020002503", Номер счёта отправителя
            "bankCode": "044525999", БИК отправителя
            "payerINN": "5001038736", ИНН за кого платят Заполняется только при платеже за 3 лицо. Допустимые значения "0", 10 или 12 значное число
            "payerKPP": "500101001", КПП за кого платят Заполняется только за 3 лицо при платеже в бюджет.
            "counterpartyBankBic": "044525999", БИК получателя
            "counterpartyAccountNumber": "40702810840020002504", Счёт получателя
            "counterpartyINN": "5001038736", ИНН получателя
            "counterpartyKPP": "500101001", КПП получателя
            "counterpartyName": "ООО \"БАЙКАЛ-СЕРВИС ТК\"", Получатель платежа
            "counterpartyBankCorrAccount": "30101810845250000999", Кор. счёт банка получателя
            "paymentAmount": "700.33", Сумма платежа
            "paymentDate": "2018-03-29", Дата платежа. Используется стандарт ISO8601
            "paymentNumber": "9195", Номер платежа
            "paymentPriority": "5", Приоритет платежа
            "paymentPurpose": "Оплата по счету № 1 от 01.01.2021. Без НДС", Назначение платежа
            "codePurpose": "1", Поле 20 Заполняется только при платеже физ лицам
            "supplierBillId": "1", Код УИН (поле 22)
            "taxInfoDocumentDate": "2018-03-29", Дата документа (поле 109). Используется стандарт ISO8601. Допустимо значение "0"
            "taxInfoDocumentNumber": "12", Номера документа (поле 108)
            "taxInfoKBK": "18210202020061000160", КБК (поле 104)
            "taxInfoOKATO": "65401364000", ОКАТО (поле 105)
            "taxInfoPeriod": "МС.08.2009", Налоговый период (поле 107). Допустимо значение "0"
            "taxInfoReasonCode": "ТП", Основание (поле 106)
            "taxInfoStatus": "08", Статус (поле 101)
            "budgetPaymentCode": "1" Код выплат из бюджета на ФЛ (поле 110)
     * 
     * @return array|Exception
     */
    public function paymentV2($data_payment)
    {
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri2('payment', 'for-sign'));
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('POST');
        $client->setRawBody(Encoder::encode($data_payment));
        $client->setOptions(['timeout' => 60]);
        
//        var_dump($this->auth->getUri2('payment', 'for-sign')); exit;
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->auth->readCode($this->auth::TOKEN_ACCESS),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isOk()){
            return Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);            
        }
//        var_dump($response->getContent());
        return $this->auth->exception($response);
        
    }

    /**
     * Возвращает статус платежного поручения в банке
     * 
     * @param string $request_id
     * @return array|Exception
     */
    public function paymentStatusV2($request_id)
    {
//        var_dump($request_id); exit; 
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri2('payment', 'status').'/'.$request_id);
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('GET');
        $client->setOptions(['timeout' => 60]);
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->auth->readCode($this->auth::TOKEN_ACCESS),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isOk()){
            return Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);            
        }
        
        return $this->auth->exception($response);
        
    }
    
}

