<?php
namespace Api\V1\Rest\ApiOrderInfo;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Application\Entity\Order;
use Application\Entity\Bid;
use Application\Entity\Supplier;
use Application\Entity\Goods;

class ApiOrderInfoResource extends AbstractResourceListener
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
     * SupplierOrder manager.
     * @var \Application\Service\SupplierOrderManager
     */
    private $supplierOrderManager;
    
    public function __construct($entityManager, $orderManager, $goodManager, $supplierOrderManager) 
    {
       $this->entityManager = $entityManager;
       $this->orderManager = $orderManager;
       $this->goodManager = $goodManager;
       $this->supplierOrderManager = $supplierOrderManager;
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
            $order = null;
            if (!empty($data->orderId)){
                $order = $this->entityManager->getRepository(Order::class)
                            ->find($data->orderId);
            }
            if (!empty($data->orderAplId) && !$order){
                $order = $this->entityManager->getRepository(Order::class)
                            ->findOneBy(['aplId' => $data->orderId]);
            }
            if ($order){
                
                $this->supplierOrderManager->removeByOrder($order);

                foreach ($data->Data as $supplierOrder){
                    $supplier = $this->entityManager->getRepository(Supplier::class)
                            ->findOneBy(['aplId' => $supplierOrder->supplierAplId]);
                    
                    $good = null;
                    
                    if (!empty($supplierOrder->goodId)){
                        $good = $this->entityManager->getRepository(Goods::class)
                                ->find($supplierOrder->goodId);
                    }    
                    if (!empty($supplierOrder->goodAplId && !$good)){
                        $good = $this->entityManager->getRepository(Goods::class)
                                ->findOneBy($supplierOrder->goodAplId);
                    }    

                    if ($supplier && $good){
                        $orderData = [
                            'good' => $good,
                            'order' => $order,
                            'supplier' => $supplier,
                            'quantity' => $data->Data->quantity,
                            'statusOrder' => $supplierOrder->status,
                        ];
                        $this->supplierOrderManager->addSupplierOrder($orderData);
                    }    
                }    
                
                return ['result' => 'ok'];
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
     * @return ApiProblem|Order
     */
    public function fetch($id)
    {
        if (is_numeric($id)){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($id);
            if ($order){
                $orderInfo = $order->toArray();
                
                $lastCommentInfo = [];
                
                $lastComment = $this->orderManager->lastComment($order);
                
                if ($lastComment){
                    $lastCommentInfo = [
                        'comment' => $lastComment->getComment(),
                        'user' => ($lastComment->getUser()) ? $lastComment->getUser()->getId():'',
                        'userName' => $lastComment->getUserName(),
                        'created' => $lastComment->getDateCreated(),
                    ];
                }
                
                $orderInfo['lastComment'] = $lastCommentInfo;
                
                $bids = $this->entityManager->getRepository(Bid::class)
                        ->findBy(['order' => $id]);
                $bidsInfo = [];
                foreach($bids as $bid){
                    $bidInfo = $bid->toLog();
                    $bidInfo['good'] = $this->goodManager->goodForApl($bid->getGood());
                    $bidsInfo[] = $bidInfo;
                }
                
                $orderInfo['goods'] = $bidsInfo;
                
                return $orderInfo;                 
            }
        }        
        return new ApiProblem(404, 'Заказ '.$id.' не найден');        
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
