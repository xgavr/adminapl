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
use Stock\Form\MsrGoodForm;
use Application\Entity\Goods;
use Stock\Form\MarketSaleReportForm;
use Admin\Filter\ArrayKeysCamelCase;

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
    
    /**
     * Дата запрета редактирования
     * @return date
     */    
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
    
    public function reportFormAction()
    {
        $reportId = (int)$this->params()->fromRoute('id', -1);
        
        $report = null;
                
        if ($reportId > 0){
            $report = $this->entityManager->getRepository(MarketSaleReport::class)
                    ->find($reportId);
        }    
        
        $form = new MarketSaleReportForm($this->entityManager);
        
        $marketplaceList = [];
        $marketplaces = $this->entityManager->getRepository(Marketplace::class)
                ->findBy([]);
        foreach ($marketplaces as $row){
            $marketplaceList[$row->getId()] = $row->getName();
        }
        $form->get('marketplace')->setValueOptions($marketplaceList);        
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                
                $marketplace = $this->entityManager->getRepository(Marketplace::class)
                        ->find($data['marketplace']);
                
                $filter = new ArrayKeysCamelCase();
                $filteredData = $filter->filter($data);
                
                $report = $this->reportManager->findReport($marketplace, $filteredData, MarketSaleReport::TYPE_COMPENSATION);
                $this->reportManager->clearReport($report);
                $this->reportManager->addReportItems($report, $filteredData['report_good']);
                $this->reportManager->repostMarketSaleReport($report);
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($report){
                $form->setData($report->toLog());
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'report' => $report,
        ]);        
    }        
    
    public function reportGoodEditFormAction()
    {        
        $params = $this->params()->fromQuery();
//        var_dump($params); exit;
        $good = $rowNo = $result = null;        
        if (isset($params['good'])){
            $good = $this->entityManager->getRepository(Goods::class)
                    ->find($params['good']['id']);            
        }
        if (isset($params['rowNumber'])){
            $rowNo = $params['rowNumber'];
        }
        
        $form = new MsrGoodForm($this->entityManager, $good);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);
            if (isset($data['good'])){
                $good = $this->entityManager->getRepository(Goods::class)
                        ->find($data['good']);            
            }

            if ($form->isValid()) {
                $result = 'ok';
                return new JsonModel([
                    'result' => $result,
                    'good' => [
                        'id' => $good->getId(),
                        'code' => $good->getCode(),
                        'aplId' => $good->getAplId(),
                        'name' => $good->getNameShort(),
                        'producer' => $good->getProducer()->getName(),
                    ],
                ]);        
            }
        } else {
            if ($good){
                $data = [
                    'good' => $good->getId(),
                    'aplId' => $good->getAplId(),
                    'code' => $good->getCode(),
                    'goodInputName' => $good->getInputName(),
                    'saleQty' => $params['saleQty'],
                    'returnQty' => $params['returnQty'],
                    'saleAmount' => $params['saleAmount'],
                    'priceSale' => $params['priceSale'],
                ];
                $form->setData($data);
            }    
        }        

        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'rowNo' => $rowNo,
            'good' => $good,
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
    
    public function goodEditFormAction()
    {        
        $itemId = $this->params()->fromRoute('id', -1);
        
        $item = $good = $result = null;
        if ($itemId>0){
            $item = $this->entityManager->getRepository(MarketSaleReportItem::class)
                    ->find($itemId);
        }
                        
        $form = new MsrGoodForm($this->entityManager, $good);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);
            $form->remove('saleQty');
            $form->remove('returnQty');
            
            if ($form->isValid()) {
                if (isset($data['good'])){
                    $good = $this->entityManager->getRepository(Goods::class)
                            ->find($data['good']);            
                }
                if ($good){
                    $result = 'ok';
                    $this->reportManager->updateItemGood($item, $good);
                    $query = $this->entityManager->getRepository(MarketSaleReportItem::class)
                                    ->findReportItems($item->getMarketSaleReport()->getId(), ['itemId' => $item->getId()]);
                    $row = $query->getOneOrNullResult(2);
                }    
                return new JsonModel([
                    'result' => $result,
                    'row' => $row,
                ]);        
            } else {
                var_dump($form->getMessages());
            }
        } else {
            if ($good){
                $data = [
                    'good' => $good->getId(),
                    'code' => $good->getCode(),
                    'goodInputName' => $good->getInputName(),
                ];
                $form->setData($data);
            }    
        }        

        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'item' => $item,
            'good' => $good,
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
