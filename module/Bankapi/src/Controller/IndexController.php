<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Bankapi\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

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
        $code = $this->params()->fromQuery('code');

        if ($code){
            try{
                $this->tochkaApi->accessToken($code, $this->tochkaApi::TOKEN_AUTH);
            } catch(\Exception $e){
            }    
            return $this->redirect()->toRoute('bankapi', ['action'=>'tochka-access']);
        }
        
        try{
            $ok = $this->tochkaApi->isAuth();
        } catch (\Exception $e){
            $ok = false;
        }    
        
        return new ViewModel([
                'ok' => $ok,
            ]);
    }
    
    public function tochkaIsAuthAction()
    {
        $result = $this->tochkaApi->isAuth();
        
        echo $result;
        
        exit;
    }
    
    public function tochkaAccountListAction()
    {
        try {
            $result = $this->tochkaApi->accountList();
        } catch (\Exception $e){
            return $this->redirect()->toRoute('bankapi', ['action'=>'tochka-access']);                
        }   
        
        return new ViewModel([
                'result' => $result,
            ]);
    }
    
    public function tochkaStatementsAction()
    {
        try {
            $result = $this->tochkaApi->statements();
        } catch (\Exception $e){
            return $this->redirect()->toRoute('bankapi', ['action'=>'tochka-access']);                
        }   
//        \Zend\Debug\Debug::dump($result);
        return new ViewModel([
                'result' => $result,
            ]);
    }    
}
