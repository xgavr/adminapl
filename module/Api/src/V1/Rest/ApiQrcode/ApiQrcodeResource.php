<?php
namespace Api\V1\Rest\ApiQrcode;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Laminas\Filter\ToFloat;
use Bank\Entity\QrCode;

class ApiQrcodeResource extends AbstractResourceListener
{
    
    /**
     * Sbp manager.
     * @var \Bank\Service\SbpManager
     */
    private $sbpManager;    

    public function __construct($sbpManager) 
    {
       $this->sbpManager = $sbpManager;       
    }
    
    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        if (is_object($data)){
            if (!empty($data->order) && !empty($data->amount)){
                $toFloat = new ToFloat();
                $qrCode = $this->sbpManager->registerQrCode([
                    'orderAplId' => $data->order,
                    'amount' => $toFloat->filter($data->amount),
                ]);
                
                if ($qrCode instanceof QrCode){
                    return $qrCode->toLog();
                }
            }
        }
        return new ApiProblem(404, 'Не верные данные');
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
        return new ApiProblem(405, 'The GET method has not been defined for individual resources');
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
        if (is_object($data)){
            if (!empty($data->order) && !empty($data->amount)){
                $toFloat = new ToFloat();
                $qrCode = $this->sbpManager->registerQrCode([
                    'orderAplId' => $data->order,
                    'amount' => $toFloat->filter($data->amount),
                ]);
                
                if ($qrCode instanceof QrCode){
                    return $qrCode->toCheck();
                }
            }
        }
        return new ApiProblem(405, 'The GET method has not been defined for collections');
    }

    /**
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
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
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function update($id, $data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }
}
