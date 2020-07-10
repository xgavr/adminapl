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

    public function __construct($entityManager, $adminManager, $ptuManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->ptuManager = $ptuManager;
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
            return $this->entityManager->getRepository(Supplier::class)
                    ->fundDefaultSupplierLegal($supplier, $dateStart);
        }    
        
        return;        
    }
    
    /**
     * Получить контракт по умолчанию
     * 
     * @param Office $office
     * @param Legal $legal
     * 
     * @return Contract
     */
    private function findDefaultContract($office, $legal)
    {
        
    }
    
    /**
     * Загрузить ПТУ
     * 
     * @param array $data
     */
    public function unloadPtu($data)
    {
        $ptu = [
            'doc_no' => $data['ns'],
            'doc_date' => $data['ds'],
            'comment' => $data['comment'],
        ];
        
        if (isset($data['desc'])){
            $ptu['info'] = Encoder::encode($data['desc']);
        }
        
        $office = $this->officeFromAplId($data['parent']);
        $legal = $this->legalFromSupplierAplId($data['name']);
        $ptu['office_id'] = $office->getId();
        $ptu['legal_id'] = $legal->getId();
        $ptu['contract_id'] = ''; 
        
        return;
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
        
        try{
            $result = Decoder::decode($body, \Laminas\Json\Json::TYPE_ARRAY);
        } catch (Exception $ex) {
            return;
        }
        
        if (is_array($result)){
            if (isset($result['type'])){
                switch ($result['type']){
                    case 'Suppliersorders': $this->unloadPtu($result); break;                        
                    default; break;    
                }                
            }
        }
        return;
    }

}
