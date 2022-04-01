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

        return new ViewModel([
            'billManager' => $this->billManager,
        ]);  
    }
    
    public function contentAction()
    {
        
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $params = [];
        
        $query = $this->entityManager->getRepository(Idoc::class)
                    ->queryAllIdocs($params);            
        
        $total = $this->entityManager->getRepository(Idoc::class)
                ->count([]);
        
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
            if (!$billSetting){
                $billSetting = $this->entityManager->getRepository(BillSetting::class)
                        ->findOneBy(['supplier' => $idoc->getSupplier()->getId()]);
            }    
        }        
        
        $form = new BillSettingForm();

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {

                if ($billSetting){
                    $this->billManager->updateBillSetting($billSetting, $data);                    
                } else {
                    $data['name'] = $idoc->getName();
                    $this->billManager->addBillSetting($idoc->getSupplier(), $data);                        
                }    
                
                return new JsonModel(
                   ['ok']
                );           
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
    
}
