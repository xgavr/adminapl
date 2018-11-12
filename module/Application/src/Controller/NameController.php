<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\Rawprice;
use Application\Entity\UnknownProducer;
use Application\Entity\Article;
use Application\Entity\NameRaw;
use Zend\View\Model\JsonModel;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class NameController extends AbstractActionController
{
   
    /**
    * Менеджер сущностей.
    * @var Doctrine\ORM\EntityManager
    */
    private $entityManager;
    
    /**
     * Менеджер производителей.
     * @var Application\Service\ProducerManager 
     */
    private $producerManager;    
    
    /**
     * Менеджер артикулов производителей.
     * @var Application\Service\ArticleManager 
     */
    private $articleManager;    
    
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
        $bind = $this->entityManager->getRepository(Rawprice::class)
                ->count(['status' => Rawprice::STATUS_PARSED, 'statusOem' => Rawprice::OEM_PARSED]);
        $noBind = $this->entityManager->getRepository(Rawprice::class)
                ->count(['status' => Rawprice::STATUS_PARSED, 'statusOem' => Rawprice::OEM_NEW]);
        $total = $this->entityManager->getRepository(OemRaw::class)
                ->count([]);
                
        return new ViewModel([
            'bind' => $bind,
            'noBind' => $noBind,
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
}
