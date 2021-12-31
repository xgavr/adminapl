<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Bank\Entity\Statement;
use Laminas\Http\Client;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;
use Company\Entity\Office;
use Application\Entity\Supplier;
use Company\Entity\Legal;
use Company\Entity\Contract;
use Stock\Entity\Ptu;
use Stock\Entity\Vtp;
use Stock\Entity\Vt;
use Application\Entity\Order;
use Stock\Entity\Ot;
use Application\Entity\Contact;
use Application\Entity\Client as AplClient;
use Stock\Entity\St;
use Stock\Entity\Pt;
use User\Entity\User;
use Company\Entity\Cost;
use Laminas\Validator\Date;
use Cash\Entity\Cash;
use Cash\Entity\CashDoc;


/**
 * Description of AplCashService
 *
 * @author Daddy
 */
class AplCashService {

    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Admin manager
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;

    /**
     * Legal manager.
     * @var \Company\Service\LegalManager
     */
    private $legalManager;  
        
    /**
     * Cash manager.
     * @var \Cash\Service\CashManager
     */
    private $cashManager;  
    
    public function __construct($entityManager, $adminManager,  
            $legalManager, $cashManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->legalManager = $legalManager;
        $this->cashManager = $cashManager;
    }
    
    protected function aplApi()
    {
        return 'https://autopartslist.ru/api/';
        
    }
    
    protected function aplApiKey()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        return md5(date('Y-m-d').'#'.$settings['apl_secret_key']);
    }
    
    /**
     * Получить офис по коду АПЛ
     * 
     * @param integer $officeAplId
     * @return Office
     */
    public function officeFromAplId($officeAplId)
    {
        $office = $this->entityManager->getRepository(Office::class)
                ->findOneBy(['aplId' => $officeAplId]);
        return $office;        
    }
    
    /**
     * Получить организацию поставщика по коду АПЛ
     * 
     * @param integer $supplierAplId
     * @param date $dateStart
     * @param array $legalInfo
     * @return Legal
     */
    private function legalFromSupplierAplId($supplierAplId, $dateStart = null, $legalInfo = null)
    {
        $supplier = $this->entityManager->getRepository(Supplier::class)
                ->findOneBy(['aplId' => $supplierAplId]);
        
        if ($supplier){
            $legal = $this->entityManager->getRepository(Supplier::class)
                    ->findDefaultSupplierLegal($supplier, $dateStart);
            
            if (!$legal){
                $legal = $this->legalManager->addLegal($supplier->getLegalContact(), [
                    'name' => $supplier->getName(),
                    'inn' => '',
                    'kpp' => '',
                    'ogrn' => '',
                    'okpo' => '',
                    'head' => '',
                    'chiefAccount' => '',
                    'info' => '',
                    'address' => '',
                    'status' => Legal::STATUS_ACTIVE,
                    'dateStart' => $dateStart,
                ]);
            }
            
            return $legal;
        }   
        
        if ($legalInfo){
            if (isset($legalInfo['inn'])){
                $legal = $this->entityManager->getRepository(Legal::class)
                        ->findOneBy(['inn' => $legalInfo['inn'], 'kpp' => $legalInfo['kpp']]);
                if ($legal){
                    return $legal; 
                }
            }    
        }    
        
        throw new \Exception("Не удалось найти поставщика ($supplierAplId) и организацию ($inn/$kpp)");
           
        return;        
    }
    
    /**
     * Получить контракт по умолчанию
     * 
     * @param Office $office
     * @param Legal $legal
     * @param date $dateStart
     * @param string $act
     * @param integer $pay
     * 
     * @return Contract
     */
    private function findDefaultContract($office, $legal, $dateStart, $act, $pay = Contract::PAY_CASH)
    {
        $dateValidator = new Date();
        $dateValidator->setFormat('Y-m-d H:i:s');
        if (!$dateValidator->isValid($dateStart)){
            $dateStart = '2012-05-15';
        }
        
        $contract = $this->entityManager->getRepository(Office::class)
                ->findDefaultContract($office, $legal, $dateStart, $pay);
        
        if (!$contract){
            $contract = $this->legalManager->addContract($legal, 
                    [
                        'office' => $office->getId(),
                        'name' => ($pay == Contract::PAY_CASH) ? 'Поставка Н':'Поставка БН',
                        'act' => trim($act),
                        'dateStart' => $dateStart,
                        'status' => Contract::STATUS_ACTIVE,
                        'kind' => Contract::KIND_SUPPLIER,
                        'pay' => $pay,
                    ]);
        }
        
        return $contract;
    }
                
    /**
     * Получить статус оплаты договора
     * 
     * @param array $data
     * @return integer
     */
    private function getCashContract($data)
    {
        $cashless = Contract::PAY_CASH;
        if (isset($data['cashless'])){
            if ($data['cashless'] == 1){
                $cashless = Contract::PAY_CASHLESS;
            }
        }
        
        return $cashless;
    }
    
    /**
     * Получить статус платежа
     * 
     * @param array $data
     * @return integer
     */
    private function getPaymentStatus($data)
    {
        $paymentStatus = CashDoc::STATUS_ACTIVE;
        if ($data['publish'] == 0 || $data['sort'] == 0){
            $paymentStatus = CashDoc::STATUS_RETIRED;            
        }
        return $paymentStatus;
    }

    /**
     * Получить статус чека
     * 
     * @param array $data
     * @return integer
     */
    private function getCheckStatus($data)
    {
        $checkStatus = CashDoc::CHECK_RETIRED;
        if ($data['check'] == 1){
            $checkStatus = CashDoc::CHECK_ACTIVE;            
        }
        
        return $checkStatus;
    }

    /**
     * Получить операцию платежа
     * 
     * @param array $data
     * @return integer
     */
    private function getPaymentKind($data)
    {
        $kind = (empty($data['kind'])) ? '':$data['kind'];
        switch($kind){
            case 'in1': return CashDoc::KIND_IN_PAYMENT_CLIENT;
            case 'in2': return CashDoc::KIND_IN_RETURN_USER;
            case 'in3': return CashDoc::KIND_IN_REFILL;
            case 'in4': return CashDoc::KIND_IN_RETURN_SUPPLIER;
            case 'out1': return CashDoc::KIND_OUT_USER;
            case 'out2': return CashDoc::KIND_OUT_SUPPLIER;
            case 'out8': return CashDoc::KIND_OUT_COURIER;
            case 'out3': return CashDoc::KIND_OUT_REFILL;
            case 'out4': return CashDoc::KIND_OUT_RETURN_CLIENT;
            case 'out5': return CashDoc::KIND_OUT_COST;
            case 'out6': return CashDoc::KIND_OUT_SALARY;
            default:
                if ($data['sort'] >= 0 && $data['comment'] == 'Orders'){
                    return CashDoc::KIND_IN_PAYMENT_CLIENT;
                }
        }
        return;
    }

    /**
     * Загрузить платеж
     * 
     * @param array $data
     */
    public function updatePayment($data)
    {
//        var_dump($data); exit;
        $docDate = (!empty($data['ds'])) ? $data['ds']:'';
        $dateValidator = new Date();
        $dateValidator->setFormat('Y-m-d H:i:s');
        if (!$dateValidator->isValid($docDate)){
            $docDate = $data['created'];
        }
        
        $dataCash = [
            'aplId' => $data['id'],
            'amount' => abs($data['sort']),
            //'status_ex' => Ptu::STATUS_EX_APL,
            'status' => $this->getPaymentStatus($data),
            'checkStatus' => $this->getCheckStatus($data),
            'dateOper' => $docDate,
            'kind' => $this->getPaymentKind($data),
            'comment' => (empty($data['info'])) ? null:$data['info'],
        ];
        
        $cash = $this->entityManager->getRepository(Cash::class)
                ->findOneByAplId($data['sf']);
        if ($cash){
            $dataCash['cash'] = $cash->getId();
        }    
        if ($data['bo']){
            $user = $this->entityManager->getRepository(User::class)
                    ->findOneByAplId($data['sf']);
            if ($user){
                $dataCash['user'] = $user->getId();
            }    
        }    
        if ($data['type'] == 'Suppliers'){
            $supplier = $this->entityManager->getRepository(Supplier::class)
                    ->findOneByAplId($data['parent']);
            if ($supplier){
                $dataCash['supplier'] = $supplier->getId();
            }    
        }    
        if ($data['type'] == 'Tills'){
            $cashRefill = $this->entityManager->getRepository(Cash::class)
                    ->findOneByAplId($data['parent']);
            if ($cashRefill){
                $dataCash['cashRefill'] = $cashRefill->getId();
            }    
        }    
        if ($data['type'] == 'Users' || $data['type'] == 'Staffs'){
            $userRefill = $this->entityManager->getRepository(User::class)
                    ->findOneByAplId($data['parent']);
            if ($userRefill){
                $dataCash['userRefill'] = $userRefill->getId();
            }    
        }    
        if ($data['type'] == 'Costs'){
            $cost = $this->entityManager->getRepository(Cost::class)
                    ->findOneByAplId($data['parent']);
            if ($cost){
                $dataCash['cost'] = $cost->getId();
            }    
        }    
        if ($data['comment'] == 'Orders'){
            $order = $this->entityManager->getRepository(Order::class)
                    ->findOneByAplId($data['name']);
            if ($order){
                $dataCash['order'] = $order->getId();
                $dataCash['contact'] = $order->getContact()->getId();
                if ($order->getLegal()){
                    $dataCash['legal'] = $order->getLegal()->getId();                    
                }
            } else {
                $client = $this->entityManager->getRepository(AplClient::class)
                        ->findOneByAplId($data['parent']);
                if ($client){
                    $contacts = $client->getContacts();
                    $dataCash['contact'] = $contacts[0]->getId();
                }                    
            }   
        }    
        if ($data['comment'] == 'Payments'){
            $client = $this->entityManager->getRepository(AplClient::class)
                    ->findOneByAplId($data['parent']);
            if ($client){
                $contacts = $client->getContacts();
                $dataCash['contact'] = $contacts[0]->getId();
            }    
        }    
        $office = $this->officeFromAplId($data['off']);
        if ($office){
            $company = $this->entityManager->getRepository(Office::class)
                    ->findDefaultCompany($office, $docDate);
            $dataCash['company'] = $company->getId();
        }    
        
        $cashDoc = $this->entityManager->getRepository(CashDoc::class)
                ->findOneByAplId($data['id']);
        if ($cashDoc){
            $this->cashManager->updateCashDoc($cashDoc, $dataCash);
        } else {        
            $cashDoc = $this->cashManager->addCashDoc($dataCash);
        }    
                
        if ($cashDoc){
            return true;            
        }
                
        return false;
    }
        
    /**
     * Обновить статус загруженного платежа
     * @param integer $aplPaymentId
     * @return boolean
     */
    public function unloadedPayment($aplPaymentId)
    {
        $result = true;
        if (is_numeric($aplPaymentId)){
            $url = $this->aplApi().'aa-payment?api='.$this->aplApiKey();

            $post = [
                'paymentId' => $aplPaymentId,
            ];
            
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setParameterPost($post);

            $result = $ok = FALSE;
            try{
                $response = $client->send();
//                var_dump($response->getBody()); exit;
                if ($response->isOk()) {
                    $result = $ok = TRUE;
                }
            } catch (\Laminas\Http\Client\Adapter\Exception\RuntimeException $e){
                $ok = true;
            } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                $ok = true;
            }    
            
            if ($ok){
            }

            unset($post);
        }    
        return $result;        
    }
    
    /**
     * Загрузить платеж из Апл
     * @param int $start
     * @return 
     */
    public function unloadPayment($start = 0)
    {
        $url = $this->aplApi().'unload-payment?api='.$this->aplApiKey();
        
        $post = [
            'start' => $start,
        ];

        $client = new Client();
        $client->setUri($url);
        $client->setMethod('POST');
        $client->setOptions(['timeout' => 30]);
        $client->setParameterPost($post);

        $response = $client->send();
        $body = $response->getBody();

//        var_dump($body); exit;
        try{
            $result = json_decode($body, true);
        } catch (\Laminas\Json\Exception\RuntimeException $ex) {
            var_dump($ex->getMessage());
            var_dump($body);
            exit;
        }
//        var_dump($result); exit;

        if (is_array($result)){
            if ($this->updatePayment($result)){ 
                $this->unloadedPayment($result['id']);
            }    
        } else {
            return false;
        }
        return true;
    }

    /**
     * Загрузка платежей
     * 
     * @return
     */
    public function unloadPayments()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(900);
        $startTime = time();
        $start = 0;
        while (true){
            if ($this->unloadPayment($start)) {
                usleep(100);
                if (time() > $startTime + 840){
                    break;
                }
            } else {
                break;
            }    
           // $start++;
        }    
        return;
    }    
}
