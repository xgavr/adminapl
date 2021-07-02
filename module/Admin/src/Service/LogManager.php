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
use Stock\Entity\Vtp;
use Stock\Entity\VtpGood;
use Stock\Entity\Ot;
use Stock\Entity\OtGood;
use Stock\Entity\Pt;
use Stock\Entity\PtGood;
use Stock\Entity\St;
use Stock\Entity\StGood;
use Admin\Entity\Log;
use Application\Entity\Rate;
use Application\Entity\ScaleTreshold;
use Application\Entity\Order;
use Application\Entity\Bid;

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
     * Добавить запись в лог vtp
     * @param Vtp $vtp
     * @param integer $status 
     */
    public function infoVtp($vtp, $status)
    {
        $currentUser = $this->currentUser();
        
        if ($currentUser){
            
            $vtpLog = $vtp->toLog();
            $vtpGoods = $this->entityManager->getRepository(VtpGood::class)
                    ->findByVtp($vtp->getId());
            foreach ($vtpGoods as $vtpGood){
                $vtpLog['goods'][$vtpGood->getRowNo()] = $vtpGood->toLog();
            }
            
            $data = [
                'log_key' => $vtp->getLogKey(),
                'message' => Json::encode($vtpLog),
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
     * Добавить запись в лог ot
     * @param Ot $ot
     * @param integer $status 
     */
    public function infoOt($ot, $status)
    {
        $currentUser = $this->currentUser();
        
        if ($currentUser){
            
            $otLog = $ot->toLog();
            $otGoods = $this->entityManager->getRepository(OtGood::class)
                    ->findByOt($ot->getId());
            foreach ($otGoods as $otGood){
                $otLog['goods'][$otGood->getRowNo()] = $otGood->toLog();
            }
            
            $data = [
                'log_key' => $ot->getLogKey(),
                'message' => Json::encode($otLog),
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
     * Добавить запись в лог pt
     * @param Pt $pt
     * @param integer $status 
     */
    public function infoPt($pt, $status)
    {
        $currentUser = $this->currentUser();
        
        if ($currentUser){
            
            $ptLog = $pt->toLog();
            $ptGoods = $this->entityManager->getRepository(PtGood::class)
                    ->findByPt($pt->getId());
            foreach ($ptGoods as $ptGood){
                $ptLog['goods'][$ptGood->getRowNo()] = $ptGood->toLog();
            }
            
            $data = [
                'log_key' => $pt->getLogKey(),
                'message' => Json::encode($ptLog),
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
     * Добавить запись в лог st
     * @param St $st
     * @param integer $status 
     */
    public function infoSt($st, $status)
    {
        $currentUser = $this->currentUser();
        
        if ($currentUser){
            
            $stLog = $st->toLog();
            $stGoods = $this->entityManager->getRepository(StGood::class)
                    ->findBySt($st->getId());
            foreach ($stGoods as $stGood){
                $stLog['goods'][$stGood->getRowNo()] = $stGood->toLog();
            }
            
            $data = [
                'log_key' => $st->getLogKey(),
                'message' => Json::encode($stLog),
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

    /**
     * Добавить запись в лог order
     * @param Order $order
     * @param integer $status 
     */
    public function infoOrder($order, $status)
    {
        $currentUser = $this->currentUser();
        
        if ($currentUser){
            
            $orderLog = $order->toLog();
            $bids = $this->entityManager->getRepository(Bid::class)
                    ->findByOrder($order->getId());
            foreach ($bids as $bid){
                $orderLog['goods'][$bid->getRowNo()] = $bid->toLog();
            }
            
            $data = [
                'log_key' => $order->getLogKey(),
                'message' => Json::encode($orderLog),
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
