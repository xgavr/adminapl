<?php
namespace Api\V1\Rest\ApiClientInfo;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use User\Filter\PhoneFilter;
use Application\Entity\Phone;
use Application\Entity\Client;
use Application\Entity\Order;
use Application\Entity\Bid;
use Application\Entity\Goods;

class ApiClientInfoResource extends AbstractResourceListener
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Order manager.
     * @var \Application\Service\OrderManager
     */
    private $orderManager;

    /**
     * Goods manager.
     * @var \Application\Service\GoodsManager
     */
    private $goodManager;
    
    /**
     * Client manager.
     * @var \Application\Service\ClientManager
     */
    private $clientManager;
    
    public function __construct($entityManager, $orderManager, $goodManager, $clientManager) 
    {
       $this->entityManager = $entityManager;
       $this->orderManager = $orderManager;
       $this->goodManager = $goodManager;
       $this->clientManager = $clientManager;
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
     * Информация о клиенте
     * 
     * @param Client $client
     * @param array $params
     * @return array
     */
    private function clientInfo($client, $params=null)
    {
        $result = $client->toArray();
        
        $ordersQuery = $this->entityManager->getRepository(Order::class)
                ->findClientOrder($client, $params);
        
        $orders = $ordersQuery->getResult();
        
        foreach ($orders as $order){
            
            $orderInfo = $order->toArray();
            
            $bids = $this->entityManager->getRepository(Bid::class)
                    ->findBy(['order' => $order->getId()]);
            
            $bidsInfo = [];
            
            foreach($bids as $bid){
                $bidsInfo[] = $bid->toArray();
            }

            $orderInfo['goods'] = $bidsInfo;
            
            $result['orders'][] = $orderInfo;
        }
        
        return $result;
    }
    
    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        $client = $this->entityManager->getRepository(Client::class)
                ->find($id);
        
        if ($client){
            return $this->clientInfo($client, ['orderStatus' => Order::STATUS_SHIPPED]);
        }

        return new ApiProblem(404, "Клиент с идентификаторм $id не найден");
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
        $phone = $client = null;
        $result = [];
        
        $paramsArray = $params->toArray();
        if (!empty($paramsArray['phone'])){
            $filter = new PhoneFilter();
            $phoneStr = $filter->filter($paramsArray['phone']);
            if ($phoneStr){
                $phone = $this->entityManager->getRepository(Phone::class)
                        ->findOneBy(['name' => $phoneStr]);
            }                        
        }
        
        if ($phone){
            $client = $phone->getContact()->getClient();
        }
        
        if ($client){
            $result[] = $this->clientInfo($client, $paramsArray);
//            var_dump($result); exit;
            return $result;
        }
        
        return new ApiProblem(404, 'Ничего не нашлось :(');
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
