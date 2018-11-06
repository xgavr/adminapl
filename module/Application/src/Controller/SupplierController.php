<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\Supplier;
use Application\Entity\Contact;
use Application\Entity\PriceGetting;
use Application\Entity\PriceDescription;
use Application\Entity\BillGetting;
use Application\Entity\RequestSetting;
use Application\Entity\SupplySetting;
use Application\Form\SupplierForm;
use Application\Form\PriceGettingForm;
use Application\Form\PriceDescriptionForm;
use Application\Form\BillGettingForm;
use Application\Form\RequestSettingForm;
use Application\Form\ContactForm;
use Application\Form\SupplySettingForm;
use Application\Form\UploadForm;
use Zend\View\Model\JsonModel;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class SupplierController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер.
     * @var Application\Service\SupplierManager 
     */
    private $supplierManager;    
    
    /**
     * Менеджер.
     * @var Application\Service\ContactManager 
     */
    private $contactManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $supplierManager, $contactManager) 
    {
        $this->entityManager = $entityManager;
        $this->supplierManager = $supplierManager;
        $this->contactManager = $contactManager;
    }    
    
    public function indexAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        $status = $this->params()->fromQuery('status');
        
        $query = $this->entityManager->getRepository(Supplier::class)
                    ->findAllSupplier($status);
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);    
        
        $statuses = $this->entityManager->getRepository(Supplier::class)
                ->statuses();
        foreach ($statuses as $key => $status){
            $statuses[$key]['name'] = Supplier::getStatusName($status['status']);
        }
        
        $absentPriceDescriptions = $this->entityManager->getRepository(Supplier::class)
                ->absentPriceDescriptions();

        $absentRaws = $this->entityManager->getRepository(Supplier::class)
                ->absentRaws();
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'supplier' => $paginator,
            'supplierManager' => $this->supplierManager,
            'statuses' => $statuses,
            'absentPriceDescriptions' => $absentPriceDescriptions,
            'absentRaws' => $absentRaws,
        ]);  
    }
    
    public function addAction() 
    {     
        // Создаем форму.
        $form = new SupplierForm($this->entityManager);
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер supplier для добавления нового good в базу данных.                
                $supplier = $this->supplierManager->addNewSupplier($data);
                
                // Перенаправляем пользователя на страницу "supplier".
                return $this->redirect()->toRoute('supplier', ['action' => 'view', 'id' => $supplier->getId()]);
            }
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form
        ]);
    }   
    
   public function editAction()
   {
        // Создаем форму.
        $form = new SupplierForm($this->entityManager);
    
        // Получаем ID tax.    
        $supplierId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий supplier в базе данных.    
        $supplier = $this->entityManager->getRepository(Supplier::class)
                ->findOneById($supplierId);  
        	
        if ($supplier == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер постов, чтобы добавить новый пост в базу данных.                
                $this->supplierManager->updateSupplier($supplier, $data);
                
                // Перенаправляем пользователя на страницу "supplier".
                return $this->redirect()->toRoute('supplier', ['action' => 'view', 'id' => $supplier->getId()]);
            }
        } else {
            $data = [
               'name' => $supplier->getName(),
               'aplId' => $supplier->getAplId(),
               'status' => $supplier->getStatus(),
            ];
            
            $form->setData($data);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
            'supplier' => $supplier
        ]);  
    }    
    
    public function editFormAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $supplier = $this->entityManager->getRepository(Supplier::class)
                ->find($id);
        
        if ($supplier == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Create form
        $form = new SupplierForm($this->entityManager);
        
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            
            // Fill in the form with POST data
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Validate form
            if($form->isValid()) {
                
                // Get filtered and validated data
                $data = $form->getData();
                
                // Update permission.
                $this->supplierManager->updateSupplier($supplier, $data);
                
                return new JsonModel(
                   ['ok']
                );           
            }               
        } else {
            $form->setData(array(
                    'name'=>$supplier->getName(),
                    'aplId'=>$supplier->getAplId(),     
                    'status'=>$supplier->getStatus(),     
                ));
        }
        
        $this->layout()->setTemplate('layout/terminal');
        
        return new ViewModel([
                'form' => $form,
                'supplier' => $supplier
            ]);
    }
    
    
    public function deleteAction()
    {
        $supplierId = $this->params()->fromRoute('id', -1);
        
        $supplier = $this->entityManager->getRepository(Supplier::class)
                ->findOneById($supplierId);        
        if ($supplier == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->supplierManager->removeSupplier($supplier);
        
        // Перенаправляем пользователя на страницу "rb/tax".
        return $this->redirect()->toRoute('supplier', []);
    }    

    public function deleteContactAction()
    {
        $contactId = $this->params()->fromRoute('id', -1);
        
        $contact = $this->entityManager->getRepository(Contact::class)
                ->findOneById($contactId);
        
        if ($contact == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $supplierId = $contact->getSupplier()->getId();
        
        $this->contactManager->removeContact($contact);
        
        // Перенаправляем пользователя на страницу "supplier/view".
        return $this->redirect()->toRoute('supplier', ['action' => 'view', 'id' => $supplierId]);
    }    

    public function viewAction() 
    {       
        $supplierId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($supplierId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the tax supplier ID
        $supplier = $this->entityManager->getRepository(Supplier::class)
                ->findOneById($supplierId);
        
        if ($supplier == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $forLegals = $supplier->getLegalContacts();

        if (!count($forLegals)){
            $data['full_name'] = $data['name'] = $supplier->getName();
            $data['status'] = Contact::STATUS_LEGAL;
            $this->contactManager->addNewContact($supplier, $data);
        }
        
        $raws = $this->entityManager->getRepository(\Application\Entity\Raw::class)
            ->findAllRaw(null, $supplier)
            ->getResult();

        
        // Render the view template.
        return new ViewModel([
            'supplier' => $supplier,
            'legalContact' => $supplier->getLegalContact(),
            'supplierManager' => $this->supplierManager,
            'lastPrice' => $this->supplierManager->getLastPriceFile($supplier),
            'arxPrice' => $this->supplierManager->getArxPriceFile($supplier),
            'raws' => $raws,
        ]);
    }    
    
    public function priceGettingFormAction()
    {
        $supplierId = (int)$this->params()->fromRoute('id', -1);
        
        if ($supplierId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $supplier = $this->entityManager->getRepository(Supplier::class)
                ->findOneById($supplierId);
        
        if ($supplier == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $priceGettingId = (int)$this->params()->fromQuery('priceGetting', -1);
        
        // Validate input parameter
        if ($priceGettingId>0) {
            $priceGetting = $this->entityManager->getRepository(PriceGetting::class)
                    ->findOneById($priceGettingId);
        } else {
            $priceGetting = null;
        }
        
        $form = new PriceGettingForm();

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {

                if ($priceGetting){
                    $this->supplierManager->updatePriceGetting($priceGetting, $data, true);                    
                } else{
                    $this->supplierManager->addNewPriceGetting($supplier, $data, true);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($priceGetting){
                $data = [
                    'name' => $priceGetting->getName(),  
                    'ftp' => $priceGetting->getFtp(),  
                    'ftpDir' => $priceGetting->getFtpDir(),  
                    'ftpLogin' => $priceGetting->getFtpLogin(),  
                    'ftpPassword' => $priceGetting->getFtpPassword(),  
                    'email' => $priceGetting->getEmail(),  
                    'emailPassword' => $priceGetting->getEmailPassword(),  
                    'link' => $priceGetting->getLink(),  
                    'status' => $priceGetting->getStatus(),  
                    'filename' => $priceGetting->getFilename(),  
                    'statusFilename' => $priceGetting->getStatusFilename(),  
                    'orderToApl' => $priceGetting->getOrderToApl(),  
                ];
                $form->setData($data);
            }    
        }        
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'priceGetting' => $priceGetting,
            'supplier' => $supplier,
        ]);                
    }
    
    public function deletePriceGettingAction()
    {
        $priceGettingId = (int) $this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($priceGettingId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $priceGetting = $this->entityManager->getRepository(PriceGetting::class)
                ->findOneById($priceGettingId);
        
        if ($priceGetting == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $supplier = $priceGetting->getSupplier();

        $this->supplierManager->removePriceGetting($priceGetting);
        
        // Перенаправляем пользователя на страницу "legal".
        return $this->redirect()->toRoute('supplier', ['action' => 'view', 'id' => $supplier->getId()]);
    }
    
    public function deletePriceGettingFormAction()
    {
        $priceGettingId = $this->params()->fromRoute('id', -1);
        
        $priceGetting = $this->entityManager->getRepository(PriceGetting::class)
                ->findOneById($priceGettingId);
        
        if ($priceGetting == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->supplierManager->removePriceGetting($priceGetting);
        
        return new JsonModel(
           ['ok']
        );           
        
        exit;
    }    
    
    
        
    public function billGettingFormAction()
    {
        $supplierId = (int)$this->params()->fromRoute('id', -1);
        
        if ($supplierId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $supplier = $this->entityManager->getRepository(Supplier::class)
                ->findOneById($supplierId);
        
        if ($supplier == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $billGettingId = (int)$this->params()->fromQuery('billGetting', -1);
        
        // Validate input parameter
        if ($billGettingId>0) {
            $billGetting = $this->entityManager->getRepository(BillGetting::class)
                    ->findOneById($billGettingId);
        } else {
            $billGetting = null;
        }
        
        $form = new BillGettingForm();

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {

                if ($billGetting){
                    $this->supplierManager->updateBillGetting($billGetting, $data, true);                    
                } else{
                    $this->supplierManager->addNewBillGetting($supplier, $data, true);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($billGetting){
                $data = [
                    'name' => $billGetting->getName(),  
                    'email' => $billGetting->getEmail(),  
                    'emailPassword' => $billGetting->getEmailPassword(),  
                    'status' => $billGetting->getStatus(),  
                ];
                $form->setData($data);
            }    
        }        
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'billGetting' => $billGetting,
            'supplier' => $supplier,
        ]);                
    }
    
    public function deleteBillGettingAction()
    {
        $billGettingId = (int) $this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($billGettingId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $billGetting = $this->entityManager->getRepository(BillGetting::class)
                ->findOneById($billGettingId);
        
        if ($billGetting == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $supplier = $billGetting->getSupplier();

        $this->supplierManager->removeBillGetting($billGetting);
        
        // Перенаправляем пользователя на страницу "legal".
        return $this->redirect()->toRoute('supplier', ['action' => 'view', 'id' => $supplier->getId()]);
    }
    
    public function deleteBillGettingFormAction()
    {
        $billGettingId = $this->params()->fromRoute('id', -1);
        
        $billGetting = $this->entityManager->getRepository(BillGetting::class)
                ->findOneById($billGettingId);
        
        if ($billGetting == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->supplierManager->removeBillGetting($billGetting);
        
        return new JsonModel(
           ['ok']
        );           
        
        exit;
    }    
    
    

    public function managerFormAction()
    {
        $supplierId = (int)$this->params()->fromRoute('id', -1);
        
        if ($supplierId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $supplier = $this->entityManager->getRepository(Supplier::class)
                ->findOneById($supplierId);
        
        if ($supplier == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $managerId = (int)$this->params()->fromQuery('manager', -1);
        
        // Validate input parameter
        if ($managerId>0) {
            $manager = $this->entityManager->getRepository(Contact::class)
                    ->findOneById($managerId);
        } else {
            $manager = null;
        }
        
        $form = new ContactForm();

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {

                if ($manager){
                    $this->contactManager->updateContact($manager, $data);                    
                } else{
                    $this->contactManager->addNewContact($supplier, $data);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($manager){
                $data = [
                    'name' => $manager->getName(),  
                    'description' => $manager->getDescription(),  
                    'status' => $manager->getStatus(),  
                ];
                $form->setData($data);
            }    
        }        
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'manager' => $manager,
            'supplier' => $supplier,
        ]);                
    }
    
    public function deleteManagerAction()
    {
        $contactId = (int) $this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($contactId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $contact = $this->entityManager->getRepository(Contact::class)
                ->findOneById($contactId);
        
        if ($contact == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $supplier = $contact->getSupplier();

        $this->contactManager->removeContact($contact);
        
        // Перенаправляем пользователя на страницу "legal".
        return $this->redirect()->toRoute('supplier', ['action' => 'view', 'id' => $supplier->getId()]);
    }
    
    public function deleteManagerFormAction()
    {
        $contactId = $this->params()->fromRoute('id', -1);
        
        $contact = $this->entityManager->getRepository(Contact::class)
                ->findOneById($contactId);
        
        if ($contact == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->contactManager->removeContact($contact);
        
        return new JsonModel(
           ['ok']
        );           
        
        exit;
    }    
    
    public function requestSettingFormAction()
    {
        $supplierId = (int)$this->params()->fromRoute('id', -1);
        
        if ($supplierId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $supplier = $this->entityManager->getRepository(Supplier::class)
                ->findOneById($supplierId);
        
        if ($supplier == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $requestSettingId = (int)$this->params()->fromQuery('requestSetting', -1);
        
        // Validate input parameter
        if ($requestSettingId>0) {
            $requestSetting = $this->entityManager->getRepository(RequestSetting::class)
                    ->findOneById($requestSettingId);
        } else {
            $requestSetting = null;
        }
        
        $form = new RequestSettingForm();

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {

                if ($requestSetting){
                    $this->supplierManager->updateRequestSetting($requestSetting, $data, true);                    
                } else{
                    $this->supplierManager->addNewRequestSetting($supplier, $data, true);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($requestSetting){
                $data = [
                    'name' => $requestSetting->getName(),  
                    'description' => $requestSetting->getDescription(),  
                    'site' => $requestSetting->getSite(),  
                    'login' => $requestSetting->getLogin(),  
                    'password' => $requestSetting->getPassword(),  
                    'mode' => $requestSetting->getMode(),  
                    'status' => $requestSetting->getStatus(),  
                ];
                $form->setData($data);
            }    
        }        
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'requestSetting' => $requestSetting,
            'supplier' => $supplier,
        ]);                
    }
    
    public function deleteRequestSettingAction()
    {
        $requestSettingId = (int) $this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($requestSettingId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $requestSetting = $this->entityManager->getRepository(RequestSetting::class)
                ->findOneById($requestSettingId);
        
        if ($requestSetting == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $supplier = $requestSetting->getSupplier();

        $this->supplierManager->removeRequestSetting($requestSetting);
        
        // Перенаправляем пользователя на страницу "legal".
        return $this->redirect()->toRoute('supplier', ['action' => 'view', 'id' => $supplier->getId()]);
    }
    
    public function deleteRequestSettingFormAction()
    {
        $requestSettingId = $this->params()->fromRoute('id', -1);
        
        $requestSetting = $this->entityManager->getRepository(RequestSetting::class)
                ->findOneById($requestSettingId);
        
        if ($requestSetting == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->supplierManager->removeRequestSetting($requestSetting);
        
        return new JsonModel(
           ['ok']
        );           
        
        exit;
    }
    

    public function supplySettingFormAction()
    {
        $supplierId = (int)$this->params()->fromRoute('id', -1);
        
        if ($supplierId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $supplier = $this->entityManager->getRepository(Supplier::class)
                ->findOneById($supplierId);
        
        if ($supplier == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $supplySettingId = (int)$this->params()->fromQuery('supplySetting', -1);
        
        // Validate input parameter
        if ($supplySettingId>0) {
            $supplySetting = $this->entityManager->getRepository(SupplySetting::class)
                    ->findOneById($supplySettingId);
        } else {
            $supplySetting = null;
        }
        
        $form = new SupplySettingForm($this->entityManager);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);
            if ($form->isValid()) {

                if ($supplySetting){
                    $this->supplierManager->updateSupplySetting($supplySetting, $data, true);                    
                } else{
                    $this->supplierManager->addNewSupplySetting($supplier, $data, true);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($supplySetting){
                $data = [
                    'orderBefore' => $supplySetting->getOrderBeforeHi(),  
                    'supplyTime' => $supplySetting->getSupplyTime(),  
                    'office' => $supplySetting->getOffice(),  
                    'supplySat' => $supplySetting->getSupplySat(),  
                    'status' => $supplySetting->getStatus(),  
                ];
                $form->setData($data);
            }    
        }        
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'supplySetting' => $supplySetting,
            'supplier' => $supplier,
        ]);                
    }
    
    public function deleteSupplySettingAction()
    {
        $suplySettingId = (int) $this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($suplySettingId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $suplySetting = $this->entityManager->getRepository(SupplySetting::class)
                ->findOneById($suplySettingId);
        
        if ($suplySetting == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $supplier = $suplySetting->getSupplier();

        $this->supplierManager->removeSupplySetting($suplySetting);
        
        // Перенаправляем пользователя на страницу "legal".
        return $this->redirect()->toRoute('supplier', ['action' => 'view', 'id' => $supplier->getId()]);
    }
    
    public function deleteSupplySettingFormAction()
    {
        $suplySettingId = $this->params()->fromRoute('id', -1);
        
        $suplySetting = $this->entityManager->getRepository(SupplySetting::class)
                ->findOneById($suplySettingId);
        
        if ($suplySetting == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->supplierManager->removeSupplySetting($suplySetting);
        
        return new JsonModel(
           ['ok']
        );           
        
        exit;
    }    
    

    public function priceDescriptionFormAction()
    {
        $supplierId = (int)$this->params()->fromRoute('id', -1);
        
        if ($supplierId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $supplier = $this->entityManager->getRepository(Supplier::class)
                ->findOneById($supplierId);
        
        if ($supplier == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $priceDescriptionId = (int)$this->params()->fromQuery('priceDescription', -1);
        
        // Validate input parameter
        if ($priceDescriptionId>0) {
            $priceDescription = $this->entityManager->getRepository(PriceDescription::class)
                    ->findOneById($priceDescriptionId);
        } else {
            $priceDescription = null;
        }
        
        $form = new PriceDescriptionForm();

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            
            if (!$data['status']) $data['status'] = 1;
            if (!$data['name']) $data['name'] = 'Описание полей прайса '.$supplier->getName();
            
//            var_dump($data);
            
            $form->setData($data);
            if ($form->isValid()) {

                if ($priceDescription){
                    $this->supplierManager->updatePriceDescription($priceDescription, $data, true);                    
                } else{
                    $this->supplierManager->addNewPriceDescription($supplier, $data, true);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
                return new JsonModel(
                   $form->getMessages()
                );                           
            }
        } else {
            if ($priceDescription){
                $data = [
                    'article' => $priceDescription->getArticle(),  
                    'bar' => $priceDescription->getBar(),  
                    'car' => $priceDescription->getCar(),  
                    'comment' => $priceDescription->getComment(),  
                    'country' => $priceDescription->getCountry(),  
                    'currency' => $priceDescription->getCurrency(),  
                    'iid' => $priceDescription->getIid(),  
                    'lot' => $priceDescription->getLot(),  
                    'name' => $priceDescription->getName(),  
                    'oem' => $priceDescription->getOem(),  
                    'brand' => $priceDescription->getBrand(),  
                    'price' => $priceDescription->getPrice(),  
                    'producer' => $priceDescription->getProducer(),  
                    'defaultProducer' => $priceDescription->getDefaultProducer(),  
                    'rest' => $priceDescription->getRest(),  
                    'status' => $priceDescription->getStatus(),  
                    'unit' => $priceDescription->getUnit(),  
                    'pack' => $priceDescription->getPack(),  
                    'title' => $priceDescription->getTitle(),  
                    'vendor' => $priceDescription->getVendor(),  
                    'weight' => $priceDescription->getWeight(),  
                    'type' => $priceDescription->getType(),  
                    'markdown' => $priceDescription->getMarkdown(),  
                    'sale' => $priceDescription->getSale(),  
                    'image' => $priceDescription->getImage(),  
                ];
                $form->setData($data);
            }    
        }        
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'priceDescription' => $priceDescription,
            'supplier' => $supplier,
        ]);                
    }

    public function deletePriceDescriptionFormAction()
    {
        $priceDescriptionId = $this->params()->fromRoute('id', -1);
        
        $priceDescription = $this->entityManager->getRepository(PriceDescription::class)
                ->findOneById($priceDescriptionId);
        
        if ($priceDescription == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->supplierManager->removePriceDescription($priceDescription);
        
        return new JsonModel(
           ['ok']
        );           
        
        exit;
    }    
    
    public function deletePriceDescriptionAction()
    {
        $priceDescriptionId = (int) $this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($priceDescriptionId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $priceDescription = $this->entityManager->getRepository(PriceDescription::class)
                ->findOneById($priceDescriptionId);
        
        if ($priceDescription == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $supplier = $priceDescription->getSupplier();

        $this->supplierManager->removePriceDescription($priceDescription);
        
        // Перенаправляем пользователя на страницу "supplier/view".
        return $this->redirect()->toRoute('supplier', ['action' => 'view', 'id' => $supplier->getId()]);
    }
    
    public function parsPriceListTextAction()
    {
        $priceDescriptionId = $this->params()->fromRoute('id', -1);
        
        $priceDescription = $this->entityManager->getRepository(PriceDescription::class)
                ->findOneById($priceDescriptionId);
        
        if ($priceDescription == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
                
        $text = '<pre>'.$this->supplierManager->parsPriceListText($priceDescription).'</pre>';
        return new JsonModel(
           ['text' => $text]
        );           
        
        exit;        
    }
    
    public function uploadPriceFormAction()
    {
        $supplierId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($supplierId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the supplier ID
        $supplier = $this->entityManager->getRepository(Supplier::class)
                ->findOneById($supplierId);
        
        if ($supplier == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $form = new UploadForm($this->supplierManager->getPriceFolder($supplier));

        if($this->getRequest()->isPost()) {
            
            $data = array_merge_recursive(
                $this->params()->fromPost(),
                $this->params()->fromFiles()
            );            
//            var_dump($data);

            // Заполняем форму данными.
            $form->setData($data);
            if($form->isValid()) {
                                
                // Получаем валадированные данные формы.
                $data = $form->getData();
                //$this->supplierManager->checkPriceFolder($supplier);
              
                return new JsonModel(
                   ['ok']
                );           
            }
            
        }
        
        $this->layout()->setTemplate('layout/terminal');
        
        return new ViewModel([
            'supplier' => $supplier,
            'form' => $form,
        ]);
        
    }
}
