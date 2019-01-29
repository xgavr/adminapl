<?php

namespace Application\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Config\Config;
use Application\Entity\Token;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Строка содержит слово из черного списка?
 *
 * @author Daddy
 */
class IsBlackList extends AbstractValidator
{
    private $dict;
    
    public function __construct($options = null) 
    {
        // Call the parent class constructor
        parent::__construct($options);
        
        if (file_exists(Token::MY_BLACK_LIST)){
            $this->dict = new Config(include Token::MY_BLACK_LIST, true);
//            return $dict->get($word) !== null;            
        } else {
            $this->dict = [];
        }       
    }    
    
    /**
     * Возвращает истину, если строка содержит слово из черного списка
     *
     * @param string $str
     * 
     * @return bool
     */
    public function isValid($str)
    {
        foreach ($this->dict as $needle){
            if (mb_stristr($str, $needle)!== false){
                return true;
            }
        }
        
        return false;
    }
    
}
