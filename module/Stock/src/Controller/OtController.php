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
use Stock\Entity\Ot;
use Stock\Form\OtForm;
use Stock\Form\OtGoodForm;
use Application\Entity\Goods;
use Company\Entity\Office;
use Company\Entity\Legal;
use Application\Entity\Contact;

class OtController extends AbstractActionController
{

    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Менеджер пту.
     * @var \Stock\Service\OtManager
     */
    private $otManager;

    public function __construct($entityManager, $otManager) 
    {
        $this->entityManager = $entityManager;
        $this->otManager = $otManager;
    }   

    public function indexAction()
    {
        $offices = $this->entityManager->getRepository(Office::class)
                ->findAll();
        return new ViewModel([
            'offices' => $offices,
            'years' => array_combine(range(date("Y"), 2014), range(date("Y"), 2014)),
            'monthes' => array_combine(range(1, 12), range(1, 12)),
        ]);  
    }
        
    public function contentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        $officeId = $this->params()->fromQuery('office');
        $year_month = $this->params()->fromQuery('month');
        
        $year = $month = null;
        if ($year_month){
            $year = date('Y', strtotime($year_month));
            $month = date('m', strtotime($year_month));
        }
        
        $params = [
            'q' => $q, 'sort' => $sort, 'order' => $order, 
            'officeId' => $officeId,
            'year' => $year, 'month' => $month,
        ];
        $query = $this->entityManager->getRepository(Ot::class)
                        ->findAllOt($params);
        
        $total = $this->entityManager->getRepository(Ot::class)
                        ->findAllOtTotal($params);
        
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
        	        
        $otId = $this->params()->fromRoute('id', -1);
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        
        $query = $this->entityManager->getRepository(Ot::class)
                        ->findOtGoods($otId, ['q' => $q, 'sort' => $sort, 'order' => $order]);
        
        $total = count($query->getResult(2));
        
        $result = $query->getResult(2);
        
        return new JsonModel($result);          
    }        
    
    public function repostAllOtAction()
    {                
        $this->otManager->repostAllOt();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }        
    
    public function editFormAction()
    {
        $otId = (int)$this->params()->fromRoute('id', -1);
        
        $ot = $office = $company = $comiss = $contactName= null;
        $notDisabled = true;        
        if ($otId > 0){
            $ot = $this->entityManager->getRepository(Ot::class)
                    ->findOneById($otId);
        }    
        
        if ($ot == null) {
            $officeId = (int)$this->params()->fromQuery('office', 1);
            $office = $this->entityManager->getRepository(Office::class)
                    ->findOneById($officeId);
        } else {
            $office = $ot->getOffice();
            $company = $ot->getCompany();
            $comiss = $ot->getComiss();
        }       
        
        if ($this->getRequest()->isPost()){
            $data = $this->params()->fromPost();
            $office = $this->entityManager->getRepository(Office::class)
                    ->findOneById($data['office_id']);
            $company = $this->entityManager->getRepository(Legal::class)
                    ->findOneById($data['company']);
            $comiss = $this->entityManager->getRepository(Contact::class)
                    ->findOneById($data['comiss']);
        }
                
        $form = new OtForm($this->entityManager, $office, $company, $comiss);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                unset($data['csrf']);
                $otGood = $data['otGood'];
                unset($data['otGood']);
                $data['status_ex'] = Ot::STATUS_EX_NEW;
                $data['office'] = $office;
                $data['company'] = $company;
                $data['comiss'] = $comiss;
                if ($data['status'] != Ot::STATUS_COMMISSION){
                    $data['comiss'] = null;
                }
                $data['apl_id'] = 0;

                if ($ot){
                    $data['apl_id'] = $ot->getAplId();
                    $this->otManager->updateOt($ot, $data);
                    $this->entityManager->refresh($ot);
                } else {
                    $ot = $this->otManager->addOt($data);
                }    
                
                $this->otManager->updateOtGoods($ot, $otGood);
                
                $this->otManager->repostOt($ot);
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($ot){
                $data = [
                    'office_id' => $ot->getOffice()->getId(),
                    'company' => $ot->getCompany()->getId(),
                    'doc_date' => $ot->getDocDate(),  
                    'doc_no' => $ot->getDocNo(),
                    'comment' => $ot->getComment(),
                    'status' => $ot->getStatus(),
                ];
                if ($ot->getComiss()){
                    $data['comiss'] = $ot->getComiss()->getId();
                    if ($ot->getComiss()->getPhone()){
                        $data['comissSearch'] = $ot->getComiss()->getPhone()->getName(\User\Filter\PhoneFilter::PHONE_FORMAT_DB);
                    }    
                    $contactName = $ot->getComiss()->getName();
                }
                $form->setData($data);
                $notDisabled = $ot->getDocDate() > $this->otManager->getAllowDate();
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'ot' => $ot,
            'allowDate' => $this->otManager->getAllowDate(),
            'disabled' => !$notDisabled,
            'contactName' => $contactName,
        ]);        
    }    
        
    public function combinedFormAction()
    {
        $ot = $office = $company = $comiss = $contactName= null;
        $notDisabled = true;   
        
        $goodId = $this->params()->fromRoute('id', -1);
        $officeId = (int) $this->params()->fromQuery('office');
        
        if ($officeId > 0){
            $office = $this->entityManager->getRepository(Office::class)
                    ->find($officeId);
            if (!$office){
                $office = $this->stManager->currentUser()->getOffice();
            }
        }    

        if ($this->getRequest()->isPost()){
            $data = $this->params()->fromPost();
            $office = $this->entityManager->getRepository(Office::class)
                    ->find($data['office_id']);
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($data['company']);
            $comiss = $this->entityManager->getRepository(Contact::class)
                    ->find($data['comiss']);
        }
                
        $form = new OtForm($this->entityManager, $office, $company, $comiss);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                unset($data['csrf']);
                $otGood = ['good_id' => $good->getId(), 'quantity' => $data['quantity'], 'amount' => $data['amount']];
                unset($data['otGood']);
                $data['status_ex'] = Ot::STATUS_EX_NEW;
                $data['office'] = $office;
                $data['company'] = $company;
                $data['comiss'] = $comiss;
                if ($data['status'] != Ot::STATUS_COMMISSION){
                    $data['comiss'] = null;
                }
                $data['apl_id'] = 0;

                $ot = $this->otManager->addOt($data);
                
                $this->otManager->addOtGood($ot, $otGood, 1);
                
                $this->otManager->updateOtAmount($ot);
                
                return new JsonModel(
                   ['ok']
                );           
            }
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'ot' => $ot,
            'allowDate' => $this->otManager->getAllowDate(),
            'disabled' => !$notDisabled,
            'contactName' => $contactName,
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
        
        $form = new OtGoodForm($this->entityManager, $good);

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
                    'good' => $good->getId(),
                    'code' => $good->getCode(),
                    'goodInputName' => $good->getInputName(),
                    'quantity' => $params['quantity'],
                    'amount' => $params['amount'],
                    'price' => $params['amount']/$params['quantity'],
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
    
    public function deleteOtAction()
    {
        $otId = $this->params()->fromRoute('id', -1);
        $ot = $this->entityManager->getRepository(Ot::class)
                ->findOneById($otId);        

        if ($ot == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->otManager->removeOt($ot);
        
        return new JsonModel(
           ['ok']
        );           
    }
        
}
