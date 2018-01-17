<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;

class Module
{
    const VERSION = '3.0.3-dev';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
    /**
     * Метод обработчика событий для события 'Dispatch'. Мы обрабатываем событие Dispatch
     * и вызываем фильтр доступа. Фильтр доступа позволяет определить, разрешено ли текущему
     * посетителю просматривать страницу или нет. Если он не авторизован и доступ к странице
     * для него запрещен, мы перенаправляем такого пользователя на страницу входа на сайт. 
     */
    public function onDispatch(MvcEvent $event)
    {
        // Получаем контроллер и действие, к которому был отправлен HTTP-запрос.
        $controller = $event->getTarget();
        $controllerName = $event->getRouteMatch()->getParam('controller', null);
        $actionName = $event->getRouteMatch()->getParam('action', null);
        
        // Конвертируем написанное через дефис имя действия в верблюжий регистр.
        $actionName = str_replace('-', '', lcfirst(ucwords($actionName, '-')));
        
        // Получаем экземпляр сервиса AuthManager.
        $authManager = $event->getApplication()->getServiceManager()->get(AuthManager::class);
        
        // Применяем фильтр доступа к каждому контроллеру кроме AuthController
        // (во избежание бесконечного перенаправления).
        if ($controllerName!=AuthController::class)
        {
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
        
    }    
}
