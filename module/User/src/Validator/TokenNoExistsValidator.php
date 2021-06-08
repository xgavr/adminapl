<?php
namespace User\Validator;

use Laminas\Validator\AbstractValidator;
use User\Entity\User;
/**
 * This validator class is designed for checking if there is an existing role 
 * with such a name.
 */
class TokenNoExistsValidator extends AbstractValidator 
{
    
    
    /**
     * Available validator options.
     * @var array
     */
    protected $options = array(
        'entityManager' => null,
    );
    
    // Validation failure message IDs.
    const NOT_DIGIT  = 'notDigit';
    const TOKEN_NO_EXISTS = 'tokenNoExists';
    const TOKEN_EXPIRED = 'tokenExpired';
        
    /**
     * Validation failure messages.
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_DIGIT  => "Не верный код",
        self::TOKEN_NO_EXISTS  => "Не верный код",        
        self::TOKEN_EXPIRED  => "Время вышло",        
    );
    
    /**
     * Constructor.     
     */
    public function __construct($options = null) 
    {
        // Set filter options (if provided).
        if(is_array($options)) {            
            if(isset($options['entityManager']))
                $this->options['entityManager'] = $options['entityManager'];
        }
        
        // Call the parent class constructor
        parent::__construct($options);
    }
        
    /**
     * Check if user exists.
     */
    public function isValid($value) 
    {
        if(!is_numeric($value)) {
            $this->error(self::NOT_DIGIT);
            return false; 
        }
        
        // Get Doctrine entity manager.
        $entityManager = $this->options['entityManager'];
        
        $user = $entityManager->getRepository(User::class)
                ->findOneByPasswordResetToken($value);
        
        $isValid = ($user!=null);
        
        if ($isValid){
            $tokenCreationDate = $user->getPasswordResetTokenCreationDate();
            $tokenCreationDate = strtotime($tokenCreationDate);

            $currentDate = strtotime('now');

            if ($currentDate - $tokenCreationDate > 24*60*60) {
                $isValid = false; // expired
                $this->error(self::TOKEN_EXPIRED);
                return $isValid;
            }
        
        }
        // If there were an error, set error message.
        if(!$isValid) {            
            $this->error(self::TOKEN_NO_EXISTS);            
        }
        
        // Return validation result.
        return $isValid;
    }
}