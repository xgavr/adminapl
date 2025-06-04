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
                'oems' => [],
                'images' => [],
                'product_cars' => [],
                'related' => [],
            ];
            $limit = $paramsArray['limit'] ?? 1000;
            $fasade = $paramsArray['fasade'] ?? Goods::FASADE_EX_NEW;
            
            $goods = $this->entityManager->getRepository(Goods::class)
                    ->findForFasade(['fasade' => $fasade, 'limit' => $limit]);
            
            foreach ($goods as $good){
                switch ($fasade){
                    case Goods::FASADE_EX_NEW:
                        $data = $good->toArray();
                        $data['categories'] = $good->getCategoryIdsAsArray();
                        $data['lot'] = $this->entityManager->getRepository(Goods::class)->goodLot($good);
                        $data['attributes'] = $good->getAttributeValuesAsArray();
                        
                        $result['products'][$good->getId()] = $data;
                        $result['categories'] = array_replace($result['categories'], $good->getCategoriesAsFlatArray());
                        break;
                    case Goods::FASADE_EX_OEM:
                        $data['oems'] = $good->getOemsAsArray();
                        $result['oems'][$good->getId()] = $data;
                        break;
                    case Goods::FASADE_EX_IMG:
                        $data['images'] = $good->getImagesAsArray();
                        $result['images'][$good->getId()] = $data;
                        break;
                    case Goods::FASADE_EX_CAR:
                        $data['cars'] = $good->getCarIdsAsArray();       
                        $result['product_cars'][$good->getId()] = $data;
                        
                        $result['makes'] = array_replace($result['makes'], $good->getMakesAsArray());
                        $result['models'] = array_replace($result['models'], $good->getModelsAsArray());
                        $result['cars'] = array_replace($result['cars'], $good->getCarsAsArray());
                        break;
                    case Goods::FASADE_EX_RLT:
                        $data['related'] = $this->entityManager->getRepository(Goods::class)
                            ->relatedGoods($good);
                        $result['related'][$good->getId()] = $data;
                        break;
                }
                
                
            }
            return [$result];
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
//            var_dump($data[0]['fasade'], $data[1]['fasade_loaded']); exit;
            switch ($data[0]['fasade']){
                case Goods::FASADE_EX_NEW: $nextFasade = Goods::FASADE_EX_OEM; break;
                case Goods::FASADE_EX_OEM: $nextFasade = Goods::FASADE_EX_IMG; break;
                case Goods::FASADE_EX_IMG: $nextFasade = Goods::FASADE_EX_CAR; break;
                case Goods::FASADE_EX_CAR: $nextFasade = Goods::FASADE_EX_RLT; break;
                default:
                    $nextFasade = Goods::FASADE_EX_FULL_LOADED;
            }
            
            foreach ($data[1]['fasade_loaded'] as $row){
                var_dump($nextFasade, $row); exit;
                if (!empty($row['fasade_loaded'])){
                    foreach($row['fasade_loaded'] as $goodId){
                        $this->entityManager->getConnection()->update('goods', ['fasade_ex' => $nextFasade], ['id' => $goodId]);                        
                    } 
                    return 'Успешно обновлено!';
                }
            } 
            return new ApiProblem(204, 'Нет данных для обновления!');
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
