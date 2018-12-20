<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Zend\Json\Json;

use Application\Entity\Email;
use Application\Entity\Phone;
use Application\Entity\Messenger;
use User\Entity\User;
use Company\Entity\Office;
use Application\Entity\Contact;
use Application\Entity\Supplier;
use Zend\Http\Client;

/**
 * Description of AplService
 *
 * @author Daddy
 */
class AplService {
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * User manager
     * @var User\Service\UserManager
     */
    private $userManager;

    /**
     * User manager
     * @var Application\Service\ContactManager
     */
    private $contactManager;

    /**
     * Supplier manager
     * @var Application\Service\SupplierManager
     */
    private $supplierManager;

    /**
     * Legal manager
     * @var Company\Service\LegalManager
     */
    private $legalManager;
    
    /**
     * Telegram manager
     * @var Admin\Service\TelegramManager
     */
    private $telegramManager;
    
    /**
     * Admin manager
     * @var Admin\Service\AdminManager
     */
    private $adminManager;
    
    public function __construct($entityManager, $userManager, $contactManager, $supplierManager, $legalManager, $telegramManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->contactManager = $contactManager;
        $this->supplierManager = $supplierManager;
        $this->legalManager = $legalManager;
        $this->telegramManager = $telegramManager;
        $this->adminManager = $adminManager;
    }
    
    protected function aplApi()
    {
        return 'https://autopartslist.ru/api/';
        
    }
    
    protected function aplApiKey()
    {
        $settings = $this->adminManager->getSettings();
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

        $response = $client->send();

        if ($response->isSuccess()) {
            $body = $response->getBody();
            return (array) Json::decode($body);
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
            if ($data){
                $phone = (array) Json::decode($data);
//                var_dump($phone);
                $this->contactManager->addPhone($contact, ['phone' => $phone['phone']], true);
            }
        }    
    }
   
    //** 
    //put your code here
    public function getStaffs()
    {
        $url = $this->aplApi().'get-staffs?api='.$this->aplApiKey();
        
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
                $desc = (array) Json::decode($row['desc']);

                $user = $contact = null;
                if ($row['email']){
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
                } elseif ($row['phone']){
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
                    
//                    var_dump($desc['dob']);
//                    var_dump(date_format(date_create($desc['dob']), 'Y-m-d'));
                    
                    $user_data = [
                        'email' => $row['email'],
                        'full_name' => $row['name'],
                        'status' => ($row['publish'] == 1 ? 1:2),
                        'roles' => $user->getRolesAsArray(),
                        'aplId' => $row['id'],
                        'birthday' => date_format(date_create($desc['dob']), 'Y-m-d'),
                    ];    

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
                            'birthday' => date_format(date_create($desc['dob']), 'Y-m-d'),
                        ];    
                            
                        $user = $this->userManager->addUser($user_data);                        
                    }
                }
                
                if ($user){
                    if ($contact){
                        $contact_data = [
                            'name' => $row['name'],
                            'phone' => $row['phone'],
                            'email' => $row['email'],
                            'status' => Contact::STATUS_LEGAL,
                        ];

                        $this->contactManager->updateContact($contact, $contact_data);                                                
                    } else {
                        $contact_data = [
                            'name' => $row['name'],
                            'phone' => $row['phone'],
                            'email' => $row['email'],
                            'status' => Contact::STATUS_LEGAL,
                        ];

                        $contact = $this->contactManager->addNewContact($user, $contact_data);                        
                    }   

                    //$desc = (array) Json::decode($row['desc']);
//                    var_dump($desc['icq']);
                    if ($contact){
                        //$this->contactManager->updateMessengers($contact, ['icq' => $desc['icq']]);
                        $messenger = $this->entityManager->getRepository(Messenger::class)
                                ->findOneBy(['type' => Messenger::TYPE_ICQ, 'ident' => $desc['icq']]);
                        
                        if ($messenger == null){
                            $this->contactManager->addNewMessenger($contact, ['type' => Messenger::TYPE_ICQ, 'ident' => $desc['icq'], 'status' => Messenger::STATUS_ACTIVE]);
                        }
                        
                        
                        
                        $this->contactManager->updateSignature($contact, ['signature' => $desc['signature']]);
                        $this->contactManager->updateUserOffice($contact, ['office' => $this->getOffice($row['parent'])]);
                    }
                    
                    $this->getStaffPhone($contact);
                }                
            }          
        }        
    }
        
    public function sendTelegramMessage($params)
    {
        if (is_array($params)){
            if (isset($params['api_key'])){
                if ($params['api_key'] == $this->aplApiKey()){

                    return $this->telegramManager->sendMessage([
                        'chat_id' => $params['chat_id'], 
                        'text' => $params['text'],
                    ]);

                }
            }    
        }
        return;
    }
    
    /**
     * Получить код товара
     * @param Application\Entity\Goods $good
     */ 
    public function getGoodAplId($good)
    {
        foreach ($good->getRawprice() as $rawprice){
            
            $key = md5($rawprice->getRaw()->getSupplier()->getAplId().":".trim($rawprice->getArticle()).":".mb_strtoupper($rawprice->getUnknownProducer()->getName(), 'utf-8'));
            
            $url = $this->aplApi().'get-good-id?key='.$key.'&api='.$this->aplApiKey();

            $response = file_get_contents($url);
            try {
                $data = Json::decode($response);
                if (is_object($data)){
                    if (is_numeric($data->parent)){
                        $good->setAplId($data->parent);
                        $this->entityManager->persist($good);
                        $this->entityManager->flush($good);
                        return;
                    }
                }    
            } catch (Exception $ex) {
//                var_dump($url); 
//                var_dump($data); 
//                var_dump($ex->getMessage());
                return;
            }
        }
        
        return;
    }
    
    /**
     * Обновление AplId в товарах
     * 
     * @return type
     */
    public function updateGoodAplId()
    {
        set_time_limit(60);
        $startTime = time();
        
        $goods = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->findGoodsForUpdateAplId();
        
        foreach ($goods as $good){
            $this->getGoodAplId($good);
            if (time() > $startTime + 50){
                return;
            }
            usleep(100);
        }
        return;
    }
    
}
