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
use Application\Entity\GenericGroup;
use Application\Entity\Goods;
use Application\Entity\Rate;
use Stock\Entity\Movement;

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
                        ->findAllGroup(['prev1' => $group->getName(), 'status' => GenericGroup::STATUS_ACTIVE]);
        $nextQuery = $this->entityManager->getRepository(GenericGroup::class)
                        ->findAllGroup(['next1' => $group->getName(), 'status' => GenericGroup::STATUS_ACTIVE]); 
        $aplGroups = $this->entityManager->getRepository(GenericGroup::class)
                ->getGroupApl($group);

        $rate = $this->entityManager->getRepository(Rate::class)
                ->findRate(['genericGroup' => $group->getId()]);
        
        return new ViewModel([
            'group' => $group,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
            'aplGroups' => $aplGroups,
            'rate' => $rate,
        ]);
    }      
    
    public function tokenGroupContentAction()
    {
        $groupId = $this->params()->fromRoute('id', -1);

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
        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        $limit = $this->params()->fromQuery('limit');
        
        $query = $this->entityManager->getRepository(GenericGroup::class)
                        ->tokenGenericGroup($group, ['q' => $q, 'sort' => $sort, 'order' => $order]);

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

    public function tokenContentAction()
    {
        $groupId = $this->params()->fromRoute('id', -1);

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
        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        $limit = $this->params()->fromQuery('limit');
        
        $query = $this->entityManager->getRepository(GenericGroup::class)
                        ->getTokens($group, ['q' => $q, 'sort' => $sort, 'order' => $order]);

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

        $goodCount = $this->entityManager->getRepository(Goods::class)
                ->count(['genericGroup' => $group->getId()]);
        $this->entityManager->getConnection()->update('generic_group', ['good_count' => $goodCount], ['id' => $group->getId()]);            


        return new JsonModel([
            'result' => 'ok-reload',
        ]);                  
                
    }
 
    public function updateMovementAction()
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

        $this->entityManager->getRepository(Movement::class)
                ->groupMovementCount($group);

        return new JsonModel([
            'result' => 'ok-reload',
        ]);                  
                
    }
    
    /**
     * Обновить движения
     */
    public function updateAllMovementAction()
    {
        $groups = $this->entityManager->getRepository(GenericGroup::class)
                ->findBy([]);
        foreach ($groups as $group){
            $this->entityManager->getRepository(Movement::class)
                    ->groupMovementCount($group);
        }
        
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
            'result' => 'ok-reload',
        ]);                          
    }
    
    public function updateAplGroupsAction()
    {
        $groups = $this->entityManager->getRepository(GenericGroup::class)
                ->findBy(['status' => GenericGroup::STATUS_ACTIVE]);
        foreach ($groups as $group){
            if ($group->getTdId() > 0){
                $this->entityManager->getRepository(GenericGroup::class)
                        ->updateGroupApl($group);
            } else {
                $this->entityManager->getRepository(GenericGroup::class)
                        ->updateGroupApl(0);                
            }   
        }
        
        return new JsonModel([
            'result' => 'ok',
        ]);                          
    }
    
    public function updateGenericGroupTokenAction()
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
                ->updateGenericGroupToken($group);

        return new JsonModel([
            'result' => 'ok-reload',
        ]);                          
        
    }
    
    public function carUploadEditAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $groupId = $data['pk'];
            $group = $this->entityManager->getRepository(GenericGroup::class)
                    ->findOneById($groupId);
                    
            if ($group){
                $carUpload = GenericGroup::CAR_RETIRED;
                if ($group->getCarUpload() == GenericGroup::CAR_RETIRED){
                    $carUpload = GenericGroup::CAR_ACTIVE;
                }
                $this->entityManager->getConnection()->update('generic_group', ['car_upload' => $carUpload], ['id' => $group->getId()]);
            }    
        }
        
        exit;
    }
    
}
