<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

class TelegrammController extends AbstractActionController
{
    
    /**
     * TelegrammManager manager.
     * @var \Admin\Service\TelegrammManager
     */
    private $telegramManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($telegramManager) 
    {
        $this->telegramManager = $telegramManager;        
    }   
    
    public function indexAction()
    {
        return [];
    }
    
    /**
     * Telegramm hook
     */
    public function hookAction()
    {
        $this->telegramManager->hook();
        exit;        
    }
    
    public function postponeAction()
    {
        $this->telegramManager->sendPostponeMessage();
        exit;                
    }
    
    public function setAction()
    {
        $this->telegramManager->setHook();
        exit;
    }
    
    public function unsetAction()
    {
        $this->telegramManager->unsetHook();
        exit;
    }    
    
    public function checkProxyAction()
    {
        $result = $this->telegramManager->checkEndChangeProxy();
        
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => $result,
        ]);   
        
    }
}
