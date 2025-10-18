<?php
namespace Api\V1\Rest\GoodApl;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Application\Filter\ProducerName;
use Application\Entity\UnknownProducer;
use Application\Entity\Goods;
use Application\Filter\ArticleCode;
use Application\Entity\GoodRelated;
use Application\Entity\Oem;

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
    // Проверяем, является ли $id строкой, начинающейся с 'Z'
        if (is_string($id) && strpos($id, 'Z') === 0) {
            // Извлекаем число после 'Z'
            $numericId = substr($id, 1);
            // Проверяем, что остаток является числом
            if (is_numeric($numericId)) {
                $good = $this->entityManager->getRepository(Goods::class)
                    ->find($numericId);
                if ($good) {
                    return $good->toLog();                
                }
            }
        } elseif (is_string($id) && strpos($id, 'C') === 0) {
            // Извлекаем после 'C'
            list($code, $producerId) = explode('_', substr($id, 1));
            
            if ($code && $producerId) {
                $good = $this->entityManager->getRepository(Goods::class)
                    ->findOneBy(['code' => $code, 'producer' => $producerId]);
                if ($good) {
                    return $good->toLog();                
                }
            }
        } elseif (is_numeric($id)) {
            // Обработка чисто числового ID, как в исходном коде
            $good = $this->entityManager->getRepository(Goods::class)
                ->findOneBy(['aplId' => $id]);
            if ($good) {
                return $this->goodManager->goodForApl($good);                
            }
        }        
        
        return new ApiProblem(404, 'Товар с кодом '.$id.' не найден');        
    }
    
    private function inStore($good)
    {
        $params = [
            'q' => $good->getId(), 
            'office' => 1, //перово
            'accurate' => Goods::SEARCH_ID,            
        ];
        
        $query = $this->entityManager->getRepository(Goods::class)
                        ->presence($params);
        
        $rests = $query->getResult();
        $result = [];
        foreach ($rests as $rest){
//            var_dump($rest); exit;
            $result[] = [
                'office' => $rest['officeName'].' ('.$rest['companyName'].')',
                'officeId' => $rest['officeId'],
                'officeAplId' => $rest['officeAplId'],
                'rest' => $rest['rest'],
                'reserve' => $rest['reserve'],
                'delivery' => $rest['delivery'],
                'vozvrat' => $rest['vozvrat'],
                'available' => $rest['available'],                                       
            ];
        }                        
        
        return $result;
    }
    
    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
//        ini_set('memory_limit', '2048M');
        
        $code = $producerStr = $unknownProducer = null;
        $result = [];
        
        if (is_array($params)){
            $paramsArray = $params;
        } else {
            $paramsArray = $params->toArray();
        }   
        
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
                        $data['in_store'] = $this->inStore($good);
                        
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
        if (!empty($paramsArray['stat'])){
            $fasadeStat = $this->entityManager->getRepository(Goods::class)
                    ->fasadeStat();
            
            $result = [
                'fasade' => $fasadeStat,
            ];
            
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
        ini_set('memory_limit', '512M');
        
        if (is_object($data)){
//            var_dump($data[0]['fasade'], $data[0]['fasade_loaded']); exit;
            
            $i = 0;
            if (!empty($data[0]['ean'])){
                foreach ($data[0]['ean'] as $eanData){
//                    {"producer":"TRW", "article":"DF4183", "EAN":3322937320103}
                    if (!empty($eanData['ean'])){
                        if (!empty($eanData['article']) && !empty($eanData['producer'])){
                            $goods = $this->fetchAll($eanData);
                            if (!empty($goods)){
                                $goodId = $goods[0]['id'];
                                $ean = $eanData['ean'];
//                                var_dump($goodId, $ean); exit;
                                $this->entityManager->getRepository(Oem::class)
                                        ->addEanAsOe($goodId, $ean);
                                $i++;
                            }    
                        }
                    }    
                }                
            }
            
            if (!empty($data[0]['fasade_loaded'])){
                foreach ($data[0]['fasade_loaded'] as $goodId){

                    $good = $this->entityManager->getRepository(Goods::class)
                            ->find($goodId);

                    if ($good){
    //                    if ($good->getAvailable() === Goods::AVAILABLE_FALSE){
    //                        $nextFasade = Goods::FASADE_EX_FULL_LOADED;
    //                    } else {
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
                                    if ($good->getCars()->count()){
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
    //                    }    
        //                var_dump($nextFasade, $goodId); exit;
                        $this->entityManager->getConnection()->update('goods', ['fasade_ex' => $nextFasade], ['id' => $good->getId()]);                        
                    }    
                    $i++;
                } 
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
