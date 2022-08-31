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
    const WGET_URL = 'wget -O - -q -t 1 http://adminapl.ru/proc/';
    
    const CRON_EVERY_DAY = '01 01 * * *';
    const CRON_EVERY_HOUR = '02 * * * *';
    const CRON_EVERY_MIN = '*/1 * * * *';
    const CRON_EVERY_MIN_3 = '*/3 * * * *';
    const CRON_EVERY_MIN_5 = '*/5 * * * *';
    const CRON_EVERY_MIN_6 = '*/6 * * * *';
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
    
    /**
     * Получение сообщений
     * @return array
     */
    private function postJobList()
    {
        return [
          1 => ['command' => 'telegram-postpone',   'shedule' => self::CRON_EVERY_MIN,      'description' => 'Отправка отложенных сообщений'],
          2 => ['command' => 'hello',               'shedule' => self::CRON_EVERY_MIN_5,    'description' => 'Проверка ящика hello и входящих накладных'],
          3 => ['command' => 'prices-by-mail',      'shedule' => self::CRON_EVERY_MIN_5,    'description' => 'Получение писем с прайсами'],
          4 => ['command' => 'prices-by-link',      'shedule' => '17 20 * * *',             'description' => 'Скачивание прайсов по ссылке'],
          5 => ['command' => 'statement-from-post', 'shedule' => self::CRON_EVERY_MIN_5,    'description' => 'Получение писем с выписками банка'],
          6 => ['command' => 'statement-update',    'shedule' => self::CRON_EVERY_MIN_15,   'description' => 'Обновление выписки банка'],
        ];
    }

    /**
     * Очистка данных
     * @return array
     */
    private function clearJobList()
    {
        return [
          101 => ['command' => 'delete-old-prices',         'shedule' => '02 * * * *',  'description' => 'Удаление старых прайсов'],
          102 => ['command' => 'delete-article',            'shedule' => '11 22 * * *', 'description' => 'Удаление пустых артикулов производителей'],
          103 => ['command' => 'delete-unknown-producer',   'shedule' => '41 22 * * *', 'description' => 'Удаление пустых неизвестных производителей'],
          104 => ['command' => 'delete-token',              'shedule' => '11 23 * * *', 'description' => 'Удаление пустых токенов'],
          105 => ['command' => 'delete-bigram',             'shedule' => '41 23 * * *', 'description' => 'Удаление пустых биграм'],
          106 => ['command' => 'delete-goods',              'shedule' => '11 0 * * *',  'description' => 'Удаление пустых карточек товаров'],
          107 => ['command' => 'delete-producer',           'shedule' => '41 0 * * *',  'description' => 'Удаление пустых производителей'],
          108 => ['command' => 'delete-token-group',        'shedule' => '11 01 * * *', 'description' => 'Удаление пустых групп наименований с пересчетом товаров в группе'],
        ];
    }
    
    /**
     * Обработка прайсов
     * @return array
     */
    private function priceJobList()
    {
        return [
          201 => ['command' => 'raw-prices',                        'shedule' => self::CRON_EVERY_MIN_3,    'description' => 'Загрузка данных из фалов прайсов в БД'],
          202 => ['command' => 'parse-raw',                         'shedule' => self::CRON_EVERY_MIN_3,    'description' => 'Разборка прайсов'],
          203 => ['command' => 'unknown-producer-from-rawprice',    'shedule' => self::CRON_EVERY_MIN_3,    'description' => 'Обновление неизвестных производителей из прайсов'],
          204 => ['command' => 'article-from-rawprice',             'shedule' => self::CRON_EVERY_MIN_3,    'description' => 'Обновление артикулов производителей из прайсов'],
          205 => ['command' => 'oem-from-rawprice',                 'shedule' => self::CRON_EVERY_MIN_3,    'description' => 'Обновление номеров из прайса'],
          206 => ['command' => 'producer-from-unknown-producer',    'shedule' => self::CRON_EVERY_MIN_3,    'description' => 'Создание производителей из неизвестных производителей из прайса'],
          207 => ['command' => 'good-from-raw',                     'shedule' => self::CRON_EVERY_MIN_3,    'description' => 'Обновление карточек товаров'],
          208 => ['command' => 'update-good-price-raw',             'shedule' => self::CRON_EVERY_MIN_3,    'description' => 'Обновление цен товаров'],
          209 => ['command' => 'token-from-rawprice',               'shedule' => self::CRON_EVERY_MIN_3,    'description' => 'Обновление токенов из прайса'],
          210 => ['command' => 'token-group-from-rawprice',         'shedule' => self::CRON_EVERY_MIN_3,    'description' => 'Группы наименований из прайса'],
          211 => ['command' => 'update-description',                'shedule' => self::CRON_EVERY_MIN_15,    'description' => 'Обновление описаний товаров'],
        ];
    }
    
    /**
     * Обмен с АПЛ
     * @return array
     */
    private function aplExJobList()
    {
        return [
          301 => ['command' => 'update-apl-acquiring',          'shedule' => '12 06 * * *',             'description' => 'Загрузить эквайринг с АПЛ'],
          302 => ['command' => 'update-attribute-apl-id',       'shedule' => '12 07 * * *',             'description' => 'Обновление атрибутов товаров'],
          303 => ['command' => 'update-generic-group-apl-id',   'shedule' => '12 08 * * *',             'description' => 'Обновление группы Apl общих групп'],
          304 => ['command' => 'update-make-apl-id',            'shedule' => '12 09 * * *',             'description' => 'Обновление aqplId брендов машин'],
          305 => ['command' => 'update-model-apl-id',           'shedule' => '12 10 * * *',             'description' => 'Обновление aplId моделей машин'],
          306 => ['command' => 'update-producer-apl-id',        'shedule' => '12 11 * * *',             'description' => 'Обновление производителей APL ID'],
          307 => ['command' => 'update-attribute-value-apl-id', 'shedule' => '13 * * * *',              'description' => 'Обновление значений атрибутов'],
          308 => ['command' => 'update-car-apl-id',             'shedule' => '23 * * * *',              'description' => 'Обновление aplId машин'],
          309 => ['command' => 'update-good-apl-id',            'shedule' => '33 * * * *',              'description' => 'Обновление AplId товара'],
          310 => ['command' => 'update-good-group',             'shedule' => '43 * * * *',              'description' => 'Обновление групп в АПЛ'],
          311 => ['command' => 'update-group-apl-id',           'shedule' => '53 * * * *',              'description' => 'Получить апл группы товаров'],
          312 => ['command' => 'check-apl-order',               'shedule' => self::CRON_EVERY_MIN_15,   'description' => 'Проверка выгрузки заказов из АПЛ'],
          313 => ['command' => 'comments',                      'shedule' => self::CRON_EVERY_MIN_15,   'description' => 'Обновление комментариев из АПЛ'],
          314 => ['command' => 'update-apl-cash',               'shedule' => self::CRON_EVERY_MIN_15,   'description' => 'Обновление платежей из АПЛ'],
          315 => ['command' => 'update-apl-order',              'shedule' => self::CRON_EVERY_MIN_15,   'description' => 'Обновление заказов из АПЛ'],
          316 => ['command' => 'update-apl-users',              'shedule' => self::CRON_EVERY_MIN_15,   'description' => 'Загрузка пользователей из АПЛ'],
          317 => ['command' => 'update-good-names',             'shedule' => self::CRON_EVERY_MIN_15,   'description' => 'Обновление наименований товаров в Апл'],
          318 => ['command' => 'update-good-prices',            'shedule' => self::CRON_EVERY_MIN_15,   'description' => 'Обновление цен товаров в Апл'],
          319 => ['command' => 'update-rawprices',              'shedule' => self::CRON_EVERY_MIN_15,   'description' => 'Обновление строк прайсов в АПЛ'],
          320 => ['command' => 'update-supplier-order',         'shedule' => self::CRON_EVERY_MIN_30,   'description' => 'Обновить заказы поставщикам'],
          321 => ['command' => 'update-apl-ptu',                'shedule' => '25,55 * * * *',           'description' => 'Обновление поступлений из АПЛ'],
        ];
    }
    
    /**
     * Запустить работу
     */
    public function run()
    {
        $load = sys_getloadavg();
        if ($load[0] < 7){
            $processCount = $this->entityManager->getRepository(Setting::class)
                    ->count(['status' => Setting::STATUS_ACTIVE]);
            
            if ($processCount < 20){
                
                $resolver = new ArrayResolver();
                
                $jobs = array_merge($this->postJobList(), $this->clearJobList(), $this->priceJobList(), $this->aplExJobList());
                foreach ($jobs as $job){
                    
                    $newJob = new ShellJob();
                    $newJob->setCommand(self::WGET_URL.$job['command']);
//                    var_dump(self::WGET_URL.$job['command']);
                    $newJob->setSchedule(new CrontabSchedule($job['shedule']));
//                    var_dump($job['shedule']);
                    
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
