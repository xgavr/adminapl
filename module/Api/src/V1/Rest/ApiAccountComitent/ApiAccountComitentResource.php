<?php
namespace Api\V1\Rest\ApiAccountComitent;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use ApiMarketPlace\Entity\MarketSaleReport;
use Stock\Entity\Ptu;

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
        if (count($result)){
            return ['reports' => $result];
        }    
        
        return new ApiProblem(404, 'Нет отчетов для выгрузки!');
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
