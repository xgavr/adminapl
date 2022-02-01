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
    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $commentManager) 
    {
        $this->entityManager = $entityManager;
        $this->commentManager = $commentManager;
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
        $params = [];
        
        $query = $this->entityManager->getRepository(Comment::class)
                    ->queryAllComments($params);            
        
        $total = $this->entityManager->getRepository(Comment::class)
                ->count([]);
        
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
    
}
