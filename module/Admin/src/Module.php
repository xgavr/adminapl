<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin;

use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Admin\Service\SettingManager;
use Admin\Controller\ProcessingController;

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
        
//        $sharedEventManager->attach(AbstractActionController::class, 
//                MvcEvent::EVENT_DISPATCH, [$this, 'onDispatch'], 100);        

//        $sharedEventManager->attach(__NAMESPACE__, 
//                MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'onFinish'], 100);
//
//        $sharedEventManager->attach(__NAMESPACE__, 
//                MvcEvent::EVENT_RENDER_ERROR, [$this, 'onFinish'], 100);

        $sharedEventManager->attach('Zend\Mvc\Application', 
                MvcEvent::EVENT_FINISH, [$this, 'onFinish'], 100);
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
        // Если схема - не HTTPS, перенаправляем на тот же URI, но
        // со схемой HTTPS.
        if ($scheme != 'https'){
            $uri->setScheme('https');
            $response=$event->getResponse();
            $response->getHeaders()->addHeaderLine('Location', $uri);
            $response->setStatusCode(301);
            $response->sendHeaders();
            return $response;
        }
    }    

    public function onDispatch(MvcEvent $event)
    {
        // Get controller and action to which the HTTP request was dispatched.
        $controller = $event->getTarget();
        $controllerName = $event->getRouteMatch()->getParam('controller', null);
        $actionName = $event->getRouteMatch()->getParam('action', null);
        
        // Convert dash-style action name to camel-case.
        $actionName = str_replace('-', '', lcfirst(ucwords($actionName, '-')));
        
        if ($controllerName == ProcessingController::class) {
            $settingManager = $event->getApplication()->getServiceManager()->get(SettingManager::class);
            if ($settingManager->canStart($controllerName, $actionName)){
                $settingManager->addProcess($controllerName, $actionName);
            } else {
//                return $controller->redirect()->toRoute('home');
//                throw new \Exception('Процесс запущен!');
                exit;
            }    
        }
        
        return;
    }    
    
    public function onFinish(MvcEvent $event)
    {
        // Get controller and action to which the HTTP request was dispatched.
        if ($event->getRouteMatch()){
            $controllerName = $event->getRouteMatch()->getParam('controller', null);
            $actionName = $event->getRouteMatch()->getParam('action', null);

            // Convert dash-style action name to camel-case.
            $actionName = str_replace('-', '', lcfirst(ucwords($actionName, '-')));

            if ($controllerName == ProcessingController::class) {
                $settingManager = $event->getApplication()->getServiceManager()->get(SettingManager::class);
                $settingManager->removeProcess($controllerName, $actionName);
            }
        }    
        
        return;
    }        
}
