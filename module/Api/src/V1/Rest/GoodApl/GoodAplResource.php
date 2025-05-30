<?php
namespace Api\V1\Rest\GoodApl;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Application\Filter\ProducerName;
use Application\Entity\UnknownProducer;
use Application\Entity\Goods;
use Application\Filter\ArticleCode;
use Fasade\Entity\GroupSite;

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
        
        if (!empty($paramsArray['fasade'])){
            $result = [
                'categories' => [],
                'makes' => [],
                'models' => [],
                'cars' => [],
                'products' => [],
            ];
            $limit = empty($paramsArray['limit']) ? 1000:$paramsArray['limit'];
            $goods = $this->entityManager->getRepository(Goods::class)
                    ->findForFasade(['limit' => $limit]);
            foreach ($goods as $good){
                $data = $good->toArray();
                $data['images'] = $good->getImagesAsArray();
                $data['categories'] = $good->getCategoryIdsAsArray();
                $data['cars'] = $good->getCarIdsAsArray();
                $data['attributes'] = $good->getAttributeValuesAsArray();
                $data['oems'] = $good->getOemsAsArray();
                
                $data['related'] = $this->entityManager->getRepository(Goods::class)
                        ->relatedGoods($good);
                
                $data['lot'] = $this->entityManager->getRepository(Goods::class)
                        ->goodLot($good);
                
                $result['products'][] = $data;
                $result['categories'] = array_replace($result['categories'], $good->getCategoriesAsFlatArray());
                $result['makes'] = array_replace($result['makes'], $good->getMakesAsArray());
                $result['models'] = array_replace($result['models'], $good->getModelsAsArray());
                $result['cars'] = array_replace($result['cars'], $good->getCarsAsArray());
            }
            return ['data' => $result];
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
        if (is_object($data)){
            if(!empty($data->fasade)){
                foreach ($data->fasade as $key => $goodAplId){
    //                $this->entityManager->getConnection()->update('goods', ['fasade_ex' => Goods::FASADE_EX_TRANSFERRED], ['apl_id' => $goodAplId]);
                } 
                return new ApiProblem(200, 'Успешно обновлено!');
            }
            return new ApiProblem(204, 'Нет данных для обновления');
        }
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
