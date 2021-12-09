<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ApiMarketPlace\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;


class IndexController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    /**
     * Request manager.
     * @var \ApiMarketPlace\Service\SberMarket
     */
    private $sbermarketManager;
        
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $sbermarketManager) 
    {
       $this->entityManager = $entityManager;
       $this->sbermarketManager = $sbermarketManager;
    }

    
    public function indexAction()
    {
        return new ViewModel();
    }

    public function sbermarketOrderNewAction()
    {
        $this->sbermarketManager->handle();
        //{"success":1,"meta":{"source":"merchant_name"}}
        return new JsonModel([
            'success' => 1,
            'meta' => [
                'source' => 'APL',
            ],
        ]);
    }
    
    public function sbermarketOrderCancelAction()
    {
        $this->sbermarketManager->handle();
        return new JsonModel([
            'success' => 1,
            'meta' => [
                'source' => 'APL',
            ],
        ]);        
    }
    
}
