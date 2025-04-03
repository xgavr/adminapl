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
use Fin\Entity\FinBalance;
use Company\Entity\Legal;
use Zp\Entity\PersonalMutual;
use User\Entity\User;


class BalanceController extends AbstractActionController
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
     * Fin manager.
     * @var \Fin\Service\BalanceManager
     */
    private $balanceManager;
        
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $ddsManager, $balanceManager) 
    {
       $this->entityManager = $entityManager;
       $this->ddsManager = $ddsManager;
       $this->balanceManager = $balanceManager;
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
                
        $data = $this->entityManager->getRepository(FinBalance::class)
                        ->findBalance($startDate, $endDate, $company);
        
        $result = FinBalance::emptyYear();
        foreach ($data as $row){
            foreach ($row as $key => $value){  
                if (!isset($result[$key])) {
                    continue;
                }
                if (!isset($result[$key][date('m', strtotime($row['period']))])) {
                    continue;
                }
                $result[$key][date('m', strtotime($row['period']))] = $value;
                
                //$result[$key]['13'] += (float) $value;
            }    
        }        

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
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $company = $this->params()->fromQuery('company');
        $dateStart = $this->params()->fromQuery('dateStart');
        $period = $this->params()->fromQuery('period');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        
        $startDate = '2012-01-01';
        $endDate = '2199-01-01';
        if (!empty($dateStart)){
            $startDate = date('Y-m-d', strtotime($dateStart));
            $endDate = $startDate;
            if ($period == 'week'){
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 week - 1 day', strtotime($startDate)));
            }    
            if ($period == 'month'){
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 month - 1 day', strtotime($startDate)));
            }    
            if ($period == 'number'){
                $startDate = $dateStart.'-01-01';
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 year - 1 day', strtotime($startDate)));
            }    
        }    
        
        $company = $this->entityManager->getRepository(Legal::class)
                    ->find($company);
        
        $params = [
            'company' => $company,
            'startDate' => $startDate,
            'endDate' => $endDate, 'summary' => true,
            'company' => $company->getId(),
//            'sort' => $sort, 'order' => $order, 
        ];
        
        $users = $this->entityManager->getRepository(PersonalMutual::class)
                ->findMutualsUsers($company);
        
        $data = [];
        foreach ($users as $user){
            
            $params['user'] = $user->getId();
            unset($params['startDate']);
            $balanceResult = $this->entityManager->getRepository(PersonalMutual::class)
                            ->payslip($params)->getOneOrNullResult(2);
            
            $endBalance = empty($balanceResult['amount']) ? 0:round(-$balanceResult['amount'], 2);
            
            $params['startDate'] = $startDate;        
            $totalResult = $this->entityManager->getRepository(PersonalMutual::class)
                            ->payslip($params)->getOneOrNullResult(2);
            
            $amount = empty($totalResult['amount']) ? 0:round($totalResult['amount'], 2);
            $amountOut = empty($totalResult['amountIn']) ? 0:round($totalResult['amountIn'], 2);
            $amountIn = empty($totalResult['amountOut']) ? 0:round($totalResult['amountOut'], 2);
            $startBalance = $endBalance + $amount;
            
            if ($startBalance || $amountIn || $amountOut || $endBalance){
                $row = [

                    'company' => $company->toArray(),
                    'user' => $user->toArray(),

                    'start' => $startBalance,
                    'amount' =>  $amount,
                    'amountIn' => $amountIn,
                    'amountOut' => $amountOut,
                    'end' => $endBalance,
                ];                

                $data[] = $row;        
            }    
        } 
        
        return new JsonModel([
            'total' => count($data),
            'rows' => $data,
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
        
        $this->balanceManager->calculate($period);
        
        return new JsonModel([
           'ok' => 'reload',
        ]);           
    }
}
