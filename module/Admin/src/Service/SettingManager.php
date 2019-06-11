<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Admin\Entity\Setting;

/**
 * Description of SettingManager
 *
 * @author Daddy
 */
class SettingManager {

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
    
    /**
     * Установить метку запуска процесса
     * 
     * @param string $controller
     * @param string $action
     */
    public function addProcess($controller, $action)
    {
        $proc = $this->entityManager->getRepository(Setting::class)
                ->findOneBy(['controller' => $controller, 'action' => $action]);
        
        if ($proc == null){
            $proc = new Setting();
            $proc->setController($controller);
            $proc->setAction($action);
            $proc->setStatus(Setting::STATUS_ACTIVE);            
        } else {
            if ($proc->getStatus() == Setting::STATUS_RETIRED){
                $proc->setStatus(Setting::STATUS_ACTIVE);                
            }
        }
        $proc->setLastMod(date('Y-m-d H:i:s'));
        
        $this->entityManager->persist($proc);
        $this->entityManager->flush($proc);
    }    
    
    /**
     * Определяем возможность старта процесса
     * 
     * @param string $controller
     * @param string $action
     * @return boolean
     */
    public function canStart($controller, $action)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            
        } else {
            if (!$this->adminManager->canRun()){
                return FALSE;
            }            
        }

        $proc = $this->entityManager->getRepository(Setting::class)
                ->findOneBy(['controller' => $controller, 'action' => $action]);
        
        if (!$proc){
            return TRUE;
        }
        
        if ($proc->getStatus() == Setting::STATUS_RETIRED){
            return TRUE;
        }        

        $lastMod = strtotime($proc->getLastMod()) + 900;
        if ($proc->getStatus() == Setting::STATUS_ACTIVE && time() > $lastMod){
            return TRUE;
        }                
        
        return FALSE;
    }


    /**
     * Установить метку остановки процесса 
     * 
     * @param string $module
     * @param string $controller
     * @param string $action
     */
    public function removeProcess($controller, $action)
    {
        $proc = $this->entityManager->getRepository(Setting::class)
                ->findOneBy(['controller' => $controller, 'action' => $action]);
        
        if ($proc){
            $proc->setStatus(Setting::STATUS_RETIRED);                
            $proc->setLastMod(date('Y-m-d H:i:s'));
            
            $this->entityManager->persist($proc);
            $this->entityManager->flush($proc);
        }        
    }
    
}
