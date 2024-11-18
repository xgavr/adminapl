<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiSupplier\Service;

use Application\Entity\Supplier;
use Application\Entity\RequestSetting;
use Laminas\Http\Client;
use Application\Entity\SupplierApiSetting;
use Stock\Entity\Ptu;
use Company\Entity\Office;
use Company\Entity\Contract;

/**
 * Description of MikadoManager
 * 
 * @author Daddy
 */
class MikadoManager {
    
    const host = 'http://www.mikado-parts.ru';

    /**
     * Adapter
     */
    const HTTPS_ADAPTER = 'Laminas\Http\Client\Adapter\Curl';  
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * 
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;

    /**
     * 
     * @var \Stock\Service\PtuManager
     */
    private $ptuManager;

    /**
     * 
     * @var \Application\Service\BillManager
     */
    private $billManager;

    public function __construct($entityManager, $adminManager, $ptuManager, $billManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->ptuManager = $ptuManager;
        $this->billManager = $billManager;
    }
    
    /**
     * Обработка ошибок
     * @param \Laminas\Http\Response $response
     */
    private function exception($response)
    {
        switch ($response->getStatusCode()) {
            case 400: //Invalid code
            case 401: //The access token is invalid or has expired
            case 403: //The access token is missing
            default:
                $error = $response->getContent();
                $error_msg = $response->getStatusCode().' '.$response->getReasonPhrase();
                if (isset($error['error'])){
                    $error_msg .= ' ('.$error['error'].')';
                }
                if (isset($error['error_description'])){
                    $error_msg .= ' '.$error['error_description'];
                }
                if (isset($error['message'])){
                    $error_msg .= ' '.$error['message'];
                }
                if (isset($error['Errors'])){
                    foreach ($error['Errors'] as $error){
                        $error_msg .= PHP_EOL.' '.$error['errorCode'].' '.$error['message'].' '.$error['url'];
                        
                    }
                }
//                throw new \Exception($error_msg);
                return ['message' => $error_msg];
        }
        
        throw new \Exception('Неопознаная ошибка');
    }    
    
    /**
     * Поставка
     * 
     * @param SupplierApiSetting $supplierApi
     * @param string $deliveryID
     */
    private function deliveryInfo($supplierApi, $deliveryID)
    {
        $uri = '/ws1/deliveries.asmx/Delivery_Info';
        
        if (!$supplierApi){
            throw new \Exception('Нет настроек АПИ для Микадо');
        }
                
        $client = new Client();
        $client->setUri(self::host.$uri);
        $client->setAdapter(self::HTTPS_ADAPTER);
        $client->setMethod('GET');
        $client->setOptions(['timeout' => 30]);
        
        $client->setParameterGet([
            'DeliveryID' => $deliveryID,
            'nClientID' => $supplierApi->getLogin(),
            'Password' => $supplierApi->getPassword()
        ]);

        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isOk()){
            return $response->getBody();            
        }

        return $this->exception($response);        
    }

    /**
     * Поставки за период
     * @param SupplierApiSetting $supplierApi
     * @return xml
     * @throws \Exception
     */
    private function deliveries($supplierApi)
    {
        $uri = '/ws1/deliveries.asmx/Delivery_List';
        
        if (!$supplierApi){
            throw new \Exception('Нет настроек АПИ для Микадо');
        }
                
        $client = new Client();
        $client->setUri(self::host.$uri);
        $client->setAdapter(self::HTTPS_ADAPTER);
        $client->setMethod('GET');
        $client->setOptions(['timeout' => 30]);
        
        $client->setParameterGet([
            'Date_From' => date('Y-m-d', strtotime("-1 day")),
            'Date_To' => date('Y-m-d'),
            'nClientID' => $supplierApi->getLogin(),
            'Password' => $supplierApi->getPassword()
        ]);

        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
        ]);

        $client->setHeaders($headers);
        
//        var_dump($client->getUri()); exit;
        
        $response = $client->send();
        
        if ($response->isOk()){
            return $response->getBody();            
        }

        return $this->exception($response);
    }
    
    /**
     * Получить ПТУ
     *
     * @param Supplier $supplier 
     * @param array $data
     * 
     * @return Ptu 
     */
    public function deliveryToPtu($supplier, $data)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(900);

        $docDate = date('Y-m-d', strtotime($data['DelDate']));
        
        $dataPtu = [
            'apl_id' => 0,
            'doc_no' => $data['DelNumber'],
            'doc_date' => $docDate,
            'status_ex' => Ptu::STATUS_EX_UPL,
            'status' => Ptu::STATUS_ACTIVE,
            'supplier' => $supplier,
        ];
            
//        var_dump($docDate); exit;
        
        $office = $supplier->getOffice();
        
        $legal = $this->entityManager->getRepository(Supplier::class)
                ->findDefaultSupplierLegal($supplier, $docDate);
        
        $contract = $this->entityManager->getRepository(Office::class)
                ->findDefaultContract($office, $legal, $docDate, Contract::PAY_CASH);
            
        $dataPtu['office'] = $office;
        $dataPtu['legal'] = $legal;
        $dataPtu['contract'] = $contract; 
            
        $ptu = $this->entityManager->getRepository(Ptu::class)
                ->findOneBy(['supplier' => $supplier->getId(), 'docNo' => $data['DelNumber'], 'docDate' => $docDate]);
            
        if ($ptu){
            $dataPtu['apl_id'] = $ptu->getAplId();
            $this->ptuManager->updatePtu($ptu, $dataPtu);
            $this->ptuManager->removePtuGood($ptu); 
        } else {        
            $ptu = $this->ptuManager->addPtu($dataPtu);
        }    
            
        if ($ptu && isset($data['DeliveryLines']['cDeliveryLine'])){
            $rowNo = 1;                
            foreach ($data['DeliveryLines']['cDeliveryLine'] as $tp){
                if (!empty($tp['QTY']) && !empty($tp['Name'])&& !empty($tp['PriceRUR'])){
                    
                    $good = $this->billManager->findGood($supplier, [
                        'article' => $tp['ProducerCode'],
                        'producer' => $tp['producer'],
                        'good_name' => $tp['Name'],
                        'supplier_article' => $tp['Code'],
                        'price' => $tp['PriceRUR'],                        
                    ]);
                    
                    if ($good){ 
                        $this->ptuManager->addPtuGood($ptu->getId(), [
                            'status' => $ptu->getStatus(),
                            'statusDoc' => $ptu->getStatusDoc(),
                            'quantity' => $tp['QTY'],                    
                            'amount' => round($tp['PriceRUR']*$tp['QTY'], 2),
                            'good_id' => $good->getId(),
                            'comment' => '',
                            'info' => '',
                            'countryName' => '',
                            'countryCode' => '',
                            'unitName' => '',
                            'unitCode' => '',
                            'ntd' => '',
                        ], $rowNo);
                        $rowNo++;
                    }    
                }    
            }
          

            if ($ptu){
                $this->ptuManager->updatePtuAmount($ptu);
                
                return true;
            }            
        }
        return false;
    }
    
    
    /**
     * Проверка накладной
     * @param SupplierApiSetting $supplierApi
     * @param array $cDelivery
     */
    private function cDelivery($supplierApi, $cDelivery)
    {
        if ($cDelivery['SumRUR'] > 0){
            $ptu = $this->entityManager->getRepository(Ptu::class)
                ->findOneBy([
                    'supplier' => $supplierApi->getSupplier()->getId(),
                    'docNo' => $cDelivery['DelNumber'], 
                    'docDate' => date('Y-m-d', strtotime($cDelivery['DelDate'])),
                ]);
            
            if ($ptu){
                return; //уже есть
            }
            
            $ptuXml = new \SimpleXMLElement($this->deliveryInfo($supplierApi, $cDelivery['DelNumber']));
            if (is_object($ptuXml)){

                $this->deliveryToPtu($supplierApi->getSupplier(), json_decode(json_encode($ptuXml), TRUE));
                
            }    
            
        }
        
        return;
    }
    
    /**
     * 
     */
    public function deliveriesToPtu()
    {
        $supplierApi = $this->entityManager->getRepository(SupplierApiSetting::class)
                ->findOneBy(['status' => SupplierApiSetting::STATUS_ACTIVE, 'name' => SupplierApiSetting::NAME_API_MIKADO]);

        $xml = new \SimpleXMLElement($this->deliveries($supplierApi));
        
        if (is_object($xml)){
            
            $data = json_decode(json_encode($xml), TRUE);
//            var_dump($data); exit;
            foreach ($data['Deliveries'] as $deliveries){
                if (count($deliveries) === 1){
                    $this->cDelivery($supplierApi, $deliveries);
                } else {
                    foreach ($deliveries as $delivery){
                        $this->cDelivery($supplierApi, $delivery);
                    }    
                }    
            }
        }
        
        return;
    }
}
