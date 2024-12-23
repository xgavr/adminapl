<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Cash\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Cash\Entity\Cash;
use Cash\Entity\CashDoc;
use Cash\Form\UserInForm;
use Cash\Form\UserOutForm;
use Company\Entity\Office;
use User\Entity\User;
use Company\Entity\Legal;
use Cash\Entity\UserTransaction;


class UserController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    /**
     * Request manager.
     * @var \Cash\Service\CashManager
     */
    private $cashManager;
        
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $cashManager) 
    {
       $this->entityManager = $entityManager;
       $this->cashManager = $cashManager;
    }

    
    public function indexAction()
    {
        $currentUser = $this->cashManager->currentUser();
        $users = $this->entityManager->getRepository(User::class)
                ->findBy(['office' => $currentUser->getOffice()->getId()], ['status' => 'ASC', 'fullName' => 'ASC']);
        $offices = $this->entityManager->getRepository(Office::class)
                ->findBy([], ['status' => 'ASC']);
        return new ViewModel([
            'users' =>  $users,
            'offices' =>  $offices,
            'currentUser' => $currentUser,
        ]);
    }
    
    public function contentAction()
    {       
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        $officeId = $this->params()->fromQuery('office');
        $userId = $this->params()->fromQuery('user');
        $kind = $this->params()->fromQuery('kind');
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
//        var_dump($startDate, $endDate);
        $params = [
            'sort' => $sort, 'order' => $order, 'officeId' => $officeId,
            'userId' => $userId, 'kind' => $kind,
        ];
        
        $query = $this->entityManager->getRepository(CashDoc::class)
                        ->findAllUserDoc($startDate, $endDate, $params);
        
        $total = $this->entityManager->getRepository(CashDoc::class)
                        ->findAllUserDocTotal($startDate, $endDate, $params);
                
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);
        if ($userId){
            foreach ($result as $key=>$value){
//                var_dump($value);
                $result[$key]['rest'] = $this->entityManager->getRepository(UserTransaction::class)
                    ->accountantRest($userId, $value['docStamp']);
            }
        }    
        
        return new JsonModel([
            'inTotal' => empty($total['amountIn']) ? 0:$total['amountIn'],
            'outTotal' => empty($total['amountOut']) ? 0:$total['amountOut'],
            'total' => empty($total['countCd']) ? 0:$total['countCd'],
            'rows' => $result,
        ]);                  
    }

    
    public function legalsAction()
    {
        $userId = (int)$this->params()->fromRoute('id', -1);
        if ($userId<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $user = $this->entityManager->getRepository(User::class)
                ->find($userId);
        
        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $legals = $this->entityManager->getRepository(Legal::class)
                ->formOfficeLegals(['officeId' => $user->getOffice()->getId()]);
        
        foreach ($legals as $legal){
            $result[$legal->getId()] = [
                'id' => $legal->getId(),
                'name' => $legal->getName(),                
            ];
        }
        
        return new JsonModel([
            'rows' => $result,
        ]);                  
    }
    
    public function officeCashesAction()
    {
        $officeId = (int)$this->params()->fromRoute('id', -1);
        if ($officeId<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $office = $this->entityManager->getRepository(Office::class)
                ->find($officeId);
        
        if ($office == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $users = $this->entityManager->getRepository(User::class)
                ->findBy(['office' => $office->getId()], ['status' => 'ASC']);
        
        foreach ($users as $user){
            $result[$user->getId()] = [
                'id' => $user->getId(),
                'name' => $user->getName(),                
            ];
        }
        
        return new JsonModel([
            'rows' => $result,
        ]);                  
    }
    
    public function userBalanceAction()
    {
        $userId = (int)$this->params()->fromRoute('id', -1);
        $dateOper = $this->params()->fromQuery('dateOper');
        
        $balance = null;
        if ($userId > 0 && is_numeric($userId)) {
            $user = $this->entityManager->getRepository(User::class)
                    ->find($userId);
            if ($user){                
                $balance = $this->entityManager->getRepository(Cash::class)
                        ->userBalance($user->getId(), $dateOper);
            }
        }
        
        return new JsonModel([
            'balance' => $balance,
        ]);                  
    }
    
   
    public function editUserInAction()
    {
        $cashDocId = (int)$this->params()->fromRoute('id', -1);
        $orderId = (int)$this->params()->fromQuery('order', -1);
        
        $cashDoc = $order = null;
        
        if ($cashDocId > 0){
            $cashDoc = $this->entityManager->getRepository(CashDoc::class)
                    ->find($cashDocId);
        }    
        
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
        }    
        
        $form = new UserInForm($this->entityManager);
        $this->cashManager->cashFormOptions($form, $cashDoc, null, null, $orderId);
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();            
            $form->setData($data);

            if ($form->isValid()) {
                
                if ($cashDoc){
                    $this->cashManager->updateCashDoc($cashDoc, $data);
                } else {
                    $cashDoc = $this->cashManager->addCashDoc($data);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
//                var_dump($form->getMessages());
            }
        } else {
            if ($cashDoc){
                $data = $cashDoc->toArray();
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'cashDoc' => $cashDoc,
            'order' => $order, 
        ]);        
    }        
    
    public function editUserOutAction()
    {
        $cashDocId = (int)$this->params()->fromRoute('id', -1);
        $userId = (int)$this->params()->fromQuery('user', -1);
        $orderId = (int)$this->params()->fromQuery('order', -1);
        
        $cashDoc = $order = null;
        
        if ($cashDocId > 0){
            $cashDoc = $this->entityManager->getRepository(CashDoc::class)
                    ->find($cashDocId);
        }    
        
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
        }    
        
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $userId = $data['user'];
        }
        
        $form = new UserOutForm($this->entityManager);
        $this->cashManager->cashFormOptions($form, $cashDoc, null, null, $orderId);
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();            
            $form->setData($data);

            if ($form->isValid()) {
                
                if ($cashDoc){
                    $this->cashManager->updateCashDoc($cashDoc, $data);
                } else {
                    $cashDoc = $this->cashManager->addCashDoc($data);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
//                var_dump($form->getMessages());
            }
        } else {
            if ($cashDoc){
                $data = $cashDoc->toArray();
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'cashDoc' => $cashDoc,
            'order' => $order,
        ]);        
    }        
}
