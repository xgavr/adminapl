<?php
namespace User\Validator;

use Laminas\Validator\AbstractValidator;
use Application\Entity\Email;
use Laminas\Validator\EmailAddress;
/**
 * This validator class is designed for checking if there is an phone in black list 
 * with such a name.
 */
class PhoneBlackListValidator extends AbstractValidator 
{
    /**
     * Available validator options.
     * @var array
     */
    protected $options = array(
        'firstDigitBlackList' => ['7', '8'],
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
        $firstDigit = substr($value, 0, 1);
        // Return validation result.
        return !in_array($firstDigit, $this->options['firstDigitBlackList']);
    }
}