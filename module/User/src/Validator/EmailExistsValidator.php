<?php
namespace User\Validator;

use Laminas\Validator\AbstractValidator;
use Application\Entity\Email;
use Laminas\Validator\EmailAddress;
/**
 * This validator class is designed for checking if there is an existing role 
 * with such a name.
 */
class EmailExistsValidator extends AbstractValidator 
{
    /**
     * Available validator options.
     * @var array
     */
    protected $options = array(
        'entityManager' => null,
        'email' => null
    );
    
    // Validation failure message IDs.
    const NOT_EMAIL  = 'notEmail';
    const EMAIL_EXISTS = 'emailExists';
        
    /**
     * Validation failure messages.
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_EMAIL  => "Неверный формат email адреса",
        self::EMAIL_EXISTS  => "Такой email уже используется"        
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
            if(isset($options['email']))
                $this->options['email'] = $options['email'];
        }
        
        // Call the parent class constructor
        parent::__construct($options);
    }
        
    /**
     * Check if user exists.
     */
    public function isValid($value) 
    {
        $validator = new EmailAddress();
        if(!$validator->isValid($value)) {
            $this->error(self::NOT_EMAIL);
            return $false; 
        }
        
        // Get Doctrine entity manager.
        $entityManager = $this->options['entityManager'];
        
        $email = $entityManager->getRepository(Email::class)
                ->findOneByName($value);
        
        if($this->options['email']==null) {
            $isValid = ($email==null);
        } else {
            if($this->options['email']->getName()!=$value && $email!=null) 
                $isValid = false;
            else 
                $isValid = true;
        }
        
        // If there were an error, set error message.
        if(!$isValid) {            
            $this->error(self::EMAIL_EXISTS);            
        }
        
        // Return validation result.
        return $isValid;
    }
}