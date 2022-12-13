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
        'phone' => null,
        'contact' => null,
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
            if(isset($options['contact']))
                $this->options['contact'] = $options['contact'];
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
        $contact = $this->options['contact'];
        
        $phoneFilter = new PhoneFilter();
        $phoneValue = $phoneFilter->filter($value);
        
        $phone = $entityManager->getRepository(Phone::class)
                ->findOneByName($phoneValue);
        
        $flag = false;
        if ($phone){
            if ($contact){
                $flag = $contact->isParentTypeDifferent($phone->getContact());
            } else {
                $flag = true;
            }
        }
        
        if($this->options['phone']==null) {
            $isValid = ($phone==null);
        } else {
            if($this->options['phone']->getName(PhoneFilter::PHONE_FORMAT_DB) != $phoneValue && $flag) 
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