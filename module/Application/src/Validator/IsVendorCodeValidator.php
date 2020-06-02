<?php

namespace Application\Validator;

use Laminas\Validator\AbstractValidator;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IsVendorCodeValidator
 *
 * @author Daddy
 */
class IsVendorCodeValidator extends AbstractValidator
{
    
    public function __construct($options = null) 
    {
        // Call the parent class constructor
        parent::__construct($options);
    }    
    
    public function isValid($value) 
    {
        if(!is_scalar($value)) {
            $this->error(self::NOT_SCALAR);
            return $false; 
        }
        
        // Get Doctrine entity manager.
        $entityManager = $this->options['entityManager'];
        
        $user = $entityManager->getRepository(User::class)
                ->findOneByEmail($value);
        
        if($this->options['user']==null) {
            $isValid = ($user==null);
        } else {
            if($this->options['user']->getEmail()!=$value && $user!=null) 
                $isValid = false;
            else 
                $isValid = true;
        }
        
        // If there were an error, set error message.
        if(!$isValid) {            
            $this->error(self::USER_EXISTS);            
        }
        
        // Return validation result.
        return $isValid;
    }
    
}
