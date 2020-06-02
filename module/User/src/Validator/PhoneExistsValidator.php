<?php
namespace User\Validator;

use Laminas\Validator\AbstractValidator;
use Application\Entity\Phone;
use User\Filter\PhoneFilter;
/**
 * This validator class is designed for checking if there is an existing role 
 * with such a name.
 */
class PhoneExistsValidator extends AbstractValidator 
{
    /**
     * Available validator options.
     * @var array
     */
    protected $options = array(
        'entityManager' => null,
        'phone' => null
    );
    
    // Validation failure message IDs.
    const NOT_DIGIT  = 'notDigit';
    const PHONE_EXISTS = 'phoneExists';
        
    /**
     * Validation failure messages.
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_DIGIT  => "Телефонный номер должен содержать только цифры",
        self::PHONE_EXISTS  => "Такой номер уже используется"        
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
            if(isset($options['phone']))
                $this->options['phone'] = $options['phone'];
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
        
        if($this->options['phone']==null) {
            $isValid = ($phone==null);
        } else {
            if($this->options['phone']->getName($format = PhoneFilter::PHONE_FORMAT_DB)!=$phoneFilter->filter($value) && $phone!=null) 
                $isValid = false;
            else 
                $isValid = true;
        }
        
        // If there were an error, set error message.
        if(!$isValid) {            
            $this->error(self::PHONE_EXISTS);            
        }
        
        // Return validation result.
        return $isValid;
    }
}