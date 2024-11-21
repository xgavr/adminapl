<?php
namespace User\Validator;

use Laminas\Validator\AbstractValidator;
use Application\Entity\Email;
use Laminas\Validator\EmailAddress;
/**
 * This validator class is designed for checking if there is an email in black list 
 * with such a name.
 */
class EmailBlackListValidator extends AbstractValidator 
{
    /**
     * Available validator options.
     * @var array
     */
    protected $options = array(
        'hostBlackList' => ['5070687.ru', 'autopartslist.ru'],
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
        
    private function parseEmail($email)
    {
        return parse_url('mailto://'.$email);
    }
    /**
     * Check if user exists.
     */
    public function isValid($value) 
    {
        $parsed = $this->parseEmail($value);
        // Return validation result.
        return !in_array($parsed['host'], $this->options['hostBlackList']);
    }
}