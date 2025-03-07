<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Application\Entity\Article;
use Application\Entity\OemRaw;
use Application\Entity\Oem;
use Application\Entity\Goods;
use Application\Form\OemForm;
use Laminas\View\Model\JsonModel;
use Application\Entity\Supplier;


class OemController extends AbstractActionController
{
   
    /**
    * Менеджер сущностей.
    * @var \Doctrine\ORM\EntityManager
    */
    private $entityManager;
    
    /**
     * Менеджер производителей.
     * @var \Application\Service\ProducerManager 
     */
    private $producerManager;    
    
    /**
     * Менеджер артикулов производителей.
     * @var \Application\Service\ArticleManager 
     */
    private $articleManager;    
    
    /**
     * Менеджер oem.
     * @var \Application\Service\OemManager 
     */
    private $oemManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $producerManager, $articleManager, $oemManager) 
    {
        $this->entityManager = $entityManager;
        $this->producerManager = $producerManager;
        $this->articleManager = $articleManager;
        $this->oemManager = $oemManager;
    }    
    
    public function indexAction()
    {
        $stages = $this->entityManager->getRepository(Article::class)
                ->findParseStageRawpriceCount(\Application\Entity\Raw::STAGE_OEM_PARSED);
        $total = $this->entityManager->getRepository(OemRaw::class)
                ->count([]);
                
        return new ViewModel([
            'stages' => $stages,
            'total' => $total,
        ]);  
    }
    
    public function contentAction()
    {
        ini_set('memory_limit', '512M');
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        
        $query = $this->entityManager->getRepository(OemRaw::class)
                        ->findAllOemRaw(['q' => $q]);

        $total = count($query->getResult(2));
        
        if ($offset) $query->setFirstResult( $offset );
        if ($limit) $query->setMaxResults( $limit );

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);          
    }    
    
    public function viewAction() 
    {       
        $oemId = (int)$this->params()->fromRoute('id', -1);

        if ($oemId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $oem = $this->entityManager->getRepository(OemRaw::class)
                ->findOneById($oemId);
        
        if ($oem == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $rawpriceCountBySupplier = $this->entityManager->getRepository(OemRaw::class)
                ->rawpriceCountBySupplier($oem);
        
        $prevQuery = $this->entityManager->getRepository(OemRaw::class)
                        ->findAllOemRaw(['prev1' => $oem->getCode()]);
        $nextQuery = $this->entityManager->getRepository(OemRaw::class)
                        ->findAllOemRaw(['next1' => $oem->getCode()]);        

        // Render the view template.
        return new ViewModel([
            'oem' => $oem,
            'rawpriceCountBySupplier' => $rawpriceCountBySupplier,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
            'oemManager' => $this->oemManager,
        ]);
    }
    
    public function viewOnCodeAction() 
    {       
        $oemCode = $this->params()->fromQuery('code');

        if (!$oemCode) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        $filter = new \Application\Filter\ArticleCode();

        $oem = $this->entityManager->getRepository(OemRaw::class)
                ->findOneByCode($filter->filter($oemCode));
        
        if ($oem == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }   
        
        $this->redirect()->toUrl('/oem/view/'.$oem->getId());
    }
    
    public function parseAction()
    {
        $rawpriceId = (int)$this->params()->fromRoute('id', -1);
        if ($rawpriceId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $rawprice = $this->entityManager->getRepository(\Application\Entity\Rawprice::class)
                ->findOneById($rawpriceId);
        
        if ($rawprice == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->oemManager->addNewOemRawFromRawprice($rawprice);
        
        return new JsonModel([
            'ok',
        ]);          
    }
    
    public function updateOemFromRawAction()
    {
        set_time_limit(0);
        $rawId = $this->params()->fromRoute('id', -1);

        if ($rawId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                ->findOneById($rawId);

        if ($raw == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->oemManager->grabOemFromRaw($raw);
                
        return new JsonModel([
            'ok',
        ]);          
    }
    
    public function deleteEmptyAction()
    {
        $deleted = $this->oemManager->removeEmpty();
                
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => $deleted.' удалено!',
        ]);          
    }    
    
    public function oemAction()
    {
//        $total = $this->entityManager->getRepository(Oem::class)
//                ->count([]);
                
        return new ViewModel([
//            'total' => $total,
            'oemStatuses' => Oem::getStatusList(),
            'oemSources' => Oem::getSourceList(),
        ]);  
    }
    
    public function oemContentAction()
    {
        ini_set('memory_limit', '512M');
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $search = $this->params()->fromQuery('search');
        $source = $this->params()->fromQuery('source');
        
        $query = $this->entityManager->getRepository(Oem::class)
                        ->findAllOem(['q' => $search, 'mycode' => false, 'source' => $source]);

        $total = count($query->getResult());
        
        if ($offset) $query->setFirstResult( $offset );
        if ($limit) $query->setMaxResults( $limit );

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);          
    }    
    
    public function displayGoodAction()
    {
        $displayBilemma = $id = null;
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
        
            $goodId = $data['goodId'];
            
            $good = $this->entityManager->getRepository(Goods::class)
                    ->findOneById($goodId);            
        }

        return new JsonModel([
            'code' => $good->getCode(),
        ]);          
    }    
    
    public function oemFormAction()
    {
        $goodId = (int)$this->params()->fromRoute('id', -1);
        $oemId = $this->params()->fromQuery('oem');
        
        if ($goodId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $good = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodId);
        
        if ($good == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $oem = null;
        if ($oemId){
            $oem = $this->entityManager->getRepository(Oem::class)
                    ->findOneById($oemId);
        }

        $form = new OemForm();
        $this->layout()->setTemplate('layout/terminal');
        
        $suppliersList = ['нет'];
        $suppliers = $this->entityManager->getRepository(Supplier::class)
                ->findBy([]);
        foreach($suppliers as $supplier){
            $suppliersList[$supplier->getId()] = $supplier->getName(); 
        }
        $form->get('supplier')->setValueOptions($suppliersList);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {

                if ($oem){
                    $data['status'] = $oem->getStatus();
                    $this->oemManager->updateOem($oem, $data);                    
                } else {
                    $this->oemManager->addOem($good, $data);
                }    
                        
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($oem){
                $form->setData([
                    'oeNumber' => $oem->getOeNumber(),
                    'brandName' => $oem->getBrandName(),
                ]);
            }    
        }    
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'good' => $good,
            'oem' => $oem,
        ]);                
        
    }
    
    public function oemStatusFormAction()
    {
        $oemId = $this->params()->fromRoute('id', -1);
        $status = $this->params()->fromQuery('status', Oem::STATUS_ACTIVE);
        
        $oem = $this->entityManager->getRepository(Oem::class)
                ->findOneById($oemId); 
        
        if ($oem == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->oemManager->updateOem($oem, ['oeNumber' => $oem->getOeNumber(), 'brandName' => $oem->getBrandName(),
            'status' => $status, 'source' => $oem->getSource()]);
        
//        $query = $this->entityManager->getRepository(Goods::class)
//                        ->findOems($oem->getGood()->getId(), ['id' => $oem->getId()]);
//        $result = $query->getOneOrNullResult(2);
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'ok'
//            'id' => $oem->getId(),
//            'row' => $result,
        ]);           
    }    
    
}
