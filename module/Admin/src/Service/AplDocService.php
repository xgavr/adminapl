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
use Stock\Entity\Ot;
use Application\Entity\Contact;
use Application\Entity\Client as AplClient;
use Stock\Entity\St;
use Stock\Entity\Pt;
use User\Entity\User;
use Company\Entity\Cost;
use Application\Entity\Producer;
use Application\Filter\ProducerName;
use Application\Entity\UnknownProducer;
use Application\Entity\Goods;
use Application\Filter\ArticleCode;
use Laminas\Validator\Date;


/**
 * Description of AplDocService
 *
 * @author Daddy
 */
class AplDocService {

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
     * Ptu manager
     * @var \Stock\Service\PtuManager
     */
    private $ptuManager;

    /**
     * Vtp manager
     * @var \Stock\Service\VtpManager
     */
    private $vtpManager;

    /**
     * Ot manager
     * @var \Stock\Service\OtManager
     */
    private $otManager;

    /**
     * Pt manager
     * @var \Stock\Service\PtManager
     */
    private $ptManager;

    /**
     * St manager
     * @var \Stock\Service\StManager
     */
    private $stManager;

    /**
     * Legal manager.
     * @var \Company\Service\LegalManager
     */
    private $legalManager;  
        
    /**
     * Producer manager.
     * @var \Application\Service\ProducerManager
     */
    private $producerManager;  
    
    /**
     * Assembly manager.
     * @var \Application\Service\AssemblyManager
     */
    private $assemblyManager;  
    
    public function __construct($entityManager, $adminManager, $ptuManager, 
            $legalManager, $producerManager, $assemblyManager, $vtpManager, 
            $otManager, $stManager, $ptManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->ptuManager = $ptuManager;
        $this->vtpManager = $vtpManager;
        $this->otManager = $otManager;
        $this->stManager = $stManager;
        $this->ptManager = $ptManager;
        $this->legalManager = $legalManager;
        $this->producerManager = $producerManager;
        $this->assemblyManager = $assemblyManager;
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
    private function officeFromAplId($officeAplId)
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
     * Получить производителя
     * 
     * @param array $data
     * @return Producer
     */
    private function findProducer($data)
    {
        if (!empty($data['type'])){
            if (is_numeric($data['type'])){
                $producer = $this->entityManager->getRepository(Producer::class)
                        ->findOneById($data['type']);
                if ($producer){
                    return $producer;
                }
            }
        }
        
        $producerNameFilter = new ProducerName();
        if (!empty($data['comment'])){
            $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                    ->findOneByName($producerNameFilter->filter($data['comment']));
            if ($unknownProducer){
                if ($unknownProducer->getProducer()){
                    return $unknownProducer->getProducer();
                } else {
                    $producer = $this->producerManager->addProducerFromUnknownProducer($unknownProducer);
                    if ($producer){
                        return $producer;
                    }
                }    
            }
        }
        if (!empty($data['name'])){
            $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                    ->findOneByName($producerNameFilter->filter($data['name']));
            if ($unknownProducer){
                if ($unknownProducer->getProducer()){
                    return $unknownProducer->getProducer();
                } else {    
                    $producer = $this->producerManager->addProducerFromUnknownProducer($unknownProducer);
                    if ($producer){
                        return $producer;
                    }
                }    
            }
        }
                
        return $this->producerManager->addNewProducer(['name' => $data['comment']]);
    }
    
    
    /**
     * Получить товар
     * 
     * @param array $data
     * @return Goods
     */
    private function findGood($data)
    {
        if (isset($data['id'])){
            $good = $this->entityManager->getRepository(Goods::class)
                    ->findOneByAplId($data['id']);
            if ($good){
                return $good;
            }
        }
//        var_dump($data); exit;
        if (isset($data['maker'])){
            $producer = $this->findProducer($data['maker']);
            if ($producer){
                $codeFilter = new ArticleCode();
                if (isset($data['name'])){
                    $code = $codeFilter->filter($data['name']);
                    if ($code){
                        $good = $this->entityManager->getRepository(Goods::class)
                                ->findOneBy(['code' => $code, 'producer' => $producer->getId()]);
                        if ($good){
                            return $good;
                        }
                        $name = '';
                        if (isset($data['g5']['bestname'])){
                            $name = $data['g5']['bestname'];
                        } elseif (isset($data['g5']['artname'])) {
                            $name = $data['g5']['artname'];
                        } elseif (isset($data['comment'])) {
                            $name = $data['comment'];
                        }
                        return $this->assemblyManager->addNewGood($code, $producer, NULL, $data['id'], mb_substr($name, 0, 255));
                    }    
                }    
            }    
        }    
        return;
    }
    
    /**
     * Получить статус документа ПТУ
     * 
     * @param array $data
     * @return integer
     */
    private function getPtuStatus($data)
    {
        $ptuStatus = Ptu::STATUS_ACTIVE;
        if ($data['publish'] == 0){
            $ptuStatus = Ptu::STATUS_RETIRED;            
        }
        if (isset($data['desc'])){
            if (isset($data['desc']['comiss']) == 1){
                $ptuStatus = Ptu::STATUS_COMMISSION;
            }
        }                
        
        return $ptuStatus;
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
     * Загрузить ПТУ
     * 
     * @param array $data
     */
    public function unloadPtu($data)
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
     * Получить статус документа ВТП
     * 
     * @param array $data
     * @return integer
     */
    private function getVtpStatus($data)
    {
        $vtpStatus = Vtp::STATUS_ACTIVE;
        if ($data['publish'] == 0){
            $vtpStatus = Vtp::STATUS_RETIRED;            
        }
        
        return $vtpStatus;
    }
    
    
    /**
     * Загрузить ВТП
     * 
     * @param array $data
     */
    public function unloadVtp($data)
    {
//        var_dump($data); exit;
        $docDate = $data['ds'];
        $dateValidator = new Date();
        $dateValidator->setFormat('Y-m-d H:i:s');
        if (!$dateValidator->isValid($docDate)){
            $docDate = $data['created'];
        }
        
        $dataVtp = [
            'apl_id' => $data['id'],
            'doc_date' => $docDate,
            'comment' => $data['info'],
            'status_ex' => Vtp::STATUS_EX_APL,
            'status' => $this->getVtpStatus($data),
        ];
        
        $ptuAplId = $data['ns'];
        $ptu = $this->entityManager->getRepository(Ptu::class)
                ->findOneByAplId(['aplId' => $ptuAplId]);
        
        if ($ptu){        
//        var_dump($data); exit;
            $vtp = $this->entityManager->getRepository(Vtp::class)
                    ->findOneByAplId($data['id']);

            if ($vtp){
                $this->vtpManager->updateVtp($vtp, $dataVtp);
                $this->vtpManager->removeVtpGood($vtp); 
            } else {        
                $vtp = $this->vtpManager->addVtp($ptu, $dataVtp);
            }    

            if ($vtp && isset($data['tp'])){
                $rowNo = 1;
                foreach ($data['tp'] as $tp){
                    if (isset($tp['good'])){
                        $good = $this->findGood($tp['good']);   
                    }    
                    if (empty($good)){
        //                throw new \Exception("Не удалось создать карточку товара для документа {$data['id']}");
                    } else {

                        $this->vtpManager->addVtpGood($vtp->getId(), [
                            'status' => $vtp->getStatus(),
                            'statusDoc' => $vtp->getStatusDoc(),
                            'quantity' => $tp['sort'],                    
                            'amount' => $tp['bag_total'],
                            'good_id' => $good->getId(),
                            'comment' => '',
                            'info' => '',
                        ], $rowNo);
                        $rowNo++;
                    }    
                }
            }  

            if ($vtp){
                $this->vtpManager->updateVtpAmount($vtp);
                return true;            
            }
        }    
                
        return false;
    }

    /**
     * Получить статус документа ОТ
     * 
     * @param array $data
     * @return integer
     */
    private function getOtStatus($data)
    {
        $otStatus = Ot::STATUS_ACTIVE;
        if ($data['ns'] == '!ИНВ'){
            $otStatus = Ot::STATUS_INVENTORY;
        }
        if ($data['publish'] == 0){
            $otStatus = Ot::STATUS_RETIRED;            
        }
        
        return $otStatus;
    }
    
    /**
     * Загрузить ОТ
     * 
     * @param array $data
     */
    public function unloadOt($data)
    {
//        var_dump($data); exit;
        $docDate = $data['ds'];
        $dateValidator = new Date();
        $dateValidator->setFormat('Y-m-d H:i:s');
        if (!$dateValidator->isValid($docDate)){
            $docDate = $data['created'];
        }
        
        $dataOt = [
            'apl_id' => $data['id'],
            'doc_no' => $data['ns'],
            'doc_date' => $docDate,
            'comment' => $data['info'],
            'status_ex' => Ot::STATUS_EX_APL,
            'status' => $this->getOtStatus($data),
        ];
        
        if (!empty($data['comiss'])){
            if ($data['comiss'] == 1){
                if (!empty($data['comitent'])){
                    $contact = null;
                    $user = $this->entityManager->getRepository(User::class)
                            ->findOneBy(['aplId' => $data['comitent']]);
                    if ($user){
                        $contact = $this->entityManager->getRepository(Contact::class)
                                ->findOneBy(['user' => $user->getId()]);
                    }    
                    if (!$contact){
                        $client = $this->entityManager->getRepository(AplClient::class)
                                ->findOneBy(['aplId' => $data['comitent']]);
                        if ($client){
                            $contact = $this->entityManager->getRepository(Contact::class)
                                    ->findOneBy(['client' => $client->getId()]);
                        }    
                    }
                    if ($contact){
                        $dataOt['status'] = Ot::STATUS_COMMISSION;
                        $dataOt['comiss'] = $contact;
                    } else {
                        var_dump($data);
                        exit;
                    }                         
                }    
            }
        }    

        $office = $this->officeFromAplId($data['name']);
        $company = $this->entityManager->getRepository(Office::class)
                    ->findDefaultCompany($office);
        
        $dataOt['office'] = $office;
        $dataOt['company'] = $company;
        
        $ot = $this->entityManager->getRepository(Ot::class)
                ->findOneByAplId($data['id']);
        if ($ot){
            $this->otManager->updateOt($ot, $dataOt);
            $this->otManager->removeOtGood($ot); 
        } else {        
            $ot = $this->otManager->addOt($dataOt);
        }    
        
        if ($ot && isset($data['tp'])){
            $rowNo = 1;
            foreach ($data['tp'] as $tp){
                if (isset($tp['good'])){
                    $good = $this->findGood($tp['good']);   
                }    
                if (empty($good)){
    //                throw new \Exception("Не удалось создать карточку товара для документа {$data['id']}");
                } else {

                    $this->otManager->addOtGood($ot->getId(), [
                        'status' => $ot->getStatus(),
                        'statusDoc' => $ot->getStatusDoc(),
                        'quantity' => $tp['sort'],                    
                        'amount' => $tp['bag_total'],
                        'good_id' => $good->getId(),
                        'comment' => '',
                        'info' => '',
                    ], $rowNo);
                    $rowNo++;
                }    
            }
        }  
        
        if ($ot){
            $this->otManager->updateOtAmount($ot);
            return true;            
        }
                
        return false;
    }

    /**
     * Получить статус документа СТ
     * 
     * @param array $data
     * @return integer
     */
    private function getStStatus($data)
    {
        $stStatus = St::STATUS_ACTIVE;
        if ($data['publish'] == 0){
            $stStatus = St::STATUS_RETIRED;            
        }
        
        return $stStatus;
    }
    
    /**
     * Загрузить СТ
     * 
     * @param array $data
     */
    public function unloadSt($data)
    {
//        var_dump($data); exit;
        $docDate = $data['ds'];
        $dateValidator = new Date();
        $dateValidator->setFormat('Y-m-d H:i:s');
        if (!$dateValidator->isValid($docDate)){
            $docDate = $data['created'];
        }
        
        $dataSt = [
            'apl_id' => $data['id'],
            'doc_no' => $data['ns'],
            'doc_date' => $docDate,
            'comment' => $data['info'],
            'status_ex' => St::STATUS_EX_APL,
            'status' => $this->getStStatus($data),
        ];
        
        $result = false;
        if (!empty($data['kind'])){
            if ($data['kind'] == 'out6'){
                if (!empty($data['parentout6'])){
                    $user = $this->entityManager->getRepository(User::class)
                            ->findOneBy(['aplId' => $data['parentout6']]);
                    if ($user){
                        $dataSt['writeOff'] = St::WRITE_PAY;
                        $dataSt['user'] = $user;
                        $result = true;
                    }
                }    
            }
            if ($data['kind'] == 'out5'){
                if (!empty($data['parentout5'])){
                    $cost = $this->entityManager->getRepository(Cost::class)
                            ->findOneBy(['aplId' => $data['parentout5']]);
                    if ($cost){
                        $dataSt['writeOff'] = St::WRITE_COST;
                        $dataSt['cost'] = $cost;
                        $result = true;
                    }
                }    
            }
            if ($data['ns'] == '!ИНВ'){
                $dataSt['writeOff'] = St::WRITE_INVENTORY;
                $dataSt['user'] = null;
                $dataSt['cost'] = null;
                $result = true;
            }
        }    
        if (!$result){
            var_dump($data);
            exit;
        }

        $office = $this->officeFromAplId($data['parent']);
        $company = $this->entityManager->getRepository(Office::class)
                    ->findDefaultCompany($office);
        
        $dataSt['office'] = $office;
        $dataSt['company'] = $company;
        
        $st = $this->entityManager->getRepository(St::class)
                ->findOneByAplId($data['id']);

        if ($st){
            $this->stManager->updateSt($st, $dataSt);
            $this->stManager->removeStGood($st); 
        } else {        
            $st = $this->stManager->addSt($dataSt);
        }    
        
        if ($st && isset($data['tp'])){
            $rowNo = 1;
            foreach ($data['tp'] as $tp){
                if (isset($tp['good'])){
                    $good = $this->findGood($tp['good']);   
                }    
                if (empty($good)){
    //                throw new \Exception("Не удалось создать карточку товара для документа {$data['id']}");
                } else {

                    $this->stManager->addStGood($st->getId(), [
                        'status' => $st->getStatus(),
                        'statusDoc' => $st->getStatusDoc(),
                        'quantity' => $tp['sort'],                    
                        'amount' => $tp['bag_total'],
                        'good_id' => $good->getId(),
                        'comment' => '',
                        'info' => '',
                    ], $rowNo);
                    $rowNo++;
                }    
            }
        }  
        
        if ($st){
            $this->stManager->updateStAmount($st);
            return true;            
        }
                
        return false;
    }

    /**
     * Получить статус документа ПТ
     * 
     * @param array $data
     * @return integer
     */
    private function getPtStatus($data)
    {
        $ptStatus = Pt::STATUS_ACTIVE;
        if ($data['publish'] == 0){
            $ptStatus = Pt::STATUS_RETIRED;            
        }
        
        return $ptStatus;
    }

    /**
     * Загрузить ПТ
     * 
     * @param array $data
     */
    public function unloadPt($data)
    {
//        var_dump($data); exit;
        $docDate = $data['ds'];
        $dateValidator = new Date();
        $dateValidator->setFormat('Y-m-d H:i:s');
        if (!$dateValidator->isValid($docDate)){
            $docDate = $data['created'];
        }
        
        $dataPt = [
            'apl_id' => $data['id'],
            'doc_no' => $data['ns'],
            'doc_date' => $docDate,
            'comment' => $data['info'],
            'status_ex' => Pt::STATUS_EX_APL,
            'status' => $this->getPtStatus($data),
        ];
        
        $office = $this->officeFromAplId($data['parent']);
        $company = $this->entityManager->getRepository(Office::class)
                    ->findDefaultCompany($office);
        
        $dataPt['office'] = $office;
        $dataPt['company'] = $company;
        
        $office2 = $this->officeFromAplId($data['name']);
        $company2 = $this->entityManager->getRepository(Office::class)
                    ->findDefaultCompany($office2);
        
        $dataPt['office2'] = $office2;
        $dataPt['company2'] = $company2;

        $pt = $this->entityManager->getRepository(Pt::class)
                ->findOneByAplId($data['id']);
        if ($pt){
            $this->ptManager->updatePt($pt, $dataPt);
            $this->ptManager->removePtGood($pt); 
        } else {        
            $pt = $this->ptManager->addPt($dataPt);
        }    
        
        if ($pt && isset($data['tp'])){
            $rowNo = 1;
            foreach ($data['tp'] as $tp){
                if (isset($tp['good'])){
                    $good = $this->findGood($tp['good']);   
                }    
                if (empty($good)){
    //                throw new \Exception("Не удалось создать карточку товара для документа {$data['id']}");
                } else {

                    $this->ptManager->addPtGood($pt->getId(), [
                        'status' => $pt->getStatus(),
                        'statusDoc' => $pt->getStatusDoc(),
                        'quantity' => $tp['sort'],                    
                        'amount' => $tp['bag_total'],
                        'good_id' => $good->getId(),
                        'comment' => '',
                        'info' => '',
                    ], $rowNo);
                    $rowNo++;
                }    
            }
        }  
        
        if ($pt){
            $this->ptManager->updatePtAmount($pt);
            return true;            
        }
                
        return false;
    }

    /**
     * Обновить статус загруженного документа
     * @param integer $aplDocId
     * @return boolean
     */
    public function unloadedDoc($aplDocId)
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
     * Загрузить документ из Апл
     * 
     * @return 
     */
    public function unloadDoc()
    {
        $url = $this->aplApi().'unload-doc?api='.$this->aplApiKey();
        
        $post = [
        ];

        $client = new Client();
        $client->setUri($url);
        $client->setMethod('POST');
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
            if (isset($result['type'])){
                switch ($result['type']){
                    case 'Suppliersorders': 
                        if ($this->unloadPtu($result)){ 
                            $this->unloadedDoc($result['id']);
                        }    
                        break;                        
                    case 'Resup': 
                        if ($this->unloadVtp($result)){ 
                            $this->unloadedDoc($result['id']);
                        }    
                        break;                        
                    case 'Postings': 
                        if ($this->unloadOt($result)){ 
                            $this->unloadedDoc($result['id']);
                        }    
                        break;                        
                    case 'Writings': 
                        if ($this->unloadSt($result)){ 
                            $this->unloadedDoc($result['id']);
                        }    
                        break;                        
                    case 'Relocations': 
                        if ($this->unloadPt($result)){ 
                            $this->unloadedDoc($result['id']);
                        }    
                        break;                        
                    default; break;    
                }                
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     * Загрузка документов
     * 
     * @return
     */
    public function unloadDocs()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(900);
        $startTime = time();
        
        while (true){
            if ($this->unloadDoc()) {
                usleep(100);
                if (time() > $startTime + 840){
                    break;
                }
            } else {
                break;
            }    
        }    
        return;
    }    
}
