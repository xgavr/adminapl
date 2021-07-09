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
use Application\Entity\Ring;
use Application\Form\RingForm;


class RingController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер.
     * @var \Application\Service\RingManager 
     */
    private $ringManager;    
        
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $ringManager) 
    {
        $this->entityManager = $entityManager;
        $this->ringManager = $ringManager;
    }    
    
    public function indexAction()
    {        
        return new ViewModel([
        ]);  
    }
    
    public function contentAction()
    {
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        $limit = $this->params()->fromQuery('limit');
        $status = $this->params()->fromQuery('status', Ring::STATUS_ACTIVE);
        
        $query = $this->entityManager->getRepository(Ring::class)
                        ->findAllRing(['q' => $q, 'sort' => $sort, 'order' => $order, 'status' => $status]);

        $total = count($query->getResult(2));
        
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
        $ringId = (int)$this->params()->fromRoute('id', -1);
        
        $ring = null;
        
        if ($ringId > 0){
            $ring = $this->entityManager->getRepository(Ring::class)
                    ->find($ringId);
        }    

        $form = new RingForm();

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                $ring = $this->ringManager->addRing($data);
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($ring){
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
            'ring' => $ring,
        ]);        
    }    
    
    public function bindAction()
    {
        $crossId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($crossId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $cross = $this->entityManager->getRepository(Cross::class)
                ->findOneById($crossId);
        
        $this->crossManager->bindCross($cross);
        
        return new JsonModel(
           ['ok']
        );                   
    }        

    public function resetAction()
    {
        $crossId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($crossId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $cross = $this->entityManager->getRepository(Cross::class)
                ->findOneById($crossId);
        
        $this->crossManager->resetCross($cross);
        
        return new JsonModel(
           ['ok']
        );                   
    }        
}
