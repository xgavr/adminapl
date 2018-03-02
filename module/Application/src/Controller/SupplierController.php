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
use Application\Entity\Pricesettings;
use Application\Entity\BillGetting;
use Application\Entity\RequestSetting;
use Application\Entity\SupplySetting;
use Application\Form\SupplierForm;
use Application\Form\PriceGettingForm;
use Application\Form\PricesettingsForm;
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
        
        $query = $this->entityManager->getRepository(Supplier::class)
                    ->findAllSupplier();
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'supplier' => $paginator,
            'supplierManager' => $this->supplierManager
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
        
        $contacts = $supplier->getContacts();

        if (!count($contacts)){
            $data['full_name'] = $data['name'] = $supplier->getName();
            $data['status'] = Contact::STATUS_ACTIVE;
            $this->contactManager->addNewContact($supplier, $data);
        }
        
        // Render the view template.
        return new ViewModel([
            'supplier' => $supplier,
            'supplierManager' => $this->supplierManager,
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
                    'ftpLogin' => $priceGetting->getFtpLogin(),  
                    'ftpPassword' => $priceGetting->getFtpPassword(),  
                    'email' => $priceGetting->getEmail(),  
                    'emailPassword' => $priceGetting->getEmailPassword(),  
                    'link' => $priceGetting->getLink(),  
                    'status' => $priceGetting->getStatus(),  
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
            $billGetting = $this->entityManager->getRepository(PriceGetting::class)
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

        $this->supplierManager->removePriceGetting($billGetting);
        
        // Перенаправляем пользователя на страницу "legal".
        return $this->redirect()->toRoute('supplier', ['action' => 'view', 'id' => $supplier->getId()]);
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

    public function priceSettingFormAction()
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

        $priceSettingId = (int)$this->params()->fromQuery('priceSetting', -1);
        
        // Validate input parameter
        if ($priceSettingId>0) {
            $priceSetting = $this->entityManager->getRepository(Pricesettings::class)
                    ->findOneById($priceSettingId);
        } else {
            $priceSetting = null;
        }
        
        $form = new PricesettingsForm();

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);
            if ($form->isValid()) {

                if ($priceSetting){
                    $this->supplierManager->updatePricesettings($priceSetting, $data, true);                    
                } else{
                    $this->supplierManager->addNewPricesettings($supplier, $data, true);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($priceSetting){
                $data = [
                    'name' => $priceSetting->getName(),  
                    'article' => $priceSetting->getArticle(),  
                    'iid' => $priceSetting->getIid(),  
                    'producer' => $priceSetting->getProducer(),  
                    'title' => $priceSetting->getTitle(),  
                    'rest' => $priceSetting->getRest(),  
                    'price' => $priceSetting->getPrice(),  
                    'status' => $priceSetting->getStatus(),  
                ];
                $form->setData($data);
            }    
        }        
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'priceSetting' => $priceSetting,
            'supplier' => $supplier,
        ]);                
    }

    public function deletePriceSettingAction()
    {
        $priceSettingId = (int) $this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($priceSettingId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $priceSetting = $this->entityManager->getRepository(Pricesettings::class)
                ->findOneById($priceSettingId);
        
        if ($priceSetting == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $supplier = $priceSetting->getSupplier();

        $this->supplierManager->removePricesettings($priceSetting);
        
        // Перенаправляем пользователя на страницу "supplier/view".
        return $this->redirect()->toRoute('supplier', ['action' => 'view', 'id' => $supplier->getId()]);
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
                $this->supplierManager->checkPriceFolder($supplier);
              
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
