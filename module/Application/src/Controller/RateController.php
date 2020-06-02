<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Entity\Scale;
use Application\Entity\ScaleTreshold;
use Application\Entity\Rate;
use Application\Entity\Supplier;
use Application\Entity\GenericGroup;
use Application\Entity\TokenGroup;
use Application\Entity\Producer;
use Application\Entity\Goods;


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
        
        $rates = $this->entityManager->getRepository(Rate::class)
                ->findBy([], []);
        
        return new ViewModel([
            'rates' => $rates,
        ]);
    }
    
    public function addAction()
    {
        $name = $this->params()->fromQuery('prompt');
        $supplierId = $this->params()->fromQuery('supplier');
        $genericGroupId = $this->params()->fromQuery('genericGroup');
        $tokenGroupId = $this->params()->fromQuery('tokenGroup');
        $producerId = $this->params()->fromQuery('producer');
        
        $producer = $supplier = $genericGroup = $tokenGroup = null;
        if ($supplierId){
            $supplier = $this->entityManager->getRepository(Supplier::class)
                    ->findOneById($supplierId);
        }
        if ($genericGroupId){
            $genericGroup = $this->entityManager->getRepository(GenericGroup::class)
                    ->findOneById($genericGroupId);
        }
        if ($tokenGroupId){
            $tokenGroup = $this->entityManager->getRepository(TokenGroup::class)
                    ->findOneById($tokenGroupId);
        }
        if ($producerId){
            $producer = $this->entityManager->getRepository(Producer::class)
                    ->findOneById($producerId);
        }
                
        $this->rateManager->addRate([
            'name' => $name,
            'producer' => $producer,
            'genericGroup' => $genericGroup,
            'tokenGroup' => $tokenGroup,
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
        
        $params = [];
        if ($rate->getSupplier()){
            $params['supplier'] = $rate->getSupplier()->getId();
        }
        if ($rate->getGenericGroup()){
            $params['genericGroup'] = $rate->getGenericGroup()->getId();
        }
        if ($rate->getTokenGroup()){
            $params['tokenGroup'] = $rate->getTokenGroup()->getId();
        }
        if ($rate->getProducer()){
            $params['producer'] = $rate->getProducer()->getId();
        }
        
        return new ViewModel([
            'rate' => $rate,
        ]);        
    }

    public function updateRateScaleAction()
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
        
        $this->rateManager->updateRateScale($rate);
        
        $this->redirect()->toRoute('rate', ['action' => 'view', 'id' => $rate->getId()]);        
    }
    
    public function changeRateScaleAction()
    {
        $rateId = $this->params()->fromRoute('id', -1);
        $change = $this->params()->fromQuery('plus', 0);
        
        if ($rateId > 0) {
            $rate = $this->entityManager->getRepository(Rate::class)
                    ->findOneById($rateId);
            if ($rate == null) {
                $this->getResponse()->setStatusCode(404);
                return;                        
            }        
        }
        
        $this->rateManager->changeRateScale($rate, $change);
        
        $this->redirect()->toRoute('rate', ['action' => 'view', 'id' => $rate->getId()]);        
    }
    
    public function updateRateStatusAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $rateId = $data['pk'];
            $rate = $this->entityManager->getRepository(Rate::class)
                    ->findOneById($rateId);
//            var_dump($data); exit;
            $status = ($data['value'] == 'true') ? Rate::STATUS_ACTIVE:Rate::STATUS_RETIRED;
                    
            if ($rate){
                $this->rateManager->updateRateStatus($rate, $status);                    
            }    
        }
        
        exit;
    }

    public function updateRateNameAction()
    {
        $rateId = $this->params()->fromRoute('id', -1);
        $name = $this->params()->fromQuery('prompt');
        
        if ($rateId > 0) {
            $rate = $this->entityManager->getRepository(Rate::class)
                    ->findOneById($rateId);
            if ($rate == null) {
                $this->getResponse()->setStatusCode(404);
                return;                        
            }        
        }
        
        $this->rateManager->updateRateName($rate, $name);
        
        return new JsonModel([
            'ok',
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
    
    public function fixPriceAction()
    {
        return new ViewModel([
        ]);
        
    }

    public function fixPriceContentAction()
    {
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        
        $query = $this->entityManager->getRepository(Rate::class)
                        ->findFixPrice();
        
        $total = count($query->getResult(2));
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);          
        
    }
}
