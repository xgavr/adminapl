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
use Application\Entity\Producer;
use Application\Filter\ProducerName;
use Application\Entity\UnknownProducer;
use Application\Entity\Goods;
use Application\Filter\ArticleCode;
use Laminas\Escaper\Escaper;


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
            $legalManager, $producerManager, $assemblyManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->ptuManager = $ptuManager;
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
     * @return Legal
     */
    private function legalFromSupplierAplId($supplierAplId, $dateStart = null)
    {
        $supplier = $this->entityManager->getRepository(Supplier::class)
                ->findOneBy(['aplId' => $supplierAplId]);
        
        if ($supplier){
            $legal = $this->entityManager->getRepository(Supplier::class)
                    ->fundDefaultSupplierLegal($supplier, $dateStart);
            
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
                ]);
            }
            
            return $legal;
        }    
        
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
        $contract = $this->entityManager->getRepository(Office::class)
                ->findDefaultContract($office, $legal, $dateStart, $pay);
        
        if (!$contract){
            $contract = $this->legalManager->addContract($legal, 
                    [
                        'office' => $office->getId(),
                        'name' => '',
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

                        return $this->assemblyManager->addNewGood($code, $producer, NULL, $data['id']);
                    }    
                }    
            }    
        }    
        return;
    }
    
    /**
     * Загрузить ПТУ
     * 
     * @param array $data
     */
    public function unloadPtu($data)
    {
//        var_dump($data); exit;
        
        $dataPtu = [
            'apl_id' => $data['id'],
            'doc_no' => $data['ns'],
            'doc_date' => $data['ds'],
            'comment' => $data['comment'],
            'status_ex' => Ptu::STATUS_EX_APL,
        ];
        
        $cashless = Contract::PAY_CASH;
        if (isset($data['desc'])){
            if (isset($data['desc']['cashless'])){
                if ($data['desc']['cashless'] == 1){
                    $cashless = Contract::PAY_CASHLESS;
                }
            }
            $dataPtu['info'] = Encoder::encode($data['desc']);
        }
        
        $office = $this->officeFromAplId($data['parent']);
        $legal = $this->legalFromSupplierAplId($data['name'], $data['ds']);
        $contract = $this->findDefaultContract($office, $legal, $data['ds'], $data['ns'], $cashless);
        
        $dataPtu['office_id'] = $office->getId();
        $dataPtu['legal_id'] = $legal->getId();
        $dataPtu['contract_id'] = $contract->getId(); 
        
        $ptu = $this->entityManager->getRepository(Ptu::class)
                ->findOneByAplId($data['id']);
        if ($ptu){
            $this->ptuManager->updatePtu($ptu, $dataPtu);
            $this->ptuManager->removePtuGood($ptu); 
        } else {        
            $ptu = $this->ptuManager->addPtu($dataPtu);
        }    
        
        $rowNo = 1;
        foreach ($data['tp'] as $tp){
            if (isset($tp['good'])){
                $good = $this->findGood($tp['good']);   
            }    
            if (empty($good)){
                throw new \Exception("Не удалось создать карточку товара для документа {$data['id']}");
            }
            
            $this->ptuManager->addPtuGood($ptu->getId(), [
                'quantity' => $tp['sort'],
                'amount' => $tp['bag_total'],
                'good_id' => $good->getId(),
                'comment' => '',
                'info' => '',
                'countryName' => $tp['country'],
                'countryCode' => $tp['countrycode'],
                'unitName' => $tp['pack'],
                'unitCode' => $tp['packcode'],
                'ntd' => $tp['gtd'],
            ], $rowNo);
            $rowNo++;
        }
        
        $this->ptuManager->updatePtuAmount($ptu);
        
        return;
    }
    
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
            $result = Decoder::decode($body, \Laminas\Json\Json::TYPE_ARRAY);
        } catch (\Laminas\Json\Exception\RuntimeException $ex) {
            var_dump($ex->getMessage());
            var_dump($body);
            exit;
        }
        var_dump($result); exit;

        if (is_array($result)){
            if (isset($result['type'])){
                switch ($result['type']){
                    case 'Suppliersorders': 
                        $this->unloadPtu($result); 
                        $this->unloadedDoc($result['id']);
                        break;                        
                    default; break;    
                }                
            }
        }
        return;
    }

}
