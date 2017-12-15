<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;
//use ZfcRbac\View\Strategy\RedirectStrategy;

class Module
{
    const VERSION = '3.0.3-dev';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
    /**
     * Этот метод вызывается по завершении самозагрузки MVC. 
     */
    public function onBootstrap(MvcEvent $event)
    {
        $application = $event->getApplication();
        $serviceManager = $application->getServiceManager();
        $eventManager = $application->getEventManager();
        
        
        // Следующая строка инстанцирует SessionManager и автоматически
        // делает его выбираемым 'по умолчанию'.
        $sessionManager = $serviceManager->get(SessionManager::class);
        
//        rbac
//        $listener = $serviceManager->get(RedirectStrategy::class);
//        $listener->attach($eventManager);    
        
//        $t = $$event->getTarget();
//
//        $t->getEventManager()->attach(
//            $t->getServiceManager()->get('UserRbac\View\Strategy\SmartRedirectStrategy')
//        );        
    }    
}
