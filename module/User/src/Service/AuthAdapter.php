<?php
namespace User\Service;

use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Result;
use Laminas\Crypt\Password\Bcrypt;
use User\Entity\User;
use Application\Entity\Phone;
use Application\Entity\Email;
use User\Filter\PhoneFilter;


/**
 * Adapter used for authenticating user. It takes login and password on input
 * and checks the database if there is a user with such login (email) and password.
 * If such user exists, the service returns its identity (email). The identity
 * is saved to session and can be retrieved later with Identity view helper provided
 * by ZF3.
 */
class AuthAdapter implements AdapterInterface
{
    /**
     * User email.
     * @var string 
     */
    private $email;
    
    /**
     * User phone.
     * @var string 
     */
    private $phone;
    
    /**
     * Password
     * @var string 
     */
    private $password;
    
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager 
     */
    private $entityManager;
        
    /**
     * Constructor.
     */
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Sets user email.     
     */
    public function setEmail($email) 
    {
        $this->email = $email;        
    }
    
    /**
     * Sets user phone.     
     */
    public function setPhone($phone) 
    {
        $this->phone = $phone;        
    }
    
    /**
     * Sets password.     
     */
    public function setPassword($password) 
    {
        $this->password = (string)$password;        
    }
    
    /**
     * Performs an authentication attempt.
     */
    public function authenticate()
    {            
        $user = null;
        
        if ($this->phone){
            
            $filter = new PhoneFilter();
            $filter->setFormat(PhoneFilter::PHONE_FORMAT_DB);
            
            $phone = $this->entityManager->getRepository(Phone::class)
                    ->findOneByName($filter->filter($this->phone));
            
            if ($phone){
                $user = $phone->getContact()->getUser();
            }    
        }
        
        if ($this->email){
            $email = $this->entityManager->getRepository(Email::class)
                    ->findOneByName($this->email);
            if ($email){
                $user = $email->getContact()->getUser();
            }    
        }

        if ($user == null && $this->email){
            // Check the database if there is a user with such email.
            $user = $this->entityManager->getRepository(User::class)
                    ->findOneByEmail($this->email);
        }    
        
        // If there is no such user, return 'Identity Not Found' status.
        if ($user == null) {
            return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND, 
                null, 
                ['Invalid credentials.']);        
        }   
        
        // If the user with such email exists, we need to check if it is active or retired.
        // Do not allow retired users to log in.
        if ($user->getStatus()==User::STATUS_RETIRED) {
            return new Result(
                Result::FAILURE, 
                null, 
                ['User is retired.']);        
        }
        
        // Now we need to calculate hash based on user-entered password and compare
        // it with the password hash stored in database.
        $bcrypt = new Bcrypt();
        $passwordHash = $user->getPassword();
        
        if ($bcrypt->verify($this->password, $passwordHash)) {
            // Great! The password hash matches. Return user identity (email) to be
            // saved in session for later use.
            return new Result(
                    Result::SUCCESS, 
//                    $this->email, 
                    $user->getEmail(),
                    ['Authenticated successfully.']);        
        }             
        
        // If password check didn't pass return 'Invalid Credential' failure status.
        return new Result(
                Result::FAILURE_CREDENTIAL_INVALID, 
                null, 
                ['Invalid credentials.']);        
    }
}


