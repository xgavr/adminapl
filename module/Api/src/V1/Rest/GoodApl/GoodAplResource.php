<?php
namespace Api\V1\Rest\GoodApl;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Application\Filter\ProducerName;
use Application\Entity\UnknownProducer;
use Application\Entity\Goods;
use Application\Filter\ArticleCode;

class GoodAplResource extends AbstractResourceListener
{
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Entity manager.
     * @var \Application\Service\GoodsManager
     */
    private $goodManager;

    public function __construct($entityManager, $goodManager) 
    {
       $this->entityManager = $entityManager;
       $this->goodManager = $goodManager;
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
            $good = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                    ->findOneBy(['aplId' => $id]);
            if ($good){
                return $this->goodManager->goodForApl($good);                
            }
        }        
        return new ApiProblem(404, 'Товар с кодом '.$id.' не найден');        
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
        $code = $producerStr = $unknownProducer = null;
        $result = [];
        
        $paramsArray = $params->toArray();
        
        if (!empty($paramsArray['article'])){
            $articleFilter = new ArticleCode();
            $code = $articleFilter->filter($paramsArray['article']);
        }
        
        if (!empty($paramsArray['producer'])){
            $producerStr = $paramsArray['producer'];
        }
        
        if ($producerStr){
            $producerNameFilter = new ProducerName();
            $producerName = $producerNameFilter->filter($producerStr);
            $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                    ->findOneBy(['name' => $producerName]);
        }    
        
        if ($unknownProducer && $code){
            if ($unknownProducer->getProducer()){
                $producer = $unknownProducer->getProducer();
                if ($producer){
                    $good = $this->entityManager->getRepository(Goods::class)
                            ->findOneBy(['code' => $code, 'producer' => $producer->getId()]);
                    if ($good){
                        $result[] = $good->toArray();
                        return $result;
                    }
                }    
            }
        }
        
        return new ApiProblem(404, 'Ничего не нашлось :(');
//        return new ApiProblem(405, 'The GET method has not been defined for collections');
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
