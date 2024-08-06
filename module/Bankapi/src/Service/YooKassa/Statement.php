<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bankapi\Service\Sber;

use Laminas\Http\Client;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;

/**
 * Description of Statement
 *
 * @author Daddy
 */
class Statement {

    /**
     * @var Bankapi\Service\Sber\Authenticate
     */
    private $auth;
    
    public function __construct($auth) 
    {
        $this->auth = $auth;
    }
    
    /**
     * Получить список счетов
     * @return array|\Exception
     */
    public function accountList()
    {
        $this->auth->isAuth();
        
        $client = new Client();
        $client->setUri($this->auth->getUri().'/account/list');
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
            return Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);            
        }

        return $this->auth->exception($response);
    }
    
    /**
     * Получить список счетов V2
     * @return array|\Exception
     */
    public function accountListV2()
    {
        $this->auth->isAuth();
        
        // https://enter.tochka.com/uapi/open-banking/{apiVersion}/accounts
        
        $client = new Client();
        $client->setUri($this->auth->getUri2('open-banking', 'accounts'));
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
            return Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);            
        }

        return $this->auth->exception($response);
    }

    /**
     * Получить выписку за период
     * @param string $request_id
     * @return array|\Exception
     */
    public function statementResult($request_id)
    {
        $this->auth->isAuth();
        
        $client = new Client();
        $client->setUri($this->auth->getUri().'/statement/result/'.$request_id);
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
            return Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY); 
        }
        
        return $this->exception($response);
    }
    
    /**
     * Статус запроса выписки
     * @param string $request_id
     * @return array|\Exception
     */
    public function statementStatus($request_id)
    {
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri().'/statement/status/'.$request_id);
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
            if (isset($result->status)){
                if ($result->status == 'ready'){
                    return $this->statementResult($request_id);
                }
                if ($result->status == 'queued'){
                    sleep(5);
                    return $this->statementStatus($request_id);
                }
            }
        }
        
        return $this->auth->exception($response);
    }
    
    /**
     * Запрос выписки
     * @param array $params
     * 
     */
    public function statementRequest($params)
    {
        $postParameters = [
            'account_code' => $params['account_code'],
            'bank_code' => $params['bank_code'],
            'date_end' => $params['date_end'],
            'date_start' => $params['date_start'],            
        ];
        
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri().'/statement');
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('POST');
        $client->setRawBody(Encoder::encode($postParameters));
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
            if (isset($result->request_id)){
                return $this->statementStatus($result->request_id);
            }
        }
        
        return $this->auth->exception($response);
    }
    
    /**
     * Получить выписку по счету за период
     * 
     * @param string $bank_code БИК
     * @param string $account_code Номер счета
     * @param date $date_start
     * @param date $date_end
     * 
     * @return array|null
     */
    public function statement($bank_code, $account_code, $date_start = null, $date_end = null)
    {
//        if (!$date_start) $date_start = date('Y-m-d');
        if (!$date_start) $date_start = date('Y-m-d', strtotime("-1 days"));
        if (!$date_end) $date_end = date('Y-m-d');

        return $this->statementRequest([
            'date_start' => $date_start,
            'date_end' => $date_end,
            'bank_code' => $bank_code,
            'account_code' => $account_code,
        ]);
    }
    
    /**
     * Получить выписки по всем счетам за период
     * @param date $date_start
     * @param date $date_end
     * @return array|null
     */
    public function statements($date_start = null, $date_end = null)
    {
//        if (!$date_start) $date_start = date('Y-m-d');
        if (!$date_start) $date_start = date('Y-m-d', strtotime("-1 days"));
        if (!$date_end) $date_end = date('Y-m-d');
        
        $result['date_start'] = $date_start;
        $result['date_end'] = $date_end;
        $result['statements'] = [];
        
        $accounts = $this->accountList();
        if (is_array($accounts)){
            foreach ($accounts as $account){
                if ($this->auth->getMode() == 'sandbox'){ //в песочнице номер счета: account_code, в api - code
                    $result['statements'][$account['bank_code']][$account['account_code']] = $this->statement(
                        $account['bank_code'],
                        $account['account_code'],
                        $date_start,
                        $date_end
                    );
                } else {
                    $result['statements'][$account['bank_code']][$account['code']] = $this->statement(
                        $account['bank_code'],
                        $account['code'],
                        $date_start,
                        $date_end
                    );                    
                }    
                usleep(100);
            }
            
            return $result;
        }
        
        return;
    }
    
    /**
     * Получить выписку v2
     * @param array $params
     * @param int $attempt
     * 
     */
    public function getStatementV2($params, $attempt = 1)
    {        
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri2('open-banking', "accounts/{$params['accountId']}/statements/{$params['statementId']}"));
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('GET');
//        $client->setRawBody(Encoder::encode($postParameters));
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
            $statements = $result->Data->Statement;
            foreach ($statements as $statement){
                switch ($statement->status){
                    case 'Ready': 
                        return $statement;
                    case 'Error': 
                        return ['Error'];
                    default: 
                        if ($attempt > 3){
                            return ['Error attempt'];                        
                        }
                        sleep(5);
                        $nextAttempt = $attempt+1;
                        return $this->getStatementV2($params, $nextAttempt);
                }
            }    
        }
        
        return $this->auth->exception($response);
    }
    
    /**
     * Запрос выписки v2
     * @param array $params
     * 
     */
    public function initStatementV2($params)
    {
        $postParameters = [
            'Data' => [
                'Statement' => [
                    'accountId' => $params['accountId'],
                    'endDateTime' => $params['date_end'],
                    'startDateTime' => $params['date_start'],                                
                ]
            ] 
        ];
        
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri2('open-banking', 'statements'));
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('POST');
        $client->setRawBody(Encoder::encode($postParameters));
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
            switch ($result->Data->Statement->status){
                case 'Ready': 
                    return $result->Data->Statement;
                case 'Error': 
                    return ['Error'];
                default: 
                    sleep(5);
                    return $this->getStatementV2([
                        'accountId' => $result->Data->Statement->accountId,
                        'statementId' => $result->Data->Statement->statementId,
                    ]);
            }
        }
        
        return $this->auth->exception($response);
    }
    
    /**
     * Получить выписку по счету за период v2
     * 
     * @param string $accountId Уникальный и неизменный идентификатор счёта 40817810802000000008/044525999
     * @param date $date_start
     * @param date $date_end
     * 
     * @return array|null
     */
    public function statementV2($accountId, $date_start = null, $date_end = null)
    {
//        if (!$date_start) $date_start = date('Y-m-d');
        if (!$date_start) $date_start = date('Y-m-d', strtotime("-1 days"));
        if (!$date_end) $date_end = date('Y-m-d');

        return $this->initStatementV2([
            'date_start' => $date_start,
            'date_end' => $date_end,
            'accountId' => $accountId,
        ]);
    }
    
    /**
     * Получить выписки по всем счетам за период v2
     * @param date $date_start
     * @param date $date_end
     * @return array|null
     */
    public function statementsV2($date_start = null, $date_end = null)
    {
//        if (!$date_start) $date_start = date('Y-m-d');
        if (!$date_start) $date_start = date('Y-m-d', strtotime("-1 days"));
        if (!$date_end) $date_end = date('Y-m-d');
        
        $result['date_start'] = $date_start;
        $result['date_end'] = $date_end;
        $result['statements'] = [];
        
        $data = $this->accountListV2();
        $result = [];
        if (!empty($data['Data'])){
            if (!empty($data['Data']['Account'])){
                $accounts = $data['Data']['Account'];
                if (is_array($accounts)){
                    foreach ($accounts as $account){
                        $result['statements'][$account['accountId']] = $this->statementV2(
                            $account['accountId'],
                            $date_start,
                            $date_end
                        );                    
                        usleep(100);
                    }
                }
            }
        }
        
        return $result;
    }
}
