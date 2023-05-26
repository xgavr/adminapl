<?php
namespace Api\V1\Rest\ApiSuppliersPrices;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Application\Entity\GoodSupplier;
use Application\Entity\Goods;

class ApiSuppliersPricesResource extends AbstractResourceListener
{
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    public function __construct($entityManager, $reportManager) 
    {
       $this->entityManager = $entityManager;       
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
     * Цены и наличие товара из прайсов поставщиков
     *
     * @param  integer $id Апл Ид товара
     * @return ApiProblem|array
     */
    public function fetch($id)
    {
        if (is_numeric($id)){
            $goodId = $this->entityManager->getRepository(Goods::class)
                    ->find($id);
            
            if ($goodId){
                $goodSuppliersQuery = $this->entityManager->getRepository(GoodSupplier::class)
                        ->orderGoodSuppliers($goodId);
                $data = $goodSuppliersQuery->getResult();
                $result = [];
                foreach ($data as $row){
                    $result[] = [
                        'price' => $row->getPrice(),
                        'name' => $row->getSupplier()->getName()->getAplId(),
                        'created' => $row->getUpdate(),
                        'supplier' => $row->getSupplier()->getName(),
                        'saleprice' => $row->getGood()->getPrice(),
                    ];
                }

                return $result;                
            }    
        }                
        return new ApiProblem(404, 'Товар с апл ид '.$id.' не найден!');
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
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
