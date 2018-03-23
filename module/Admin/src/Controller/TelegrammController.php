<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TelegrammController extends AbstractActionController
{
    
    /**
     * TelegrammManager manager.
     * @var Admin\Service\TelegrammManager
     */
    private $telegrammManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($telegrammManager) 
    {
        $this->telegrammManager = $telegrammManager;        
    }   
    
    public function indexAction()
    {
        return [];
    }
    
    /*
     * Telegramm hook
     */
    public function hookAction()
    {
        $this->telegrammManager->hook();
        exit;        
    }
    
    public function setAction()
    {
        $this->telegrammManager->setHook();
        exit;
    }
    
    public function unsetAction()
    {
        $this->telegrammManager->unsetHook();
        exit;
    }    
}
