<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

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
        $result = 'ok index';
        if ($this->getRequest()->isPost()) {
            
            $post = $this->params()->fromPost();
            $result = $this->soapManager->transapl('index', $post);            
        }
        echo $result;
        exit;
    }    

    public function wsdlAction()
    {
        $result = 'ok wsdl';
        if ($this->getRequest()->isPost()) {
            
            $post = $this->params()->fromPost();
            $result = $this->soapManager->transapl('wsdl', $post);            
        }
        echo $result;
        exit;
    }    
}
