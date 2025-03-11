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
use Application\Form\BillSettingForm;
use Application\Entity\BillSetting;
use Application\Entity\Supplier;
use Application\Form\UploadForm;


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
        $suppliers = $this->entityManager->getRepository(Supplier::class)
                ->findBy([], ['status' => 'ASC']);
        return new ViewModel([
            'billManager' => $this->billManager,
            'suppliers' => $suppliers,
        ]);  
    }
    
    public function contentAction()
    {
        
        $q = $this->params()->fromQuery('search');
        $supplier = $this->params()->fromQuery('supplier');
        $status = $this->params()->fromQuery('status');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $year_month = $this->params()->fromQuery('month');
        
        $year = $month = null;
        if ($year_month){
            $year = date('Y', strtotime($year_month));
            $month = date('m', strtotime($year_month));
        }
        
        $params = ['search' => $q, 'supplier' => $supplier, 'status' => $status, 
            'year' => $year, 'month' => $month];
        
        $query = $this->entityManager->getRepository(Idoc::class)
                    ->queryAllIdocs($params);            
        
        $total = $this->entityManager->getRepository(Idoc::class)
                ->totalAllIdocs($params);
        
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

    public function idocStatusEditAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $idocId = $data['pk'];
            $idoc = $this->entityManager->getRepository(Idoc::class)
                    ->find($idocId);
                    
            if ($idoc){
                $idocStatus = Idoc::STATUS_RETIRED;
                if ($idoc->getStatus() == Idoc::STATUS_RETIRED){
                    $idocStatus = Idoc::STATUS_ACTIVE;
                }
                $this->billManager->updateIdocStatus($idoc, ['status' => $idocStatus]);
            }    
        }
        
        exit;
    }    
    
    public function idocDataAction()
    {        
        $idocId = (int)$this->params()->fromRoute('id', -1);
        $ruleCell = (bool) $this->params()->fromQuery('ruleCell', true);

        if ($idocId<0) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $idoc = $this->entityManager->getRepository(Idoc::class)
                ->find($idocId);
        
        return new JsonModel([
            'data' => $idoc->getDescriptionAsHtmlTable($ruleCell),
        ]);         
    }

    public function readIdocAction()
    {        
        $idocId = (int)$this->params()->fromRoute('id', -1);

        if ($idocId<0) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $idoc = $this->entityManager->getRepository(Idoc::class)
                ->find($idocId);
        $ptuData = [];
        if ($this->getRequest()->isPost()) {
            
            $post = $this->params()->fromPost();
            $ptuData = $idoc->idocToPtu($post); 
        }    
        
        return new JsonModel([
            'data' => $ptuData,
        ]);         
    }

    public function billSettingFormAction()
    {
        $idocId = (int)$this->params()->fromRoute('id', -1);
        $billSettingId = (int)$this->params()->fromQuery('template', -1);

        $billSetting = NULL;
        
        if ($billSettingId>0) {
            $billSetting = $this->entityManager->getRepository(BillSetting::class)
                    ->find($billSettingId);
        }        
        if ($idocId>0) {
            $idoc = $this->entityManager->getRepository(Idoc::class)
                    ->find($idocId);
//            var_dump($idoc->getDescriptionAsArray());
            if (!$billSetting){
                $billSetting = $this->entityManager->getRepository(BillSetting::class)
                        ->billSettingForIdoc($idoc);
            }    
        }        
        
        $form = new BillSettingForm();

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {

                if ($billSetting && !empty($data['name'])){
                    $this->billManager->updateBillSetting($billSetting, $data);                    
                } else {
                    $data['name'] = $idoc->getName();
                    $this->billManager->addBillSetting($idoc->getSupplier(), $data);                        
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
               // var_dump($form->getMessages($elementName));
            }
        } else {
            if ($billSetting){
                $form->setData($billSetting->toArray());
            }  
        }    
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'billSetting' => $billSetting,
            'idoc' => $idoc,
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
    }
    
    public function billsByMailAction()
    {
        $this->billManager->billsByMail();
        
        return new JsonModel(
           ['ok']
        );                   
    }
    
    public function deleteIdocAction()
    {
        $idocId = $this->params()->fromRoute('id', -1);
        
        $idoc = $this->entityManager->getRepository(Idoc::class)
                ->find($idocId);        
        if ($idoc == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->billManager->removeIdoc($idoc);
        
        return new JsonModel(
           ['ok']
        );           
    }    
    
    public function idocToPtuAction()
    {        
        $idocId = (int)$this->params()->fromRoute('id', -1);

        if ($idocId<0) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $idoc = $this->entityManager->getRepository(Idoc::class)
                ->find($idocId);
        $this->billManager->tryPtu($idoc); 
        
        return new JsonModel([
            'ok'
        ]);         
    }

    public function idocsToPtuAction()
    {        
        $this->billManager->tryIdocs(); 
        
        return new JsonModel([
            'ok'
        ]);         
    }

    public function correctionAction()
    {        
        $this->billManager->correction(); 
        
        return new JsonModel([
            'ok'
        ]);         
    }
    
    public function uploadBillFormAction()
    {
        $form = new UploadForm($this->billManager->getBillFolder());

        if($this->getRequest()->isPost()) {
            
            $data = array_merge_recursive(
                $this->params()->fromPost(),
                $this->params()->fromFiles()
            );            
//            var_dump($data); exit;

            // Заполняем форму данными.
            $form->setData($data);
            if($form->isValid()) {
                                
                // Получаем валадированные данные формы.
                $data = $form->getData();
//                var_dump($data); exit;
                
                $this->billManager->addIdoc(null, [
                    'status' => Idoc::STATUS_NEW,
                    'name' => $data['name']['name'],
                    'description' => '',
                    'tmpfile' => $data['name']['tmp_name'],
                ]);
                
                return new JsonModel(
                   ['ok']
                );           
            }
            
        }
        
        $this->layout()->setTemplate('layout/terminal');
        
        return new ViewModel([
            'form' => $form,
        ]);        
    }
    
    public function suppliersAction()
    {
        $suppliers = $this->entityManager->getRepository(Supplier::class)
                ->findForFormPtu();
        $result = [];

        foreach ($suppliers as $supplier){
            $result[$supplier->getId()] = $supplier->getName();
        }
        
        return new JsonModel($result);                
    }
    
    public function changeIdocSupplierAction()
    {
        $idocId = $supplierId = -1;
        
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $idocId = $data['pk'];
            $supplierId = $data['value'];            
        }    

        if ($idocId<0 || $supplierId < 0) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }           
        $idoc = $this->entityManager->getRepository(Idoc::class)
                ->find($idocId);
        
        $supplier = $this->entityManager->getRepository(Supplier::class)
                ->find($supplierId);
        
        $this->billManager->updateIdocSupplier($idoc, $supplier); 
        $this->billManager->tryPtu($idoc);
        
        return new JsonModel([
            'ok'
        ]);         
    }
}
