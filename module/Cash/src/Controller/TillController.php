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
use Cash\Form\CashInForm;
use Cash\Form\CashOutForm;
use Company\Entity\Office;
use Company\Entity\Legal;
use Application\Entity\Phone;
use User\Filter\PhoneFilter;
use Application\Entity\Order;
use Bank\Entity\Statement;
use Bank\Entity\QrCodePayment;
use User\Entity\User;


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
                ->findBy(['office' => $currentUser->getOffice()->getId()], ['status' => 'ASC']);
        return new ViewModel([
            'cashes' =>  $cashes,
            'offices' =>  $offices,
            'currentUser' => $currentUser,
            'allowDate' => $this->cashManager->getAllowDate(),
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
        $dateStart = $this->params()->fromQuery('dateStart');
        $period = $this->params()->fromQuery('period', 'date');
        
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        if ($dateStart){
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
                $endDate = $dateStart.'-12-31';
            }    
        }    
        
        $params = [
            'sort' => $sort, 'order' => $order, 'office' => $officeId,
            'cashId' => $cashId, 'kind' => $kind,
        ];
        
        $query = $this->entityManager->getRepository(CashDoc::class)
                        ->findAllCashDoc($startDate, $endDate, $params);
        
        $total = $this->entityManager->getRepository(CashDoc::class)
                        ->findAllCashDocTotal($startDate, $endDate, $params);
                
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total['countCd'],
            'inTotal' => $total['inTotal'],
            'outTotal' => $total['outTotal'],
            'rows' => $result,
        ]);                  
    }

    public function balancesAction()
    {       
        $officeId = $this->params()->fromQuery('office');
                
        $data = $this->entityManager->getRepository(Cash::class)
                    ->findBy(['office' => $officeId, 'restStatus' => Cash::REST_ACTIVE]);
        
        $result = [];
        
        foreach ($data as $row){
            if (round($row->getBalance(), 2) != 0){
                $result[] = $row->toArray();
            }    
        }                        
        
        return new JsonModel([
            'total' => count($result),
            'rows' => $result,
        ]);                  
    }

    public function userBalancesAction()
    {       
        $officeId = $this->params()->fromQuery('office');
                
        $data = $this->entityManager->getRepository(User::class)
                    ->findBy(['office' => $officeId]);
        
        $result = [];
        
        foreach ($data as $row){
            if (round($row->getBalance(), 2) != 0){
                $result[] = $row->toArray();
            }    
        }                        
        
        return new JsonModel([
            'total' => count($result),
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
        
        $clientId = null;
        if ($phone->getContact()->getClient()){
            $clientId = $phone->getContact()->getClient()->getId();
        }
        
        return new JsonModel([
            'name' => $phone->getContact()->getName(),
            'id' => $phone->getContact()->getId(),
            'clientId' => $clientId,
        ]);                  
    }

    public function orderAplAction()
    {
        $orderAplId = $this->params()->fromRoute('id', -1);
        if ($orderAplId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        $order = $this->entityManager->getRepository(Order::class)
                ->findOneByAplId($orderAplId);
        
        if ($order == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        return new JsonModel([
            'name' => $order->getContact()->getName(),
            'phone' => ($order->getContact()->getPhone()) ? $order->getContact()->getPhone()->getName():null,
            'order' => $order->getId(),
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
                ->findBy(['office' => $office->getId()], ['status' => 'ASC']);
        
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
        $dateOper = $this->params()->fromQuery('dateEnd');
        
        $balance = null;
        
        if ($cashId > 0 && is_numeric($cashId)) {
            $cash = $this->entityManager->getRepository(Cash::class)
                    ->find($cashId);
            if ($cash){                
                $balance = $this->entityManager->getRepository(Cash::class)
                        ->cashBalance($cash->getId(), $dateOper);
            }
        }
                
        return new JsonModel([
            'balance' => $balance,
        ]);                  
    }
       
    public function editCashInAction()
    {
        $cashDocId = (int)$this->params()->fromRoute('id', -1);
        $cashId = (int)$this->params()->fromQuery('cash', -1);
        $statementId = (int)$this->params()->fromQuery('statement', -1);
        $orderId = (int)$this->params()->fromQuery('order', -1);
        
        $cashDoc = $statement = $order = null;
        
        if ($cashDocId > 0){
            $cashDoc = $this->entityManager->getRepository(CashDoc::class)
                    ->find($cashDocId);
        }
        
        if ($statementId > 0){
            $statement = $this->entityManager->getRepository(Statement::class)
                    ->find($statementId);
        }    
        
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
        }    
        
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $cashId = $data['cash'];
        }
        
        $form = new CashInForm($this->entityManager);
        $this->cashManager->cashFormOptions($form, $cashDoc, $cashId, $statementId, $orderId);
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();            
            $form->setData($data);

            if ($form->isValid()) {
                
                if ($cashDoc){
                    $this->cashManager->updateCashDoc($cashDoc, $data);
                } else {
                    $cashDoc = $this->cashManager->addCashDoc($data);
                }    
                
                $out['ok'] = 1;
                if (!empty($data['order'])){
                    $query = $this->entityManager->getRepository(Order::class)
                            ->findAllOrder(['orderId' => $data['order']]);
                    $result = $query->getOneOrNullResult(2);
                    $out['row'] = $result;
                    $out['orderId'] = $data['order'];
                }    
                return new JsonModel($out);           
            } else {
//                var_dump($form->getMessages());
            }
        } else {
            if ($cashDoc){
                $data = $cashDoc->toArray();
//                var_dump($data);
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'cashDoc' => $cashDoc,
            'statement' => $statement,
            'order' => $order,
        ]);        
    }        
    
    public function editCashOutAction()
    {
        $cashDocId = (int)$this->params()->fromRoute('id', -1);
        $cashId = (int)$this->params()->fromQuery('cash', -1);
        $statementId = (int)$this->params()->fromQuery('statement', -1);
        $orderId = (int)$this->params()->fromQuery('order', -1);
        
        $cashDoc = $statement = $order = null;
        
        if ($cashDocId > 0){
            $cashDoc = $this->entityManager->getRepository(CashDoc::class)
                    ->find($cashDocId);
        }    
        
        if ($statementId > 0){
            $statement = $this->entityManager->getRepository(Statement::class)
                    ->find($statementId);
        }    
        
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
        }    
        
        $form = new CashOutForm($this->entityManager);
        $this->cashManager->cashFormOptions($form, $cashDoc, $cashId, $statementId, $orderId);
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();            
            $form->setData($data);

            if ($form->isValid()) {
                
                if ($statement){
                    $data['statement'] = $statement;
                }    
                
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
            'statement' => $statement,
            'order' => $order,
        ]);        
    }     
    
    public function statementToCashDocAction()
    {
        $statementId = $this->params()->fromRoute('id', -1);
        if ($statementId > 0){
            $statement = $this->entityManager->getRepository(Statement::class)
                    ->find($statementId);
            if ($statement){
//                $this->cashManager->cashDocFromStatement($statement);
                $this->cashManager->bindCashDocStatement($statement);

                $query = $this->entityManager->getRepository(Statement::class)
                                ->findStatement(null, null, ['statementId' => $statementId]);
                
                $result = $query->getOneOrNullResult(2);
                return new JsonModel([
                    'id' => $statement->getId(),
                    'row' => $result,
                ]);
            }
        }
        
        
        return new JsonModel(
           ['ok']
        );                   
    }

    public function statementToCashDocsAction()
    {
        $this->cashManager->bindCashDocStatements();        
        
        return new JsonModel(
           ['result' => 'ok']
        );                   
    }

    public function qrcodePaymentToCashDocAction()
    {
        $qrCodePaymentId = $this->params()->fromRoute('id', -1);
        if ($qrCodePaymentId > 0){
            $qrCodePayment = $this->entityManager->getRepository(QrCodePayment::class)
                    ->find($qrCodePaymentId);
            if ($qrCodePayment){
                $this->cashManager->cashDocFromQrCodePayment($qrCodePayment);
            }
        }
        return new JsonModel(
           ['ok']
        );                   
    }

    public function updateBalancesAction()
    {
        $this->cashManager->updateAllCashBalance();
        $this->cashManager->updateAllUserBalance();
        
        return new JsonModel(
           ['result' => 'ok']
        );                   
    }
    
    public function statusAction()
    {
        $cashDocId = $this->params()->fromRoute('id', -1);
        $status = $this->params()->fromQuery('status', CashDoc::STATUS_ACTIVE);
        
        $cashDoc = $this->entityManager->getRepository(CashDoc::class)
                ->find($cashDocId);        

        if ($cashDoc == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->cashManager->updateCashDocStatus($cashDoc, $status);
        
        $result = [];
        
        return new JsonModel(
           $result
        );           
    }     
    
    public function updateLegalAction()
    {
        $cashDocId = $this->params()->fromRoute('id', -1);
        
        $cashDoc = $this->entityManager->getRepository(CashDoc::class)
                ->find($cashDocId);        

        if ($cashDoc == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->cashManager->changeLegal($cashDoc);
        
        $result = [];
        
        return new JsonModel(
           $result
        );           
    }                
}
