<?php
namespace Company\Validator;

use Laminas\Validator\AbstractValidator;
use Company\Entity\Legal;

/**
 * This validator class is designed for checking if there is an existing role 
 * with such a name.
 */
class LegalExistsValidator extends AbstractValidator 
{
    /**
     * Available validator options.
     * @var array
     */
    protected $options = array(
        'entityManager' => null,
        'legal' => null
    );
    
    // Validation failure message IDs.
    const LEGAL_EXISTS = 'legalExists';
        
    /**
     * Validation failure messages.
     * @var array
     */
    protected $messageTemplates = array(
        self::LEGAL_EXISTS  => "Такая организация уже существует"        
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
     * Check if legal exists.
     */
    public function isValid($inn, $kpp = null) 
    {
        // Get Doctrine entity manager.
        $entityManager = $this->options['entityManager'];
        
        $legal = $entityManager->getRepository(Legal::class)
                ->findOneByInnKpp($inn, $kpp);
        
        if($this->options['legal']==null) {
            $isValid = ($legal==null);
        } else {
            if($this->options['legal']->getInn()!=$inn && $this->options['legal']->getKpp()!=$kpp && $legal!=null) 
                $isValid = false;
            else 
                $isValid = true;
        }
        
        // If there were an error, set error message.
        if(!$isValid) {            
            $this->error(self::LEGAL_EXISTS);            
        }
        
        // Return validation result.
        return $isValid;
    }
}