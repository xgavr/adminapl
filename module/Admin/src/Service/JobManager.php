<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Laminas\Http\Client;
use Laminas\Json\Json;
use Cron\Cron;
use Cron\Resolver\ArrayResolver;
use Cron\Job\ShellJob;
use Cron\Executor\Executor;

/**
 * Description of JobManager
 * 
 * @author Daddy
 */
class JobManager 
{
    const CRON_DAILY = '01 01 * * *';
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    private $adminManager;

    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
    }
    
    /**
     * Запустить работу
     * @param ArrayResolver $resolver
     */
    public function run($resolver)
    {
        $cron = new Cron();
        $cron->setExecutor(new Executor());
        $cron->setResolver($resolver);
        
        $cron->run();
        
    }
    
}
