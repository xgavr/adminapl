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
     * @param string $module
     * @param string $controller
     * @param string $action
     */
    public function addProcess($module, $controller, $action)
    {
        $proc = $this->entityManager->getRepository(Setting::class)
                ->findOneBy(['module' => $module, $controller, $controller]);
        
        if ($proc == null){
            $setting = new Setting();
            $setting->setModule($module);
            $setting->setController($controller);
            $setting->setAction($action);
            $setting->setStatus(Setting::STATUS_ACTIVE);
            
        } else {
            if ($setting->getStatus() === Setting::STATUS_RETIRED){
                $setting->setStatus(Setting::STATUS_ACTIVE);                
            }
        }
        
        $this->entityManager->persist($setting);
        $this->entityManager->flush();
    }    
    
    /**
     * Установить метку остановки процесса 
     * 
     * @param string $module
     * @param string $controller
     * @param string $action
     */
    public function removeProcess($module, $controller, $action)
    {
        $proc = $this->entityManager->getRepository(Setting::class)
                ->findOneBy(['module' => $module, $controller, $controller]);
        
        if ($proc){
            $setting->setStatus(Setting::STATUS_RETIRED);                
            $this->entityManager->persist($setting);
            $this->entityManager->flush();
        }        
    }
    
}
