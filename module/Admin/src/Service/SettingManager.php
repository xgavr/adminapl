<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Admin\Entity\Setting;
use Laminas\Http\Response;

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
            $this->entityManager->getConnection()->insert('setting',
                    [
                        'controller' => $controller,
                        'action' => $action,
                        'status' => Setting::STATUS_ACTIVE,
                        'last_mod' => date('Y-m-d H:i:s'),
                    ]);
        } else {
            //if ($proc->getStatus() == Setting::STATUS_RETIRED){
                $this->entityManager->getConnection()->update('setting',
                        [
                            'status' => Setting::STATUS_ACTIVE,
                            'last_mod' => date('Y-m-d H:i:s'),
                        ], ['id' => $proc->getId()]);
            //}
        }
        return;
    }    
    
    /**
     * Установить метку процесс завершился с ошибкой
     * 
     * @param string $controller
     * @param string $action
     * @param Response $response
     */
    public function errorProcess($controller, $action, $response)
    {
        $proc = $this->entityManager->getRepository(Setting::class)
                ->findOneBy(['controller' => $controller, 'action' => $action]);
        
        if ($proc){
            $this->entityManager->getConnection()->update('setting',
                    [
                        'status' => Setting::STATUS_ERROR,
                        'last_mod' => date('Y-m-d H:i:s'),
                        'err_code' => $response->getStatusCode(),
                        'err_text' => $response->getContent(),
                    ], ['id' => $proc->getId()]);
        }
        return;
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
        $activeCount = $this->entityManager->getRepository(Setting::class)
                ->count(['status' => Setting::STATUS_ACTIVE]);
        if ($activeCount > 20){
            return false;
        }

        $proc = $this->entityManager->getRepository(Setting::class)
                ->findOneBy(['controller' => $controller, 'action' => $action]);
        
        if (!$proc){
            return TRUE;
        }
        
        if ($proc->getStatus() == Setting::STATUS_RETIRED){
            return TRUE;
        }        

        $lastMod = strtotime($proc->getLastMod()) + 3600*3;
        if ($proc->getStatus() == Setting::STATUS_ACTIVE && time() > $lastMod){
            return TRUE;
        }                
        if ($proc->getStatus() == Setting::STATUS_ERROR && time() > $lastMod){
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
            try {
                $this->entityManager->getConnection()->update('setting',
                        [
                            'status' => Setting::STATUS_RETIRED,
                            'last_mod' => date('Y-m-d H:i:s'),
                            'err_code' => null,
                            'err_text' => null,
                        ], ['id' => $proc->getId()]);
            } catch(\Doctrine\ORM\ORMException $e){
                
            }    
        }
        return;
    }
    
    /**
     * Изменить наименование процесса
     * 
     * @param Setting $process
     * @param string $name
     */
    public function editProcessName($process, $name)
    {
        $process->setName($name);
        $this->entityManager->persist($process);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Изменить статус процесса
     * 
     * @param Setting $process
     * @param int $status
     */
    public function editProcessStatus($process, $status)
    {
        $process->setStatus($status);
        $this->entityManager->persist($process);
        $this->entityManager->flush();
        
        return;
    }
    
}
