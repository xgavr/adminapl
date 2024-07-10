<?php
namespace Api\V1\Rest\ApiLanding;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Application\Entity\Order;
use Company\Entity\Office;

class ApiLandingResource extends AbstractResourceListener
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
     * Client manager.
     * @var \Application\Service\ClientManager
     */
    private $clientManager;

    public function __construct($entityManager, $orderManager, $clientManager) 
    {
       $this->entityManager = $entityManager;
       $this->orderManager = $orderManager;
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
        $contact = $this->orderManager->findContactByOrderData([
            'phone' => $data->phone,
            'email' => (empty($data->email)) ? null:$data->email,
            'name' => (empty($data->name)) ? 'Покупатель':$data->name,
        ]);
        
        if ($contact){
            $office = $this->entityManager->getRepository(Office::class)
                    ->findDefaultOffice();
            
            if (is_numeric($data->office)){
                $office = $this->entityManager->getRepository(Office::class)
                        ->find($data->office);
            }
            
            $order = $this->orderManager->addNewOrder($office, $contact, [
                'mode' => ($data->mode) ? $data->mode:Order::MODE_LANDING,
                'info' => (empty($data->need)) ? null:$data->need,
                'address' => (empty($data->address)) ? null:$data->address,
                'geo' => (empty($data->geo)) ? null:$data->geo,                
                'vin' => (empty($data->vin)) ? null:$data->vin,
                'user' => $data->user,
            ]);
            
            if ($order && !empty($data->goods)){
                $i = 1;
                foreach ($data->goods as $good){
//                    var_dump($good); exit;
                    $bid = [
                        'good' => $good['id'],
                        'price' => $good['price'],
                        'num' => $good['num'],
                        'rowNo' => $i,
                    ];
                    $i++;
                    $this->orderManager->addNewBid($order, $bid, false);
                }                    
            }
            
            $this->orderManager->updateDependInfo($order);
            $this->entityManager->flush();

            $this->orderManager->updOrderTotal($order);
            
            return ['result' => 'Z'.$order->getId()];
//            return ['result' => $data];            
        }    
        
        return new ApiProblem(404, 'Заказ не создан :(');
//        return new ApiProblem(405, 'The POST method has not been defined');
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
        return new ApiProblem(405, 'The GET method has not been defined for individual resources');
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
