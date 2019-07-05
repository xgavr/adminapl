<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Entity\GenericGroup;

class GroupController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager) 
    {
        $this->entityManager = $entityManager;
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
        $status = $this->params()->fromQuery('status', GenericGroup::STATUS_ACTIVE);
        
        $query = $this->entityManager->getRepository(GenericGroup::class)
                        ->findAllGroup(['q' => $q, 'sort' => $sort, 'order' => $order, 'status' => $status]);

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
    
    public function viewAction() 
    {       
        $groupId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($groupId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $group = $this->entityManager->getRepository(GenericGroup::class)
                ->findOneById($groupId);
        
        if ($group == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $prevQuery = $this->entityManager->getRepository(GenericGroup::class)
                        ->findAllGroup(['prev1' => $group->getName()]);
        $nextQuery = $this->entityManager->getRepository(GenericGroup::class)
                        ->findAllGroup(['next1' => $group->getName()]);        

        // Render the view template.
        return new ViewModel([
            'group' => $group,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
        ]);
    }      
    
    public function updateGoodCountAction()
    {
        $groupId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($groupId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $group = $this->entityManager->getRepository(GenericGroup::class)
                ->findOneById($groupId);
        
        if ($group == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $goodCount = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->count(['genericGroup' => $group->getId()]);
        $this->entityManager->getConnection()->update('generic_group', ['good_count' => $goodCount], ['id' => $group->getId()]);            


        return new JsonModel([
            'result' => 'ok-reload',
        ]);                  
                
    }
 
    public function updateGroupAplAction()
    {
        $groupId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($groupId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $group = $this->entityManager->getRepository(GenericGroup::class)
                ->findOneById($groupId);
        
        if ($group == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->entityManager->getRepository(GenericGroup::class)
                ->updateGroupApl($group);

        return new JsonModel([
            'result' => 'ok',
        ]);                  
        
    }
}
