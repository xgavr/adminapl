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
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $contactManager) 
    {
       $this->entityManager = $entityManager;
       $this->contactManager = $contactManager;
    }

    
    public function indexAction()
    {
        return new ViewModel();
    }

    public function orderNewAction()
    {
        
    }
    
    public function orderCancelAction()
    {
        
    }
    
}
