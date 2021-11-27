<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Laminas\Json\Json;

use Application\Entity\Email;
use Application\Entity\Phone;
use Application\Entity\Messenger;
use User\Entity\User;
use Application\Entity\Client as AplClient;
use Company\Entity\Office;
use Application\Entity\Contact;
use Application\Entity\Supplier;
use Application\Entity\Raw;
use Application\Entity\Rawprice;
use Application\Entity\Goods;
use Application\Entity\Model;
use Application\Entity\Car;
use Application\Entity\Attribute;
use Application\Entity\AttributeValue;
use Bank\Entity\AplPayment;
use Company\Entity\Contract;
use Application\Entity\PriceGetting;
use Laminas\Http\Client;
use Application\Filter\ParseRawpriceApl;
use Application\Entity\Article;
use Application\Entity\CarFillVolume;

/**
 * Description of AplService
 *
 * @author Daddy
 */
class AplService {
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * User manager
     * @var \User\Service\UserManager
     */
    private $userManager;

    /**
     * Client manager
     * @var \Application\Service\ClientManager
     */
    private $clientManager;

    /**
     * User manager
     * @var \Application\Service\ContactManager
     */
    private $contactManager;

    /**
     * Supplier manager
     * @var \Application\Service\SupplierManager
     */
    private $supplierManager;

    /**
     * Legal manager
     * @var \Company\Service\LegalManager
     */
    private $legalManager;
    
    /**
     * Telegram manager
     * @var \Admin\Service\TelegramManager
     */
    private $telegramManager;
    
    /**
     * Admin manager
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;
    
    public function __construct($entityManager, $userManager, $contactManager, 
            $supplierManager, $legalManager, $telegramManager, $adminManager,
            $clientManager)
    {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->clientManager = $clientManager;
        $this->contactManager = $contactManager;
        $this->supplierManager = $supplierManager;
        $this->legalManager = $legalManager;
        $this->telegramManager = $telegramManager;
        $this->adminManager = $adminManager;
    }
    
    public function aplApi()
    {
        return 'https://autopartslist.ru/api/';
        
    }
    
    public function aplApiKey()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        return md5(date('Y-m-d').'#'.$settings['apl_secret_key']);
    }
    
    protected function getOffice($officeAplId)
    {
        if ($officeAplId){
            $office = $this->entityManager->getRepository(Office::class)
                    ->findOneByAplId($officeAplId);
            
            return $office;
        }
        
        return;
    }
    
    /*
     * Создать заказ в апл
     * array $data
     */
    public function checkout($data)
    {
        $url = $this->aplApi().'checkout?api='.$this->aplApiKey();
        
        $client = new Client();
        $client->setUri($url);
        $client->setMethod('POST');
        $client->setParameterPost($data);
        $client->setOptions(['timeout' => 30]);

        $response = $client->send();

        if ($response->isSuccess()) {
            $body = $response->getBody();
            return (array) Json::decode($body);
        }
        
        return;
    }

    /**
     * Обновить юр.лицо поставщика в Апл
     * 
     * @param Supplier $supplier
     * @param Contract $contract
     */
    public function updateSupplierLegal($supplier, $contract)
    {
        $legal = $contract->getLegal();
        if ($supplier->getAplId()){

            $url = $this->aplApi().'update-supplier-legal?api='.$this->aplApiKey();
            
            $post = $legal->getAplTransfer();
            $post['contract'] = $contract->getAct();
            $post['contractdate'] = $contract->getDateStart();
            $post['supplierId'] = $supplier->getAplId();
            
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setOptions(['timeout' => 30]);
            $client->setParameterPost($post);

            $ok = $result = false;
            try{
                $response = $client->send();
//                var_dump($response->getBody()); exit;
                if ($response->isOk()) {
                    $ok = $result = true;
                }
            } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                $ok = true;
            }    
        }
        
        return;
    }
    
    /**
     * Создать поставщика в Апл
     * 
     * @param Supplier $supplier
     */
    public function addSupplier($supplier)
    {
        if (!$supplier->getAplId()){
            $url = $this->aplApi().'add-supplier?api='.$this->aplApiKey();

            $manualDesc = $manualSite = $manualLogin = $manualPassword = null;            
            $requestSettings = $supplier->getActiveManualRequestSetting();
            foreach ($requestSettings as $requestSetting){
                $manualDesc .= $requestSetting->getDescription().PHP_EOL;
                $manualSite = $requestSetting->getSite();
                $manualLogin = $requestSetting->getLogin();
                $manualPassword = $requestSetting->getPassword();
            }
            
            $manualManager = $manualPhone = $manualEmail = null;
            foreach ($supplier->getOtherContacts() as $contact){
                if ($contact->getStatus() == Contact::STATUS_ACTIVE){
                    $manualManager = $contact->getName();
                    $manualPhone = $contact->getPhonesAsString();
                    $manualEmail = $contact->getEmail;
                }    
            }

            $ftp = $ftpuser = $ftppassw = $email = $epassw = $download = null;
            foreach ($supplier->getPriceGettings() as $priceGetting){
                if ($priceGetting->getStatus() == PriceGetting::STATUS_ACTIVE){
                    $ftp = $priceGetting->getFtp();
                    $ftpuser = $priceGetting->getFtpLogin();
                    $ftppassw = $priceGetting->getEmail();
                    $email = $priceGetting->getEmail();
                    $epassw = $priceGetting->getEmailPassword();
                    $download = $priceGetting->getLink();
                }    
            }
            
            
            $desc = [
                'prepay' => $supplier->getAplPrepayStatus(),
                'yml' => $supplier->getAplPriceListStatus(),
                'manualDesc' => $manualDesc,
                'manualSite' => $manualSite,
                'manualLogin' => $manualLogin,
                'manualPassword' => $manualPassword,
                'manualManager' => $manualManager,
                'manualPhone' => $manualPhone,
                'manualEmail' => $manualEmail,
                'ftp' => $ftp,
                'ftpuser' => $ftpuser,
                'ftppassw' => $ftppassw,
                'email' => $email,
                'epassw' => $epassw,
                'download' => $download,
            ]; 
            
            $post = [
                'name' => $supplier->getName(),
                'publish' => $supplier->getAplStatus(),
                'desc' => Json::encode($desc),
            ];
            
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setOptions(['timeout' => 30]);
            $client->setParameterPost($post);

            $ok = $result = false;
            try{
                $response = $client->send();
    //                var_dump($response->getBody()); exit;
                if ($response->isOk()) {
                    $ok = $result = true;
                    if (is_numeric($response->getBody())){
                        $supplier->setAplId($response->getBody());
                        $this->entityManager->persist($supplier);
                        $this->entityManager->flush($supplier);
                    }
                }
            } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                $ok = true;
            }    
        }    
        return;
    }
    
    /*
     * Загрузка поставщиков
     */
    public function getSuppliers()
    {
        $url = $this->aplApi().'get-suppliers?api='.$this->aplApiKey();
        
        $data = file_get_contents($url);

        if ($data){
            $data = (array) Json::decode($data);
        } else {
            $data = [];
        }
        
        if (count($data)){
            foreach ($data as $item){                
                $row = (array) $item;

                $supplier = null;
                if ($row['id']){
//                    var_dump($row); exit;
                    $supplier = $this->entityManager->getRepository(Supplier::class)
                            ->findOneByAplId($row['id']);
                    
                    if (!$supplier){
                        $data = [
                            'name' => $row['name'],
                            'aplId' => $row['id'],
                            'status' => ($row['publish'] == 1 ? 1:2),
                        ];
                        
                        $supplier = $this->supplierManager->addNewSupplier($data);
                    }
                    
                    if ($supplier){
                        $legalContact = $supplier->getLegalContact();

                        if (!$legalContact){
                            $contact_data = [];
                            $contact_data['full_name'] = $contact_data['name'] = $supplier->getName();
                            $contact_data['status'] = Contact::STATUS_LEGAL;
                            $this->contactManager->addNewContact($supplier, $contact_data); //Legal contact
                            $legalContact = $supplier->getLegalContact();                    
                        } 
                            
                        $legals = $legalContact->getLegals();
                        
                        $legal_data = null;
                        if ($row['inn']){
                            $legal_data = [
                                'inn' => $row['inn'],
                                'kpp' => $row['kpp'],
                                'name' => $row['firmName'],
                                'ogrn' => $row['ogrn'],
                                'okpo' => $row['okpo'],
                                'address' => $row['firmAddress'],
                                'status' => $supplier->getStatus(),
                            ];
                        }    
                        
                        $legal = null;
                        if (is_array($legal_data)){
                            if (count($legals) == 0){                            
                                $legal = $this->legalManager->addLegal($legalContact, $legal_data, true);
                            } else {
                                $legal = $legals[0];
                                $legal_data['dateStart'] = $legal->getDateStart();
                                $this->legalManager->addLegal($legalContact, $legal_data, true);
                            }    
                        }    
                                
                        if ($legal){
                            $bank_account_data = null;
                            if ($row['bik']){
                                $bank_account_data = [
                                    'bik' => $row['bik'],
                                    'name' => $row['bank'],
                                    'ks' => $row['firmAccount1'],
                                    'rs' => $row['firmAccount'],
                                    'status' => $supplier->getStatus(),
                                ];
                            }
                            if (is_array($bank_account_data)){
                                $bankAccounts = $legal->getBankAccounts();
                                if (count($bankAccounts)){
                                    $bankAccount = $bankAccounts[0];
                                    $this->legalManager->updateBankAccount($bankAccount, $bank_account_data, true);
                                } else {
                                    $this->legalManager->addBankAccount($legal, $bank_account_data, true);
                                }    
                            }
                            
                            $contract_data = null;
                            if ($row['contract']){
                                $contract_data = [
                                    'name' => 'Договор поставки',
                                    'act' => $row['contract'],
                                    'dateStart' => trim($row['contractdate'], ' T'),
                                    'status' => $supplier->getStatus(),
                                ];
                            }
                            if (is_array($contract_data)){
                                $contracts = $legal->getContracts();
                                if (count($contrats)){
                                    $contract = $contracts[0];
                                    $this->legalManager->updateContract($contract, $contract_data, true);                                                                    
                                } else {
                                    $this->legalManager->addContract($legal, $contract_data, true);                                
                                }
                            }
                        }
                        
                        if ($row['manualPhone'] || $row['manualManager'] || $row['manualEmail']){
                            if (count($contacts) == 1){
                                $manager_data = [
                                    'name' => $row['manualManager'],
                                    'description' => 'Менеджер',
                                    'phone' => $row['manualPhone'],
                                    'email' => $row['manualEmail'],
                                    'status' => $supplier->getStatus(),
                                ];
                                $this->contactManager->addNewContact($supplier, $manager_data); //Manager contact                            
                                $contacts = $supplier->getContacts();                    
                            }
                        }    
                        
                        $priceGettings = $supplier->getPriceGettings();
                        
                        if (count($priceGettings) == 0){
                            if ($row['download'] || $row['email'] ||$row['ftp']){
                                if ($row['download']) $priceGettingName = 'Загрузка прайса по ссылке';
                                if ($row['email']) $priceGettingName = 'Получение прайса по почте';
                                if ($row['ftp']) $priceGettingName = 'Загрузка прайса по фтп';
                                
                                $price_getting_data = [
                                    'name' => $priceGettingName,
                                    'ftp' => $row['ftp'],
                                    'ftpLogin' => $row['ftpuser'],
                                    'ftpPassword' => $row['ftppassw'],
                                    'email' => $row['email'],
                                    'emailPassword' => $row['epassw'],
                                    'link' => $row['download'],
                                    'status' => ($row['publish'] == 1 ? 1:2),
                                ];
                                
                                $this->supplierManager->addNewPriceGetting($supplier, $price_getting_data);
                            }    
                        }
                        
                        $billGettings = $supplier->getBillGettings();
                        
                        if (count($billGettings) == 0){
                            if ($row['billemail']){
                                
                                $bill_getting_data = [
                                    'name' => 'Получение электронных наклданых по почте',
                                    'email' => $row['billemail'],
                                    'emailPassword' => $row['billepassw'],
                                    'status' => ($row['publish'] == 1 ? 1:2),
                                ];
                                
                                $this->supplierManager->addNewBillGetting($supplier, $bill_getting_data);
                            }    
                        }

                        $requestSettings = $supplier->getRequestSettings();
                        
                        if (count($requestSettings) == 0){
                            if ($row['manualSite'] || $row['manualDesc']){
                                $request_setting_data = [
                                    'name' => 'Параметры ручного заказа',
                                    'description' => $row['manualDesc'],
                                    'site' => $row['manualSite'],
                                    'login' => $row['manualLogin'],
                                    'password' => $row['manualPassword'],
                                    'mode' => 1,
                                    'status' => ($row['publish'] == 1 ? 1:2),
                                ];

                                $this->supplierManager->addNewRequestSetting($supplier, $request_setting_data);
                            }

                            if ($row['portal']){
                                $request_setting_data = [
                                    'name' => 'Параметры автозаказа',
                                    'site' => $row['portal'],
                                    'login' => $row['portalUser'],
                                    'password' => $row['portalPass'],
                                    'mode' => 2,
                                    'status' => ($row['publish'] == 1 ? 1:2),
                                ];

                                $this->supplierManager->addNewRequestSetting($supplier, $request_setting_data);
                            }    
                        }
                        
                        $supplySettings = $supplier->getSupplySettings();
                        
                        if (count($supplySettings)){
                            foreach ($supplySettings as $supplySetting){
                                $this->supplierManager->removeSupplySetting($supplySetting);
                            }
                        }
                        
                        if (count($supplySettings) == 0){
                            if (count($row['offices'])){
                                $offices = (array) $row['offices'];
                                foreach ($offices as $office){
                                    $oldOffice = (array) $office;
                                    $desc = (array) Json::decode($oldOffice['desc']);
//                                    var_dump($desc); exit;
                                    $newOffice = $this->getOffice($oldOffice['parent']);
                                    
                                    if ($newOffice){
                                        $supply_setting_data = [
                                            'orderBefore' => trim($desc['orderbefore'], ' T'),
                                            'supplyTime' => $desc['speed'],
                                            'supplySat' => (($desc['satdlv']==1) ? 1:2),
                                            'status' => ($row['publish'] == 1 ? 1:2),
                                            'office' => $newOffice->getId(),
                                        ];

                                        $this->supplierManager->addNewSupplySetting($supplier, $supply_setting_data);
                                    }    
                                }   
                            }    
                        }

                    }
                }
            }
        }
    }




    /*
     * Загрузка сотрудников
     */
    public function getStaffPhone($contact)
    {
        $aplId = $contact->getUser()->getAplId();
        if ($aplId){
            $url = $this->aplApi().'get-staff-phone/id/'.$aplId.'?api='.$this->aplApiKey();

            $data = file_get_contents($url);
            if ($data === false){
                echo 'Потерян доступ к Апл!';
                exit;
            }
            if ($data){
                $phone = (array) Json::decode($data);
//                var_dump($phone);
                $this->contactManager->addPhone($contact, ['phone' => $phone['phone']], true);
            }
        }    
    }
   
    /**
     * Загрузить сотрудника
     * @param array $row
     */
    public function getStaff($row)
    {
        $user = $contact = null;
        $user = $this->entityManager->getRepository(User::class)
                ->findOneBy(['aplId' => $row['id']]);
        if (!$user && $row['email']){
            $email = $this->entityManager->getRepository(Email::class)
                    ->findOneByName($row['email']);

            if ($email){
               $contact = $email->getContact();
               if ($contact){
                   $user = $contact->getUser();
               }
            }

            if (!$user){
                $user = $this->entityManager->getRepository(User::class)
                        ->findOneByEmail($row['email']);
            }
        } 
        if (!$user && !empty($row['phone'])){
            $phone = $this->entityManager->getRepository(Phone::class)
                    ->findOneByName($row['phone']);

            if ($phone){
               $contact = $phone->getContact();
               if ($contact){
                   $user = $contact->getUser();
               }
            }                    
        }

        if ($user){

            $user_data = [
                'email' => $row['email'],
                'full_name' => $row['name'],
                'status' => ($row['publish'] == 1 ? 1:2),
                'roles' => $user->getRolesAsArray(),
                'aplId' => $row['id'],
            ];    
            if (!empty($desc['dob'])){
               $user_data['birthday'] = date_format(date_create($row['dob']), 'Y-m-d'); 
            }

            $this->userManager->updateUser($user, $user_data);

        } else {
            if ($row['email']){

                $roles = [3]; //сотрудник
                $user_data = [
                    'email' => $row['email'],
                    'full_name' => $row['name'],
                    'password' => $row['password_salt'],
                    'status' => ($row['publish'] == 1 ? 1:2),
                    'roles' => $roles,
                    'aplId' => $row['id'],
                    'birthday' => date_format(date_create($row['dob']), 'Y-m-d'),
                ];    

                $user = $this->userManager->addUser($user_data);                        
            }
        }

        if ($user){
            $contact = $user->getLegalContact();
            if ($contact){
                $contact_data = [
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'status' => Contact::STATUS_LEGAL,
                ];
                if (!empty($row['phone'])){
                   $contact_data['phone'] = $row['phone']; 
                }

                $this->contactManager->updateContact($contact, $contact_data, $user);                                                
            } else {
                $contact_data = [
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'status' => Contact::STATUS_LEGAL,
                ];
                if (!empty($row['phone'])){
                   $contact_data['phone'] = $row['phone']; 
                }

                $contact = $this->contactManager->addNewContact($user, $contact_data);                        
            }   

            //$desc = (array) Json::decode($row['desc']);
//                    var_dump($desc['icq']);
            if ($contact){
                $this->contactManager->updateSignature($contact, ['signature' => $row['signature']]);
                $this->contactManager->updateUserOffice($contact, ['office' => $this->getOffice($row['parent'])]);
            }

            $this->getStaffPhone($contact);
        }        
        return;
    }

    /**
     * Получить телефоны клента
     * 
     * @param Contact $contact
     */
    public function getClientPhone($contact)
    {
        $aplId = $contact->getClient()->getAplId();
        if ($aplId){
            $url = $this->aplApi().'get-staff-phone/id/'.$aplId.'?api='.$this->aplApiKey();

            $data = file_get_contents($url);
            if ($data === false){
                echo 'Потерян доступ к Апл!';
                exit;
            }
            if ($data){
                try{
                    $phone = (array) Json::decode($data);
    //                var_dump($phone);
                    $this->contactManager->addPhone($contact, ['phone' => $phone['phone']], true);
                } catch (\Laminas\Json\Exception\RuntimeException $ex){
                    return;
                }    
            }
        }   
        return;
    }
    
    /**
     * Обновить статус загруженного клиента
     * @param integer $userId
     * @return boolean
     */
    public function unloadedClient($userId)
    {
        $result = true;
        if (is_numeric($userId)){
            $url = $this->aplApi().'aa-user?api='.$this->aplApiKey();

            $post = [
                'userId' => $userId,
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

    /*
     * Получить клиента
     * @param array $row;
     */
    public function getClient($row)
    {
        $contact = null;
        
        $client = $this->entityManager->getRepository(AplClient::class)
                ->findOneBy(['aplId' => $row['id']]);
        
        if (!$client && !empty($row['email'])){
            $email = $this->entityManager->getRepository(Email::class)
                    ->findOneByName($row['email']);

            if ($email){
               $contact = $email->getContact();
               if ($contact){
                   $client = $contact->getClient();
               }
            } 
        }    
        if (!$client && !empty($row['phone'])){
            $phone = $this->entityManager->getRepository(Phone::class)
                    ->findOneByName($row['phone']);

            if ($phone){
               $contact = $phone->getContact();
               if ($contact){
                   $client = $contact->getClient();
               }
            }
        }

        $client_data = [
            'name' => $row['name'],
            'status' => ($row['publish'] == 1 ? AplClient::STATUS_ACTIVE:AplClient::STATUS_RETIRED),
            'aplId' => $row['id'],
        ];    

        if ($client){                    
            $this->clientManager->updClient($client, $client_data);                    
        } else {                            
            $client = $this->clientManager->addClient($client_data);                        
        }

        if ($client){
            $contact = $client->getLegalContact();
            if ($contact){
                $contact_data = [
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'status' => Contact::STATUS_LEGAL,
                ];
                if (!empty($row['phone'])){
                   $contact_data['phone'] = $row['phone']; 
                }

                $this->contactManager->updateContact($contact, $contact_data, $client);
            } else {
                $contact_data = [
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'status' => Contact::STATUS_LEGAL,
                ];
                if (!empty($row['phone'])){
                   $contact_data['phone'] = $row['phone']; 
                }

                $contact = $this->contactManager->addNewContact($client, $contact_data);                        
            }   

            $this->getClientPhone($contact);
        }    
        
        return $client;
    }

    public function uploadUsers()
    {
        set_time_limit(1800);
        $startTime = time();
        $url = $this->aplApi().'get-clients?api='.$this->aplApiKey();
        
        $data = file_get_contents($url);
        if ($data){
            $data = (array) Json::decode($data);
        } else {
            $data = [];
        }
        
        $items = $data['items'];
        if (count($items)){
            foreach ($items as $item){
                $row = (array) $item;
                if (!empty($row['desc'])){
                    $data = $row + Json::decode($row['desc'], Json::TYPE_ARRAY);
                } else {
                    $data = $row;
                }    
                unset($data['desc']);
//                var_dump($data); exit;
                if ($data['parent'] > 0){
                    $this->getStaff($data);
                } else {
                    $this->getClient($data);
                }                

                $this->unloadedClient($data['id']);
                usleep(100);
                if (time() > $startTime + 1740){
                    return;
                }
            }    
        }
        
        return;
    }
    /**
     * Сообщить в телеграм
     * 
     * @param array $params
     * @return type
     */
    public function sendTelegramMessage($params)
    {
        if (is_array($params)){
            if (isset($params['api_key'])){
                if ($params['api_key'] == $this->aplApiKey()){

//                    return $this->telegramManager->sendMessage([
//                        'chat_id' => $params['chat_id'], 
//                        'text' => $params['text'],
//                    ]);
                    
                    $this->telegramManager->addPostponeMesage([
                        'chat_id' => $params['chat_id'], 
                        'text' => $params['text'],
                    ]);

                }
            }    
        }
        return;
    }
    
    /**
     * Обновление Апл Ид производителя
     * 
     * @param \Application\Entity\Producer $producer
     * @return type
     */
    public function updateProducerAplId($producer)
    {
    $producerName = mb_strtoupper($producer->getName(), 'utf-8');

        $url = $this->aplApi().'get-maker-id?api='.$this->aplApiKey();
        
        $post = [
            'name' => $producer->getName(),
            'type' => $producer->getId(),
        ];

        $client = new Client();
        $client->setUri($url);
        $client->setMethod('POST');
        $client->setParameterPost($post);

        $response = $client->send();
        $body = $response->getBody();
        
        if (is_numeric($body)){
            $producer->setAplId((int) $body);
            $this->entityManager->persist($producer);
            $this->entityManager->flush();
        }

        return;
    }
    
    /**
     * Обновление AplId у производителей
     * 
     * @return type
     */
    public function updateProducersAplId()
    {
        set_time_limit(600);
        $startTime = time();
        
        $producers = $this->entityManager->getRepository(\Application\Entity\Producer::class)
                ->findProducerForUpdateAplId();
        
        foreach ($producers as $producer){
            $this->updateProducerAplId($producer);
            if (time() > $startTime + 500){
                return;
            }
            usleep(100);
        }
        return;
    }
    
    /**
     * Получить код товара
     * @param Goods $good
     */ 
    public function getGoodAplId($good)
    {
        $result = true;
        if ($good->getProducer()->getAplId()){
        
            $url = $this->aplApi().'get-good-id?api='.$this->aplApiKey();
            
            $post = [                
                'art' => $good->getCode(),
                'makerid' => $good->getProducer()->getAplId(),
                'createnew' => 1,
            ];
            if ($good->getGroupApl()>0){
                $post['g2'] = $good->getGroupApl();
            }
            if ($good->getAplId()){
                $post['id'] = $good->getAplId();
            }
            
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setOptions(['timeout' => 30]);
            $client->setParameterPost($post);

//            var_dump($body); exit; 
            $result = false;
            try {
                $response = $client->send();
                $body = $response->getBody();
                if (is_numeric($body)){
                    $this->entityManager->getRepository(Goods::class)
                            ->updateGoodId($good->getId(), ['apl_id' => $body]);
                    $result = true;
                }
            } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $ex){
//                var_dump($ex->getMessage());
                $result = false;
            }
        }
        
        return $result;
    }
    
    /**
     * Обновление AplId в товарах
     * 
     * @return type
     */
    public function updateGoodAplId()
    {
        set_time_limit(1800);
        $startTime = time();
        
        $goodsQuery = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateAplId();
        $iterable = $goodsQuery->iterate();
        foreach ($iterable as $row){
            foreach ($row as $good){
                $result = $this->getGoodAplId($good);
                $this->entityManager->detach($good);
                if (!$result){
                    return;
                }
            }    
            usleep(100);
            if (time() > $startTime + 1740){
                return;
            }
        }
        return;
    }
    
    /**
     * Получить код группы товара
     * @param Goods $good
     */ 
    public function getGroupAplId($good)
    {
        
        if ($good->getAplId()){
        
            $url = $this->aplApi().'get-group-id?api='.$this->aplApiKey();
            
            $post = [
                'id' => $good->getAplId(),
                'art' => $good->getCode(),
                'makerid' => $good->getProducer()->getAplId(),
                'createnew' => 1,
            ];
            
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setParameterPost($post);

            $response = $client->send();
            $body = $response->getBody();

            try {
                if (is_numeric($body)){
                    $this->entityManager->getRepository(Goods::class)
                            ->updateGoodId($good->getId(), ['group_apl' => $body]);
                    return true;
                }
            } catch (\Exception $ex) {
//                var_dump($ex->getMessage());
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Обновление группы в товарах
     * 
     * @return type
     */
    public function updateGroupAplId()
    {
        set_time_limit(1800);
        $startTime = time();
        
        $goodsQuery = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateGroupAplId();
        $iterable = $goodsQuery->iterate();
        foreach ($iterable as $row){
            foreach ($row as $good){
                $result = $this->getGroupAplId($good);
                if (!$result){
                    return;
                }
                $this->entityManager->detach($good);
            }    
            usleep(100);
            if (time() > $startTime + 1740){
                return;
            }
        }
        return;
    }
    
    /**
     * Обновить наименование товара в АПЛ
     * 
     * @param Goods $good
     */ 
    public function updateGoodName($good)
    {
        
        $result = true;
        if ($good->getName() && $good->getAplId()){
        
            $url = $this->aplApi().'update-bestname?api='.$this->aplApiKey();
            
            $post = [
                'goodId' => $good->getAplId(),
                'newname' => $good->getName(),
                'description' => $good->getDescription(),
                'nameok' => 0,
            ];
            
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setOptions(['timeout' => 30]);
            $client->setParameterPost($post);

            $ok = $result = false;
            try{
                $response = $client->send();
//                var_dump($response->getStatusCode()); exit;
                if ($response->isOk()) {
                    $ok = $result = true;
                }
                if ($response->getStatusCode() == 204) {
                    $this->entityManager->getRepository(Goods::class)
                            ->updateGood($good, ['aplId' => 0]);
                    $result = true;
                }
            } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                $ok = false;
            }    

            if ($ok){
                $this->entityManager->getRepository(Goods::class)
                        ->updateGood($good, ['statusNameEx' => Goods::NAME_EX_TRANSFERRED]);
            }
        }
        
        return $result;
    }
    
    /**
     * Обновление наименований в товарах
     * 
     * @return type
     */
    public function updateGoodNames()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(900);
        $startTime = time();
        
        $goodsQuery = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateName();
        
        $iterable = $goodsQuery->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $good){
                $result = $this->updateGoodName($good);
                $this->entityManager->detach($good);
                
                if (!$result){
                    return;
                }
            }    
            usleep(100);
            if (time() > $startTime + 840){
                return;
            }
        }
        return;
    }    
    
    /**
     * Обновить aplId машин
     * 
     * @param \Application\Entity\Make $make
     * @return null
     */
    public function getMakeAplId($make)
    {
        if ($make->getName()){

            $url = $this->aplApi().'get-brand-id?name='.urlencode($make->getTransferName()).'&api='.$this->aplApiKey();
            
//                var_dump($url); exit;
            $response = file_get_contents($url);
            try {
                if (is_numeric($response)){
//                    var_dump($response);
                    $make->setAplId($response);
                    $this->entityManager->persist($make);
                    $this->entityManager->flush($make);
                    return;
                }
            } catch (Exception $ex) {
//                var_dump($ex->getMessage());
                return;
            }
        }    
        
        return;        
    }

    public function updateMakeAplId()
    {
        $makes = $this->entityManager->getRepository(\Application\Entity\Make::class)
                ->findBy(['status' => \Application\Entity\Make::STATUS_ACTIVE, 'aplId' => 0]);
        foreach ($makes as $make){
            $this->getMakeAplId($make);
        }
        
        return;        
    }

    /**
     * Обновление aplId моделей авто
     * 
     * @param Model $model
     * @return null
     */
    public function getModelAplId($model)
    {
        if ($model->getMake()->getAplId() && $model->getTdId()){

            $url = $this->aplApi().'get-serie-id?api='.$this->aplApiKey();
            
            $sf = '';
            $intervals = explode('-', $model->getInterval());
            if (!empty(trim($intervals[0]))){
                $ym = explode('.', trim($intervals[0]));
                $sf = $ym[1].$ym[0];
            }
//            var_dump($sf); exit;
            
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setParameterPost([
                'parent' => $model->getMake()->getAplId(),
                'type' => $model->getTdId(),
                'name' => urlencode($model->getTransferName()),
                'desc' => $model->getInterval(),
                'sf' => $sf,
            ]);

            $response = $client->send();
//            var_dump($response->getBody()); exit;
            try {
                if (is_numeric($response->getBody())){
//                        var_dump($response);
                    $model->setAplId($response->getBody());
                    $model->setTransferFlag(Model::TRANSFER_YES);
                    $this->entityManager->persist($model);
                    $this->entityManager->flush($model);
                    return;
                }
            } catch (Exception $ex) {
    //                var_dump($ex->getMessage());
                return;
            }
        }    
        return;        
    }

    public function updateModelAplId()
    {
        set_time_limit(1800);
        $startTime = time();
        
        $models = $this->entityManager->getRepository(Model::class)
                ->findBy(['status' => Model::STATUS_ACTIVE, 'transferFlag' => Model::TRANSFER_NO]);
        foreach ($models as $model){
            $this->getModelAplId($model);
            if (time() > $startTime + 1740){
                return;
            }
            usleep(100);
        }
        
        return;        
    }
    
    /**
     * Обновление aplId машин
     * 
     * @param Car $car
     * @return null
     */
    public function getCarAplId($car)
    {
        if ($car->getModel()->getAplId() && $car->getTdId()){

            $url = $this->aplApi().'get-model-id?api='.$this->aplApiKey();
            
            $desc = [];
            $sf = 0;
            foreach ($car->getVehicleDetailsCar() as $vehicleDetailCar){
                if ($vehicleDetailCar->getVehicleDetailValue()->getVehicleDetail()->getNameApl()){
                    $desc[$vehicleDetailCar->getVehicleDetailValue()->getVehicleDetail()->getNameApl()] = $vehicleDetailCar->getVehicleDetailValue()->getNameApl();
                }
                if ($vehicleDetailCar->getVehicleDetailValue()->getVehicleDetail()->getName() == 'yearOfConstrFrom'){
                   $sf = $vehicleDetailCar->getVehicleDetailValue()->getNameApl(); 
                }
            }
            
            if (count($desc)){
                $client = new Client();
                $client->setUri($url);
                $client->setMethod('POST');
                $client->setParameterPost([
                    'parent' => $car->getModel()->getAplId(),
                    'type' => $car->getTdId(),
                    'sort' => $car->getTdId(),
                    'publish' => 1,
                    'name' => urlencode($car->getTransferName()),
                    'comment' => urlencode($car->getTransferFullName()),
                    'desc' => Json::encode($desc),
                    'sf' => $sf,
                ]);

                $response = $client->send();
                
//                var_dump($response->getBody()); exit;
                try {
                    if (is_numeric($response->getBody())){
    //                        var_dump($response);
                        $car->setAplId($response->getBody());
                        $car->setTransferFlag(Car::TRANSFER_YES);
                        $this->entityManager->persist($car);
                        $this->entityManager->flush($car);
                        return;
                    }
                } catch (Exception $ex) {
        //                var_dump($ex->getMessage());
                    return;
                }
            }    
        }    
        return;        
    }

    public function updateCarAplId()
    {
        set_time_limit(1800);
        $startTime = time();
        
        $cars = $this->entityManager->getRepository(Car::class)
                ->findBy(['status' => Car::STATUS_ACTIVE, 'updateFlag' => date('m'), 'transferFlag' => Car::TRANSFER_NO], null, 1000);
        foreach ($cars as $car){
            $this->getCarAplId($car);
            if (time() > $startTime + 1740){
                return;
            }
            usleep(100);
        }
        
        return;        
    }
    
    /**
     * Выгрузка эквайринга с Апл
     * 
     * @return null
     */
    public function updateAcquiringPayments()
    {
        $url = $this->aplApi().'get-acquiring?api='.$this->aplApiKey();
        
        try{
            $data = Json::decode(file_get_contents($url), Json::TYPE_ARRAY);
        } catch (\Laminas\Json\Exception\RuntimeException $ex){
            $data = null;
        }    
        if (is_array($data)){
            foreach ($data as $row){
                $payment = $this->entityManager->getRepository(AplPayment::class)
                        ->findOneByAplPaymentId($row['id']);
                if ($payment == null){

                    $payment = new AplPayment();
                    $payment->setAplPaymentId($row['id']);
                    $payment->setAplPaymentDate($row['created']);
                    $payment->setAplPaymentSum($row['sort']);
                    $payment->setAplPaymentType($row['comment']);
                    $payment->setAplPaymentTypeId($row['name']);

                    $this->entityManager->persist($payment);
                }    
            }
            $this->entityManager->flush();
        }
        return;
        
    }
        
    /**
     * Отправить строки прайсов товара
     * 
     * @param Goods $good
     */
    public function sendRawprices($good)
    {
        $url = $this->aplApi().'update-rawprice?api='.$this->aplApiKey();

        $result = true;
        
        if ($good->getAplId()>0){

            $post = [
                'id' => $good->getAplId(),
                'rawprices' => [],            
            ];        

            $filter = new ParseRawpriceApl(['aplGoodId' => $good->getAplId()]);        

            $articles = $this->entityManager->getRepository(Article::class)
                    ->findBy(['good' => $good->getId()]);
            foreach ($articles as $article){
                $rawprices = $this->entityManager->getRepository(Rawprice::class)
                        ->findBy([
                            'code' => $article->getId(),
                            'status' => Rawprice::STATUS_PARSED,
                        ]);        
                foreach ($rawprices as $rawprice){
                    $post['rawprices'][] = $filter->filter($rawprice);
                }
            }            

    //        var_dump($post); //exit;
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setOptions(['timeout' => 30]);
            $client->setParameterPost($post);

            $ok = $result = false;
            try{
                $response = $client->send();
    //            var_dump($response->getStatusCode()); exit;
                if ($response->isOk()) {
                    $ok = $result = true;
                }
                if ($response->getStatusCode() == 204) {
                    $this->entityManager->getRepository(Goods::class)
                            ->updateGood($good, ['aplId' => 0]);
                    $result = true;
                }
            } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                $ok = true;
            }    

            if ($ok) {            
                $this->entityManager->getRepository(Goods::class)
                        ->updateGoodId($good->getId(), ['status_rawprice_ex' => Goods::RAWPRICE_EX_TRANSFERRED, 'date_ex' => date('Y-m-d H:i:s')]);                
            }

            unset($post);
            unset($rawprices);
        }    
        
        return $result;
    }

    /**
     * Отправить строки прайсов пакета товаров 
     * 
     * @param array $data
     */
    public function sendRawpricesPackage($data)
    {
        $url = $this->aplApi().'update-rawprice-package?api='.$this->aplApiKey();

        $result = true;
        $aplIdFlag = false;
        $post = [];
        
        foreach ($data as $good){
            if ($good->getAplId()>0){
                
                $rp = [];
                
                $filter = new ParseRawpriceApl(['aplGoodId' => $good->getAplId()]);        

                $articles = $this->entityManager->getRepository(Article::class)
                        ->findBy(['good' => $good->getId()]);
                foreach ($articles as $article){
                    $rawprices = $this->entityManager->getRepository(Rawprice::class)
                            ->findBy([
                                'code' => $article->getId(),
                                'status' => Rawprice::STATUS_PARSED,
                            ]);        
                    foreach ($rawprices as $rawprice){
                        if ($rawprice->getRealRest()){
                            $rp[] = $filter->filter($rawprice);
                        }    
                        $this->entityManager->detach($rawprice);
                    }
                    $this->entityManager->detach($article);
                }
                
                $package[] = [
                    'id' => $good->getAplId(),
                    'rawprices' => $rp,            
                ];        

            }
        }
        
        if (count($package)){
            $post['package'] = $package;

//            var_dump($post); //exit;
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setOptions(['timeout' => 60]);
            $client->setParameterPost($post);

            $ok = $result = false;
            try{
                $response = $client->send();
//                var_dump($response->getBody());
                if ($response->isOk()) {
                    $ok = $result = true;
                    $aplIdFlag = (int) $response->getBody() === 1000;
                }
            } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                $ok = true;
            }    

            if ($ok) {            
                foreach ($data as $good){
                    $upd = ['status_rawprice_ex' => Goods::RAWPRICE_EX_TRANSFERRED, 'date_ex' => date('Y-m-d H:i:s')];
                    if ($aplIdFlag){
                        $upd['apl_id'] = 0;
                    }    
                    $this->entityManager->getRepository(Goods::class)
                            ->updateGoodId($good->getId(), $upd);
                    
                    $this->entityManager->detach($good);
                }    
            }

            unset($post);
            unset($rawprices);
        }    
        
        return $result;
    }

    /**
     * Обновить строки прайсов товаров
     * 
     */
    public function updateRawprices()
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(900);
        $startTime = time();
        
        $goodsQuery = $this->entityManager->getRepository(Goods::class)
                ->findForRawpriceEx();
        
        $iterable = $goodsQuery->iterate();
        $k = 1; $border = 100;
        $goods = [];
        foreach($iterable as $item){
            foreach ($item as $good){
                $goods[] = $good;
            }
            if ($k >= $border && count($goods)){
                $k = 0;
                if (!$this->sendRawpricesPackage($goods)){
                    return;
                }
                $goods = [];
            }
            $k++;
            if (time() > $startTime + 840){
                break;
            }
        }        
        unset($iterable);
        return;
    }

    /**
     * Обновить строки прайсов товара
     * 
     * @param int $goodId
     */
    public function updateGoodsRawprice($goodId = null)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(900);
        $startTime = time();

        $goodsQuery = $this->entityManager->getRepository(Goods::class)
                ->findForRawpriceEx(Goods::RAWPRICE_EX_NEW, ['goodId' => $goodId]);
        
        $iterable = $goodsQuery->iterate();

        foreach($iterable as $item){
            foreach ($item as $row){
                $rawprices = $this->entityManager->getRepository(Goods::class)
                        ->rawpriceArticlesEx($row['id'], ['statusEx' => Rawprice::EX_NEW, 'status' => Rawprice::STATUS_PARSED]);
                
                if (count($rawprices) == 0){                
                    $this->entityManager->getRepository(Goods::class)
                            ->updateGoodId($row['id'], ['status_rawprice_ex' => Goods::RAWPRICE_EX_TRANSFERRED, 'date_ex' => date('Y-m-d H:i:s')]);
                }
                unset($rawprices);
                unset($row);
            }    
            
            if (time() > $startTime + 840){
                break;
            }
        }
        
        unset($iterable);
        return;
    }
    
    /**
     * НЕИСПОЛЬЗУЕТСЯ
     * Удалить прайс
     * 
     * @param Raw $raw
     */
    public function deleteRaw($raw)
    {
        $url = $this->aplApi().'delete-raw?api='.$this->aplApiKey();

        $post = [
            'raw' => $raw->getId(),
        ];

//                var_dump($post); exit;
        $client = new Client();
        $client->setUri($url);
        $client->setOptions(['timeout' => 210]);
        $client->setMethod('POST');
        $client->setParameterPost($post);
        
        $ok = $result = false;
        try{
            $response = $client->send();
            if ($response->isOk()) {
                $ok = $result = true;
            }
        } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
            $ok = true;
        }    
        
        if ($ok){
           $raw->setStatusEx(\Application\Entity\Raw::EX_DELETED);
           $this->entityManager->persist($raw);
           $this->entityManager->flush($raw);
        }
//        var_dump($response->getBody()); exit;
        return $result;
    }
    
    public function deleteRaws()
    {
        set_time_limit(900);
        $startTime = time();

        $raws = $this->entityManager->getRepository(Raw::class)
                ->findBy(['statusEx' => Raw::EX_TO_DELETE], null, 5);

        foreach ($raws as $raw){
            $result = $this->deleteRaw($raw);
            if (!$result){
                return;
            }
            usleep(100);
            if (time() > $startTime + 840){
                return;
            }
        }    
        return;
    }
    
    /**
     * Обновить номера товара
     * 
     * @param Goods $good
     */
    public function sendGoodOem($good)
    {
        $result = true;
        if ($good->getAplId()){
            $url = $this->aplApi().'update-good-oem?api='.$this->aplApiKey();

            $post = [
                'good' => $good->getId(),
                'parent' => $good->getAplId(),
                'sf' => \Application\Entity\Oem::SOURCE_TD_NAME,
                'sf2' => \Application\Entity\Oem::INTERSECT_NAME,
                'oems' => [],
            ];

            $oemsQuery = $this->entityManager->getRepository(Goods::class)
                    ->findOems($good);
            $oemsQuery->setMaxResults(500);
            $oems = $oemsQuery->getResult();
            
            foreach ($oems as $oem){
                $post['oems'][$oem->getId()] = [                
                    'parent'    => $good->getAplId(),
                    'sort'      => $oem->getSourceTagAsString(),
                    'name'      => $oem->getOeNumber(),
                    'desc'      => $oem->getTransferBrandName(),
                    'sf'        => $oem->getSourceAsString(),
                ]; 
            }
//            var_dump($post); exit;
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setParameterPost($post);

            $ok = $result = false;
            try{
                $response = $client->send();
    //            var_dump($response->getBody()); exit;
                if ($response->isOk()) {
                    $ok = $result = true;
                }
                if ($response->getStatusCode() == 204) {
                    $this->entityManager->getRepository(Goods::class)
                            ->updateGood($good, ['aplId' => 0]);
                    $result = true;
                }
            } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                $ok = true;
            }    

            if ($ok){
                $this->entityManager->getRepository(Goods::class)
                        ->updateGood($good, ['statusOemEx' => Goods::OEM_EX_TRANSFERRED]);
            }

            unset($post);
            unset($oems);
        }    
        return $result;
    }
    
    /**
     * Обновление номеров в товарах
     * 
     * @return type
     */
    public function updateGoodsOem()
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(1800);
        $startTime = time();
        
        $goodsQuery = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateOem();
        
        $iterable = $goodsQuery->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $good){
                $result = $this->sendGoodOem($good);
                $this->entityManager->detach($good);
                
                if (!$result){
                    return;
                }
            }    
            usleep(100);
            if (time() > $startTime + 1740){
                return;
            }
        }
        return;
    }
    
    /**
     * Обновить картинки товара
     * 
     * @param Goods $good
     */
    public function sendGoodImg($good)
    {
        $result = true;
        if ($good->getAplId()){
            $url = $this->aplApi().'update-good-img?api='.$this->aplApiKey();

            $post = [
                'good' => $good->getId(),
                'good_id' => $good->getAplId(),
//                'images' => [],
            ];

            $imgQuery = $this->entityManager->getRepository(Goods::class)
                    ->findImages($good);
            
            $images = $imgQuery->getResult();                       

            foreach ($images as $image){
                if ($image->allowTransfer()){
                    $post['images'][$image->getId()] = [                
                        'parent'    => $good->getAplId(),
                        'comment'   => $image->getName(),
                        'sort'      => $image->getSimilarAplAsString(),
                        'source'      => 'http://adminapl.ru'. $image->getTransferPath(),
                    ]; 
                }    
            }
            
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setOptions(['timeout' => 30]);
            $client->setParameterPost($post);

            $ok = $result = false;
            try{
                $response = $client->send();
//                var_dump($response->getBody()); exit;
                if ($response->isOk()) {
                    $ok = $result = true;                    
                }
                if ($response->getStatusCode() == 204) {
                    $this->entityManager->getRepository(Goods::class)
                            ->updateGood($good, ['aplId' => 0]);
                    $result = true;
                }
            } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                $ok = true;
            }    
            
            if ($ok){
                $this->entityManager->getRepository(Goods::class)
                        ->updateGood($good, ['statusImgEx' => Goods::IMG_EX_TRANSFERRED]);                
            }

            unset($post);
            unset($images);
        }    
        return $result;
    }
    
    /**
     * Обновление картинок в товарах
     * 
     * @return type
     */
    public function updateGoodsImg()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1800);
        $startTime = time();
        
        $goodsQuery = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateImg();
        $iterable = $goodsQuery->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $good){
                $result = $this->sendGoodImg($good);
                $this->entityManager->detach($good);
                if (!$result){
                    return;
                }
            }    
            usleep(100);
            if (time() > $startTime + 1740){
                return;
            }
        }
        
        return;
    }
    
    /**
     * Обновить группу товара
     * 
     * @param Goods $good
     */
    public function sendGroup($good)
    {
        $result = true;
        if ($good->getAplId()){
            $url = $this->aplApi().'update-good-group?api='.$this->aplApiKey();

            $post = [
                'good' => $good->getAplId(),
                'group' => $good->getTransferGroupApl(),
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
                if ($response->getStatusCode() == 204) {
                    $this->entityManager->getRepository(Goods::class)
                            ->updateGood($good, ['aplId' => 0]);
                    $result = true;
                }
            } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                $ok = true;
            }    
            
            if ($ok){
                $this->entityManager->getRepository(Goods::class)
                        ->updateGood($good, ['statusGroupEx' => Goods::GROUP_EX_TRANSFERRED]);                
            }

            unset($post);
        }    
        return $result;
    }

    /**
     * Обновление групп в товарах
     * 
     * @return type
     */
    public function updateGoodsGroup()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1800);
        $startTime = time();
        
        $goodsQuery = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateGroup();
        $iterable = $goodsQuery->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $good){
                $result = $this->sendGroup($good);
                $this->entityManager->detach($good);
            }    
            if (!$result){
                return;
            }
            usleep(100);
            if (time() > $startTime + 1740){
                return;
            }
        }

        return;
    }
    
    /**
     * Обновить машины товара
     * 
     * @param Goods $good
     */
    public function sendGoodCar($good)
    {
        $ok = $result = FALSE;
        
        if ($good->getAplId() && $good->getGroupApl() != Goods::DEFAULT_GROUP_APL_ID){
            $url = $this->aplApi().'update-good-car?api='.$this->aplApiKey();

            $post = [
                'good' => $good->getAplId(),
                'cars' => [],
            ];

            $carsQuery = $this->entityManager->getRepository(Goods::class)
                    ->findCars($good, ['constructionFrom' => 198601, 'limit' => 2000]);
            
            $cars = $carsQuery->getResult();
            
            foreach ($cars as $car){
                $post['cars'][$car->getId()] = [                
                    'parent'    => $good->getAplId(),
                    'name'      => $car->getAplId(),
                    'comment'   => $good->getGroupApl(),
                ]; 
            }
//            var_dump($post); exit;
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setOptions(['timeout' => 30]);
            $client->setParameterPost($post);

            try{
                $response = $client->send();
//                var_dump($response->getBody()); exit;
                if ($response->isOk()) {
                    $ok = $result = TRUE;
                }
                if ($response->getStatusCode() == 204) {
                    $this->entityManager->getRepository(Goods::class)
                            ->updateGood($good, ['aplId' => 0]);
                    $result = true;
                }
            } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                $ok = TRUE;
            }    
            
            if ($ok){
                $this->entityManager->getRepository(Goods::class)
                        ->updateGood($good, ['statusCarEx' => Goods::CAR_EX_TRANSFERRED]);
            }
            

            unset($post);
            unset($cars);
        }    
        return $result;
    }

    /**
     * Обновление машин в товарах
     * 
     * @return type
     */
    public function updateGoodsCar()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1800);
        $startTime = time();
        
        $goodsQuery = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateCar();
        $iterable = $goodsQuery->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $good){
                $result = $this->sendGoodCar($good);
                $this->entityManager->detach($good);
                if (!$result){
                    return;
                }
            }
//            usleep(100);
            if (time() > $startTime + 1740){
                return;
            }
        }
        return;
    }
    
    
     /**
     * Обновить aplId атрибутов
     * 
     * @param Attribute $attribute
     * @return null
     */
    public function getAttributeAplId($attribute)
    {
        if ($attribute->getName()){

            $url = $this->aplApi().'get-attribute-id?api='.$this->aplApiKey();
            
            $post = [
                'type' => $attribute->getId(),
                'name' => $attribute->getTransferName(),
                'publish' => $attribute->getAplStatus(),
                'sort' => $attribute->getTdId(),
                'sf'  => $attribute->getValueType(),
            ];
            
            if ($attribute->getAplId() > 0){
                $post['id'] = $attribute->getAplId();
            }
            
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setParameterPost($post);
//                var_dump($post); exit;

            try{
                $response = $client->send();
//                var_dump($response->getBody()); exit;
                if (is_numeric($response->getBody())){
                    $attribute->setAplId($response->getBody());
                    $attribute->setStatusEx(\Application\Entity\Attribute::EX_TRANSFERRED);
                    $this->entityManager->persist($attribute);
                    $this->entityManager->flush($attribute);
                }
            } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                return;
            }    
        }    
        
        return;        
    }

    /**
     * 
     * @return 
     */
    public function updateAttributeAplId()
    {
        $attributes = $this->entityManager->getRepository(Attribute::class)
                ->findBy(['statusEx' => Attribute::EX_TO_TRANSFER]);
        
        foreach ($attributes as $attribute){
            $this->getAttributeAplId($attribute);
            unset($attribute);
        }
        unset($attributes);
        return;        
    }

     /**
     * Обновить aplId атрибутов
     * 
     * @param \Application\Entity\AttributeValue $attributeValue
     * @return null
     */
    public function getAttributeValueAplId($attributeValue)
    {
        if ($attributeValue->getValue()){

            $url = $this->aplApi().'get-attribute-value-id?api='.$this->aplApiKey();
            
            $post = [
                'type' => $attributeValue->getId(),
                'name' => $attributeValue->getTransferValue(),
            ];
            
            if ($attributeValue->getAplId() > 0){
                $post['id'] = $attributeValue->getAplId();
            }
            
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setParameterPost($post);
//                var_dump($post); exit;

            try{
                $response = $client->send();
//                var_dump($response->getBody()); exit;
                if (is_numeric($response->getBody())){
                    $attributeValue->setAplId($response->getBody());
                    $attributeValue->setStatusEx(AttributeValue::EX_TRANSFERRED);
                    $this->entityManager->persist($attributeValue);
                    $this->entityManager->flush($attributeValue);
                }
            } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                return;
            }    
        }    
        
        return;        
    }

    /**
     * 
     * @return 
     */
    public function updateAttributeValueAplId()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1800);

        $startTime = time();
        $attributeValuesQuery = $this->entityManager->getRepository(AttributeValue::class)
                ->queryAtributeValueEx();

        $iterable = $attributeValuesQuery->iterate();

        foreach($iterable as $item){
            foreach ($item as $attributeValue){
                $this->getAttributeValueAplId($attributeValue);
            }    
            if (time() > $startTime + 1740){
                return;
            }
        }
        
        return;        
    }

    /**
     * Обновить атрибуты товара
     * 
     * @param Goods $good
     */
    public function sendGoodAttribute($good)
    {
        $result = true;
        if ($good->getAplId()){
            $url = $this->aplApi().'update-good-attribute-value?api='.$this->aplApiKey();

            $post = [
                'good' => $good->getAplId(),
                'attributes' => [],
            ];

            $attrQuery = $this->entityManager->getRepository(Goods::class)
                    ->findGoodAttributeValuesEx($good, ['status' => Attribute::STATUS_ACTIVE]);
            
            $attributes = $attrQuery->getResult();                       

            foreach ($attributes as $attribute){
                $post['attributes'][$attribute->getId()] = [                
                    'parent' => $good->getAplId(),
                    'type'   => $attribute->getAttribute()->getAplId(),
                    'name'   => $attribute->getAttributeValue()->getAplId(),
                ]; 
            }
            
//            var_dump($post); exit;
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setOptions(['timeout' => 30]);
            $client->setParameterPost($post);

            $result = $ok = false;
            try{
                $response = $client->send();
//                var_dump($response->getBody()); exit;
                if ($response->isOk()) {
                    $result = $ok = true;
                }
                if ($response->getStatusCode() == 204) {
                    $this->entityManager->getRepository(Goods::class)
                            ->updateGood($good, ['aplId' => 0]);
                    $result = true;
                }
            } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                $result = false;
            }    
            
            if ($ok){
                $this->entityManager->getRepository(Goods::class)
                        ->updateGood($good, ['statusAttrEx' => Goods::ATTR_EX_TRANSFERRED]);                
            }

            unset($post);
            unset($attributes);
        }    
        return $result;
    }
    
    /**
     * Обновление атрибутов в товарах
     * 
     * @return type
     */
    public function updateGoodsAttribute()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1800);
        $startTime = time();
        
        $goodsQuery = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateAttribute();
        $iterator = $goodsQuery->iterate();
        foreach ($iterator as $row){
            foreach ($row as $good){
                $result = $this->sendGoodAttribute($good);
                $this->entityManager->detach($good);
                if (!$result){
                    return;
                }
                if (time() > $startTime + 1740){
                    return;
                }
            }
        }    
        
        return;
    }
    
    /**
     * Обновить цен товара в АПЛ
     * 
     * @param array $goods
     */ 
    public function updateGoodPrice($goods)
    {        
        $result = true;
        if (count($goods)){
            
            $url = $this->aplApi().'update-price-package?api='.$this->aplApiKey();
            
            $post['package'] = [];
            foreach ($goods as $good){
                if ($good->getAplId() && $good->getPrice()){
                    $post['package'][$good->getAplId()] = [
                        'goodId' => $good->getAplId(),
                        'price' => $good->getPrice(),
                        'mp' => $good->getMinPrice(),
                        'optsn' => $good->getOpts(),
                        'presence' => $good->getAvailable(),
                    ];
                }    
            }    
            
//            var_dump($post);
            if (count($post['package'])){
            
                $client = new Client();
                $client->setUri($url);
                $client->setMethod('POST');
                $client->setOptions(['timeout' => 60]);
                $client->setParameterPost($post);

                $ok = $result = false;
                try{
                    $response = $client->send();
//                    var_dump($response->getStatusCode()); exit;
                    if ($response->isOk()) {
                        $ok = $result = true;
                    }
                } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                    $ok = false;
//                    var_dump($e->getMessage()); exit;
                }    

                if ($ok){
                    foreach ($goods as $good){
                        $this->entityManager->getRepository(Goods::class)
                                ->updateGood($good, ['statusPriceEx' => Goods::PRICE_EX_TRANSFERRED]);
                    }    
                }
            }    
        }
        
        return $result;
    }    
    
    /**
     * Обновление цен в товарах
     * 
     * @return type
     */
    public function updateGoodPrices()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(900);
        $startTime = time();
        
        $goodsQuery = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdatePrice();
        
        $iterable = $goodsQuery->iterate();
        $k = 1; $border = 200; $rows = 0;
        $goods = [];        
        foreach ($iterable as $row){
            foreach ($row as $good){
                $goods[] = $good;
            }    
            if ($k >= $border && count($goods)){
                $k = 0;
                if (!$this->updateGoodPrice($goods)){
                    return;
                }
                $goods = [];
            }
            $k++; $rows++;
            if (time() > $startTime + 840){
                break;
            }
        }    
       
        if ($k < $border && count($goods)){
            if (!$this->updateGoodPrice($goods)){
                return;
            }
        }
        
//        var_dump($rows);
        return;
    }    
    
    /**
     * Обновить автонормы машины
     * 
     * @param Car $car
     */
    public function sendFillVolumes($car)
    {
        $result = true;
        if ($car->getAplId()){
            $url = $this->aplApi().'update-fill-volumes?api='.$this->aplApiKey();

            $post = [
                'car' => $car->getAplId(),
                'volumes' => [],
            ];

            $fillVolumes = $this->entityManager->getRepository(CarFillVolume::class)
                    ->findBy(['car' => $car->getId(), 'lang' => CarFillVolume::LANG_RU, 'status' => CarFillVolume::STATUS_ACTIVE]);
            
            foreach ($fillVolumes as $fillVolume){
                $post['volumes'][$fillVolume->getId()] = [                
                    'parent'  => $car->getAplId(),
                    'title'   => $fillVolume->getCarFillTitle()->getTitle(),
                    'titleId'   => $fillVolume->getCarFillTitle()->getId(),
                    'unit'    => $fillVolume->getCarFillUnit()->getTitle(),
                    'volume'  => $fillVolume->getVolume(),
                    'info'    => $fillVolume->getInfo(),
                ]; 
            }
            
//            var_dump($post); exit;
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setOptions(['timeout' => 30]);
            $client->setParameterPost($post);

            $result = $ok = false;
            try{
                $response = $client->send();
//                var_dump($response->getBody()); exit;
                if ($response->isOk()) {
                    $result = $ok = true;
                }
            } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                $result = false;
            }    
            
            if ($ok){
                $car->setTransferFillVolumesFlag(Car::FILL_VOLUMES_TRANSFER_YES);
                $this->entityManager->persist($car);
                $this->entityManager->flush($car);
            }

            unset($post);
        }    
        return $result;
    }
    
    /**
     * Обновление автонорм
     * 
     * @return type
     */
    public function updateFillVolumes()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(900);
        $startTime = time();
        
        $cars = $this->entityManager->getRepository(Car::class)
                ->findBy(['status' => Car::STATUS_ACTIVE, 'transferFillVolumesFlag' => Car::FILL_VOLUMES_TRANSFER_NO]);
        
        foreach ($cars as $car){
            $this->sendFillVolumes($car);
            if (time() > $startTime + 840){
                break;
            }
        }    
        return;
    }    
    
    /**
     * Перейти по ссылке
     * 
     * @param string $uri
     * @return type
     */
    public function rdrct($uri)
    {

        $headers[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg,text/html,application/xhtml+xml'; 
        $headers[] = 'Connection: Keep-Alive'; 
        $headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8'; 
        $useragent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)'; 

        $process = curl_init($uri); 
        curl_setopt($process, CURLOPT_HTTPHEADER, $headers); 
        curl_setopt($process, CURLOPT_HEADER, 0); 
        curl_setopt($process, CURLOPT_USERAGENT, $useragent);
        curl_setopt($process, CURLOPT_REFERER, 'http://autopartslist.ru');
        curl_setopt($process, CURLOPT_TIMEOUT, 30); 
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1); 

        $return = curl_exec($process); 
        curl_close($process); 

        return $return; 
    }
    
}
