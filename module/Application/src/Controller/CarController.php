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
use Application\Entity\Make;
use Application\Entity\Model;
use Application\Entity\Car;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Laminas\Paginator\Paginator;

class CarController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер товаров.
     * @var \Application\Service\CarManager 
     */
    private $carManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $carManager) 
    {
        $this->entityManager = $entityManager;
        $this->carManager = $carManager;
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
        $status = $this->params()->fromQuery('status', 1);
        
        $query = $this->entityManager->getRepository(Make::class)
                        ->findAllMake(['q' => $q, 'sort' => $sort, 'order' => $order, 'status' => $status]);

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
        $carId = (int)$this->params()->fromRoute('id', -1);
        $page = $this->params()->fromQuery('page', 1);
        
        // Validate input parameter
        if ($carId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $car = $this->entityManager->getRepository(Car::class)
                ->findOneById($carId);
        
        if ($car == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $prevQuery = $this->entityManager->getRepository(Car::class)
                        ->findAllCar($car->getModel(), ['prev1' => $car->getTdId()]);
        $nextQuery = $this->entityManager->getRepository(Car::class)
                        ->findAllCar($car->getModel(), ['next1' => $car->getTdId()]);        

        $goodsQuery = $this->entityManager->getRepository(Car::class)
                        ->findGoods($car);
        
        $goodsAdapter = new DoctrineAdapter(new ORMPaginator($goodsQuery, false));
        $goodsPaginator = new Paginator($goodsAdapter);
        $goodsPaginator->setDefaultItemCountPerPage(10);        
        $goodsPaginator->setCurrentPageNumber($page);

        $totalGoods = $goodsPaginator->getTotalItemCount();
        // Render the view template.
        return new ViewModel([
            'car' => $car,
            'goods' => $goodsPaginator,
            'totalGoods' => $totalGoods,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
        ]);
    }      
    
    public function updateAvailableAction()
    {
        $carId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($carId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $car = $this->entityManager->getRepository(Car::class)
                ->findOneById($carId);
        
        if ($car == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->entityManager->getRepository(Car::class)
                ->updateAvailable($car);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);                  
    }

    public function updateAllStatusAction()
    {

        $this->entityManager->getRepository(Car::class)
                ->updateAllCarStatus();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);                  
    }


    public function fillCarsAction()
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

        $this->carManager->fillCars($model);

        return new JsonModel([
            'result' => 'ok-reload',
        ]);                  
    }
    
    public function fillMakeCarsAction()
    {
        set_time_limit(1800);
        
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

        foreach ($make->getModels() as $model){
            $this->carManager->fillCars($model);
        }    

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

        // Render the view template.
        return new ViewModel([
            'model' => $model,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
        ]);
    }      
 
    
    public function AttributeTypesAction()
    {
        return new ViewModel([
        ]);
        
    }
    
    public function attributeTypesContentAction()
    {
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');

        $query = $this->entityManager->getRepository(\Application\Entity\CarAttributeType::class)
                        ->findAttributeTypes();

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
    
    public function attributeEditAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $attributeId = $data['pk'];
            $attribute = $this->entityManager->getRepository(\Application\Entity\CarAttributeType::class)
                    ->findOneById($attributeId);
                    
            if ($attribute){
                $this->carManager->updateAttributeType($attribute, $data);
            }    
        }
        
        exit;
    }

    public function vehicleDetailContentAction()
    {
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');

        $query = $this->entityManager->getRepository(\Application\Entity\VehicleDetail::class)
                        ->findVehicleDetails();

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
    
    public function vehicleDetailEditAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $attributeId = $data['pk'];
            $attribute = $this->entityManager->getRepository(\Application\Entity\VehicleDetail::class)
                    ->findOneById($attributeId);
                    
            if ($attribute){
                $this->carManager->updateVehicleDetail($attribute, $data);
            }    
        }
        
        exit;
    }

    public function vehicleDetailStatusEditAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $attributeId = $data['pk'];
            $attribute = $this->entityManager->getRepository(\Application\Entity\VehicleDetail::class)
                    ->findOneById($attributeId);
                    
            if ($attribute){
                $currentStatusEdit = $attribute->getStatusEdit();
                
                if ($currentStatusEdit == \Application\Entity\VehicleDetail::CANNOT_VALUE_EDIT){
                    $currentStatusEdit = \Application\Entity\VehicleDetail::CAN_VALUE_EDIT;
                } else {
                    $currentStatusEdit = \Application\Entity\VehicleDetail::CANNOT_VALUE_EDIT;
                }
                
                $this->carManager->updateVehicleDetailStatusEdit($attribute, $currentStatusEdit);
            }    
        }
        
        exit;
    }

    public function vehicleDetailValueEditAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $valueId = $data['pk'];
            $value = $this->entityManager->getRepository(\Application\Entity\VehicleDetailValue::class)
                    ->findOneById($valueId);
                    
            if ($value){
                $this->carManager->updateVehicleDetailValue($value, $data);
            }    
        }
        
        exit;
    }
    
    public function fixModelAction()
    {
        $carId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($carId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $car = $this->entityManager->getRepository(Car::class)
                ->findOneById($carId);
        
        if ($car == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->carManager->fixModel($car);

        return new JsonModel([
            'result' => 'ok-reload',
        ]);                  
    }

    public function fixCarsAction()
    {
        
        $this->carManager->fixCars();

        return new JsonModel([
            'result' => 'ok-reload',
        ]);                  
    }

    public function fillVolumesAction()
    {
        $carId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($carId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $car = $this->entityManager->getRepository(Car::class)
                ->findOneById($carId);
        
        if ($car == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->carManager->addCarFillVolume($car);

        return new JsonModel([
            'result' => 'ok-reload',
        ]);                  
    }

    public function carFillVolumesAction()
    {
       
        $this->carManager->carFillVolumes();

        return new JsonModel([
            'result' => 'ok-reload',
        ]);                  
    }

}
