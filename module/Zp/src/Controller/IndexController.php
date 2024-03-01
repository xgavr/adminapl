<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zp\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Company\Entity\Legal;
use User\Entity\User;
use Zp\Entity\Accrual;
use Zp\Entity\PersonalMutual;

class IndexController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    /**
     * Zp manager.
     * @var \Zp\Service\ZpManager
     */
    private $zpManager;
        
    /**
     * Zp calculator.
     * @var \Zp\Service\ZpCalculator
     */
    private $zpCalculator;
        
    /**
     * Log manager.
     * @var \Admin\Service\LogManager
     */
    private $logManager;
        
    /**
     * Rbac manager.
     * @var \User\Service\RbacManager
     */
    private $rbacManager;
        
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $zpManager, $zpCalculator, 
            $logManager, $rbacManager) 
    {
       $this->entityManager = $entityManager;
       $this->zpManager = $zpManager;
       $this->zpCalculator = $zpCalculator;
       $this->logManager = $logManager;
       $this->rbacManager = $rbacManager;
    }

    
    public function indexAction()
    {
        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        $users = $this->entityManager->getRepository(User::class)
                ->findBy(['status' => User::STATUS_ACTIVE]);
        $accruals = $this->entityManager->getRepository(Accrual::class)
                ->findBy(['status' => Accrual::STATUS_ACTIVE]);
        
        return new ViewModel([
            'companies' => $companies,
            'users' => $users,
            'accruals' => $accruals,
        ]);
    }
 
    public function contentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $company = $this->params()->fromQuery('company');
        $user = $this->params()->fromQuery('user');
        $accrual = $this->params()->fromQuery('accrual');
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
        
        $params = [
            'q' => $q, 'company' => $company, 'user' => $user, 'accrual' => $accrual,
            'startDate' => $startDate, 'endDate' => $endDate,             
            'sort' => $sort, 'order' => $order, 
        ];
        
        $query = $this->entityManager->getRepository(PersonalMutual::class)
                        ->findMutuals($params);
        
        $total = count($query->getResult());
        
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
    
    public function updateZpAction()
    {
        $this->zpCalculator->periodCalculator();        
        
        return new JsonModel(
           ['result' => 'ok']
        );                   
    }    
    
    public function payslipAction()
    {
        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        $users = $this->entityManager->getRepository(User::class)
                ->findBy(['status' => User::STATUS_ACTIVE]);
        $accruals = $this->entityManager->getRepository(Accrual::class)
                ->findBy(['status' => Accrual::STATUS_ACTIVE]);
        
        return new ViewModel([
            'companies' => $companies,
            'users' => $users,
            'accruals' => $accruals,
            'currentUser' => $this->logManager->currentUser(),
            'rbacManager' => $this->rbacManager,
        ]);
    }
    
    public function payslipContentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $company = $this->params()->fromQuery('company');
        $user = $this->params()->fromQuery('user');
        $accrual = $this->params()->fromQuery('accrual');
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
        
        $params = [
            'q' => $q, 'company' => $company, 'user' => $user, 'accrual' => $accrual,
            'startDate' => $startDate, 'endDate' => $endDate, 'summary' => false,             
            'sort' => $sort, 'order' => $order, 
        ];
        
        $query = $this->entityManager->getRepository(PersonalMutual::class)
                        ->payslip($params);
        
        $total = count($query->getResult());
        
        if ($offset) {
            //$query->setFirstResult($offset);
        }
        if ($limit) {
            //$query->setMaxResults($limit);
        }

        $result = $query->getResult(2);
        
        $data = [];
        $totalIn = $totalOut = $startBalance = 0;
        foreach ($result as $rows){
            $row = [
                'company' => $this->entityManager->getRepository(Legal::class)
                    ->find($rows['company'])->toArray(),
                'user' => $this->entityManager->getRepository(User::class)
                    ->find($rows['user'])->toArray(),
                'amount' => $rows['amount'],
                'amountIn' => $rows['amountIn'],
                'amountOut' => $rows['amountOut'],
            ];
            
            $totalIn += $rows['amountIn'];
            $totalOut += $rows['amountOut'];
            
            if (!empty($rows['accrual'])){
                $row['accrual'] = $this->entityManager->getRepository(Accrual::class)
                    ->find($rows['accrual'])->toArray();
            }
            
            $data[] = $row;        
        } 
        
        $params['startDate'] = date('2012-01-01');        
        $params['endDate'] = date('Y-m-d 23:59:59', strtotime($startDate.' -1 day'));
        $params['summary'] = true;
        
        $balaceQuery = $this->entityManager->getRepository(PersonalMutual::class)
                        ->payslip($params);
        $balanceResult = $balaceQuery->getOneOrNullResult(2);
        $startBalance = empty($balanceResult['amount']) ? 0:$balanceResult['amount'];
//        var_dump($params, $balanceResult);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $data,
            'startBalance' => round(-$startBalance),
            'totalIn' => round(abs($totalOut)),
            'totalOut' => round(abs($totalIn)),
            'endBalance' => round(-$startBalance - $totalIn + $totalOut),
        ]);          
    }            
    
}
