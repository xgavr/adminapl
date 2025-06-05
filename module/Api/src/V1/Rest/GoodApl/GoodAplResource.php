<?php
namespace Api\V1\Rest\GoodApl;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Application\Filter\ProducerName;
use Application\Entity\UnknownProducer;
use Application\Entity\Goods;
use Application\Filter\ArticleCode;
use Application\Entity\GoodRelated;

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
            
            $limit = $paramsArray['limit'] ?? 1000;
            $fasade = $paramsArray['fasade'] ?? Goods::FASADE_EX_NEW;

            switch ($fasade){
                case Goods::FASADE_EX_NEW:
                    $result = [
                        'categories' => [],
                        'products' => [],
                    ];
                    $goods = $this->entityManager->getRepository(Goods::class)
                            ->findForFasade(['fasade' => $fasade, 'limit' => $limit]);
                    foreach ($goods as $good){
                        $data = $good->toArray();
                        $data['categories'] = $good->getCategoryIdsAsArray();
                        $data['lot'] = $this->entityManager->getRepository(Goods::class)->goodLot($good);
                        $data['attributes'] = $good->getAttributeValuesAsArray();
                        
                        $result['products'][$good->getId()] = $data;
                        $result['categories'] = array_replace($result['categories'], $good->getCategoriesAsFlatArray());
                    }
                    return [$result];
                case Goods::FASADE_EX_OEM:
                    $result = $this->entityManager->getRepository(Goods::class)
                        ->oemForFasade(['fasade' => $fasade, 'limit' => $limit]);
                    return $result;
                case Goods::FASADE_EX_IMG:
                    $result = $this->entityManager->getRepository(Goods::class)
                        ->imgForFasade(['fasade' => $fasade, 'limit' => $limit]);
                    return $result;
                case Goods::FASADE_EX_CAR:
                    $result = $this->entityManager->getRepository(Goods::class)
                        ->carsForFasade(['fasade' => $fasade, 'limit' => $limit]);
                    return $result;
                case Goods::FASADE_EX_RLT:
                    $result = $this->entityManager->getRepository(Goods::class)
                        ->relatedForFasade(['fasade' => $fasade, 'limit' => $limit]);
                    return $result;
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
        if (is_object($data)){
//            var_dump($data[0]['fasade'], $data[0]['fasade_loaded']); exit;
            
            $i = 0;
            foreach ($data[0]['fasade_loaded'] as $goodId){
                
                $good = $this->entityManager->getRepository(Goods::class)
                        ->find($goodId);
                
                if ($good){
                    switch ($data[0]['fasade']){
                        case Goods::FASADE_EX_NEW: 
                            if ($good->getOems()->count()){
                                $nextFasade = Goods::FASADE_EX_OEM; break;
                            }    
                        case Goods::FASADE_EX_OEM: 
                            if ($good->getImageCount()){
                                $nextFasade = Goods::FASADE_EX_IMG; break;
                            }    
                        case Goods::FASADE_EX_IMG:
                            if ($good->getCarCount()){
                                $nextFasade = Goods::FASADE_EX_CAR; break;
                            }    
                        case Goods::FASADE_EX_CAR: 
                            $goodRelatedCount = $this->entityManager->getRepository(GoodRelated::class)
                                ->count(['good' => $good->getId()]);
                            if ($goodRelatedCount){
                                $nextFasade = Goods::FASADE_EX_RLT; break;
                            }    
                        default:
                            $nextFasade = Goods::FASADE_EX_FULL_LOADED;
                    }
    //                var_dump($nextFasade, $goodId); exit;
                    $this->entityManager->getConnection()->update('goods', ['fasade_ex' => $nextFasade], ['id' => $good->getId()]);                        
                }    
                $i++;
            } 
            
            if ($i){
                return "$i - успешно обновлено!";
            } else {
                return new ApiProblem(204, 'Нет данных для обновления');
            }
            
//            return new ApiProblem(204, 'Нет данных для обновления!');
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
