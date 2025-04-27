<?php
namespace Api\V1\Rest\ApiAccountComitent;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use ApiMarketPlace\Entity\MarketSaleReport;
use Stock\Entity\Ptu;
use Stock\Entity\St;
use Stock\Entity\Pt;
use Stock\Entity\Ot;
use Stock\Entity\Vt;
use Stock\Entity\Vtp;
use Application\Entity\Order;
use Bank\Entity\Statement;
use Cash\Entity\CashDoc;
use Company\Entity\Office;
use Stock\Entity\Mutual;
use Company\Entity\Contract;
use Company\Entity\BankAccount;
use Application\Entity\Supplier;

class ApiAccountComitentResource extends AbstractResourceListener
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Report manager.
     * @var \ApiMarketPlace\Service\ReportManager
     */
    private $reportManager;

    /**
     * Zp manager.
     * @var \Zp\Service\ZpManager
     */
    private $zpManager;

    /**
     * Payment manager.
     * @var \Bank\Service\PaymentManager
     */
    private $paymentManager;

    public function __construct($entityManager, $reportManager, $zpManager, $paymentManager) 
    {
       $this->entityManager = $entityManager;       
       $this->reportManager = $reportManager;       
       $this->zpManager = $zpManager;       
       $this->paymentManager = $paymentManager;       
    }

    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        return new ApiProblem(405, 'The POST method has not been defined');
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for individual resources');
    }

    /**
     * Delete a collection, or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function deleteList($data)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for collections');
    }

    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        if (is_numeric($id)){
            $report = $this->entityManager->getRepository(MarketSaleReport::class)
                    ->find($id);
            if ($report){
                return $report->toArray();                
            }
        }                
        return new ApiProblem(404, 'Отчет комиссионера с ид '.$id.' не найден!');
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
        $result = [];
        if ($params['docType'] == 'MarketSaleReport'){
            $reports = $this->entityManager->getRepository(MarketSaleReport::class)
                    ->findBy(['statusAccount' => MarketSaleReport::STATUS_ACCOUNT_NO]);
            foreach ($reports as $report){
                $result[] = $report->toArray(); 
            }
        }    
        if ($params['docType'] == 'Ptu'){
            $ptus = $this->entityManager->getRepository(Ptu::class)
                    ->findBy(['statusAccount' => Ptu::STATUS_ACCOUNT_NO]);
            foreach ($ptus as $ptu){
                $result[] = $ptu->toArray(); 
            }
        }    
        if ($params['docType'] == 'St'){
            $sts = $this->entityManager->getRepository(St::class)
                    ->findBy(['statusAccount' => St::STATUS_ACCOUNT_NO]);
            foreach ($sts as $st){
                $result[] = $st->toArray(); 
            }
        }    
        if ($params['docType'] == 'Ot'){
            $ots = $this->entityManager->getRepository(Ot::class)
                    ->findBy(['statusAccount' => Ot::STATUS_ACCOUNT_NO]);
            foreach ($ots as $ot){
                $result[] = $ot->toArray(); 
            }
        }    
        if ($params['docType'] == 'Pt'){
            $pts = $this->entityManager->getRepository(Pt::class)
                    ->findBy(['statusAccount' => Pt::STATUS_ACCOUNT_NO]);
            foreach ($pts as $pt){
                $result[] = $pt->toArray(); 
            }
        }    
        if ($params['docType'] == 'Vt'){
            $vts = $this->entityManager->getRepository(Vt::class)
                    ->findBy(['statusAccount' => Vt::STATUS_ACCOUNT_NO]);
            foreach ($vts as $vt){
                $result[] = $vt->toArray(); 
            }
        }    
        if ($params['docType'] == 'Vtp'){
            $vtps = $this->entityManager->getRepository(Vtp::class)
                    ->findBy(['statusAccount' => Vtp::STATUS_ACCOUNT_NO]);
            foreach ($vtps as $vtp){
                $result[] = $vtp->toArray(); 
            }
        }    
        if ($params['docType'] == 'Order'){
            $orders = $this->entityManager->getRepository(Order::class)
                    ->findBy(['statusAccount' => Order::STATUS_ACCOUNT_NO]);
            foreach ($orders as $order){
                $row = $order->toArray();
                $row['goods'] = $order->goodsToArray();
                $result[] = $row;
                
            }
        }    
        if ($params['docType'] == 'Statement'){
            $statements = $this->entityManager->getRepository(Statement::class)
                    ->findBy(['statusAccount' => Statement::STATUS_ACCOUNT_NO]);
            foreach ($statements as $statement){
                $row = $statement->toArray();
                $result[] = $row;
                
            }
        }    
        if ($params['docType'] == 'CashDoc'){
            $cashDocs = $this->entityManager->getRepository(CashDoc::class)
                    ->findBy(['statusAccount' => CashDoc::STATUS_ACCOUNT_NO]);
            foreach ($cashDocs as $cashDoc){
                $row = $cashDoc->toExport();
                $result[] = $row;
                
            }
        }    
        if ($params['docType'] == 'Zp'){
            
            $companies = $this->entityManager->getRepository(Office::class)
                    ->findAllCompanies();
            
            $startDate = date('Y-m-01', strtotime('first day of previous month'));
            $endDate = date('Y-m-t', strtotime('last day of previous month'));
                
            $startDateCurrent = date('Y-m-01');
            $endDateCurrent = date('Y-m-t');

            if (!empty($params['startDate'])){
                $startDate = $startDateCurrent = date('Y-m-01', strtotime($params['startDate']));
                $endDate = $endDateCurrent = date('Y-m-t', strtotime($params['startDate']));                
            }
            
            foreach ($companies as $company){
                
                $zpParams1= [
                    'startDate' => $startDate, 
                    'endDate' => $endDate, 
                    'summary' => false,
                    'company' => $company->getId(),
                ];
                $data1 = $this->zpManager->payslip($zpParams1);
                if (count($data1)){
                    $result[] = [
                        'company' => $company->toArray(),
                        'dateOper' => date('Ymd', strtotime($zpParams1['endDate'])),
                        'data' => $data1, 
                    ];   
                }    

                if ($startDate != $startDateCurrent){
                    $zpParams0= [
                        'startDate' => $startDateCurrent, 
                        'endDate' => $endDateCurrent, 
                        'summary' => false,
                        'company' => $company->getId(),
                    ];
                    $data0 = $this->zpManager->payslip($zpParams0);
                    if (count($data0)){
                        $result[] = [
                            'company' => $company->toArray(),
                            'dateOper' => date('Ymd', strtotime($zpParams0['endDate'])),
                            'data' => $data0, 
                        ];   
                    }    
                }    
            }            
        }    
        if ($params['docType'] == 'supplierBalance'){
            
            $supplierBalances = $this->entityManager->getRepository(Mutual::class)
                    ->contractBalances([
                        'kind' => Contract::KIND_SUPPLIER,
                        'pay' => Contract::PAY_CASHLESS,
                        'priceListStatus' => Supplier::REMOVE_PRICE_LIST_ON,
                        'companyId' => $params['company'],
                    ])->getResult(2);
            
            foreach ($supplierBalances as $row){
                $out = [
                    'companyId' => $row['company']['id'],
                    'companyName' => $row['company']['name'],
                    'legalId' => $row['legal']['id'],
                    'legalName' => $row['legal']['name'],
                    'supplierId' => $row['legal']['contacts'][0]['supplier']['id'],
                    'supplierAplId' => $row['legal']['contacts'][0]['supplier']['aplId'],
                    'supplierName' => $row['legal']['contacts'][0]['supplier']['name'],
                    'act' => $row['act'],
                    'balance' => $row['balance'],
                ];
                $result[] = $out;                
            }
        }    
        
        if ($params['docType'] == 'bankBalance'){
            $bankAccounts = $this->entityManager->getRepository(BankAccount::class)
                    ->findBy([
                        'legal' => $params['company'], 
                        'accountType' => BankAccount::ACСOUNT_CHECKING,
                        'status' => BankAccount::STATUS_ACTIVE,
                    ]);
            foreach ($bankAccounts as $bankAccount){
                $result[$bankAccount->getRs()] = $this->entityManager->getRepository(Statement::class)
                                    ->currentBalance($bankAccount->getRs());                
            }    
        }    

        return ['reports' => $result];
        
//        return new ApiProblem(404, 'Нет отчетов для выгрузки!');
    }

    /**
     * Patch (partial in-place update) a resource
     *
     * @param  integer $id
     * @param  array $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        if (is_object($data)){
            if ($data->docType == 'MarketSaleReport'){
                $report = $this->entityManager->getRepository(MarketSaleReport::class)
                        ->find($id);
                if ($report){
                    $report = $this->reportManager->updateReportSatusAccount($report, $data->statusAccount);
                    return ['statusAccount' => $report->getStatusAccount()];
                }
            }
            if ($data->docType == 'Ptu'){
                $ptu = $this->entityManager->getRepository(Ptu::class)
                        ->find($id);
                if ($ptu){
                    $ptu->setStatusAccount($data->statusAccount);
                    $this->entityManager->persist($ptu);
                    $this->entityManager->flush();
                    return ['statusAccount' => $ptu->getStatusAccount()];
                }
            }
            if ($data->docType == 'St'){
                $st = $this->entityManager->getRepository(St::class)
                        ->find($id);
                if ($st){
                    $st->setStatusAccount($data->statusAccount);
                    $this->entityManager->persist($st);
                    $this->entityManager->flush();
                    return ['statusAccount' => $st->getStatusAccount()];
                }
            }
            if ($data->docType == 'Ot'){
                $ot = $this->entityManager->getRepository(Ot::class)
                        ->find($id);
                if ($ot){
                    $ot->setStatusAccount($data->statusAccount);
                    $this->entityManager->persist($ot);
                    $this->entityManager->flush();
                    return ['statusAccount' => $ot->getStatusAccount()];
                }
            }
            if ($data->docType == 'Pt'){
                $pt = $this->entityManager->getRepository(Pt::class)
                        ->find($id);
                if ($pt){
                    $pt->setStatusAccount($data->statusAccount);
                    $this->entityManager->persist($pt);
                    $this->entityManager->flush();
                    return ['statusAccount' => $pt->getStatusAccount()];
                }
            }
            if ($data->docType == 'Vt'){
                $vt = $this->entityManager->getRepository(Vt::class)
                        ->find($id);
                if ($vt){
                    $vt->setStatusAccount($data->statusAccount);
                    $this->entityManager->persist($vt);
                    $this->entityManager->flush();
                    return ['statusAccount' => $vt->getStatusAccount()];
                }
            }
            if ($data->docType == 'Vtp'){
                $vtp = $this->entityManager->getRepository(Vtp::class)
                        ->find($id);
                if ($vtp){
                    $vtp->setStatusAccount($data->statusAccount);
                    $this->entityManager->persist($vtp);
                    $this->entityManager->flush();
                    return ['statusAccount' => $vtp->getStatusAccount()];
                }
            }
            if ($data->docType == 'Order'){
                $order = $this->entityManager->getRepository(Order::class)
                        ->find($id);
                if ($order){
                    $order->setStatusAccount($data->statusAccount);
                    $this->entityManager->persist($order);
                    $this->entityManager->flush();
                    return ['statusAccount' => $order->getStatusAccount()];
                }
            }
            if ($data->docType == 'Statement'){
                $statement = $this->entityManager->getRepository(Statement::class)
                        ->find($id);
                if ($statement){
                    $statement->setStatusAccount($data->statusAccount);
                    $this->entityManager->persist($statement);
                    $this->entityManager->flush();
                    return ['statusAccount' => $statement->getStatusAccount()];
                }
            }
            if ($data->docType == 'CashDoc'){
                $cashDoc = $this->entityManager->getRepository(CashDoc::class)
                        ->find($id);
                if ($cashDoc){
                    $cashDoc->setStatusAccount($data->statusAccount);
                    $this->entityManager->persist($cashDoc);
                    $this->entityManager->flush();
                    return ['statusAccount' => $cashDoc->getStatusAccount()];
                }
            }
            if ($data->docType == 'supplierPayment'){
                $supplier = $this->entityManager->getRepository(Supplier::class)
                        ->find($id);
                $bankAccount = $this->entityManager->getRepository(BankAccount::class)
                        ->findOneBy(['rs' => $data->rs]);
                
                if ($supplier && $bankAccount && !empty($data->amount)){

                    $this->paymentManager->suppliersPayment([
                        'bankAccount' => $bankAccount,
                        'amount' => [
                            'supplier' => $id,
                            'amount' => $data->amount,
                        ],
                    ]);

                    return ['supplierPayment' => 'ok'];
                }
            }
        }
        return new ApiProblem(404, 'Не верные данные');
    }

    /**
     * Patch (partial in-place update) a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patchList($data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for collections');
    }

    /**
     * Replace a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function replaceList($data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for collections');
    }

    /**
     * Update a resource
     *
     * @param  integer $id
     * @param  array $data
     * @return ApiProblem|mixed
     */
    public function update($id, $data)
    {
        if (is_object($data)){
            if (!empty($data->statusAccount)){
                $error = 'statusAccount not in array';
                if ($data->statusAccount == MarketSaleReport::STATUS_ACCOUNT_OK || $data->statusAccount == MarketSaleReport::STATUS_ACCOUNT_NO){
                    $report = $this->entityManager->getRepository(MarketSaleReport::class)
                            ->find($id);
                    if ($report){
                        $report = $this->reportManager->updateReportSatusAccount($report, $data['statusAccount']);
                        return ['statusAccount' => $report->getStatusAccount()];
                    }
                }
            }
        }    
        return new ApiProblem(404, 'Не верные данные');
    }
}
