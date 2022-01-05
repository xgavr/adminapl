<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ApiSupplier\Controller;

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
     * @var \ApiSupplier\Service\MskManager
     */
    private $mskManager;
        
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $mskManager) 
    {
       $this->entityManager = $entityManager;
       $this->mskManager = $mskManager;
    }

    
    public function indexAction()
    {
        $marketplaces = $this->entityManager->getRepository(Marketplace::class)
                ->findAll();
        return new ViewModel([
            'marketplaces' =>  $marketplaces,
        ]);
    }

    public function editFormAction()
    {
        $marketplaceId = (int)$this->params()->fromRoute('id', -1);
        
        $marketplace = null;
        
        if ($marketplaceId > 0){
            $marketplace = $this->entityManager->getRepository(Marketplace::class)
                    ->find($marketplaceId);
        }    
        
        $form = new MarketplaceSetting($this->entityManager);
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();            
            $form->setData($data);

            if ($form->isValid()) {
                
                if ($marketplace){
                    $this->marketplaceService->update($marketplace, $data);
                } else {
                    $marketplace = $this->marketplaceService->add($data);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
                //var_dump($form->getMessages());
            }
        } else {
            if ($marketplace){
                $data = $marketplace->toArray();
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'marketplace' => $marketplace,
        ]);        
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

    public function yandexOrderAcceptAction()
    {
        $updId = $this->sbermarketManager->handle();
        return new JsonModel([
            'order' => [
                'accepted' => true,
                'id' => $updId,
                'reason' => '',
            ],
        ]);
    }
    
    
}
