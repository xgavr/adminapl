<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Bankapi\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{

    /**
     * TochkaApi manager.
     * @var Bankapi\Service\TochkaApi
     */
    private $tochkaApi;    

    public function __construct($tochkaApi) 
    {
        $this->tochkaApi = $tochkaApi;        
    }   
    
    public function indexAction()
    {
        return [];
    }
    
    public function tochkaAuthAction()
    {
        $url = $this->tochkaApi->authUrl();
        $this->redirect()->toUrl($url);
    }
    
    public function tochkaAccessAction()
    {
        $code = $this->params()->fromQuery('code', 'test');
        if ($code){
            $result = $this->tochkaApi->accessToken($code, 'authorization_code');
        }    
        
        echo 'ok';
        
        exit;
    }
    
    public function tochkaIsAuthAction()
    {
        $result = $this->tochkaApi->isAuth();
        
        echo $result;
        
        exit;
    }
    
    public function tochkaAccountListAction()
    {
        $result = $this->tochkaApi->accountList();
        var_dump($result);
        
        echo 'ok';
        
        exit;
    }
    
    public function tochkaStatementsAction()
    {
        $result = $this->tochkaApi->statements();
        var_dump($result);
        
        echo 'ok';
        
        exit;
    }    
}
