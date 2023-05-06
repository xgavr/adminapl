<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Stock\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use ApiMarketPlace\Entity\Marketplace;
use ApiMarketPlace\Entity\MarketSaleReport;
use Stock\Entity\RegisterVariable;
use ApiMarketPlace\Entity\MarketSaleReportItem;

class ComitentController extends AbstractActionController
{

    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Менеджер отчетов.
     * @var \ApiMarketPlace\Service\ReportManager
     */
    private $reportManager;        

    public function __construct($entityManager, $reportManager) 
    {
        $this->entityManager = $entityManager;
        $this->reportManager = $reportManager;
    }   
    
    private function getAllowDate()
    {
        $var = $this->entityManager->getRepository(RegisterVariable::class)
                ->findOneBy([]);
        return $var->getAllowDate();
    }

    public function indexAction()
    {
        $marketplaces = $this->entityManager->getRepository(Marketplace::class)
                ->findBy([]);
        
        return new ViewModel([
            'marketplaces' => $marketplaces,
            'allowDate' => $this->getAllowDate(),
        ]);  
    }
    
    public function contentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        $marketplaceId = $this->params()->fromQuery('marketplace');
        $year_month = $this->params()->fromQuery('month');
        
        $year = $month = null;
        if ($year_month){
            $year = date('Y', strtotime($year_month));
            $month = date('m', strtotime($year_month));
        }
        
        $params = [
            'q' => $q, 'sort' => $sort, 'order' => $order, 
            'marketplaceId' => $marketplaceId,
            'year' => $year, 'month' => $month,
        ];
        $query = $this->entityManager->getRepository(MarketSaleReport::class)
                        ->queryAllReport($params);
        
        $total = $this->entityManager->getRepository(MarketSaleReport::class)
                        ->findAllReportTotal($params);
        
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
    
    public function editFormAction()
    {
        $mspId = (int)$this->params()->fromRoute('id', -1);
        
        if ($mspId > 0){
            $msp = $this->entityManager->getRepository(MarketSaleReport::class)
                    ->find($mspId);
        }    
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'marketSaleReport' => $msp,
        ]);        
    }    
    
    public function goodContentAction()
    {
        	        
        $mspId = $this->params()->fromRoute('id', -1);
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        
        $query = $this->entityManager->getRepository(MarketSaleReportItem::class)
                        ->findReportItems($mspId, ['q' => $q, 'sort' => $sort, 'order' => $order]);
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $total = count($query->getResult(2));
        
        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);          
    }        
    
    public function repostAction()
    {
        $mspId = $this->params()->fromRoute('id', -1);
        $msp = $this->entityManager->getRepository(MarketSaleReport::class)
                ->find($mspId);        

        if ($msp == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->reportManager->repostMarketSaleReport($msp);
        $query = $this->entityManager->getRepository(MarketSaleReport::class)
                ->queryAllReport(['marketSaleReportId' => $msp->getId()]);
        $result = $query->getOneOrNullResult(2);
        
        return new JsonModel(
           $result
        );           
    }        
    
    public function statusAction()
    {
        $mspId = $this->params()->fromRoute('id', -1);
        $status = $this->params()->fromQuery('status', MarketSaleReport::STATUS_ACTIVE);
        $msp = $this->entityManager->getRepository(MarketSaleReport::class)
                ->find($mspId);        

        if ($msp == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->reportManager->changeStatus($msp, $status);
        $query = $this->entityManager->getRepository(MarketSaleReport::class)
                ->queryAllReport(['marketSaleReportId' => $msp->getId()]);
        $result = $query->getOneOrNullResult(2);
        
        return new JsonModel(
           $result
        );           
    }            
    
}
