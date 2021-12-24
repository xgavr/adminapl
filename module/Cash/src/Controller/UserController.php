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
        $users = $this->entityManager->getRepository(User::class)
                ->findBy(['status' => User::STATUS_ACTIVE]);
        $offices = $this->entityManager->getRepository(Office::class)
                ->findBy(['status' => Office::STATUS_ACTIVE]);
        return new ViewModel([
            'users' =>  $users,
            'offices' =>  $offices,
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
        $dateOper = $this->params()->fromQuery('dateOper');
        
        $params = [
            'sort' => $sort, 'order' => $order, 
            'userId' => $userId, 'kind' => $kind,
        ];
        
        $query = $this->entityManager->getRepository(CashDoc::class)
                        ->findAllUserDoc($dateOper, $params);
        
        $total = $this->entityManager->getRepository(CashDoc::class)
                        ->findAllUserDocTotal($dateOper, $params);
                
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
        
        $cashes = $this->entityManager->getRepository(Cash::class)
                ->findBy(['office' => $office->getId(), 'status' => Cash::STATUS_ACTIVE]);
        
        foreach ($cashes as $cash){
            $result[$cash->getId()] = [
                'id' => $cash->getId(),
                'name' => $cash->getName(),                
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
        
        $balance = $this->entityManager->getRepository(Cash::class)
                ->userBalance($user->getId(), $dateOper);
        
        return new JsonModel([
            'balance' => $balance,
        ]);                  
    }
    
   
    public function editUserInAction()
    {
        $cashDocId = (int)$this->params()->fromRoute('id', -1);
        
        $cashDoc = null;
        
        if ($cashDocId > 0){
            $cashDoc = $this->entityManager->getRepository(CashDoc::class)
                    ->find($cashDocId);
        }    
        
        $form = new UserInForm($this->entityManager);
        $this->cashManager->cashFormOptions($form);
        
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
        ]);        
    }        
    
    public function editUserOutAction()
    {
        $cashDocId = (int)$this->params()->fromRoute('id', -1);
        
        $cashDoc = null;
        
        if ($cashDocId > 0){
            $cashDoc = $this->entityManager->getRepository(CashDoc::class)
                    ->find($cashDocId);
        }    
        
        $form = new UserOutForm($this->entityManager);
        $this->cashManager->cashFormOptions($form);
        
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
        ]);        
    }        
}
