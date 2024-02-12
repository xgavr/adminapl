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
use Zp\Entity\Position;
use Zp\Form\PositionForm;
use Company\Entity\Legal;


class PositionController extends AbstractActionController
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
        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        
        return new ViewModel([
            'companies' => $companies,
        ]);
    }
    
    public function contentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $company = $this->params()->fromQuery('company');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        
        $params = [
            'q' => $q, 'company' => $company, 'sort' => $sort, 'order' => $order, 
        ];
        
        $query = $this->entityManager->getRepository(Position::class)
                        ->findPosition($params);
        
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
    
    public function selectAction()
    {
        $companyId = (int)$this->params()->fromQuery('company');

        $result = [];
        $parentPositions = $this->entityManager->getRepository(Position::class)
                ->findParentPositions(['company' => $companyId]);
        foreach ($parentPositions as $parentPosition){
            $result[$parentPosition->getId()] = [
                'id' => $parentPosition->getId(),
                'name' => $parentPosition->getName(),                
            ];
        }                

        return new JsonModel([
            'rows' => $result,
        ]);                  
    }
    
    public function editFormAction()
    {
        $positionId = (int)$this->params()->fromRoute('id', -1);
        $companyId = $this->params()->fromQuery('company');
        
        $position = null;
        if ($positionId > 0){
            $position = $this->entityManager->getRepository(Position::class)
                    ->find($positionId);
            $companyId = $position->getCompany()->getId();
        }    
        
        $parentPositionList = ['это группа'];
        $parentPositions = $this->entityManager->getRepository(Position::class)
                ->findParentPositions(['company' => $companyId]);
        foreach ($parentPositions as $parentPosition){
            $parentPositionList[$parentPosition->getId()] = $parentPosition->getname();
        }                
        
        $companyList = [];
        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        foreach ($companies as $company){
            $companyList[$company->getId()] = $company->getname();
        }                
        
        $form = new PositionForm();
        $form->get('parentPosition')->setValueOptions($parentPositionList);
        $form->get('company')->setValueOptions($companyList);
        $form->get('company')->setValue($companyId);
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                if (is_numeric($data['parentPosition'])){
                    $data['parentPosition'] = $this->entityManager->getRepository(Position::class)
                            ->find($data['parentPosition']);
                    $data['kind'] = $data['parentPosition']->getKind();
                }
                if (is_numeric($data['company'])){
                    $data['company'] = $this->entityManager->getRepository(Legal::class)
                            ->find($data['company']);
                }
                if ($position){
                    $this->zpManager->updatePosition($position, $data);
                } else {
                    $position = $this->zpManager->addPosition($data);
                }    
                                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($position){
                $data = [
                    'aplId' => $position->getAplId(),
                    'name' => $position->getName(),
                    'num' => $position->getNum(),
                    'status' => $position->getStatus(),
                    'kind' => $position->getKind(),
                    'parentPosition' => ($position->getParentPosition()) ? $position->getParentPosition()->getId():null,
                    'company' => $position->getCompany()->getId(),
                ];
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'position' => $position,
        ]);        
    }    
    
}
