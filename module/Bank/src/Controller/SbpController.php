<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Bank\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Bank\Entity\Payment;
use Bank\Form\PaymentForm;
use Company\Entity\BankAccount;
use Application\Entity\Supplier;
use Bank\Form\SuppliersPayForm;

class SbpController extends AbstractActionController
{

    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Менеджер sbp.
     * @var \Bank\Service\SbpManager
     */
    private $sbpManager;

    public function __construct($entityManager, $sbpManager) 
    {
        $this->entityManager = $entityManager;
        $this->sbpManager = $sbpManager;
    }   

    public function indexAction()
    {
        return new ViewModel([
        ]);
    }
    
    public function contentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $rs = $this->params()->fromQuery('rs');
        $year_month = $this->params()->fromQuery('month');
        $supplier = $this->params()->fromQuery('supplier');
        $status = $this->params()->fromQuery('status');
        $paymentType = $this->params()->fromQuery('paymentType');
        $offset = $this->params()->fromQuery('offset');
        $order = $this->params()->fromQuery('order', 'id');
        $sort = $this->params()->fromQuery('sort', 'DESC');
        $limit = $this->params()->fromQuery('limit');

        $year = $month = null;
        if ($year_month){
            $year = date('Y', strtotime($year_month));
            $month = date('m', strtotime($year_month));
        }        
        
        $query = $this->entityManager->getRepository(Payment::class)
                        ->findPayments(trim($q), $rs, [
                            'order' => $order,
                            'sort' => $sort,
                            'year' => $year, 'month' => $month,
                            'supplier' => $supplier,
                            'status' => $status,
                            'paymentType' => $paymentType,
                    ]);
        
//        $total = count($query->getResult());
        $total = $this->entityManager->getRepository(Payment::class)
                        ->findTotalPayments(trim($q), $rs, [
                            'year' => $year, 'month' => $month,
                            'supplier' => $supplier,
                            'status' => $status,
                            'paymentType' => $paymentType,
                            'count' => true,
                        ]);
        
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
        $paymentId = (int)$this->params()->fromRoute('id', -1);
        $copy = $this->params()->fromQuery('copy');
        
        $payment = null;
        if ($paymentId > 0){
            $payment = $this->entityManager->getRepository(Payment::class)
                    ->find($paymentId);
        }    
        
        $form = new PaymentForm($this->entityManager);
        
        $accounts = $this->entityManager->getRepository(BankAccount::class)
                ->findBy(['status' => BankAccount::STATEMENT_ACTIVE, 'api' => BankAccount::API_TOCHKA]);
        $accountList = [];
        foreach ($accounts as $account){
            $accountList[$account->getId()] = $account->getShortRs();
        }        
        $form->get('bankAccount')->setValueOptions($accountList);

        $supplierList = ['нет'];
        if ($payment){
            $supplierAccounts = $this->entityManager->getRepository(Payment::class)
                    ->supplierAccounts($payment->getBankAccount()->getLegal());
            foreach ($supplierAccounts as $supplierAccount){
                $supplier = $supplierAccount->getLegal()->getSupplier();
                if ($supplier){
                    $supplierList[$supplier->getId()] = $supplier->getName();
                }    
            }
        }    
        $form->get('supplier')->setValueOptions($supplierList);
        $form->get('supplier')->setDisableInArrayValidator(true);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                $bankAccount = $this->entityManager->getRepository(BankAccount::class)
                        ->find($data['bankAccount']);
                $data['bankAccount'] = $bankAccount;
                
                if (is_numeric($data['supplier'])){
                    $supplier = $this->entityManager->getRepository(Supplier::class)
                            ->find($data['supplier']);
                    $data['supplier'] = $supplier;                    
                }
                
                if ($payment){
                    $this->paymentManager->updatePayment($payment, $data);
                } else {
                    $payment = $this->paymentManager->addPayment($data);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
                var_dump($form->getMessages()); exit;
            }
        } else {
            if ($payment){
                $form->setData($payment->toLog());
                if ($copy){
                    $form->get('paymentDate')->setValue(date('Y-m-d'));
                    $form->get('status')->setValue(Payment::STATUS_ACTIVE);
                }
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'payment' => ($copy) ? null:$payment,
        ]);        
    }        
    
    public function statusAction()
    {
        $paymentId = $this->params()->fromRoute('id', -1);
        $version = $this->params()->fromQuery('version', 1);
        
        if ($paymentId > 0){
            $payment = $this->entityManager->getRepository(Payment::class)
                    ->find($paymentId);
            if ($payment){
                if ($version == 1){
                    $result = $this->paymentManager->statusPayment($payment);
                }    
                if ($version == 2){
                    $result = $this->paymentManager->statusPaymentV2($payment);
                }    
            }
        }
        return new JsonModel(
           $result
        );           
    }
    
    public function paymentStatusesAction()
    {
        $result = $this->sbpManager->updatePaymentStatuses();
        
        return new JsonModel(
           $result
        );           
    }
    
}
