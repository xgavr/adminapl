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
        $vtpStatus = Cash::STATUS_ACTIVE;
        if ($data['publish'] == 0){
            $vtpStatus = Cash::STATUS_RETIRED;            
        }
        
        return $vtpStatus;
    }

    /**
     * Загрузить платеж
     * 
     * @param array $data
     */
    public function updatePayment($data)
    {
//        var_dump($data); exit;
        $docDate = $data['ds'];
        $dateValidator = new Date();
        $dateValidator->setFormat('Y-m-d H:i:s');
        if (!$dateValidator->isValid($docDate)){
            $docDate = $data['created'];
        }
        
        $dataPtu = [
            'apl_id' => $data['id'],
            'doc_no' => $data['ns'],
            'doc_date' => $docDate,
            'comment' => $data['comment'],
            'status_ex' => Ptu::STATUS_EX_APL,
            'status' => $this->getPtuStatus($data),
        ];
        
        if (isset($data['desc'])){
            $dataPtu['info'] = Encoder::encode($data['desc']);
        }
        
        $office = $this->officeFromAplId($data['parent']);
        $legal = $this->legalFromSupplierAplId($data['name'], $data['ds'], $data['supplier']);        
        $contract = $this->findDefaultContract($office, $legal, $data['ds'], $data['ns'], $this->getCashContract($data));
        
        $dataPtu['office'] = $office;
        $dataPtu['legal'] = $legal;
        $dataPtu['contract'] = $contract; 
        
        $ptu = $this->entityManager->getRepository(Ptu::class)
                ->findOneByAplId($data['id']);
        if ($ptu){
            $this->ptuManager->updatePtu($ptu, $dataPtu);
            $this->ptuManager->removePtuGood($ptu); 
        } else {        
            $ptu = $this->ptuManager->addPtu($dataPtu);
        }    
        
        if ($ptu && isset($data['tp'])){
            $rowNo = 1;
            foreach ($data['tp'] as $tp){
                if (isset($tp['good'])){
                    $good = $this->findGood($tp['good']);   
                }    
                if (empty($good)){
    //                throw new \Exception("Не удалось создать карточку товара для документа {$data['id']}");
                } else {

                    $this->ptuManager->addPtuGood($ptu->getId(), [
                        'status' => $ptu->getStatus(),
                        'statusDoc' => $ptu->getStatusDoc(),
                        'quantity' => $tp['sort'],                    
                        'amount' => $tp['bag_total'],
                        'good_id' => $good->getId(),
                        'comment' => '',
                        'info' => '',
                        'countryName' => (isset($tp['country'])) ? $tp['country']:'',
                        'countryCode' => (isset($tp['countrycode'])) ? $tp['countrycode']:'',
                        'unitName' => (isset($tp['pack'])) ? $tp['pack']:'',
                        'unitCode' => (isset($tp['packcode'])) ? $tp['packcode']:'',
                        'ntd' => (isset($tp['gtd'])) ? $tp['gtd']:'',
                    ], $rowNo);
                    $rowNo++;
                }    
            }
        }  
        
        if ($ptu){
            $this->ptuManager->updatePtuAmount($ptu);
            return true;            
        }
                
        return false;
    }
        
    /**
     * Обновить статус загруженного документа
     * @param integer $aplDocId
     * @return boolean
     */
    public function unloadedPayment($aplDocId)
    {
        $result = true;
        if (is_numeric($aplDocId)){
            $url = $this->aplApi().'aa-doc?api='.$this->aplApiKey();

            $post = [
                'docId' => $aplDocId,
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
        var_dump($result); exit;

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
            $start++;
        }    
        return;
    }    
}
