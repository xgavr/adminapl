<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Application\Entity\Goods;
use Application\Entity\Cart;
use Application\Entity\Client;
use Laminas\View\Model\JsonModel;

class SapiController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * AdminManager.
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;    
    
    /**
     * Менеджер товаров.
     * @var \Application\Service\SupplierApi\AutoEuroManager 
     */
    private $autoEuroManager;          
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $adminManager, $autoEuroManager) 
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->autoEuroManager = $autoEuroManager;
    }    
    
    
    protected function aplApiKey()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        return md5(date('Y-m-d').'#'.$settings['apl_secret_key']);
    }
        
    public function indexAction()
    {
        return new ViewModel([
        ]);
    }
    
    public function autoEuroTestAction()
    {
        $data = $this->autoEuroManager->stockItems('1417901100');
        
        return new JsonModel(
            $data
        );        
    }
            
    /*
     * АвтоЕвро
     * $post api_key, action, params
     */
    public function autoEuroApiAction()
    {
        $data = [];
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            if (isset($data['api_key'])){
                if ($data['api_key'] == $this->aplApiKey()){
                    $params = $data['params'];
                    switch ($data['action']){
                        case 'stock_items': $data = $this->autoEuroManager->stockItems($params['art']); break;
                        default: break;
                    }
                }    
            }
        }    
        
        return new JsonModel(
            $data
        );
    }
    
}
