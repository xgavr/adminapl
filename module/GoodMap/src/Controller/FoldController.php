<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace GoodMap\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Company\Entity\Office;
use GoodMap\Entity\Rack;
use GoodMap\Entity\Shelf;
use GoodMap\Entity\Cell;
use GoodMap\Entity\FoldDoc;
use Application\Entity\Goods;


class FoldController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    /**
     * GoodMap manager.
     * @var \GoodMap\Service\GoodMapManager
     */
    private $goodMapManager;
        
    /**
     * Fold manager.
     * @var \GoodMap\Service\FoldManager
     */
    private $foldManager;
        
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $goodMapManager, $foldManager) 
    {
       $this->entityManager = $entityManager;
       $this->goodMapManager = $goodMapManager;    
       $this->foldManager = $foldManager;           
    }

    
    public function indexAction()
    {
        $offices = $this->entityManager->getRepository(Office::class)
                ->findAll();
        
        return new ViewModel([
            'offices' => $offices,
            'currentOfficeId' => $this->goodMapManager->currentUser()->getOffice()->getId(),
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
        
        $params = [
            'q' => $q, 'sort' => $sort, 'order' => $order, 
            'officeId' => $officeId,
        ];
        
        $result = $this->entityManager->getRepository(Rack::class)
                        ->findAllRack($params);
        
        $total = count($result);
        
//        if ($offset) {
//            $query->setFirstResult($offset);
//        }
//        if ($limit) {
//            $query->setMaxResults($limit);
//        }

//        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);          
    }        
    
    public function foldsAction()
    {
        $officeId = (int)$this->params()->fromQuery('office', -1);
        
        $params = array_filter([
           'officeId' => $officeId, 
           'sort' => 'code', 
           'order' => 'asc', 
        ]);
        
        $data = $this->entityManager->getRepository(Rack::class)
                ->findAllRack($params);
        $result = [];
        foreach ($data as $row){
            $result[$row['code']] = $row['name'];  
        }
        
        return new JsonModel($result);                  
    }
    
    public function changeCodeAction()
    {
        $goodId = $this->params()->fromQuery('good', -1);
        $officeId = $this->params()->fromQuery('office', -1);
        $code = $this->params()->fromQuery('code');
        $rest = $this->params()->fromQuery('rest');
        
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $code = $data['value'];
        }    
        
        $good = $this->entityManager->getRepository(Goods::class)
                ->find($goodId);
        $office = $this->entityManager->getRepository(Office::class)
                ->find($officeId);
        
        $decodedCode = $this->goodMapManager->decodeCode($code);
        
        $foldDoc = $this->entityManager->getRepository(FoldDoc::class)
                ->findOneBy(['office' => $office, 
                    'good' => $good,
                    'docDate' => date('Y-m-d'),
                    'status' => FoldDoc::STATUS_ACTIVE,
                    'kind' => FoldDoc::KIND_SET,
        ]);
        
        if ($foldDoc){
            $this->foldManager->updateFoldDoc($foldDoc, [
                'cell' => $decodedCode['cell'],
                'docDate' => date('Y-m-d'),
                'good' => $good,
                'kind' => FoldDoc::KIND_SET,
                'office' => $office,
                'quantity' => $rest,
                'rack' => $decodedCode['rack'],
                'shelf' => $decodedCode['shelf'],
                'status' => FoldDoc::STATUS_ACTIVE,
            ]);            
        } else {        
            $this->foldManager->addFoldDoc([
                'cell' => $decodedCode['cell'],
                'docDate' => date('Y-m-d'),
                'good' => $good,
                'kind' => FoldDoc::KIND_SET,
                'office' => $office,
                'quantity' => $rest,
                'rack' => $decodedCode['rack'],
                'shelf' => $decodedCode['shelf'],
                'status' => FoldDoc::STATUS_ACTIVE,
            ]);
        }    
        
        return new JsonModel(
           ['ok']
        );                   
    }    
    
    public function editFormAction()
    {
        $foldDocId = (int)$this->params()->fromRoute('id', -1);
        
        $foldDoc = null;
        $notDisabled = true;        

        if ($foldDocId > 0){
            $foldDoc = $this->entityManager->getRepository(FoldDoc::class)
                    ->find($foldDocId);
        }    
        
        $office = $this->goodMapManager->currentUser()->getOffice();
        if ($foldDoc){
            $office = $foldDoc->getOffice();
        }       
        
        $form = new PtForm($this->entityManager);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                $data['status_ex'] = Pt::STATUS_EX_NEW;
                $data['office'] = $office;
                $data['company'] = $company;
                $data['office2'] = $office2;
                $data['company2'] = $company2;
                $data['apl_id'] = 0;

                if ($foldDoc){
                    $data['apl_id'] = $pt->getAplId();
                    $this->foldManager->updatePt($pt, $data);
                } else {
                    $this->foldManager->addPt($data);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($foldDoc){
                $data = [
                    'office' => $foldDoc->getOffice()->getId(),
                    'fold' => $pt->getCompany()->getId(),
                    'good' => $foldDoc->getGood()->getId(),
                    'code' => $foldDoc->getGood()->getCode(),
                    'goodInputName' => $foldDoc->getGood()->getNameShort(),  
                    'quantity' => $foldDoc->getQuantity(),
                ];
                $form->setData($data);
                $notDisabled = $pt->getDocDate() > $this->ptManager->getAllowDate();
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'foldDoc' => $foldDoc,
            'disabled' => !$notDisabled,
            'allowDate' => $this->foldManager->getAllowDate(),
        ]);        
    }    
}
