<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Entity\Comment;
use Application\Entity\Order;
use Application\Entity\Client;
use Application\Form\CommentForm;


class CommentController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер.
     * @var \Application\Service\CommentManager 
     */
    private $commentManager;    
    
    /**
     * Менеджер.
     * @var \Application\Service\OrderManager 
     */
    private $orderManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $commentManager, $orderManager) 
    {
        $this->entityManager = $entityManager;
        $this->commentManager = $commentManager;
        $this->orderManager = $orderManager;
    }    
    
    public function indexAction()
    {

        return new ViewModel([
            'commentManager' => $this->commentManager,
        ]);  
    }
    
    public function contentAction()
    {
        
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $clientId = $this->params()->fromQuery('client');
        $params = ['clientId' => $clientId];
        
        $query = $this->entityManager->getRepository(Comment::class)
                    ->queryAllComments($params);   
        
        $total = count($query->getResult(2));
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);         
    }

    public function editFormAction()
    {
        $clientId = (int)$this->params()->fromQuery('client', -1);
        $orderId = (int)$this->params()->fromQuery('order', -1);
        $commentId = (int)$this->params()->fromRoute('id', -1);

        $comment = $order = $client = NULL;
        
        if ($commentId>0) {
            $comment = $this->entityManager->getRepository(Comment::class)
                    ->find($commentId);
        }        
        if ($orderId>0) {
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
        }        
        if ($clientId>0) {
            $client = $this->entityManager->getRepository(Client::class)
                    ->find($clientId);
        }        
        
        $form = new CommentForm();

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {

                if ($comment){
                    $this->commentManager->updateComment($comment, $data);                    
                } else {
                    if ($order){
                        $this->commentManager->addOrderComment($order, $data);
                    } elseif ($client){
                        $this->commentManager->addClientComment($client, $data);                        
                    }    
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($comment){
                $data = [
                    'comment' => $comment->getComment(),  
                ];
                $form->setData($data);
            }  
        }    
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'comment' => $comment,
            'client' => $client,
            'order' => $order,
            'currentUser' => $this->commentManager->currentUser(),
        ]);                
        
    }

    public function editLocalFormAction()
    {        
        $orderId = (int)$this->params()->fromRoute('id', -1);  
        $order = null;
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
        }    
        
        $form = new CommentForm();

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {

                return new JsonModel(
                   ['ok']
                );           
            }
        }    
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'order' => $order,
            'currentUser' => $this->commentManager->currentUser(),
        ]);                
        
    }
    
    public function orderFormAction()
    {
        $orderId = (int)$this->params()->fromRoute('id', -1);
        
        if ($orderId <= 0){
            $this->getResponse()->setStatusCode(404);
            return;
        }    
        
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);
        
        if ($order == null){
            $this->getResponse()->setStatusCode(404);
            return;            
        }
        $comments = $this->entityManager->getRepository(Comment::class)
                ->findBy(['order' => $order->getId()], ['id' => 'DESC']);
        
        $this->layout()->setTemplate('layout/terminal');
        
        return new ViewModel([
            'order' => $order,
            'comments' => $comments,
        ]);                
    }    

    public function viewAction() 
    {       
        $commentId = (int)$this->params()->fromRoute('id', -1);

        if ($commentId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $comment = $this->entityManager->getRepository(Comment::class)
                ->find($commentId);
        
        if ($comment == null) {
            return $this->redirect()->toRoute('comment');
        }        
        // Render the view template.
        return new ViewModel([
            'comment' => $comment,
            'commentManager' => $this->commentManager,
        ]);
    }      
    
    public function addOrderCommentAction()
    {
        $orderId = (int)$this->params()->fromRoute('id', -1);
        
        if ($orderId <= 0){
            $this->getResponse()->setStatusCode(404);
            return;
        }    
        
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);
        
        if ($order == null){
            $this->getResponse()->setStatusCode(404);
            return;            
        }

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            if ($order){
                $comment = $this->commentManager->addOrderComment($order, $data);
                $dependInfo = $this->orderManager->updateDependInfo($order, true);
                $commentsQuery = $this->entityManager->getRepository(Comment::class)
                        ->orderComments($order);
            }
        }    
        
        return new JsonModel([
            'commentId' => $comment->getId(),
            'comments' => $commentsQuery->getResult(2),
            'dependInfo' => $dependInfo,
        ]);                   
    }
    
    public function updateAction()
    {
        $commentId = (int)$this->params()->fromRoute('id', -1);
        
        $comment = $comments = null;
        
        if ($commentId > 0){
            $comment = $this->entityManager->getRepository(Comment::class)
                    ->find($commentId);
        }    
                
        if ($this->getRequest()->isPost()) {            
            $data = $this->params()->fromPost();
            if (!$comment && !empty($data['pk'])){
                $comment = $this->entityManager->getRepository(Comment::class)
                        ->find($data['pk']);                
            }
            $upd['comment'] = $data['value'];
            if ($comment){
                $comment = $this->commentManager->updateComment($comment, $upd);
                $dependInfo = $this->orderManager->updateDependInfo($comment->getOrder(), true);
                $commentsQuery = $this->entityManager->getRepository(Comment::class)
                        ->orderComments($comment->getOrder());
                
                $comments = $commentsQuery->getResult(2);
            }
        }    
        
        return new JsonModel([
            'comments' => $comments,
            'dependInfo' => $dependInfo,
        ]);                   
    }    
}
