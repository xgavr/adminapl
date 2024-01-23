<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zp\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Zp\Entity\Personal;
use Zp\Form\PersonalForm;


class PersonalController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    /**
     * Zp manager.
     * @var \Zp\Service\ZpManager
     */
    private $zpManager;
        
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $zpManager) 
    {
       $this->entityManager = $entityManager;
       $this->zpManager = $zpManager;
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
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        
        $params = [
            'q' => $q, 'sort' => $sort, 'order' => $order, 
        ];
        
        $query = $this->entityManager->getRepository(Personal::class)
                        ->findPersonal($params);
        
        $total = count($query->getResult());
        
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
        $accrualId = (int)$this->params()->fromRoute('id', -1);
        
        $accrual = null;
        if ($accrualId > 0){
            $accrual = $this->entityManager->getRepository(Accrual::class)
                    ->find($accrualId);
        }    
                
        $form = new AccrualForm();

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {

                if ($accrual){
                    $this->zpManager->updateAccrual($accrual, $data);
                } else {
                    $accrual = $this->zpManager->addAccrual($data);
                }    
                                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($accrual){
                $data = [
                    'aplId' => $accrual->getAplId(),
                    'basis' => $accrual->getBasis(),
                    'name' => $accrual->getName(),
                    'status' => $accrual->getStatus(),
                ];
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'accrual' => $accrual,
        ]);        
    }    
    
}
