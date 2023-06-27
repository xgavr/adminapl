<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Bankapi\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Json\Decoder;

class IndexController extends AbstractActionController
{

    /**
     * Authentication manager.
     * @var \Bankapi\Service\Tochka\Authenticate
     */
    private $tochkaAuth;    

    /**
     * Statement manager.
     * @var \Bankapi\Service\Tochka\Statement
     */
    private $tochkaStatement;    

    /**
     * Sbp manager.
     * @var \Bankapi\Service\Tochka\SbpManager
     */
    private $sbpManager;    

    public function __construct($tochkaAuth, $tochkaStatement, $sbpManager) 
    {
        $this->tochkaAuth = $tochkaAuth;
        $this->tochkaStatement = $tochkaStatement;        
        $this->sbpManager = $sbpManager;        
    }   
    
    public function indexAction()
    {
        return [];
    }
    
    public function tochkaAuthAction()
    {
        $this->tochkaAuth->reAuth();
        $url = $this->tochkaAuth->authUrl();
        $this->redirect()->toUrl($url);
    }
    
    public function tochkaAccessAction()
    {
        $code = $this->params()->fromQuery('code');
        $error = $this->params()->fromQuery('error');

        if ($code){
            try{
                $this->tochkaAuth->accessToken($code, $this->tochkaAuth::TOKEN_AUTH);
            } catch(\Exception $e){
            }    
            return $this->redirect()->toRoute('bankapi', ['action'=>'tochka-access']);
        }
        
        try{
            $ok = $this->tochkaAuth->isAuth();
        } catch (\Exception $e){
            $ok = false;
        }    
        
        return new ViewModel([
                'ok' => $ok,
                'error' => $error,
            ]);
    }
    
    public function tochkaIsAuthAction()
    {
        $result = $this->tochkaAuth->isAuth();
        
        echo $result;
        
        exit;
    }
    
    public function tochkaAccountListAction()
    {
        try {
            $result = $this->tochkaStatement->accountList();
        } catch (\Exception $e){
            return $this->redirect()->toRoute('bankapi', ['action'=>'tochka-access'], ['query' => ['error' => $e->getMessage()]]);                
        }   
        //\Zend\Debug\Debug::dump($result);
        
        return new ViewModel([
                'result' => $result,
                'mode' => $this->tochkaAuth->getMode(),
            ]);
    }
    
    public function tochkaStatementsAction()
    {
        try {
            $result = $this->tochkaStatement->statements();
        } catch (\Exception $e){
            return $this->redirect()->toRoute('bankapi', ['action'=>'tochka-access'], ['query' => ['error' => $e->getMessage()]]);                
        }   
        //\Zend\Debug\Debug::dump($result);
        return new ViewModel([
                'result' => $result,
            ]);
    }    
    
    public function registerQrCodeAction()
    {
        $account = '40817810802000000008/044525999';
        $merchant_id = 'MF0000000001';
        $data = [
            'Data' => [
                "amount" => 0,
                "currency" => "RUB",
                "paymentPurpose" => "?",
                "qrcType" => "01",
                "imageParams" => [
                    "width" => 200,
                    "height" => 200,
                    "mediaType" => "image/png"
                ],
                "sourceName" => "string",  
                "ttl" => 0  
            ],
        ];
        $result = $this->sbpManager->registerQrCode($account, $merchant_id, $data);
        $this->layout()->setTemplate('layout/terminal');
        return new ViewModel([
                'result' => $result,
            ]);
    }
}
