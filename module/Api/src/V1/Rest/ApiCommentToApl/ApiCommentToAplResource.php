<?php
namespace Api\V1\Rest\ApiCommentToApl;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Application\Entity\Order;
use Application\Entity\Comment;

class ApiCommentToAplResource extends AbstractResourceListener
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Comment manager.
     * @var \Application\Service\ReportManager
     */
    private $commentManager;
    
    /**
     * Apl order manager.
     * @var \Admin\Service\AplOrderService
     */
    private $aplOrderService;    

    public function __construct($entityManager, $commentManager, $aplOrderService) 
    {
       $this->entityManager = $entityManager;       
       $this->commentManager = $commentManager;       
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
            if (!empty($data->order) && !empty($data->message)){
                if (is_numeric($data->order)){
                    $order = $this->entityManager->getRepository(Order::class)
                            ->findOneBy(['aplId' => $data->order]);
                    if ($order){
                        $comment = new Comment();
                        $comment->setAplId(null);
                        $comment->setDateCreated(date('Y-m-d H:i:s'));
                        $comment->setUser(null);
                        $comment->setOrder($order);
                        $comment->setClient($order->getContact()->getClient());
                        $comment->setComment($data->message);
                        $comment->setStatusEx(Comment::STATUS_EX_NEW);

                        $this->entityManager->persist($comment);
                        $this->entityManager->flush();
                        $this->entityManager->refresh($comment); 
                        
                        $result = $this->aplOrderService->sendComment($comment);
                        
                        return ['result' => $result];
                    } else {
                        $post = [
                            'parent' => $data->order,
                            'type' => 'Orders',
                            'comment' => $data->message,
                            'sf' => 0,
                        ];
                        
                        $result = $this->aplOrderService->sendComment(null, $post);
                        
                        return ['result' => $result];
                    }
                }
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
