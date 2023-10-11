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
use Application\Entity\Supplier;
use Stock\Entity\Mutual;
use Company\Entity\Legal;
use Admin\Entity\Log;
use Company\Entity\Contract;

class RevisionController extends AbstractActionController
{

    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Менеджер банк.
     * @var \Admin\Service\LogManager
     */
    private $logManager;

    public function __construct($entityManager, $logManager) 
    {
        $this->entityManager = $entityManager;
        $this->logManager = $logManager;
    }   

    public function indexAction()
    {
        $supplierId = (int)$this->params()->fromQuery('supplier', -1);
        $contractId = (int)$this->params()->fromQuery('contract', -1);
        
        $legals=[]; $contracts=[];

        $supplier = $this->entityManager->getRepository(Supplier::class)
                ->find($supplierId);
        if ($supplier){
            $legals = $supplier->getLegalContact()->getLegals();
        }
        
        $contract = $this->entityManager->getRepository(Contract::class)
                ->find($contractId);
        if ($contract){
            $params = [
                'legal' => $contract->getLegal()->getId(),
                'company' => $contract->getCompany()->getId(),
            ];
            
            $contracts = $this->entityManager->getRepository(Contract::class)
                    ->findBy($params);            
        }

        $suppliers = $this->entityManager->getRepository(Supplier::class)
                ->findAll(null, ['status' => 'ASC', 'name' => 'ASC']);

        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        
        return new ViewModel([
                'supplier' => $supplier,
                'contract' => $contract,
                'suppliers' => $suppliers,
                'companies' => $companies,
                'legals' => $legals,
                'contracts' => $contracts,
            ]);
    }
    
    public function contentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort', 'docDate');
        $order = $this->params()->fromQuery('order', 'DESC');
        $supplierId = $this->params()->fromQuery('supplier');
        $companyId = $this->params()->fromQuery('company');
        $legalId = $this->params()->fromQuery('legal');
        $contractId = $this->params()->fromQuery('contract');
        $officeId = $this->params()->fromQuery('office');
        $dateStart = $this->params()->fromQuery('dateStart');
        $period = $this->params()->fromQuery('period');
        $status = $this->params()->fromQuery('status');
        
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
            'q' => trim($q), 'sort' => $sort, 'order' => $order, 
            'supplierId' => $supplierId, 'officeId' => $officeId,
            'startDate' => $startDate, 'endDate' => $endDate, 'status' => $status,
            'companyId' => $companyId, 'legalId' => $legalId, 'contractId' => $contractId,
        ];
                
        $query = $this->entityManager->getRepository(Mutual::class)
                        ->mutuals($params);
        
        $total = $this->entityManager->getRepository(Mutual::class)
                        ->mutualsCount($params);
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }
        
//        var_dump($query->getSql()); exit;
        $result = $query->getResult(2);
        foreach ($result as $key=>$value){
//                var_dump($value);
            $result[$key]['rest'] = $this->entityManager->getRepository(Mutual::class)
                ->mutualBalance([
                    'supplierId' => (is_numeric($supplierId)) ? $supplierId:null, 
                    'companyId' => (is_numeric($companyId)) ? $companyId:null, 
                    'legalId' => (is_numeric($legalId)) ? $legalId:null, 
                    'contractId' => (is_numeric($contractId)) ? $contractId:null, 
                    'docStamp' => $value['docStamp']
                ])->getOneOrNullResult();            
        }
        
        $turnover = $this->entityManager->getRepository(Mutual::class)
                ->mutualBalance([
                    'supplierId' => (is_numeric($supplierId)) ? $supplierId:null, 
                    'companyId' => (is_numeric($companyId)) ? $companyId:null, 
                    'legalId' => (is_numeric($legalId)) ? $legalId:null, 
                    'contractId' => (is_numeric($contractId)) ? $contractId:null, 
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'turnover' => true,
                    'endBalance' => true,
                ])->getOneOrNullResult();

        return new JsonModel([
            'total' => $total,
            'rows' => $result,
            'turnover' => $turnover,
        ]);          
    } 
    
    public function supplierRevisionAction()
    {

        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        $suppliers = $this->entityManager->getRepository(Supplier::class)
                ->findAll(null, ['status' => 'ASC', 'name' => 'ASC']);
        
        return new ViewModel([
                'companies' => $companies,
                'suppliers' => $suppliers,
            ]);
    }
    
    public function supplierRevisionContentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        $supplierId = $this->params()->fromQuery('supplier');
        $companyId = $this->params()->fromQuery('company');
        $legalId = $this->params()->fromQuery('legal');
        $contractId = $this->params()->fromQuery('contract');
        $officeId = $this->params()->fromQuery('office');
        $status = $this->params()->fromQuery('status');
        $pay = $this->params()->fromQuery('pay');
        
        $params = [
            'q' => trim($q), 'sort' => $sort, 'order' => $order, 
            'supplierId' => $supplierId, 'officeId' => $officeId,
            'status' => $status, 'companyId' => $companyId, 'legalId' => $legalId,
            'contractId' => $contractId, 'pay' => $pay,
        ];
                
        $query = $this->entityManager->getRepository(Mutual::class)
                        ->contractBalances($params);
        
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
    
    public function changeReviseAction()
    {
        $mutualId = (int)$this->params()->fromRoute('id', -1);
        $check = (int)$this->params()->fromQuery('check', Mutual::REVISE_OK);
        
        $mutual = $this->entityManager->getRepository(Mutual::class)
                ->find($mutualId);
        
        if ($mutual){
            $this->entityManager->getRepository(Mutual::class)
                    ->changeRevise($mutual, $check);
            
            $this->logManager->infoMutual($mutual, Log::STATUS_INFO);
        }
        
        return new JsonModel([
                'result' => 'ok',
            ]);
        
    }
}
