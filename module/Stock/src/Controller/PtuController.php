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
use Stock\Entity\Ptu;
use Stock\Entity\PtuGood;
use Application\Entity\Goods;
use Stock\Form\PtuForm;
use Stock\Form\PtuGoodForm;
use Company\Entity\Office;
use Stock\Entity\Unit;
use Stock\Entity\Ntd;
use Company\Entity\Country;
use Application\Entity\Supplier;
use Company\Entity\Legal;
use Company\Entity\Contract;

class PtuController extends AbstractActionController
{

    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Менеджер пту.
     * @var \Stock\Service\PtuManager
     */
    private $ptuManager;

    public function __construct($entityManager, $ptuManager) 
    {
        $this->entityManager = $entityManager;
        $this->ptuManager = $ptuManager;
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
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        $supplierId = $this->params()->fromQuery('supplier');
        
        $params = [
            'q' => $q, 'sort' => $sort, 'order' => $order, 'supplierId' => $supplierId,
        ];
        $query = $this->entityManager->getRepository(Ptu::class)
                        ->findAllPtu($params);
        
        $total = $this->entityManager->getRepository(Ptu::class)
                        ->findAllPtuTotal($params);
        
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
    
    public function goodContentAction()
    {
        	        
        $ptuId = $this->params()->fromRoute('id', -1);
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        
        $query = $this->entityManager->getRepository(Ptu::class)
                        ->findPtuGoods($ptuId, ['q' => $q, 'sort' => $sort, 'order' => $order]);
        
        $total = count($query->getResult(2));
        
//        if ($offset) {
//            $query->setFirstResult($offset);
//        }
//        if ($limit) {
//            $query->setMaxResults($limit);
//        }

        $result = $query->getResult(2);
        
        return new JsonModel($result);          
    }        
    
    public function repostAllPtuAction()
    {                
        $this->ptuManager->repostAllPtu();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }        
    
    public function editFormAction()
    {
        $ptuId = (int)$this->params()->fromRoute('id', -1);
        
        $ptu = $supplier = $legal = $company = null;
        
        if ($ptuId > 0){
            $ptu = $this->entityManager->getRepository(Ptu::class)
                    ->findOneById($ptuId);
        }    
        
        if ($ptu == null) {
            $officeId = (int)$this->params()->fromQuery('office', 1);
            $office = $this->entityManager->getRepository(Office::class)
                    ->findOneById($officeId);
        } else {
            $supplier = $ptu->getSupplier();
            $office = $ptu->getOffice();
            $company = $ptu->getContract()->getCompany();
            $legal = $ptu->getLegal();
            
            if ($this->getRequest()->isPost()){
                $data = $this->params()->fromPost();
                $supplier = $this->entityManager->getRepository(Supplier::class)
                        ->findOneById($data['supplier']);
                $office = $this->entityManager->getRepository(Office::class)
                        ->findOneById($data['office_id']);
                $contract = $this->entityManager->getRepository(Contract::class)
                        ->findOneById($data['contract_id']);
                $company = $contract->getCompany();
                $legal = $this->entityManager->getRepository(Legal::class)
                        ->findOneById($data['legal_id']);
            }
        }       
        
        $form = new PtuForm($this->entityManager, $office, $supplier, $company, $legal);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                unset($data['supplier']);
                unset($data['company']);
                unset($data['csrf']);
                $ptuGood = $data['ptuGood'];
                unset($data['ptuGood']);
                
                if ($ptu){
                    $this->ptuManager->updatePtu($ptu, $data);
                    $this->entityManager->refresh($ptu);
                } else {
                    $ptu = $this->ptuManager->addPtu($data);
                }    
                
                $this->ptuManager->updatePtuGoods($ptu, $ptuGood);
                
                $this->ptuManager->repostPtu($ptu);
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($ptu){
                $data = [
                    'office_id' => $ptu->getContract()->getOffice()->getId(),
                    'company' => $ptu->getContract()->getCompany()->getId(),
                    'supplier' => $ptu->getSupplier()->getId(),
                    'legal_id' => $ptu->getLegal()->getId(),  
                    'contract_id' => $ptu->getContract()->getId(),  
                    'doc_date' => $ptu->getDocDate(),  
                    'doc_no' => $ptu->getDocNo(),
                    'comment' => $ptu->getComment(),
                    'status' => $ptu->getStatus(),
                ];
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'ptu' => $ptu,
        ]);        
    }    
        
    public function goodEditFormAction()
    {        
        $params = $this->params()->fromQuery();
//        var_dump($params); exit;
        $good = $rowNo = $result = null;        
        if (isset($params['good'])){
            $good = $this->entityManager->getRepository(Goods::class)
                    ->findOneById($params['good']['id']);            
        }
        if (isset($params['rowNo'])){
            $rowNo = $params['rowNo'];
        }
        
        $form = new PtuGoodForm($this->entityManager, $good);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);
            if (isset($data['good'])){
                $good = $this->entityManager->getRepository(Goods::class)
                        ->findOneById($data['good']);            
            }

            if ($form->isValid()) {
                $result = 'ok';
                return new JsonModel([
                    'result' => $result,
                    'good' => [
                        'id' => $good->getId(),
                        'code' => $good->getCode(),
                        'name' => $good->getName(),
                        'producer' => $good->getProducer()->getName(),
                    ],
                ]);        
            }
        } else {
            if ($good){
                $data = [
                    'good' => $params['good']['id'],
                    'quantity' => $params['quantity'],
                    'amount' => $params['amount'],
                    'price' => $params['amount']/$params['quantity'],
                    'unit' => (isset($params['unit']['name'])) ? $params['unit']['name']:null,
                    'country' => (isset($params['country']['name'])) ? $params['country']['name']:null,
                    'ntd' => (isset($params['ntd']['ntd'])) ? $params['ntd']['ntd']:null,
                ];
                $form->setData($data);
            }    
        }        

        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'rowNo' => $rowNo,
            'good' => $good,
        ]);        
    }
    
    public function deletePtuAction()
    {
        $ptuId = $this->params()->fromRoute('id', -1);
        $ptu = $this->entityManager->getRepository(Ptu::class)
                ->findOneById($ptuId);        

        if ($ptu == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->ptuManager->removePtu($ptu);
        
        // Перенаправляем пользователя на страницу "rb/tax".
        return new JsonModel(
           ['ok']
        );           
    }
    
    public function autocompeteUnitAction()
    {
        $result = [];
        $q = $this->params()->fromQuery('q');
        
        if ($q){
            $query = $this->entityManager->getRepository(Unit::class)
                            ->autocompeteUnit(['search' => $q]);

            $data = $query->getResult();
            foreach ($data as $row){
                $result[] = $row->getName();
            }
        }    
        
        return new JsonModel($result);
    }        
    
    public function autocompeteNtdAction()
    {
        $result = [];
        $q = $this->params()->fromQuery('q');
        
        if ($q){
            $query = $this->entityManager->getRepository(Ntd::class)
                            ->autocompeteNtd(['search' => $q]);

            $data = $query->getResult();
            foreach ($data as $row){
                $result[] = $row->getName();
            }
        }    
        
        return new JsonModel($result);
    }        
    
    public function autocompeteCountryAction()
    {
        $result = [];
        $q = $this->params()->fromQuery('q');
        
        if ($q){
            $query = $this->entityManager->getRepository(Country::class)
                            ->autocompeteCountry(['search' => $q]);

            $data = $query->getResult();
            foreach ($data as $row){
                $result[] = $row->getName();
            }
        }    
        
        return new JsonModel($result);
    }        
    
}
