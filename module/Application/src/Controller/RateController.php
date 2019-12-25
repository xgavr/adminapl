<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Entity\Scale;
use Application\Entity\Rate;


class RateController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Rate manager.
     * @var \Application\Srvice\RateManager
     */
    private $rateManager;
    
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $rateManager) 
    {
       $this->entityManager = $entityManager;
       $this->rateManager = $rateManager;
    }

    
    public function indexAction()
    {
        $scales = $this->entityManager->getRepository(Scale::class)
                ->findBy([], []);
        
        $rates = $this->entityManager->getRepository(Rate::class)
                ->findBy([], []);
        
        return new ViewModel([
            'scales' => $scales,
            'rates' => $rates,
        ]);
    }
    
    public function addAction()
    {
        $name = $this->params()->fromQuery('prompt');
                
        $this->rateManager->addRate(['name' => $name]);
        
        return new JsonModel([
            'ok',
        ]);                  
    }

    public function viewAction()
    {
        return new ViewModel([
        ]);        
    }
}
