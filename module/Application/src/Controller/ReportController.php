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
use Company\Entity\Office;
use User\Entity\User;
use Cash\Entity\CashDoc;


class ReportController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер.
     * @var \Application\Service\ReportManager 
     */
    private $reportManager;    
    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $reportManager) 
    {
        $this->entityManager = $entityManager;
        $this->reportManager = $reportManager;
    }    
    
    public function indexAction()
    {
        $offices = $this->entityManager->getRepository(Office::class)
                ->findBy([]);
        
        return new ViewModel([
            'offices' => $offices,
        ]);  
    }
    
    public function revenueByYearsAction()
    {
        
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $office = $this->params()->fromQuery('office');
        $year = $this->params()->fromQuery('year');
        $month = $this->params()->fromQuery('month');
        $base = $this->params()->fromQuery('base');
        
        $params = ['office' => $office, 'year' => $year, 'month' => $month,
            'base' => $base];
        
        $query = $this->entityManager->getRepository(Order::class)
                    ->revenueByYears($params);            
        
        $total = count($query->getResult());
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult();
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);         
    }

    public function accountantAction()
    {
        $offices = $this->entityManager->getRepository(Office::class)
                ->findBy([]);
        
        $users = $this->entityManager->getRepository(User::class)
                ->findBy([]);

        return new ViewModel([
            'offices' => $offices,
            'users' => $users,
        ]);  
    }

    public function accountantContentAction()
    {
        
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $office = $this->params()->fromQuery('office');
        $user = $this->params()->fromQuery('user');
        $year = $this->params()->fromQuery('year');
        $month = $this->params()->fromQuery('month');
        $base = $this->params()->fromQuery('base');
        $dateStart = $this->params()->fromQuery('dateStart');
        $period = $this->params()->fromQuery('period', 'date');

        $startDate = '2012-01-01';
        $endDate = '2199-01-01';
        if (!empty($dateStart)){
            $startDate = date('Y-m-d', strtotime($dateStart));
            $endDate = $startDate;
            if ($period == 'week'){
                $endDate = date('Y-m-d', strtotime('+ 1 week - 1 day', strtotime($startDate)));
            }    
            if ($period == 'month'){
                $endDate = date('Y-m-d', strtotime('+ 1 month - 1 day', strtotime($startDate)));
            }    
            if ($period == 'number'){
                $startDate = $dateStart.'-01-01';
                $endDate = date('Y-m-d', strtotime('+ 1 year - 1 day', strtotime($startDate)));
            }    
        }    
        
        $params = ['office' => $office, 'year' => $year, 'month' => $month,
            'base' => $base, 'user' => $user];
        
        $query = $this->entityManager->getRepository(CashDoc::class)
                    ->periodTransaction($startDate, $endDate, $period, $params);            
        
        $total = count($query->getResult());
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult();
        
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
