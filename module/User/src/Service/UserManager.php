<?php
namespace User\Service;

use User\Entity\User;
use User\Entity\Role;
use Application\Entity\Contact;
use Laminas\Crypt\Password\Bcrypt;
use Laminas\Math\Rand;
use Application\Entity\Email;
use User\Validator\TokenNoExistsValidator;
use Company\Entity\Office;
use User\Filter\Rudate;
use Application\Entity\Order;
use Application\Entity\Client;

/**
 * This service is responsible for adding/editing users
 * and changing user password.
 */
class UserManager
{
    
    const EMAIL_SENDER = 'noreply@adminapl.ru';
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;  
    
    /**
     * Role manager.
     * @var \User\Service\RoleManager
     */
    private $roleManager;
    
    /**
     * Permission manager.
     * @var \User\Service\PermissionManager
     */
    private $permissionManager;
    
    /**
     * Post manager.
     * @var \Admin\Service\PostManager
     */
    private $postManager;
    
    /**
     * Sms manager.
     * @var \Admin\Service\SmsManager
     */
    private $smsManager;
    
    /**
     * Admin manager.
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;
    
    /**
     * Rbac manager.
     * @var \User\Service\RbacManager
     */
    private $rbacManager;
    
    /**
     * Constructs the service.
     */
    public function __construct($entityManager, $roleManager, $permissionManager,
            $postManager, $smsManager, $adminManager, $rbacmanager) 
    {
        $this->entityManager = $entityManager;
        $this->roleManager = $roleManager;
        $this->permissionManager = $permissionManager;
        $this->postManager = $postManager;
        $this->smsManager = $smsManager;
        $this->adminManager = $adminManager;
        $this->rbacManager = $rbacmanager;
    }
    
    /**
     * This method adds a new user.
     */
    public function addUser($data) 
    {
        // Do not allow several users with the same email address.
        if($this->checkUserExists($data['email'])) {
            throw new \Exception("User with email address " . $data['$email'] . " already exists");
        }
        
        // Create new User entity.
        $user = new User();
        $user->setEmail($data['email']);
        $user->setFullName($data['full_name']);        

        // Encrypt password and store the password in encrypted state.
        $bcrypt = new Bcrypt();
        $passwordHash = $bcrypt->create($data['password']);        
        $user->setPassword($passwordHash);
        
        $user->setStatus($data['status']);
        $user->setAplId($data['aplId']);
        $user->setBirthday(null);
        $user->setOrderCount(0);
        if (!empty($data['birthday'])){
            $user->setBirthday($data['birthday']);
        }    
        
        $currentDate = date('Y-m-d H:i:s');
        $user->setDateCreated($currentDate);        
        
        $office = $this->entityManager->getRepository(Office::class)
                ->find($data['office']);
        $user->setOffice($office);
        
        // Assign roles to user.
        $this->assignRoles($user, $data['roles']);        
        
        //as client
        $client = new Client();
        $client->setName($data['full_name']);
        $client->setAplId(0);
        $client->setStatus(Client::STATUS_ACTIVE);
        $client->setPricecol(Client::PRICE_5);
        $client->setDateCreated(date('Y-m-d H:i:s'));
        $this->entityManager->persist($client);
        
        //Legal contact
        $contact = new Contact();
        $contact->setName($data['full_name']);        
        $contact->setStatus(Contact::STATUS_LEGAL);
        $contact->setDescription('');
        $contact->setSignature(empty($data['sign']) ? null:$data['sign']);

        $contact->setDateCreated($currentDate);
        $contact->setUser($user);         
        $contact->setClient($client);         
        $this->entityManager->persist($contact);
        
        $email = $this->entityManager->getRepository(Email::class)
                ->findOneByName($data['email']);
        $settings = $this->adminManager->getSettings();
        if ($email){
            $email->setMailPassword(empty($data['mailPassword']) ? null:$data['mailPassword'], $settings['turbo_passphrase']);
        } else {
            $email = new Email();
            $email->setDateCreated($currentDate);
            $email->setMailPassword(empty($data['mailPassword']) ? null:$data['mailPassword'], $settings['turbo_passphrase']);
            $email->setName($data['email']);
            $email->setContact($contact);
        }
        $this->entityManager->persist($email);
        
        // Add the entity to the entity manager.
        $this->entityManager->persist($user);
        
        // Apply changes to database.
        $this->entityManager->flush();
        
        $post = [
            'to' => $data['email'],
//            'from' => self::EMAIL_SENDER,
            'from' => $settings['hello_email'],
            'username' => $settings['hello_email'],
            'password' => $settings['hello_app_password'],
            'subject' => 'Регистрация на сайте adminapl.ru',
            'body' => "Здравствуйте, {$data['full_name']}!<br/>Вы зарегистрированы на сайте <a href='http://adminapl.ru'>adminapl.ru</a>!<br/>Логин: {$data['email']}<br/>Пароль: {$data['password']}.<br/><br/><br/>С уважением,<br/>AdminAPL",
        ];
//        $this->postManager->send($post);    
        
        return $user;
    }
    
    /**
     * This method updates data of an existing user.
     * @param User $user
     * @param array $data
     * 
     * @return User 
     */
    public function updateUser($user, $data) 
    {
        $flag = true;
        
        // Do not allow to change user email if another user with such email already exits.
        if($user->getEmail()!=$data['email'] && $this->checkUserExists($data['email'])) {
            throw new \Exception($user->getId()."! - Another user with email address " . $data['email'] . " already exists");
        }

        // Если изменилась идентификация надо будет перелогинится.
        if($user->getEmail() != $data['email']) {
//            $flag = 'logout';
        }
        
        $user->setEmail($data['email']);
        $user->setFullName($data['full_name']);  
        if (!empty($data['status'])){
            $user->setStatus($data['status']);
        }    
        if (isset($data['aplId'])){
            $user->setAplId($data['aplId']);
        }    
        $user->setBirthday(null);
        if (!empty($data['birthday'])){
            $user->setBirthday($data['birthday']);
        }    
        
        $office = null;
        if (!empty($data['office'])){
            $office = $this->entityManager->getRepository(Office::class)
                    ->find($data['office']);
        } elseif (!empty($data['officeAplId'])){
            $office = $this->entityManager->getRepository(Office::class)
                    ->findOneByAplId($data['officeAplId']);
        }    
        if ($office){
            $user->setOffice($office);
        }    

        // Assign roles to user.
        if (!empty($data['roles'])){
            $this->assignRoles($user, $data['roles']);
        }    
        
        $currentDate = date('Y-m-d H:i:s');
        $legalContact = $user->getLegalContact();
        if (!$legalContact){
            $legalContact = new Contact();
            $legalContact->setName($data['full_name']);        
            $legalContact->setStatus(Contact::STATUS_LEGAL);
            $legalContact->setDescription('');
            $legalContact->setSignature(empty($data['sign']) ? null:$data['sign']);

            $legalContact->setDateCreated($currentDate);
            $legalContact->setUser($user);         
            // Добавляем сущность в менеджер сущностей.
        } else {
            $legalContact->setSignature(empty($data['sign']) ? null:$data['sign']);            
        }
        
        $client = $legalContact->getClient();
        if (!$client){
                    //as client
            $client = new Client();
            $client->setName($data['full_name']);
            $client->setAplId(0);
            $client->setStatus(Client::STATUS_ACTIVE);
            $client->setPricecol(Client::PRICE_5);
            $client->setDateCreated(date('Y-m-d H:i:s'));
            $this->entityManager->persist($client);
            
            $legalContact->setClient($client);
        }
        
        $this->entityManager->persist($legalContact);
        
        $email = $this->entityManager->getRepository(Email::class)
                ->findOneByName($data['email']);
        $settings = $this->adminManager->getSettings();
        if ($email){
            $email->setMailPassword(empty($data['mailPassword']) ? null:$data['mailPassword'], $settings['turbo_passphrase']);
        } else {
            $email = new Email();
            $email->setDateCreated(date('Y-m-d H:i:s'));
            $email->setMailPassword(empty($data['mailPassword']) ? null:$data['mailPassword'], $settings['turbo_passphrase']);
            $email->setName($data['email']);
            $email->setContact($legalContact);
        }
        $this->entityManager->persist($email);
        
        // Apply changes to database.
        $this->entityManager->flush();   
        $this->entityManager->refresh($user);
        
        return $flag;
    }
    
    /**
     * Пароль почты пользователя
     * @param User $user 
     * @return string
     */
    public function userMailPassword($user)
    {
        $settings = $this->adminManager->getSettings();
        $email = $this->entityManager->getRepository(Email::class)
                ->findOneByName($user->getEmail());
        if ($email){
            return $email->getMailPassword($settings['turbo_passphrase']);
        }
        return;
    }
    
    /**
     * A helper method which assigns new roles to the user.
     */
    private function assignRoles($user, $roleIds)
    {
        // Remove old user role(s).
        $user->getRoles()->clear();
        
        // Assign new role(s).
        foreach ($roleIds as $roleId) {
            $role = $this->entityManager->getRepository(Role::class)
                    ->find($roleId);
            if ($role==null) {
                throw new \Exception('Not found role by ID');
            }
            
            $user->addRole($role);
        }
    }
    
    /**
     * This method checks if at least one user presents, and if not, creates 
     * 'Admin' user with email 'admin@example.com' and password 'Secur1ty'. 
     */
    public function createAdminUserIfNotExists()
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([]);
        if ($user==null) {
            
            $this->permissionManager->createDefaultPermissionsIfNotExist();
            $this->roleManager->createDefaultRolesIfNotExist();
            
            $user = new User();
            $user->setEmail('admin@example.com');
            $user->setFullName('Admin');
            $bcrypt = new Bcrypt();
            $passwordHash = $bcrypt->create('Secur1ty');        
            $user->setPassword($passwordHash);
            $user->setStatus(User::STATUS_ACTIVE);
            $user->setDateCreated(date('Y-m-d H:i:s'));
            
            // Assign user Administrator role
            $adminRole = $this->entityManager->getRepository(Role::class)
                    ->findOneByName('Administrator');
            if ($adminRole==null) {
                throw new \Exception('Administrator role doesn\'t exist');
            }

            $user->getRoles()->add($adminRole);
            
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }
    
    /**
     * Checks whether an active user with given email address already exists in the database.     
     */
    public function checkUserExists($emailStr) {
        
        $user = $this->entityManager->getRepository(User::class)
                ->findOneByEmail($email);
//        $email = $this->entityManager->getRepository(Email::class)
//                ->findOneBy(['name' => $emailStr]);
        
        return $user !== null;
    }
    
    /**
     * Checks that the given password is correct.
     */
    public function validatePassword($user, $password) 
    {
        $bcrypt = new Bcrypt();
        $passwordHash = $user->getPassword();
        
        if ($bcrypt->verify($password, $passwordHash)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Generates a password reset token for the user. This token is then stored in database and 
     * sent to the user's E-mail address. When the user clicks the link in E-mail message, he is 
     * directed to the Set Password page.
     */
    public function generatePasswordResetToken($user)
    {
        // Generate a token.
        $token = Rand::getString(32, '0123456789abcdefghijklmnopqrstuvwxyz', true);
        $user->setPasswordResetToken($token);
        
        $currentDate = date('Y-m-d H:i:s');
        $user->setPasswordResetTokenCreationDate($currentDate);  
        
        $this->entityManager->flush();
        
        $subject = 'Восстановление пароля';
            
        $httpHost = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'localhost';
        $passwordResetUrl = 'http://' . $httpHost . '/set-password?token=' . $token;
        
        $body = 'Перейдите по приведенной ниже ссылке, чтобы сбросить пароль:<br/>';
        $body .= "<a href='$passwordResetUrl'>$passwordResetUrl</a><br/>";
        $body .= "Если вы не попросили сбросить пароль, пожалуйста, проигнорируйте это сообщение.<br/>";
        
        // Send email to user.
//        mail($user->getEmail(), $subject, $body);
        $settings = $this->adminManager->getSettings();
        
        $post = [
            'to' => $user->getEmail(),
//            'from' => self::EMAIL_SENDER,
            'from' => $settings['hello_email'],
            'username' => $settings['hello_email'],
            'password' => $settings['hello_app_password'],
            'subject' => $subject,
            'body' => $body,
        ];
        $this->postManager->send($post);             
    }
    
    /**
     * @param \Application\Entity\Phone $phone
     */
    public function generatePasswordSMSResetToken($phone)
    {
        
        $user = $phone->getContact()->getUser();
        
        // Generate a token.
        $token = Rand::getInteger(1000, 9999);
        $user->setPasswordResetToken($token);
        
        $currentDate = date('Y-m-d H:i:s');
        $user->setPasswordResetTokenCreationDate($currentDate);  
        
        $this->entityManager->flush();
        
        $sms = [
            'phone' => '7'.$phone->getName(\User\Filter\PhoneFilter::PHONE_FORMAT_DB),
            'text' => $token,
        ];
        
        $this->smsManager->send($sms);             
    }
    
    /**
     * Checks whether the given password reset token is a valid one.
     */
    public function validatePasswordResetToken($passwordResetToken)
    {
        $validator =  new TokenNoExistsValidator(['entityManager' => $this->entityManager]);
        
        return  $validator->isValid($passwordResetToken);
    }
    
    /**
     * This method sets new password by password reset token.
     */
    public function setNewPasswordByToken($passwordResetToken, $newPassword)
    {
        if (!$this->validatePasswordResetToken($passwordResetToken)) {
           return false; 
        }
        
        $user = $this->entityManager->getRepository(User::class)
                ->findOneByPasswordResetToken($passwordResetToken);
        
        if ($user===null) {
            return false;
        }
                
        // Set new password for user        
        $bcrypt = new Bcrypt();
        $passwordHash = $bcrypt->create($newPassword);        
        $user->setPassword($passwordHash);
                
        // Remove password reset token
        $user->setPasswordResetToken(null);
        $user->setPasswordResetTokenCreationDate(null);
        
        $this->entityManager->flush();

        $settings = $this->adminManager->getSettings();
        $post = [
            'to' => $user->getEmail(),
//            'from' => self::EMAIL_SENDER,
            'from' => $settings['hello_email'],
            'username' => $settings['hello_email'],
            'password' => $settings['hello_app_password'],
            'subject' => 'Изменение пароля для входа на сайт adminapl.ru',
            'body' => "Здравствуйте, {$user->getFullName()}!<br/>Вами был изменен пароль для входа на сайт <a href='http://adminapl.ru'>adminapl.ru</a>!<br/>Логин: {$user->getEmail()}<br/>Пароль: $newPassword<br/><br/><br/>С уважением,<br/>AdminAPL",
        ];
        $this->postManager->send($post);    
        
        return true;
    }
    
    /**
     * This method is used to change the password for the given user. To change the password,
     * one must know the old password.
     */
    public function changePassword($user, $data)
    {
        $oldPassword = $data['old_password'];
        
        // Check that old password is correct
        if (!$this->validatePassword($user, $oldPassword)) {
            return false;
        }                
        
        $newPassword = $data['new_password'];
        
        // Check password length
        if (strlen($newPassword)<4 || strlen($newPassword)>64) {
            return false;
        }
        
        // Set new password for user        
        $bcrypt = new Bcrypt();
        $passwordHash = $bcrypt->create($newPassword);
        $user->setPassword($passwordHash);
        
        // Apply changes
        $this->entityManager->flush();
        
        $settings = $this->adminManager->getSettings();
        $post = [
            'to' => $user->getEmail(),
//            'from' => self::EMAIL_SENDER,
            'from' => $settings['hello_email'],
            'username' => $settings['hello_email'],
            'password' => $settings['hello_app_password'],
            'subject' => 'Смена пароля на сайте adminapl.ru',
            'body' => "Здравствуйте, {$user->getFullName()}!<br/>Вам был сменен пароль для входа на сайт <a href='http://adminapl.ru'>adminapl.ru</a>!<br/>Логин: {$user->getEmail()}<br/>Новый пароль: {$data['new_password']}.<br/><br/><br/>С уважением,<br/>AdminAPL",
        ];
        $this->postManager->send($post);    

        return true;
    } 
    
    /**
     * Обновить количество заказов
     * @param User $user
     */
    public function updateOrderCount($user)
    {
        $this->entityManager->getRepository(User::class)
                ->updateOrderCount($user);
        
        return;
    }
    
    /**
     * Обновить количество заказов
     * 
     */
    public function updateOrderCounts()
    {
        $this->entityManager->getRepository(User::class)
                ->updateOrderCounts();
        
        return;
    }
    
    /**
     * 
     */
    private function currentGeo()
    {
        $ipApiUrl = 'http://ip-api.com/json/';
        $remote = new \Laminas\Http\PhpEnvironment\RemoteAddress();
        $currentIp = $remote->getIpAddress();
        $result = file_get_contents($ipApiUrl.$currentIp.'?lang=ru');
        return \Laminas\Json\Decoder::decode($result, \Laminas\Json\Json::TYPE_ARRAY);
    }
    
    
    /**
     * Отчеты из 1с по зп
     * @param string $period 
     */
    public function ddReport($period = null)
    {
        $rudateFilter = new Rudate();
        
        $currentUser = $this->smsManager->currentUser();
        $rdir = './data/reports/'; 
        $report = 'Отчета за этот месяц еще нет';
        $result['report'] = $report;	
        
        if (!$currentUser){
            return $result;
        }
        if (!$currentUser->getAplId()){
            return $result;
        }
        
        if (!$period){
            if ($this->rbacManager->isGranted($currentUser, 'founder')){
                $period = 'dd'.date('Ym');
            } else {
                $period = 'rl'.$currentUser->getAplId().date('Ym');
            }	
        }
        
        $result['period'] = $period;
        
        $reportfilename = $rdir.$period.'.html';
        if (file_exists($reportfilename)){
            $report = strip_tags(file_get_contents($reportfilename), '<style><table><col><tr><td><span><div>');

            $p = substr($period, 0, 2);
            $m = substr($period, -2);
            $y = substr($period, -6, 4);
            $g = substr($period, 2);

            $uid = substr(substr($period, 0, -6), 2);
            if (!$uid) {
                $uid = '';
            }

            $prevmonth = strtotime("$y-$m-01 -1 month");
            $nextmonth = strtotime("$y-$m-01 +1 month");
            $currmonth = strtotime("$y-$m-01");

            $prev = $p.$uid.date('Ym', $prevmonth);			
            if (file_exists($rdir.$prev.'.html')){
                $result['prevlabel'] = $rudateFilter->filter('F Y', $prevmonth);
                $result['prevhref'] = "/users/dd-report?report=$prev";
            }

            $next = $p.$uid.date('Ym', $nextmonth);
            if (file_exists($rdir.$next.'.html')){
                $result['nextlabel'] = $rudateFilter->filter('F Y', $nextmonth);
                $result['nexthref'] = "/users/dd-report?report=$next";
            }

            if ($p == 'dd'){
                $zp = 'zp';
                if (file_exists($rdir.$zp.$g.'.html')){
                    $str = "Оплата&nbsp;труда";
                    $zphref = '<a href="/users/dd-report?report='.$zp.$g.'">'.$str.'</a>';
                    $report = str_replace($str, $zphref, $report);
                }	
            }	
            if ($p == 'zp'){
                $dd = 'dd';
                if (file_exists($rdir.$dd.$g.'.html')){
                    $result['returnlabel'] = $rudateFilter->filter('F Y', $currmonth);
                    $result['returnhref'] = "/users/dd-report?report=$dd$g";
                }	
                $rl = 'rl';
                $users = $this->entityManager->getRepository(User::class)
                        ->findAll();
                foreach ($users as $user){	
                    if (file_exists($rdir.$rl.$user->getAplId().date('Ym', $currmonth).'.html')){
                        $str = $rl.$user->getAplId().date('Ym', $currmonth);
                        $zphref = '<a href="/users/dd-report?report='.$rl.$user->getAplId().date('Ym', $currmonth).'">'.$str.'</a>';
                        $report = str_replace($str, $zphref, $report);
                    }	
                }	
            }	
            if ($this->rbacManager->isGranted($currentUser, 'founder')){
                if ($p == 'rl'){
                    $dd = 'zp';
                    if (file_exists($rdir.$dd.$y.$m.'.html')){
                        $result['returnlabel'] = $rudateFilter->filter('F Y', $currmonth);
                        $result['returnhref'] = "/users/dd-report?report=$dd$y$m";
                    }	
                }
            }
        }	
        
        $result['report'] = $report;	
        return $result;
    }    
}

