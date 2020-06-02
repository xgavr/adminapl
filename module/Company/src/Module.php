<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Company;

use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\MvcEvent;

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
        $sharedEventManager->attach(__NAMESPACE__, 'dispatch', 
                                    [$this, 'onDispatch'], 100);
    }

    // Обработчик события.
    public function onDispatch(MvcEvent $event)
    {
        // Получаем контроллер, к которому был отправлен HTTP-запрос.
        $controller = $event->getTarget();
        // Получаем полностью определенное имя класса контроллера.
        $controllerClass = get_class($controller);
        // Получаем имя модуля контроллера.
        $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
           
        // Переключаем лэйаут только для контроллеров, принадлежащих нашему модулю.
        if ($moduleNamespace == __NAMESPACE__) {
            $viewModel = $event->getViewModel();
//            $viewModel->setTemplate('layout/layout2');  
        }        
    }
    
}
