<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class SoapController extends AbstractActionController
{
    
    /**
     * SoapManager manager.
     * @var \Admin\Service\SoapManager
     */
    private $soapManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($soapManager) 
    {
        $this->soapManager = $soapManager;        
    }   
    
    public function indexAction()
    {
        $post = null;
        if ($this->getRequest()->isPost()) {
            $post = $this->params()->fromPost();
        }    
        $data = $this->soapManager->transapl('https://autopartslist.ru/soap/index/', $post);
        header('Content-Type: application/wsdl+xml');
        echo $data;
        exit;
    }    

    public function wsdlAction()
    {
        $post = null;
        if ($this->getRequest()->isPost()) {
            $post = $this->params()->fromPost();
        }    
        $data = $this->soapManager->transapl('https://autopartslist.ru/soap/wsdl/', $post);
        header('Content-Type: application/wsdl+xml');
        echo $data;
        exit;
    }    
}
