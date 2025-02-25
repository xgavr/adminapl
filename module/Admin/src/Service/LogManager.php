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
use Stock\Entity\PtuCost;
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
use Stock\Entity\Vt;
use Stock\Entity\VtGood;
use Cash\Entity\CashDoc;
use Application\Entity\Comment;
use Stock\Entity\Retail;
use Application\Entity\Email;
use Stock\Entity\Mutual;

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
    public function currentUser($useCachedUser = true)
    {
        if ($useCachedUser && $this->user !== null) {
            return $this->user;
        }

        if ($this->authService->hasIdentity()) {
            
            $this->user = $this->entityManager->getRepository(User::class)
                    ->findOneByEmail($this->authService->getIdentity());
            if ($this->user==null) {
                $email = $this->entityManager->getRepository(Email::class)
                        ->findOneBy(['name' => $this->authService->getIdentity()]);
                $this->user = $email->getUser();
                if ($this->user == null){
                    throw new \Exception('Not found user with such email '.$this->authService->getIdentity());
                }    
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

            $ptuCosts = $this->entityManager->getRepository(PtuCost::class)
                    ->findByPtu($ptu->getId());
            foreach ($ptuCosts as $ptuCost){
                $ptuLog['costs'][$ptuCost->getRowNo()] = $ptuCost->toLog();
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
     * @param bool $nullUser 
     */
    public function infoPt($pt, $status, $nullUser = false)
    {
        $currentUser = $this->currentUser();
        
        if ($currentUser || $nullUser){
            
            $ptLog = $pt->toLog();
            $ptLog['goods'] = [];
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
                'user_id' => ($currentUser) ? $currentUser->getId():null,
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
    
    /**
     * Добавить запись в лог order
     * @param Comment $comment
     * @param integer $status 
     */
    public function infoComment($comment, $status)
    {
        $currentUser = $this->currentUser();
        
        if ($currentUser){
            
            $commentLog = ['comment' => $comment->toLog()];
            
            $data = [
                'log_key' => $comment->getOrder()->getLogKey(),
                'message' => Json::encode($commentLog),
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
     * Добавить запись в лог vt
     * @param Vt $vt
     * @param integer $status 
     */
    public function infoVt($vt, $status)
    {
        $currentUser = $this->currentUser();
        
        if ($currentUser){
            
            $vtLog = $vt->toLog();
            $vtGoods = $this->entityManager->getRepository(VtGood::class)
                    ->findByVt($vt->getId());
            foreach ($vtGoods as $vtGood){
                $vtLog['goods'][$vtGood->getRowNo()] = $vtGood->toLog();
            }
            
            $data = [
                'log_key' => $vt->getLogKey(),
                'message' => Json::encode($vtLog),
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
     * Добавить запись в лог cash
     * @param CashDoc $cashDoc
     * @param integer $status 
     */
    public function infoCash($cashDoc, $status)
    {
        $currentUser = $this->currentUser();
        
        if ($currentUser){
            
            $cashLog = $cashDoc->toLog();
            $data = [
                'log_key' => $cashDoc->getLogKey(),
                'message' => Json::encode($cashLog),
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
     * Добавить запись в лог revise
     * @param Revise $revise
     * @param integer $status 
     */
    public function infoRevise($revise, $status)
    {
        $currentUser = $this->currentUser();
        
        if ($currentUser){
            
            $reviseLog = $revise->toLog();
            
            $data = [
                'log_key' => $revise->getLogKey(),
                'message' => Json::encode($reviseLog),
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
     * Добавить запись в лог good
     * @param Good $good
     * @param integer $status 
     */
    public function infoGood($good, $status)
    {
        $currentUser = $this->currentUser();
        
        if ($currentUser){
            
            $goodLog = $good->toLog();
            
            $data = [
                'log_key' => $good->getCode(),
                'message' => Json::encode($goodLog),
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
     * Лог ревизии 
     * @param Mutual $mutual
     * @param int $status
     * @return null
     */
    public function infoMutual($mutual, $status)
    {
        $currentUser = $this->currentUser();
        
        if ($currentUser){
            $mutualLog = ['revision' => $mutual->getRevise(), 'revisionName' => $mutual->getReviseAsString()];

            $data = [
                'log_key' => $mutual->getDocKey(),
                'message' => Json::encode($mutualLog),
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
     * Лог ревизии 
     * @param Retail $retail
     * @param int $status
     * @return null
     */
    public function infoRetail($retail, $status)
    {
        $currentUser = $this->currentUser();
        
        if ($currentUser){
            $retailLog = ['revision' => $retail->getRevise(), 'revisionName' => $retail->getReviseAsString()];

            $data = [
                'log_key' => $retail->getDocKey(),
                'message' => Json::encode($retailLog),
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
     * Получить последние строки error.log
     */
    public function errorLog()
    {
        $errorLogFile = './../../logs/adminapl.ru.error.log';
        $result = [];
        $size = filesize($errorLogFile);
//        var_dump(realpath($errorLogFile));
        if ($size > 1024*1000){
            $result[] = 'Слишком большой размер лога - '.$size;
        }
        
        $fp = @fopen($errorLogFile, "r");
        if ($fp) {
            while (($buffer = fgets($fp, 4096)) !== false) {
                $result[] = strip_tags($buffer);
            }
            if (!feof($fp)) {
                $result[] = "Ошибка: fgets() неожиданно потерпел неудачу\n";
            }
            fclose($fp);
        }        
        
        return array_slice($result, -5);
    }
}
