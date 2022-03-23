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
use Application\Entity\Idoc;
use Application\Entity\BillGetting;
use Application\Entity\Order;
use Application\Entity\Client;
use Application\Form\CommentForm;


class BillController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер.
     * @var \Application\Service\BillManager 
     */
    private $billManager;    
    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $billManager) 
    {
        $this->entityManager = $entityManager;
        $this->billManager = $billManager;
    }    
    
    public function indexAction()
    {

        return new ViewModel([
            'billManager' => $this->billManager,
        ]);  
    }
    
    public function contentAction()
    {
        
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $params = [];
        
        $query = $this->entityManager->getRepository(Idoc::class)
                    ->queryAllIdocs($params);            
        
        $total = $this->entityManager->getRepository(Idoc::class)
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
    
    public function byMailAction()
    {
        $billGettingId = $this->params()->fromRoute('id', -1);
        // Находим существующий billGetting в базе данных.    
        $billGetting = $this->entityManager->getRepository(BillGetting::class)
                ->find($billGettingId);  
        	
        if ($billGetting == null) {
            $this->getResponse()->setStatusCode(401);
            exit;                        
        } 
        
        $result = $this->billManager->getBillByMail($billGetting);
        
        return new JsonModel(
           ['ok']
        );           
        
        exit;
        
    }
    
}
