<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin;

use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;

class Module
{
    const VERSION = '0.0.1-dev';
    
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    // Метод "init" вызывается при запуске приложения и  
    // позволяет зарегистрировать обработчик событий.
    public function init(ModuleManager $manager)
    {
        // Получаем менеджер событий.
        $eventManager = $manager->getEventManager();
        $sharedEventManager = $eventManager->getSharedManager();
        // Регистрируем метод-обработчик. 
        $sharedEventManager->attach(__NAMESPACE__, 'route', 
                                    [$this, 'onRoute'], 100);        
    }

    public function onRoute(MvcEvent $event)
    {
        if (php_sapi_name() == "cli") {
            // Не выполняем перенаправление на HTTPS в консольном режиме.
            return;
        }
        
        // Получаем URI запроса
        $uri = $event->getRequest()->getUri();
        $scheme = $uri->getScheme();
   var_dump($uri);
        // Если схема - не HTTPS, перенаправляем на тот же URI, но
        // со схемой HTTPS.
//        if ($scheme != 'https'){
//            $uri->setScheme('https');
//            $response=$event->getResponse();
//            $response->getHeaders()->addHeaderLine('Location', $uri);
//            $response->setStatusCode(301);
//            $response->sendHeaders();
//            return $response;
//        }
    }    
        
}
