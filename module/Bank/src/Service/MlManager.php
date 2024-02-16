<?php
namespace Bank\Service;

use Bank\Entity\Statement;
use Phpml\Tokenization\WordTokenizer;
use Application\Filter\Lemma;
use Application\Filter\Tokenizer;
use Bank\Entity\StatementToken;
use Application\Entity\Token;

/**
 * This service ml mamager.
 */
class MlManager
{
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
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
     * Updates tokens of a statement. 
     * @param Statement $statement
     * @param array $tokens
     */
    private function updateStatementTokens($statement, $tokens)
    {
        // Remove old permissions.
        $statement->getStatementTokens()->clear();
        
        // Assign new permissions to role
        foreach ($tokens as $statementToken) {

            $statement->getStatementTokens()->add($statementToken);            
        }
        
        $statement->setStatusToken(Statement::STATUS_TOKEN_TOKEN);
        $this->entityManager->persist($statement);
        // Apply changes to database.
        $this->entityManager->flush();        
    }
    
    /**
     * леммы из назначения платежа
     * @param Statement $statement
     */
    public function statementLemms($statement)
    {
        $tokenizer = new Tokenizer();
        $lemmaFilter = new Lemma($this->entityManager, ['useStatementToken' => true]);
        $tokens = $lemmaFilter->filter($tokenizer->filter($statement->getPaymentPurpose()));
        
//        var_dump($tokens);
        $statementTokens = [];
        
        foreach ($tokens as $lemms){
            foreach ($lemms as $key => $value){
                if (!empty($value) && in_array($key, StatementToken::getUseStatusList())){
                    $statemetToken = $this->entityManager->getRepository(StatementToken::class)
                            ->findOneBy(['lemma' => $value]);

                    if (empty($statemetToken)){
                        $statemetToken = new StatementToken();
                        $statemetToken->setCorrect(null);
                        $statemetToken->setFrequency(0);
                        $statemetToken->setIdf(0);
                    }

                    $statemetToken->setLemma($value);
                    $statemetToken->setStatus($key);

                    $this->entityManager->persist($statemetToken);            
                    $this->entityManager->flush();
                    
                    $statementTokens[] = $statemetToken;
                }    
            }    
        }
        
        $this->updateStatementTokens($statement, $statementTokens);
        
        return;
    }
    
    /**
     * 
     * @param StatementToken $statementToken
     * @param integer $statementCount
     */
    public function updateStatementTokenCount($statementToken, $statementCount = 0)
    {
        if (empty($statementCount)){
            $statementCount = $this->entityManager->getRepository(Statement::class)
                    ->count([]);
        }    
        
        $statementTokenCount = $statementToken->getStatements()->count();
        
        if (!empty($statementTokenCount)){
//            $statementToken->setFrequency($statementTokenCount);
//            $statementToken->setIdf(log10($statementCount/$statementTokenCount));
//            $this->entityManager->persist($statementToken);
            $this->entityManager->getConnection()->update('statement_token', [
                'frequency' => $statementTokenCount,
                'idf' => log10($statementCount/$statementTokenCount),
            ], ['id' => $statementToken->getId()]);
        } else {
            $this->entityManager->remove($statementToken);
            $this->entityManager->flush();
        }   
        
        
        return;
    }
    
    public function statementTokens()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1800);
        $startTime = time();
                
        $statements = $this->entityManager->getRepository(Statement::class)
                ->findBy(['statusToken' => Statement::STATUS_TOKEN_NO]);
        
        foreach ($statements as $statement){
            $this->statementLemms($statement);
            if (time() > $startTime + 1740){
                break;
            }
        }
        
        return;
    }
    
    public function updateStatementTokensCount()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(900);
        $startTime = time();
        
        $statementCount = $this->entityManager->getRepository(Statement::class)
                ->count([]);
        
        $statementTokens = $this->entityManager->getRepository(StatementToken::class)
                ->findBy([]);
        
        foreach ($statementTokens as $statementToken){
            $this->updateStatementTokenCount($statementToken, $statementCount);
            if (time() > $startTime + 840){
                break;
            }
        }
        
        return;
    }
}

