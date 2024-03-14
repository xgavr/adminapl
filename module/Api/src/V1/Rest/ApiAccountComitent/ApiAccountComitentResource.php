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

    public function __construct($entityManager, $reportManager) 
    {
       $this->entityManager = $entityManager;       
       $this->reportManager = $reportManager;       
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
