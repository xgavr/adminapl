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
        set_time_limit(300);
        $startTime = time();
        
        $settings = $this->adminManager->getSettings();
        
        $box = [
            'host' => 'imap.yandex.ru',
            'server' => '{imap.yandex.ru:993/imap/ssl}',
            'user' => $settings['hello_email'],
            'password' => $settings['hello_email_password'],
            'leave_message' => false,
        ];
        
        $mail = $this->postManager->readImap($box);
        
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
        $bodies['subject'] = $log->getSubject(); 
        $bodies['from'] = $log->getFromStrName();
        $fileNames = $log->getAttachmentFileNames();
        $bodies['filename'] = implode(' ', $fileNames);
        $text = implode(' ', $bodies);
//        var_dump($bodies); exit;
        $tokens = $tokenizer->filter($text);
        
        $lemmaFilter = new Lemma($this->entityManager, ['useMailToken' => 1]);
        $lemms = $lemmaFilter->filter($tokens);
        
        return $lemms;
    }
        
}
