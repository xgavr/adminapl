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
use Cash\Form\CashForm;
use Cash\Form\CashInForm;
use Cash\Form\CashOutForm;
use Company\Entity\Office;
use Application\Entity\Supplier;
use Company\Entity\Cost;
use User\Entity\User;
use Company\Entity\Legal;


class IndexController extends AbstractActionController
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
        $cashes = $this->entityManager->getRepository(Cash::class)
                ->findAll();
        $offices = $this->entityManager->getRepository(Office::class)
                ->findAll();
        return new ViewModel([
            'cashes' =>  $cashes,
            'offices' =>  $offices,
        ]);
    }
    
    public function contentAction()
    {
        $cashId = (int)$this->params()->fromRoute('id', -1);
        $cash = null;
        
        if ($cashId > 0){
            $cash = $this->entityManager->getRepository(Cash::class)
                    ->find($cashId);
        }    
        
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        $officeId = $this->params()->fromQuery('office');
        $cashId = $this->params()->fromQuery('cash');
        $dateOper = $this->params()->fromQuery('dateOper');
        
        $params = [
            'sort' => $sort, 'order' => $order, 
            'cashId' => $cashId,
        ];
        
        $query = $this->entityManager->getRepository(CashDoc::class)
                        ->findAllCashDoc($dateOper, $params);
        
        $total = $this->entityManager->getRepository(CashDoc::class)
                        ->findAllCashDocTotal($dateOper, $params);
        
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

    public function editCashAction()
    {
        $cashId = (int)$this->params()->fromRoute('id', -1);
        $officeId = (int) $this->params()->fromQuery('office');
        
        $cash = $office = null;
        
        if ($cashId > 0){
            $cash = $this->entityManager->getRepository(Cash::class)
                    ->find($cashId);
            $office = $cash->getOffice();
        }    
        
        if ($officeId > 0){
            $office = $this->entityManager->getRepository(Office::class)
                    ->find($officeId);
        }    
        
        $form = new CashForm($this->entityManager);
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();            
            $form->setData($data);

            if ($form->isValid()) {
                
                if ($cash){
                    $this->cashManager->updateCash($cash, $data);
                } else {
                    if ($office){
                        $cash = $this->cashManager->addCash($office, $data);
                    } else {    
                        throw new \Exception('Офис не указан');
                    }    
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
                //var_dump($form->getMessages());
            }
        } else {
            if ($cash){
                $data = $cash->toArray();
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'cash' => $cash,
            'office' => $office,
        ]);        
    }        
    
    public function legalsAction()
    {
        $cashId = (int)$this->params()->fromRoute('id', -1);
        if ($cashId<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $cash = $this->entityManager->getRepository(Cash::class)
                ->find($cashId);
        
        if ($cash == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $legals = $this->entityManager->getRepository(Legal::class)
                ->formOfficeLegals(['officeId' => $cash->getOffice()->getId()]);
        
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
    
    protected function cashFormOptions($form)
    {
        if ($form->has('cost')){
            $costs = $this->entityManager->getRepository(Cost::class)
                    ->findBy(['status' => Supplier::STATUS_ACTIVE], ['name' => 'ASC']);
            $costList = ['--не выбран--'];
            foreach ($costs as $cost) {
                $costList[$cost->getId()] = $cost->getName();
            }
            $form->get('cost')->setValueOptions($costList);
        }    

        $suppliers = $this->entityManager->getRepository(Supplier::class)
                ->findBy(['status' => Supplier::STATUS_ACTIVE], ['name' => 'ASC']);
        $supplierList = ['--не выбран--'];
        foreach ($suppliers as $supplier) {
            $supplierList[$supplier->getId()] = $supplier->getName();
        }
        $form->get('supplier')->setValueOptions($supplierList);
        
        $users = $this->entityManager->getRepository(User::class)
                ->findBy(['status' => User::STATUS_ACTIVE], ['fullName' => 'ASC']);
        $userList = ['--не выбран--'];
        foreach ($users as $user) {
            $userList[$user->getId()] = $user->getFullName();
        }
        $form->get('userRefill')->setValueOptions($userList);

        $cashes = $this->entityManager->getRepository(Cash::class)
                ->findBy(['status' => Cash::STATUS_ACTIVE], ['name' => 'ASC']);
        foreach ($cashes as $cash) {
            $cashList[$cash->getId()] = $cash->getName();
        }
        $form->get('cash')->setValueOptions($cashList);
        $form->get('cashRefill')->setValueOptions($cashList);
        
        $officeId = 1;
        if ($cashDoc){
            $officeId = $cashDoc->getCash()->getOffice()->getId();
        }
        $legals = $this->entityManager->getRepository(Legal::class)
                ->formOfficeLegals(['officeId' => $officeId]);        
        foreach ($legals as $legal){
            $companyList[$legal->getId()] = $legal->getName();
        }            
        $form->get('company')->setValueOptions($companyList);

    }
   
    public function editCashInAction()
    {
        $cashDocId = (int)$this->params()->fromRoute('id', -1);
        
        $cashDoc = null;
        
        if ($cashDocId > 0){
            $cashDoc = $this->entityManager->getRepository(CashDoc::class)
                    ->find($cashDocId);
        }    
        
        $form = new CashInForm($this->entityManager);
        $this->cashFormOptions($form);
        
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
    
    public function editCashOutAction()
    {
        $cashDocId = (int)$this->params()->fromRoute('id', -1);
        
        $cashDoc = null;
        
        if ($cashDocId > 0){
            $cashDoc = $this->entityManager->getRepository(CashDoc::class)
                    ->find($cashDocId);
        }    
        
        $form = new CashOutForm($this->entityManager);
        $this->cashFormOptions($form);
        
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
