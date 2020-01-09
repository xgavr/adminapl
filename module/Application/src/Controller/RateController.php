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
use Application\Entity\ScaleTreshold;
use Application\Entity\Rate;
use Application\Entity\Supplier;
use Application\Entity\GenericGroup;
use Application\Entity\Producer;


class RateController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Rate manager.
     * @var \Application\Service\RateManager
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
        $supplierId = $this->params()->fromQuery('supplier');
        $genericGroupId = $this->params()->fromQuery('genericGroup');
        $producerId = $this->params()->fromQuery('producer');
        
        $producer = $supplier = $genericGroup = null;
        if ($supplierId){
            $supplier = $this->entityManager->getRepository(Supplier::class)
                    ->findOneById($supplierId);
        }
        if ($genericGroupId){
            $genericGroup = $this->entityManager->getRepository(GenericGroup::class)
                    ->findOneById($genericGroupId);
        }
        if ($producerId){
            $producer = $this->entityManager->getRepository(Producer::class)
                    ->findOneById($producerId);
        }
                
        $this->rateManager->addRate([
            'name' => $name,
            'producer' => $producer,
            'genericGroup' => $genericGroup,
            'supplier' => $supplier,
        ]);
        
        return new JsonModel([
            'ok',
        ]);                  
    }

    public function viewAction()
    {
        $rateId = $this->params()->fromRoute('id', -1);
        
        if ($rateId > 0) {
            $rate = $this->entityManager->getRepository(Rate::class)
                    ->findOneById($rateId);
            if ($rate == null) {
                $this->getResponse()->setStatusCode(404);
                return;                        
            }        
        }

        return new ViewModel([
            'rate' => $rate,
        ]);        
    }

    public function deleteAction()
    {
        $rateId = $this->params()->fromRoute('id', -1);
        
        if ($rateId > 0) {
            $rate = $this->entityManager->getRepository(Rate::class)
                    ->findOneById($rateId);
            if ($rate == null) {
                $this->getResponse()->setStatusCode(404);
                return;                        
            }        
        }
        
        $this->rateManager->removeRate($rate);
        
        $this->redirect()->toRoute('rate');
    }

    public function viewScaleAction()
    {
        $scaleId = $this->params()->fromRoute('id', -1);
        
        if ($scaleId > 0) {
            $scale = $this->entityManager->getRepository(Scale::class)
                    ->findOneById($scaleId);
            if ($scale == null) {
                $this->getResponse()->setStatusCode(404);
                return;                        
            }        
        }
        
        return new ViewModel([
            'scale' => $scale,
        ]);        
    }

    public function deleteScaleAction()
    {
        $scaleId = $this->params()->fromRoute('id', -1);
        
        if ($scaleId > 0) {
            $scale = $this->entityManager->getRepository(Scale::class)
                    ->findOneById($scaleId);
            if ($scale == null) {
                $this->getResponse()->setStatusCode(404);
                return;                        
            }        
        }
        
        $this->rateManager->removeScale($scale);
        
        $this->redirect()->toRoute('rate');
    }
}
