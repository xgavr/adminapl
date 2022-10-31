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
        $this->layout()->setTemplate('layout/terminal');
        $view = new ViewModel([
        ]);
        $view->setTerminal(true);

        $server = new \Laminas\Soap\Server(null, [
            'uri' => 'https://autopartslist.ru/soap/index/',
        ]);
        $server->setClass('App_Soap_Manager');
        
        $server->handle(); 
    }    

    public function wsdlAction()
    {
//        $this->layout()->setTemplate('layout/terminal');
//        $view = new ViewModel([
//        ]);        
//        $view->setTerminal(true);
        
        $autodiscover = new \Laminas\Soap\AutoDiscover();
//        $autodiscover->setOperationBodyStyle(['use' => 'literal']);
        $autodiscover->setClass('App_Soap_Manager');
        $autodiscover->setUri('https://autopartslist.ru/soap/index/');
        
        header('Content-Type: application/wsdl+xml');
        echo $autodiscover->toXml();
        exit;        
    }    
}
