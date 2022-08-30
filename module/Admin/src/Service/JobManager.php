<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Cron\Cron;
use Cron\Job\ShellJob;
use Cron\Executor\Executor;
use Admin\Entity\Setting;
use Cron\Schedule\CrontabSchedule;
use Cron\Resolver\ArrayResolver;

/**
 * Description of JobManager
 * 
 * @author Daddy
 */
class JobManager 
{
    const WGET_URL = 'wget -O - -q -t 1 https://adminapl.ru/proc/';
    
    const CRON_EVERY_DAY = '01 01 * * *';
    const CRON_EVERY_HOUR = '02 * * * *';
    const CRON_EVERY_MIN = '*/1 * * * *';
    const CRON_EVERY_MIN_5 = '*/5 * * * *';
    const CRON_EVERY_MIN_10 = '*/10 * * * *';
    const CRON_EVERY_MIN_15 = '*/15 * * * *';
    const CRON_EVERY_MIN_20 = '*/20 * * * *';
    const CRON_EVERY_MIN_25 = '*/25 * * * *';
    const CRON_EVERY_MIN_30 = '*/30 * * * *';
    const CRON_EVERY_MIN_40 = '*/40 * * * *';
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Admin Manager
     * @var \Admin\Service\Admin
     */
    private $adminManager;

    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
    }
    
    private function postJobList()
    {
        return [
          1 => ['command' => 'telegram-postpone',   'shedule' => self::CRON_EVERY_MIN,      'description' => 'Отправка отложенных сообщений, каждую минуту'],
          2 => ['command' => 'hello',               'shedule' => self::CRON_EVERY_MIN_5,    'description' => 'Проверка ящика hello и входящих накладных'],
          3 => ['command' => 'prices-by-mail',      'shedule' => self::CRON_EVERY_MIN_5,    'description' => 'Получение писем с прайсами'],
          4 => ['command' => 'prices-by-link',      'shedule' => '17 20 * * *',             'description' => 'Скачивание прайсов по ссылке'],
        ];
    }
    
    /**
     * Запустить работу
     */
    public function run()
    {
        $load = sys_getloadavg();
        if ($load[0] < 10){
            $processCount = $this->entityManager->getRepository(Setting::class)
                    ->count(['status' => Setting::STATUS_ACTIVE]);
            
            if ($processCount < 31){
                
                $resolver = new ArrayResolver();
                
                $jobs = $this->postJobList();
                foreach ($jobs as $job){
                    
                    $newJob = new ShellJob();
                    $newJob->setCommand(self::WGET_URL.$job['command']);
                    $newJob->setSchedule(new CrontabSchedule($job['shedule']));
                    
                    $resolver->addJob($newJob);
                }
                
                $cron = new Cron();
                $cron->setExecutor(new Executor());
                $cron->setResolver($resolver);

                $cron->run();
            }    
        }    
    }
    
}
