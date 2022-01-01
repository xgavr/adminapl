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
use Application\Entity\Phone;
use User\Filter\PhoneFilter;


class TillController extends AbstractActionController
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
        $offices = $this->entityManager->getRepository(Office::class)
                ->findBy([]);
        $cashes = $this->entityManager->getRepository(Cash::class)
                ->findBy(['status' => Cash::STATUS_ACTIVE, 'office' => $currentUser->getOffice()->getId()]);
        return new ViewModel([
            'cashes' =>  $cashes,
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
        $cashId = $this->params()->fromQuery('cash');
        $kind = $this->params()->fromQuery('kind');
        $dateOper = $this->params()->fromQuery('dateOper');
        
        $params = [
            'sort' => $sort, 'order' => $order, 
            'cashId' => $cashId, 'kind' => $kind,
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
    
    public function phoneContactAction()
    {
        $phoneStr = $this->params()->fromQuery('phone');
        if (empty($phoneStr)) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        $filter = new PhoneFilter();
        $phone = $this->entityManager->getRepository(Phone::class)
                ->findOneByName($filter->filter($phoneStr));
        
        if ($phone == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        return new JsonModel([
            'name' => $phone->getContact()->getName(),
        ]);                  
    }

    public function inKindsAction()
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
        
        $kinds = $this->cashManager->inKinds($cash);
        foreach ($kinds as $key=>$value){
            $result[$key] = [
                'id' => $key,
                'name' => $value,                
            ];
        }
        
        return new JsonModel([
            'rows' => $result,
        ]);                  
    }
    
    public function outKindsAction()
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
        
        $kinds = $this->cashManager->outKinds($cash);
        
        foreach ($kinds as $key=>$value){
            $result[$key] = [
                'id' => $key,
                'name' => $value,                
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
    
    public function cashBalanceAction()
    {
        $cashId = (int)$this->params()->fromRoute('id', -1);
        $dateOper = $this->params()->fromQuery('dateOper');
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
        
        $balance = null;
        if ($cash->getRestStatus() == Cash::REST_ACTIVE){
            $balance = $this->entityManager->getRepository(Cash::class)
                    ->cashBalance($cash->getId(), $dateOper);
        }    
        
        return new JsonModel([
            'balance' => $balance,
        ]);                  
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
        $this->cashManager->cashFormOptions($form, $cashDoc);
        
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
                var_dump($data);
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
        $this->cashManager->cashFormOptions($form, $cashDoc);
        
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
