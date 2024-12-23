<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Admin\Filter\AutoruOrderFilter;
use Admin\Filter\HtmlFilter;
use Admin\Filter\TurboOrderFilter;
use Admin\Entity\PostLog;
use Application\Filter\Tokenizer;
use Admin\Entity\MailToken;
use Admin\Entity\MailPostToken;
use Application\Filter\Lemma;

/**
 * Description of HelloManager
 *
 * @author Daddy
 */
class HelloManager {
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Post manager.
     * @var \Admin\Service\PostManager
     */
    private $postManager;
    
    /**
     * Apl manager.
     * @var \Admin\Service\AplService
     */
    private $aplService;

    /**
     * Telegramm manager.
     * @var \Admin\Service\TelegrammManager
     */
    private $telegramManager;
    
    /**
     * Менеджер админ
     * 
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;   

    
    public function __construct($entityManager, $postManager, $telegramManager, $aplService, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->postManager = $postManager;        
        $this->telegramManager = $telegramManager;        
        $this->aplService = $aplService;        
        $this->adminManager = $adminManager;
    }
        
    public function checkingMail()
    {
        $settings = $this->adminManager->getSettings();
        
        $box = [
            'host' => 'imap.mail.ru',
            'server' => '{imap.mail.ru:993/imap/ssl}',
            'user' => $settings['hello_email'],
            'password' => $settings['hello_app_password'],
            'leave_message' => false,
            'folders' => ['hello'],
        ];
        
        $this->postManager->readImap($box);
        
        return;
    }
    
    /**
     * Удалить токен письма
     * 
     * @param MailToken $token
     */
    public function removeToken($token)
    {
        $this->entityManager->remove($token);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Удалить связь токена и письма
     * 
     * @param MailPostToken $postToken
     */
    public function removeMailPostToken($postToken)
    {
        $this->entityManager->remove($postToken);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Удалить токены письма
     * 
     * @param PostLog $log
     */
    public function removeLogPostTokens($log)
    {
        $postTokens = $this->entityManager->getRepository(MailPostToken::class)
                ->findBy(['postLog' => $log->getId()]);
        foreach ($postTokens as $postToken){
            $this->removeMailPostToken($postToken);
        }
        
        return;
    }
    
    /**
     * Разобрать письмо на токены
     * 
     * @param PostLog $log
     */
    public function toTokens($log)
    {
        $tokenizer = new Tokenizer();
        $bodies = $log->getBodyAsArray();
        $bodies[MailPostToken::PART_SUBLECT] = $log->getSubject(); 
        $bodies[MailPostToken::PART_FROM] = $log->getFromStrName();
        $fileNames = $log->getAttachmentFileNames();
        $bodies[MailPostToken::PART_FILENAME] = implode(' ', $fileNames);

        $this->entityManager->getRepository(MailPostToken::class)
                ->removeMailPostTokens($log);
        
        foreach ($bodies as $part => $text){
            $tokens = $tokenizer->filter($text);
        
            $lemmaFilter = new Lemma($this->entityManager, ['useMailToken' => 1]);
            $lemms = $lemmaFilter->filter($tokens);
            foreach ($lemms as $k => $words){
                foreach ($words as $key => $word){
                    if (mb_strlen($word) < 64){
                        $mailToken = $this->entityManager->getRepository(MailToken::class)
                                ->findOneByLemma($word);
                        if (!$mailToken){
                            $mailToken = new MailToken();
                            $mailToken->setLemma($word);
                            $mailToken->setStatus($key);
                            $this->entityManager->persist($mailToken);
                            $this->entityManager->flush($mailToken);
                        }    

                        if ($mailToken){
                            $mailPostToken = $this->entityManager->getRepository(MailPostToken::class)
                                        ->findOneBy([
                                            'postLog' => $log->getId(), 
                                            'mailToken' => $mailToken->getId(),
                                            'mailPart' => $part,
                                                ]);

                            if (!$mailPostToken){
                                $mailPostToken = new MailPostToken();
                                $mailPostToken->setPostLog($log);
                                $mailPostToken->setMailToken($mailToken);
                                $mailPostToken->setStatus($key);
                                $mailPostToken->setMailPart($part);
                                $this->entityManager->persist($mailPostToken);
                                $this->entityManager->flush($mailPostToken);
                            }   
                        }    
                    }    
                }    
            }    
        }            
            
        $log->setStatus(PostLog::STATUS_RETIRED);
        $this->entityManager->persist($log);
        $this->entityManager->flush($log);
            
        return;
    }
    
    /**
     * Разбор писем на токены
     */
    public function logsToTokens()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(900);
        $startTime = time();
        
        $logs = $this->entityManager->getRepository(PostLog::class)
                ->findBy(['status' => PostLog::STATUS_ACTIVE], [], 10000);

        foreach ($logs as $log){
            $this->toTokens($log);
            if (time() > $startTime + 840){
                return;
            }
        }
        
        return;
    }           
        
}
