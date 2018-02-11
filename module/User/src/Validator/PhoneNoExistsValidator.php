<?php
namespace User\Validator;

use Zend\Validator\AbstractValidator;
use Application\Entity\Phone;
use User\Filter\PhoneFilter;
/**
 * This validator class is designed for checking if there is an existing role 
 * with such a name.
 */
class PhoneNoExistsValidator extends AbstractValidator 
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
    const PHONE_NO_EXISTS = 'phoneNoExists';
        
    /**
     * Validation failure messages.
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_DIGIT  => "Телефонный номер должен содержать только цифры",
        self::PHONE_NO_EXISTS  => "Такой номер не зарегистрирован"        
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
            return $false; 
        }
        
        // Get Doctrine entity manager.
        $entityManager = $this->options['entityManager'];
        
        $phoneFilter = new PhoneFilter();
        $phone = $entityManager->getRepository(Phone::class)
                ->findOneByName($phoneFilter->filter($value));
        
        $isValid = ($phone!=null);
        
        // If there were an error, set error message.
        if(!$isValid) {            
            $this->error(self::PHONE_NO_EXISTS);            
        }
        
        // Return validation result.
        return $isValid;
    }
}