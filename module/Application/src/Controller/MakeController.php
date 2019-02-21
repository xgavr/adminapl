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
use Application\Entity\Make;
use Application\Entity\Model;
use Application\Form\MakeForm;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class MakeController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер товаров.
     * @var Application\Service\MakeManager 
     */
    private $makeManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $makeManager) 
    {
        $this->entityManager = $entityManager;
        $this->makeManager = $makeManager;
    }    
    
    public function indexAction()
    {
        $totalMake = $this->entityManager->getRepository(Make::class)
                ->count([]);
        $totalModel = $this->entityManager->getRepository(Model::class)
                ->count([]);
        $totalCar = $this->entityManager->getRepository(\Application\Entity\Car::class)
                ->count([]);
        
        return new ViewModel([
            'totalMake' =>$totalMake,
            'totalModel' =>$totalModel,
            'totalCar' =>$totalCar,
        ]);  
    }
    
    public function contentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        $limit = $this->params()->fromQuery('limit');
        $status = $this->params()->fromQuery('status', 1);
        
        $query = $this->entityManager->getRepository(Make::class)
                        ->findAllMake(['q' => $q, 'sort' => $sort, 'order' => $order, 'status' => $status]);

        $total = count($query->getResult(2));
        
        if ($offset) $query->setFirstResult( $offset );
        if ($limit) $query->setMaxResults( $limit );

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);          
    }    
    
    public function makeFormAction()
    {
        $makeId = (int)$this->params()->fromRoute('id', -1);
        
        if ($makeId > 0) {
            $make = $this->entityManager->getRepository(Make::class)
                    ->findOneById($makeId);
            if ($make == null) {
                $this->getResponse()->setStatusCode(404);
                return;                        
            }        
        }
             
        $form = new MakeForm($this->entityManager);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);
            if ($form->isValid()) {
                
                $this->makeManager->addMake($data);
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
            }
        } else {
            if ($make){
                $data = [
                    'fullName' => $make->getFullName(),  
                ];
                $form->setData($data);
            }    
        }        
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'make' => $make,
        ]);                
    }
    
    public function fillMakesAction()
    {
        $this->makeManager->fillMakes();

        return new JsonModel([
            'result' => 'ok',
        ]);                  
    }
    
    public function viewAction() 
    {       
        $makeId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($makeId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $make = $this->entityManager->getRepository(Make::class)
                ->findOneById($makeId);
        
        if ($make == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $prevQuery = $this->entityManager->getRepository(Make::class)
                        ->findAllMake(['prev1' => $make->getName()]);
        $nextQuery = $this->entityManager->getRepository(Make::class)
                        ->findAllMake(['next1' => $make->getName()]); 
        
        $totalModel = $this->entityManager->getRepository(Model::class)
                ->count(['make' => $make->getId()]);
        $totalCar = count($this->entityManager->getRepository(Make::class)
                ->carMake($make));

        $totalGoods = count($this->entityManager->getRepository(Make::class)
                ->findMakeGoods($make));

        // Render the view template.
        return new ViewModel([
            'make' => $make,
            'totalModel' => $totalModel,
            'totalCar' => $totalCar,
            'totalGoods' => $totalGoods,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
        ]);
    }      
    
    public function fillModelsAction()
    {
        $makeId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($makeId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $make = $this->entityManager->getRepository(Make::class)
                ->findOneById($makeId);
        
        if ($make == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->makeManager->fillModels($make);

        return new JsonModel([
            'result' => 'ok-reload',
        ]);                  
    }
    
    public function fillAllModelsAction()
    {

        $this->makeManager->fillAllModels();

        return new JsonModel([
            'result' => 'ok',
        ]);                  
    }
    
    public function updateAvailableMakeAction()
    {
        $makeId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($makeId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $make = $this->entityManager->getRepository(Make::class)
                ->findOneById($makeId);
        
        if ($make == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->entityManager->getRepository(\Application\Entity\Car::class)
                ->updateAvailable($make);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);                  
    }
    
    public function viewModelAction() 
    {       
        $modelId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($modelId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $model = $this->entityManager->getRepository(Model::class)
                ->findOneById($modelId);
        
        if ($model == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $prevQuery = $this->entityManager->getRepository(Model::class)
                        ->findAllModel($model->getMake(), ['prev1' => $model->getName()]);
        $nextQuery = $this->entityManager->getRepository(Model::class)
                        ->findAllModel($model->getMake(), ['next1' => $model->getName()]);        

        $totalCar = $this->entityManager->getRepository(\Application\Entity\Car::class)
                ->count(['model' => $model->getId()]);
        
        $totalGoods = count($this->entityManager->getRepository(Make::class)
                ->findModelGoods($model));
        
        // Render the view template.
        return new ViewModel([
            'model' => $model,
            'totalCar' => $totalCar,
            'totalGoods' => $totalGoods,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
        ]);
    }      
    
    public function updateAvailableModelAction()
    {
        $modelId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($modelId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $model = $this->entityManager->getRepository(Model::class)
                ->findOneById($modelId);
        
        if ($model == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->entityManager->getRepository(\Application\Entity\Car::class)
                ->updateAvailable(null, $model);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);                  
    }
    
}
