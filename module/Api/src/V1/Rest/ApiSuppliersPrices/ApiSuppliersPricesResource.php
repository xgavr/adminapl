<?php
namespace Api\V1\Rest\ApiSuppliersPrices;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Application\Entity\GoodSupplier;
use Application\Entity\Goods;
use Stock\Entity\Reserve;
use Stock\Entity\Movement;
use GoodMap\Entity\FoldBalance;

class ApiSuppliersPricesResource extends AbstractResourceListener
{
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    public function __construct($entityManager) 
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
     * Резервы Апл
     * @param Goods $good
     * @return string
     */
    private function aplReserve($good)
    {
        $reserves = $this->entityManager->getRepository(Reserve::class)
                ->findBy(['good' => $good->getId()]);
        $result = [];
        foreach ($reserves as $reserve){
            $doc = $this->entityManager->getRepository(Movement::class)
                    ->docFromLogKey($reserve->getDocKey());
            $docAplId = null;
            if ($doc){
                $docAplId = ($doc->getAplId()) ? $doc->getAplId():null;
            }
            if ($reserve->getStatus() == Reserve::STATUS_VOZVRAT){
                $result[] = "Возврат({$reserve->getRest()})"; 
            }
            if ($docAplId && ($reserve->getStatus() == Reserve::STATUS_RESERVE || $reserve->getStatus() == Reserve::STATUS_DELIVERY)){
                $result[] = "<a href='/admin/orders/view/id/$docAplId' target='_blank'>$docAplId</a>({$reserve->getRest()})"; 
            }
        }      
        
        return implode(',', $result);
    }
    
    /**
     * Хранение
     * @param Goods $good
     * @return string
     */
    private function fold($good)
    {
        $foldBalances = $this->entityManager->getRepository(FoldBalance::class)
                ->findBy(['good' => $good->getId()]);
        $result = [];
        foreach ($foldBalances as $foldBalance){
            if ($foldBalance->getRest() > 0){
                $result[] = $foldBalance->getFoldName(); 
            }
        }      
        
        return implode(',', $result);
    }
    
    /**
     * Цены и наличие товара из прайсов поставщиков
     *
     * @param  integer $id Апл Ид товара
     * @return ApiProblem|array
     */
    public function fetch($id)
    {
        $result = [];
        if (is_numeric($id)){
            $good = $this->entityManager->getRepository(Goods::class)
                    ->findOneBy(['aplId' => $id]);
            
            if ($good){

                $goodSuppliersQuery = $this->entityManager->getRepository(GoodSupplier::class)
                        ->orderGoodSuppliers($good->getId());
                $data = $goodSuppliersQuery->getResult();
                foreach ($data as $row){      
                    $reserve = null;
                    $restApl = 0; $fold = '';
                    if ($row->getSupplier()->getAplId() == 6){ //если Апл
                        $reserve = $this->aplReserve($good);
                        $restApl = $row->getRest();
                        
                        $fold = $this->fold($good);
                    }
                    $result[] = [
                        'price' => $row->getPrice(),
                        'name' => $row->getSupplier()->getAplId(),
                        'created' => $row->getUpdate(),
                        'supplier' => $row->getSupplier()->getName(),
                        'saleprice' => $good->getPrice(),
                        'rest' => $row->getRest(),
                        'comp' => $row->getLot(),
                        'reserve' => $reserve,
                        'restApl' => $restApl,
                        'fold' => $fold,
                    ];
                }
            }    
        }                
        return ['data' => $result];                
//        return new ApiProblem(404, 'Товар с апл ид '.$id.' не найден!');
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
