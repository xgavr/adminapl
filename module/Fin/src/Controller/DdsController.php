<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Fin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Fin\Entity\FinDds;
use Company\Entity\Legal;


class DdsController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    /**
     * Fin manager.
     * @var \Fin\Service\DdsManager
     */
    private $ddsManager;
        
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $ddsManager) 
    {
       $this->entityManager = $entityManager;
       $this->ddsManager = $ddsManager;
    }

    
    public function indexAction()
    {
        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        
        return new ViewModel([
            'years' => range(date('Y'), 2024),
            'companies' => $companies,
        ]);
    }
    
    public function contentAction()
    {
        $year = $this->params()->fromQuery('year', date('Y'));
        $companyId = $this->params()->fromQuery('company');
        $status = $this->params()->fromQuery('status');
        
        $startDate = "$year-01-01";
        $endDate = "$year-12-31";

        $company = $this->entityManager->getRepository(Legal::class)
                ->find($companyId);
                
        $data = $this->entityManager->getRepository(FinDds::class)
                        ->findDds($startDate, $endDate, $company);
        
        $result = FinDds::emptyYear();
        foreach ($data as $row){
            foreach ($row as $key => $value){  
                if (!isset($result[$key])) {
                    continue;
                }
                if (!isset($result[$key][date('m', strtotime($row['period']))])) {
                    continue;
                }
                $result[$key][date('m', strtotime($row['period']))] = $value;
                
                $result[$key]['13'] += (float) $value;
            }    
        }
        
        $result['totalBegin']['13'] = '';
        $result['bankBegin']['13'] = '';
        $result['depositBegin']['13'] = '';
        $result['cashBegin']['13'] = '';
        $result['accountantBegin']['13'] = '';
        $result['goodBegin']['13'] = '';

        $result['totalEnd']['13'] = '';
        $result['bankEnd']['13'] = '';
        $result['depositEnd']['13'] = '';
        $result['cashEnd']['13'] = '';
        $result['accountantEnd']['13'] = '';
        $result['goodEnd']['13'] = '';

        return new JsonModel([
            'total' => count($result),
            'rows' => array_values($result),
        ]);                  
    }
    
    public function costAction()
    {
        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        
        return new ViewModel([
            'years' => range(date('Y'), 2024),
            'companies' => $companies,
        ]);
    }

    public function costContentAction()
    {
        $year = $this->params()->fromQuery('year', date('Y'));
        $companyId = $this->params()->fromQuery('company');
        $status = $this->params()->fromQuery('status');
        
        $startDate = "$year-01-01";
        $endDate = "$year-12-31";

        $company = $this->entityManager->getRepository(Legal::class)
                ->find($companyId);
                
        $data = $this->entityManager->getRepository(FinDds::class)
                        ->findCosts($startDate, $endDate, $company);
        
        $result = $this->finManager->emptyCostYear($startDate, $endDate, $company);
        
        foreach ($data as $row){
            $result[$row['costId']][date('m', strtotime($row['period']))] = round($row['amount']);
            $result[$row['costId']][13] += round($row['amount']);
        }
        
        return new JsonModel([
            'total' => count($result),
            'rows' => array_values($result),
        ]);                  
    }
    
    public function zpAction()
    {
        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        
        return new ViewModel([
            'years' => range(date('Y'), 2024),
            'companies' => $companies,
        ]);
    }

    public function zpContentAction()
    {
        $year = $this->params()->fromQuery('year', date('Y'));
        $companyId = $this->params()->fromQuery('company');
        $status = $this->params()->fromQuery('status');
        
        $startDate = "$year-01-01";
        $endDate = "$year-12-31";

        $company = $this->entityManager->getRepository(Legal::class)
                ->find($companyId);
                
        $data = $this->entityManager->getRepository(FinDds::class)
                        ->findZp($startDate, $endDate, $company);
        
        $result = $this->finManager->emptyZpYear($startDate, $endDate, $company);
        
        foreach ($data as $row){
            $result[$row['userId']][date('m', strtotime($row['period']))] = abs(round($row['amount']));
            $result[$row['userId']][13] += abs(round($row['amount']));
        }
        
        return new JsonModel([
            'total' => count($result),
            'rows' => array_values($result),
        ]);                  
    }
    
    public function retailAction()
    {
        $kind = $this->params()->fromQuery('kind');
        
        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        
        return new ViewModel([
            'years' => range(date('Y'), 2024),
            'companies' => $companies,
            'kind' => $kind,
        ]);
    }

    public function retailContentAction()
    {
        $year = $this->params()->fromQuery('year', date('Y'));
        $companyId = $this->params()->fromQuery('company');
        $kind = $this->params()->fromQuery('kind');
        $status = $this->params()->fromQuery('status');
        
        $startDate = "$year-01-01";
        $endDate = "$year-12-31";

        $company = $this->entityManager->getRepository(Legal::class)
                ->find($companyId);
        
        $result = $this->finManager->emptyRetailYear($startDate, $endDate, $company);
        
        if ($kind == 'revenueRetail'){        
            $data = $this->entityManager->getRepository(FinOpu::class)
                            ->findRetailRevenue($startDate, $endDate, $company);
            foreach ($data as $row){
                $result[$row['userId']][date('m', strtotime($row['period']))] = round($row['amount']);
                $result[$row['userId']][13] += round($row['amount']);
            }
        }    
        
        if ($kind == 'purchaseRetail'){        
            $data = $this->entityManager->getRepository(FinOpu::class)
                            ->findRetailPurchase($startDate, $endDate, $company);
            foreach ($data as $row){
                $result[$row['userId']][date('m', strtotime($row['period']))] = -round($row['purchase']);
                $result[$row['userId']][13] += -round($row['purchase']);
            }
        }    
        
        if ($kind == 'incomeRetail'){        
            $dataRevenue = $this->entityManager->getRepository(FinOpu::class)
                            ->findRetailRevenue($startDate, $endDate, $company);
            $dataPurchase = $this->entityManager->getRepository(FinOpu::class)
                            ->findRetailPurchase($startDate, $endDate, $company);
            foreach ($dataRevenue as $row){
                $result[$row['userId']][date('m', strtotime($row['period']))] = round($row['amount']);
                $result[$row['userId']][13] += round($row['amount']);
            }
            
            foreach ($dataPurchase as $row){
                $result[$row['userId']][date('m', strtotime($row['period']))] += round($row['purchase']);
                $result[$row['userId']][13] += round($row['purchase']);
            }
        }    
        
        if ($kind == 'marginRetail'){        
            $data = $this->entityManager->getRepository(FinOpu::class)
                            ->findRetailPurchase($startDate, $endDate, $company);
            foreach ($data as $row){
                $result[$row['userId']][date('m', strtotime($row['period']))] = 0;
                if (!empty(abs($row['revenue']))){
                    $result[$row['userId']][date('m', strtotime($row['period']))] = (abs($row['revenue']) - abs($row['purchase']))*100/abs($row['revenue']);
                }    
                //$result[$row['userId']][13] += round($row['purchase']);
            }
        }  
        
        if ($kind == 'orderCount'){        
            $data = $this->entityManager->getRepository(FinOpu::class)
                            ->findRetailOrderCount($startDate, $endDate, $company);
            foreach ($data as $row){
                $result[$row['userId']][date('m', strtotime($row['period']))] = $row['orderCount'];
                $result[$row['userId']][13] += $row['orderCount'];
            }
        }    
        
        if ($kind == 'avgBill'){        
            $data = $this->entityManager->getRepository(FinOpu::class)
                            ->findRetailOrderCount($startDate, $endDate, $company);
            foreach ($data as $row){
                $result[$row['userId']][date('m', strtotime($row['period']))] = $row['avgBill'];
                //$result[$row['userId']][13] += $row['avgBill'];
            }
        }    
        
//        foreach ($data as $row){
//            $result[$row['userId']][date('m', strtotime($row['period']))] = round($row['amount']);
//            $result[$row['userId']][13] += round($row['amount']);
//        }
        
        return new JsonModel([
            'total' => count($result),
            'rows' => array_values($result),
        ]);                  
    }
    
    public function calculateAction()
    {
        $year = $this->params()->fromQuery('year', date('Y'));

        $period = "$year-12-31";
        
        $this->ddsManager->calculate($period);
        
        return new JsonModel([
           'ok' => 'reload',
        ]);           
    }
}
