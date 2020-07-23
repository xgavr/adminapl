<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Laminas\Json\Json;
use User\Entity\User;
use Stock\Entity\Ptu;
use Admin\Entity\Log;

/**
 * Description of LogManager
 * 
 * @author Daddy
 */
class LogManager {
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Authentication service.
     * @var \Laminas\Authentication\AuthenticationService 
     */
    private $authService;
    
    /**
     * Logged in user.
     * @var User
     */
    private $user = null;
    
    
    public function __construct($entityManager, $authService)
    {
        $this->entityManager = $entityManager;
        $this->authService = $authService;
    }   
    
    /**
     * Получить текущего пользователя
     * 
     * @param bool $useCachedUser
     * @return User
     * @throws \Exception
     */
    private function currentUser($useCachedUser = true)
    {
        if ($useCachedUser && $this->user!==null)
            return $this->user;
        
        if ($this->authService->hasIdentity()) {
            
            $this->user = $this->entityManager->getRepository(User::class)
                    ->findOneByEmail($this->authService->getIdentity());
            if ($this->user==null) {
                throw new \Exception('Not found user with such email');
            }
            
            // Return found User.
            return $this->user;
        }
        
        return null;        
    }
    
    /**
     * Сериализация документа
     * 
     * @param Ptu $obj
     * @param array $childDoc
     */
    private function serializeDoc($obj, $childDocs = null)
    {
        
    }
    
    /**
     * Добавить запись в лог ptu
     * @param Ptu $ptu
     * @param integer $status 
     */
    public function infoPtu($ptu, $status)
    {
        $currentUser = $this->currentUser();
        
        if ($currentUser){
            
            $data = [
                'log_key' => $ptu->getLogKey(),
                'message' => Json::encode($ptu),
                'date_created' => date('Y-m-d H:i:s'),
                'status' => $status,
                'priority' => Log::PRIORITY_INFO,
                'user_id' => $currentUser->getId(),
            ];

            $this->entityManager->getConnection()->insert('log', $data);
        }    
        
        return;
    }           

}
