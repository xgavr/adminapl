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
use Stock\Entity\PtuGood;
use Admin\Entity\Log;
use Application\Entity\Rate;
use Application\Entity\ScaleTreshold;

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
     * Добавить запись в лог ptu
     * @param Ptu $ptu
     * @param integer $status 
     */
    public function infoPtu($ptu, $status)
    {
        $currentUser = $this->currentUser();
        
        if ($currentUser){
            
            $ptuLog = $ptu->toLog();
            $ptuGoods = $this->entityManager->getRepository(PtuGood::class)
                    ->findByPtu($ptu->getId());
            foreach ($ptuGoods as $ptuGood){
                $ptuLog['goods'][$ptuGood->getRowNo()] = $ptuGood->toLog();
            }
            
            $data = [
                'log_key' => $ptu->getLogKey(),
                'message' => Json::encode($ptuLog),
                'date_created' => date('Y-m-d H:i:s'),
                'status' => $status,
                'priority' => Log::PRIORITY_INFO,
                'user_id' => $currentUser->getId(),
            ];

            $this->entityManager->getConnection()->insert('log', $data);
        }    
        
        return;
    }           

    /**
     * Добавить запись в лог rate
     * @param Rate $rate
     * @param integer $status 
     * @param float $change
     */
    public function infoRate($rate, $status, $change = null)
    {
        $currentUser = $this->currentUser();
        
        if ($currentUser){
            
            $rateLog = $rate->toLog();
            $rateLog['change'] = $change;
            $tresholds = $this->entityManager->getRepository(ScaleTreshold::class)
                    ->findByScale($rate->getScale()->getId());
            foreach ($tresholds as $treshold){
                $rateLog['tresholds'][$treshold->getId()] = $treshold->toLog();
            }
            
            $data = [
                'log_key' => $rate->getLogKey(),
                'message' => Json::encode($rateLog),
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
