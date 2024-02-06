<?php
namespace Bank\Service;

use Bank\Entity\Statement;
use Phpml\Tokenization\WordTokenizer;

/**
 * This service ml mamager.
 */
class MlManager
{
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;  
    
    /**
     * Constructs the service.
     */
    public function __construct($entityManager) 
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * леммы из назначения платежа
     * @param Statement $statement
     */
    public function statementLemms($statement)
    {
        $tokenizer = new WordTokenizer();
        $tokens = $tokenizer->tokenize($statement->getPaymentPurpose());
        
        var_dump($tokens);
        foreach ($tokens as $token){
            
        }
        
        return;
    }
}

