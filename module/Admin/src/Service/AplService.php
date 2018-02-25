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
use User\Entity\User;
use Company\Entity\Office;
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

    protected function aplApi()
    {
        return 'https://autopartslist.ru/api/';
        
    }
    
    protected function aplApiKey()
    {
        return md5(date('Y-m-d').'#kjdrf4');
    }
    
    public function __construct($entityManager, $userManager, $contactManager)
    {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->contactManager = $contactManager;
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
    
    public function getStaffPhone($contact)
    {
        $aplId = $contact->getUser()->getAplId();
        if ($aplId){
            $url = $this->aplApi().'get-staff-phone/id/'.$aplId.'?api='.$this->aplApiKey();

            $data = file_get_contents($url);
            if ($data){
                $phone = (array) Json::decode($data);
//                var_dump($phone);
                $this->contactManager->addPhone($contact, $phone['phone'], true);
            }
        }    
    }
   
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
                    
                    $user_data = [
                        'email' => $row['email'],
                        'full_name' => $row['name'],
                        'status' => ($row['publish'] == 1 ? 1:2),
                        'roles' => $user->getRolesAsArray(),
                        'aplId' => $row['id'],
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
                            'status' => ($row['publish'] == 1 ? 1:2),
                        ];

                        $this->contactManager->updateContact($contact, $contact_data);                                                
                    } else {
                        $contact_data = [
                            'name' => $row['name'],
                            'phone' => $row['phone'],
                            'email' => $row['email'],
                            'status' => ($row['publish'] == 1 ? 1:2),
                        ];

                        $contact = $this->contactManager->addNewContact($user, $contact_data);                        
                    }   

                    $desc = (array) Json::decode($row['desc']);
//                    var_dump($desc['icq']);
                    if ($contact){
                        $this->contactManager->updateMessengers($contact, ['icq' => $desc['icq']]);
                        $this->contactManager->updateSignature($contact, ['signature' => $desc['signature']]);
                        $this->contactManager->updateUserOffice($contact, ['office' => $this->getOffice($row['parent'])]);
                    }
                    
                    $this->getStaffPhone($contact);
                }                
            }          
        }        
    }
        
    
}
