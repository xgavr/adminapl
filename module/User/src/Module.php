<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace User;

use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\AbstractActionController;
use User\Controller\AuthController;
use User\Service\AuthManager;
use Zend\Session\SessionManager;

class Module
{
    const VERSION = '0.0.1-dev';
    
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
    /**
     * This method is called once the MVC bootstrapping is complete and allows
     * to register event listeners. 
     */
    public function onBootstrap(MvcEvent $event)
    {
        
        // Get event manager.
        $eventManager = $event->getApplication()->getEventManager();
        $sharedEventManager = $eventManager->getSharedManager();
        // Register the event listener method. 
        $sharedEventManager->attach(AbstractActionController::class, 
                MvcEvent::EVENT_DISPATCH, [$this, 'onDispatch'], 100);
        
        
        $sessionManager =
            $event->getApplication()->getServiceManager()
              ->get(SessionManager::class);

        try {
            $sessionManager->start();
            return;
        } catch (\Exception $e) {
            session_unset();
        }        

    }


    /**
     * Event listener method for the 'Dispatch' event. We listen to the Dispatch
     * event to call the access filter. The access filter allows to determine if
     * the current visitor is allowed to see the page or not. If he/she
     * is not authorized and is not allowed to see the page, we redirect the user 
     * to the login page.
     */
    public function onDispatch(MvcEvent $event)
    {
        // Get controller and action to which the HTTP request was dispatched.
        $controller = $event->getTarget();
        $controllerName = $event->getRouteMatch()->getParam('controller', null);
        $actionName = $event->getRouteMatch()->getParam('action', null);
        
        // Convert dash-style action name to camel-case.
        $actionName = str_replace('-', '', lcfirst(ucwords($actionName, '-')));
        
        // Get the instance of AuthManager service.
        $authManager = $event->getApplication()->getServiceManager()->get(AuthManager::class);
        
        // Execute the access filter on every controller except AuthController
        // (to avoid infinite redirect).
        if ($controllerName!=AuthController::class) {
            
            $result = $authManager->filterAccess($controllerName, $actionName);
            
            if ($result==AuthManager::AUTH_REQUIRED) {
                // Запоминаем URL страницы, на которую пытался перейти пользователь. Мы
                // перенаправим пользователя на этот URL после его успешного входа на сайт.
                $uri = $event->getApplication()->getRequest()->getUri();
                // Делаем URL-адрес относительным (убираем схему, сведения о пользователе, имя хоста и порт),
                // чтобы избежать перенаправления на другой домен злоумышленниками.
                $uri->setScheme(null)
                    ->setHost(null)
                    ->setPort(null)
                    ->setUserInfo(null);
                $redirectUrl = $uri->toString();

                // Перенаправляем пользователя на страницу "Login".
                return $controller->redirect()->toRoute('login', [], 
                        ['query'=>['redirectUrl'=>$redirectUrl]]);
            }
            else if ($result==AuthManager::ACCESS_DENIED) {
                // Перенаправляем пользователя на страницу "Not Authorized".
                return $controller->redirect()->toRoute('not-authorized');
            }

        }
    }    
}
