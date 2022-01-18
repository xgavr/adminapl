<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Stock\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Entity\Supplier;
use Company\Entity\Legal;
use Company\Entity\Contract;
use Stock\Entity\Revise;
use Company\Entity\Office;
use Stock\Form\ReviseForm;

class ReviseController extends AbstractActionController
{

    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Менеджер revise.
     * @var \Stock\Service\ReviseManager
     */
    private $reviseManager;

    public function __construct($entityManager, $reviseManager) 
    {
        $this->entityManager = $entityManager;
        $this->reviseManager = $reviseManager;
    }   

    public function indexAction()
    {
        $kind = (int)$this->params()->fromQuery('kind', Revise::KIND_REVISE_SUPPLIER);

        $suppliers = $this->entityManager->getRepository(Supplier::class)
                ->findForPtu();
        $offices = $this->entityManager->getRepository(Office::class)
                ->findAll();
        return new ViewModel([
            'suppliers' => $suppliers,
            'offices' => $offices,
            'years' => array_combine(range(date("Y"), 2014), range(date("Y"), 2014)),
            'monthes' => array_combine(range(1, 12), range(1, 12)),
            'kind' => $kind,
        ]);  
    }
        
    public function contentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        $supplierId = $this->params()->fromQuery('supplier');
        $officeId = $this->params()->fromQuery('office');
        $year = $this->params()->fromQuery('year');
        $month = $this->params()->fromQuery('month');
        $kind = $this->params()->fromQuery('kind');
        
        $params = [
            'q' => $q, 'sort' => $sort, 'order' => $order, 
            'supplierId' => $supplierId, 'officeId' => $officeId,
            'year' => $year, 'month' => $month, 'kind' => $kind,
        ];
        $query = $this->entityManager->getRepository(Revise::class)
                        ->findAllRevise($params);
        
        $total = $this->entityManager->getRepository(Revise::class)
                        ->findAllReviseTotal($params);
        
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
    
    public function repostAllReviseAction()
    {                
        $this->reviseManager->repostAllRevise();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }    

    /**
     * Подготовка формы
     * 
     * @param ReviseForm $form
     * @param Revise $revise
     * @param int $kind
     * @return null
     */
    protected function prepareForm($form, $revise = null, $kind = Revise::KIND_REVISE_SUPPLIER)
    {
        $currentUser = $this->reviseManager->currentUser();
        if ($revise){
            $office = $revise->getOffice();
            
            if ($kind == Revise::KIND_REVISE_SUPPLIER){
                $supplier = $revise->getSupplier();
                $legalContact = $supplier->getLegalContact();
                $legals = $legalContact->getLegals();
                $legalList = [];
                foreach ($legals as $legal){
                    $legalList[$legal->getId()] = $legal->getName();                
                }            
                $form->get('legal')->setValueOptions($legalList);

                $contracts = $this->entityManager->getRepository(Contract::class)
                        ->findBy(['company' => $revise->getCompany()->getId(), 'legal' => $revise->getLegal()->getId()]);
                $contractList = [];
                foreach ($contracts as $contract){
                    $contractList[$contract->getId()] = $contract->getName();                
                }            
                $form->get('contract')->setValueOptions($contractList); 
            }    
        } else {
            $office = $currentUser->getOffice();
        }
        
        $offices = $this->entityManager->getRepository(Office::class)
                ->findBy(['status' => Supplier::STATUS_ACTIVE], ['name' => 'ASC']);
        $officeList = ['--не выбран--'];
        foreach ($offices as $bo) {
            $officeList[$bo->getId()] = $bo->getName();
        }
        $form->get('office')->setValueOptions($officeList);        
        $form->get('office')->setValue($office->getId());

        $companies = $this->entityManager->getRepository(Legal::class)
                ->formOfficeLegals(['officeId' => $office->getId()]);
        $companyList = [];
        foreach ($companies as $company) {
            $companyList[$company->getId()] = $company->getName();
        }
        $form->get('company')->setValueOptions($companyList);
        
        if ($kind == Revise::KIND_REVISE_SUPPLIER){
            $suppliers = $this->entityManager->getRepository(Supplier::class)
                    ->findBy(['status' => Supplier::STATUS_ACTIVE], ['name' => 'ASC']);
            $supplierList = ['--не выбран--'];
            foreach ($suppliers as $supplier) {
                $supplierList[$supplier->getId()] = $supplier->getName();
            }
            $form->get('supplier')->setValueOptions($supplierList);
        }    
    }
    
    public function editFormAction()
    {
        $reviseId = (int)$this->params()->fromRoute('id', -1);
        $kind = (int)$this->params()->fromQuery('kind');
        
        $revise = $contactName = null;
        
        if ($reviseId > 0){
            $revise = $this->entityManager->getRepository(Revise::class)
                    ->find($reviseId);
            $kind = $revise->getKind();
            $contact = $revise->getContact();
            if ($contact){
                $contactName = $revise->getContact()->getName();
            }    
        }    
        
        $form = new ReviseForm($this->entityManager);
        $this->prepareForm($form, $revise, $kind);
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $data['kind'] = $kind;
            $form->setData($data);

            if ($form->isValid()) {
                
                if ($revise){
                    $this->reviseManager->updateRevise($revise, $data);
                } else {
                    $revise = $this->reviseManager->addRevise($data);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($revise){
                $form->setData($revise->toArray());
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'revise' => $revise,
            'kind' => $kind,
            'contactName' => $contactName,
        ]);        
    }    
        
    public function deleteReviseAction()
    {
        $reviseId = $this->params()->fromRoute('id', -1);
        $revise = $this->entityManager->getRepository(Revise::class)
                ->find($reviseId);        

        if ($revise == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->reviseManager->removeRevise($revise);
        
        return new JsonModel(
           ['ok']
        );           
    }
    
}
