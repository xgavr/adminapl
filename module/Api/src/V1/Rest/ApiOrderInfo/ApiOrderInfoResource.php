<?php
namespace Api\V1\Rest\ApiOrderInfo;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Application\Entity\Order;
use Application\Entity\Bid;
use Application\Entity\Supplier;
use Application\Entity\Goods;
use Application\Entity\SupplierOrder;
use Stock\Entity\Vt;
use Stock\Entity\VtGood;

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

    /**
     * AplOrderService manager.
     * @var \Admin\Service\AplOrderService
     */
    private $aplOrderService;
    
    public function __construct($entityManager, $orderManager, $goodManager, $supplierOrderManager,
            $aplOrderService) 
    {
       $this->entityManager = $entityManager;
       $this->orderManager = $orderManager;
       $this->goodManager = $goodManager;
       $this->supplierOrderManager = $supplierOrderManager;
       $this->aplOrderService = $aplOrderService;
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
            $order = $supplier = null;
            $message = '';

            if (empty($data->orderId) && empty($data->orderAplId)){
                $message .= "id или aplId заказа не передан ";                    
            }

            if (!empty($data->orderId)){
                $order = $this->entityManager->getRepository(Order::class)
                            ->find($data->orderId);
                if (!$order){
                    $message .= "Заказ id:{$data->orderId} не найден ";
                }    
            }
            if (!empty($data->orderAplId) && !$order){
                $order = $this->entityManager->getRepository(Order::class)
                            ->findOneBy(['aplId' => $data->orderAplId]);
                if (!$order){
                    $message .= "Заказ aplId:{$data->orderAplId} не найден ";
                }    
            }
            
//            if (!$order && !empty($data->orderAplId)){
//                $this->aplOrderService->unloadOrder(0, $data->orderAplId);
//                $order = $this->entityManager->getRepository(Order::class)
//                            ->findOneBy(['aplId' => $data->orderAplId]);
//            }
            
            if ($order){
                
                if (empty($data->supplierId) && empty($data->supplierAplId)){
                    $message .= "id или aplId поставщика не передан ";                    
                }

                if (!empty($data->supplierId)){
                    $supplier = $this->entityManager->getRepository(Supplier::class)
                            ->find($data->supplierId);
                    if (!$supplier){
                        $message .= "Поставщик id:{$data->supplierId} не найден ";
                    }    
                }    
                if (!empty($data->supplierAplId)){
                    $supplier = $this->entityManager->getRepository(Supplier::class)
                            ->findOneBy(['aplId' => $data->supplierAplId]);
                    if (!$supplier){
                        $message .= "Поставщик aplId:{$data->supplierAplId} не найден ";
                    }    
                }    
                    
                $good = null;
                
                if (empty($data->goodId) && empty($data->goodAplId)){
                    $message .= "id или aplId товара не передан ";                    
                }
                    
                if (!empty($data->goodId)){
                    $good = $this->entityManager->getRepository(Goods::class)
                            ->find($data->goodId);
                    if (!$good){
                        $message .= "Товар id:{$data->goodId} не найден ";
                    }    
                }    
                if (!empty($data->goodAplId) && !$good){
                    $good = $this->entityManager->getRepository(Goods::class)
                            ->findOneBy($data->goodAplId);
                    if (!$good){
                        $message .= "Товар aplId:{$data->goodAplId} не найден ";
                    }    
                }    
                
                if ($data->status == SupplierOrder::STATUS_ORDER_NEW){
                    $message .= "Товар не заказан ";
                }
                
                if ($supplier && $good && $data->status == SupplierOrder::STATUS_ORDER_ORDERED){
                    $orderData = [
                        'good' => $good,
                        'order' => $order,
                        'supplier' => $supplier,
                        'quantity' => $data->quantity,
                        'statusOrder' => $data->status,
                    ];
                    $this->supplierOrderManager->addSupplierOrder($orderData);
                    return ['result' => 'ok'];
                }                    
            }
        }
        
        return new ApiProblem(404, trim($message));
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
     * Получить инфо по заказу
     * 
     * @param int $id
     */
    private function orderInfo($id)
    {
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
                $bidInfo = $bid->toArray();
                $bidInfo['good'] = $this->goodManager->goodForApl($bid->getGood());
                $bidsInfo[] = $bidInfo;
            }

            $orderInfo['goods'] = $bidsInfo;

            return $orderInfo;                 
        }        
        return new ApiProblem(404, 'Заказ '.$id.' не найден');                
    }
    
    /**
     * 
     * @param Order $order
     * @return ApiProblem
     */
    private function fasadeInfo($order)
    {
        if ($order){
            $orderInfo = $order->toArray();

            $bids = $this->entityManager->getRepository(Bid::class)
                    ->findBy(['order' => $order->getId()]);
            $bidsInfo = [];
            foreach($bids as $bid){
                $bidInfo = $bid->toArray();
                $bidInfo['good'] = $bid->getGood()->toArray();
                $bidsInfo[] = $bidInfo;
            }

            $orderInfo['goods'] = $bidsInfo;

            return $orderInfo;                 
        }        
        return new ApiProblem(404, 'Заказ не найден');                
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
            return $this->orderInfo($id);
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
        $result = [];
        if (!empty($params['orderAplId'])){
            if (is_numeric($params['orderAplId'])){
                $orders = $this->entityManager->getRepository(Order::class)
                        ->findBy(['aplId' => $params['orderAplId']]);
                foreach($orders as $order){
                    $result[] = $this->orderInfo($order->getId());
                } 
                
                return $result;
            }
        }
        if (!empty($params['fasade'])){
            
            $limit = $params['limit'] ?? 1000;
            $fasade = $params['fasade'] ?? Order::FASADE_EX_NEW;

            $result = [];
            
            $orders = $this->entityManager->getRepository(Order::class)
                    ->findForFasade(['fasade' => $fasade, 'limit' => $limit]);                        
            
            foreach ($orders as $order){                
                $this->entityManager->getConnection()->update('orders', ['fasade_ex' => Order::FASADE_EX_IN_JOB], ['id' => $order->getId()]);
            }
            
            $orders = $this->entityManager->getRepository(Order::class)
                    ->findForFasade(['fasade' => Order::FASADE_EX_IN_JOB, 'limit' => $limit]);                        
            
            foreach ($orders as $order){
                
                $data = $this->fasadeInfo($order);

                $result[] = $data;
            }
            return $result;
        }    
        
        if (!empty($params['refunds'])){
            
            $limit = $params['limit'] ?? 1000;
            $fasade = $params['fasade'] ?? Vt::FASADE_EX_NEW;

            $result = [];
            
            $vts = $this->entityManager->getRepository(Vt::class)
                    ->findBy(['fasadeEx' => $fasade, null, $limit]);                        
            
            foreach ($vts as $vt){                
                $this->entityManager->getConnection()->update('vt', ['fasade_ex' => Vt::FASADE_EX_IN_JOB], ['id' => $vt->getId()]);
            }
            
            $vts = $this->entityManager->getRepository(Vt::class)
                    ->findBy(['fasadeEx' => Vt::FASADE_EX_IN_JOB, 'limit' => $limit]);                        
            
            foreach ($vts as $vt){
                
                $data = $vt->toArray();

                $result[] = $data;
            }
            return $result;
        }         
        return new ApiProblem(404, 'Заказы не найдены');        
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
            if (!empty($data[0]['fasade_loaded'])){
                foreach ($data[0]['fasade_loaded'] as $orderId){

                    $order = $this->entityManager->getRepository(Order::class)
                            ->find($orderId);

                    if ($order){
                        if ($order->getFasadeEx() === Order::FASADE_EX_IN_JOB){
                            $this->entityManager->getConnection()->update('orders', ['fasade_ex' => Order::FASADE_EX_FULL_LOADED], ['id' => $order->getId()]);
                        }                        
                    }    
                    $i++;
                } 
            }    
            
            if (!empty($data[0]['refunds_loaded'])){
                foreach ($data[0]['refunds_loaded'] as $vtId){

                    $vt = $this->entityManager->getRepository(Vt::class)
                            ->find($vtId);

                    if ($vt){
                        if ($vt->getFasadeEx() === Vt::FASADE_EX_IN_JOB){
                            $this->entityManager->getConnection()->update('vt', ['fasade_ex' => Vt::FASADE_EX_FULL_LOADED], ['id' => $vt->getId()]);
                        }                        
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
