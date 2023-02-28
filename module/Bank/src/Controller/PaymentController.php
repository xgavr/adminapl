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

class PaymentController extends AbstractActionController
{

    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Менеджер банк.
     * @var \Bank\Service\PaymentManager
     */
    private $paymentManager;

    public function __construct($entityManager, $paymentManager) 
    {
        $this->entityManager = $entityManager;
        $this->paymentManager = $paymentManager;
    }   

    public function indexAction()
    {
        $suppliers = $this->entityManager->getRepository(Supplier::class)
                ->findForPtu();                
        return new ViewModel([
            'suppliers' => $suppliers,
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
                ->findBy(['status' => BankAccount::STATEMENT_ACTIVE, 'api' => BankAccount::API_TOCHKA, 'accountType' => BankAccount::ACСOUNT_CHECKING]);
        $accountList = [];
        foreach ($accounts as $account){
            $accountList[$account->getId()] = $account->getRs();
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
    
    public function accountAvailableSuppliersAction()
    {
        $bankAccountId = $this->params()->fromRoute('id', -1);
        $data = [];
        if ($bankAccountId > 0){
            $bankAccount = $this->entityManager->getRepository(BankAccount::class)
                    ->find($bankAccountId);
            if ($bankAccount){
                $supplierAccounts = $this->entityManager->getRepository(Payment::class)
                        ->supplierAccounts($bankAccount->getLegal());
                foreach ($supplierAccounts as $supplierAccount){
                    $supplier = $supplierAccount->getLegal()->getSupplier();
                    if ($supplier){
                        $data[$supplier->getId()] = ['id' => $supplier->getId(), 'name' => $supplier->getName()];
                    }    
                }
            }
        }
        return new JsonModel(
           $data
        );           
    }
    
    public function supplierCurrentDetailsAction()
    {
        $supplierId = $this->params()->fromRoute('id', -1);
        $bankAccountId = $this->params()->fromQuery('account', -1);
        
        $data = [];
        if ($supplierId > 0 && $bankAccountId > 0){
            $supplier = $this->entityManager->getRepository(Supplier::class)
                    ->find($supplierId);
            $bankAccount = $this->entityManager->getRepository(BankAccount::class)
                    ->find($bankAccountId);
            if ($bankAccount){
                $company = $bankAccount->getLegal();
                if ($supplier && $company){
                    $data = $this->paymentManager->supplierDetail($supplier, $company);
                }
            }    
        }
        
        return new JsonModel(
           $data
        );           
    }    
    
    public function statusTotalAction()
    {
        $total = $this->entityManager->getRepository(Payment::class)
                ->statusTotal();
        return new JsonModel(
           ['total' => $total]
        );           
    }

    public function sendAction()
    {
        $paymentId = $this->params()->fromRoute('id', -1);
        $version = $this->params()->fromQuery('version', 1);
        
        if ($paymentId > 0){
            $payment = $this->entityManager->getRepository(Payment::class)
                    ->find($paymentId);
            if ($payment){
                if ($version == 1){
                    $this->paymentManager->sendPayment($payment);
                }    
                if ($version == 2){
                    $this->paymentManager->sendPaymentV2($payment);
                }    
            }
        }
        return new JsonModel(
           ['ok']
        );           
    }
        
    public function sendAllAction()
    {
        $version = $this->params()->fromQuery('version', 1);
        
        $this->paymentManager->sendAll($version);
        return new JsonModel(
           ['ok']
        );           
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
    
    public function suppliersPayContentAction()
    {        	        
        $bankAccountId = $this->params()->fromRoute('id', -1);
        
        $data = [];
        if ($bankAccountId > 0){
            
            $bankAccount = $this->entityManager->getRepository(BankAccount::class)
                    ->find($bankAccountId);
        
            if ($bankAccount){
                $supplierAccounts = $this->entityManager->getRepository(Payment::class)
                        ->supplierAccounts($bankAccount->getLegal());

                foreach ($supplierAccounts as $supplierAccount){
                    $supplier = $supplierAccount->getLegal()->getSupplier();
                    if ($supplier){
                        $data[$supplier->getId()] = ['id' => $supplier->getId(), 'name' => $supplier->getName(), 'amount' => null];
                    }    
                }
            }    
        }    
        
        return new JsonModel([
            'rows' => array_values($data),
        ]);          
    }    

    public function deleteAction()
    {
        $paymentId = $this->params()->fromRoute('id', -1);
        if ($paymentId > 0){
            $payment = $this->entityManager->getRepository(Payment::class)
                    ->find($paymentId);
            if ($payment){
                $this->paymentManager->removePayment($payment);
            }
        }
        return new JsonModel(
           ['ok']
        );                   
    }
    
    public function suppliersPayFormAction()
    {
        $form = new SuppliersPayForm($this->entityManager);
        
        $accounts = $this->entityManager->getRepository(BankAccount::class)
                ->findBy(['status' => BankAccount::STATEMENT_ACTIVE, 'api' => BankAccount::API_TOCHKA, 'accountType' => BankAccount::ACСOUNT_CHECKING]);
        $accountList = [];
        foreach ($accounts as $account){
            $accountList[$account->getId()] = $account->getRs();
        }        
        $form->get('bankAccount')->setValueOptions($accountList);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                $bankAccount = $this->entityManager->getRepository(BankAccount::class)
                        ->find($data['bankAccount']);
                $data['bankAccount'] = $bankAccount;
                
                $this->paymentManager->suppliersPayment($data);
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
                var_dump($form->getMessages()); exit;
            }
        } else {

        }

        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
        ]);                
    }
}
