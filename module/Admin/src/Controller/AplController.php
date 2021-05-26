<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Bank\Entity\AplPayment;


class AplController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * AplService manager.
     * @var \Admin\Service\AplService
     */
    private $aplService;    
    
    /**
     * AplBankService manager.
     * @var \Admin\Service\AplBankService
     */
    private $aplBankService;    

    /**
     * AplDocService manager.
     * @var \Admin\Service\AplDocService
     */
    private $aplDocService;    

    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $aplService, $aplBankService, $aplDocService) 
    {
        $this->entityManager = $entityManager;
        $this->aplService = $aplService;        
        $this->aplBankService = $aplBankService;        
        $this->aplDocService = $aplDocService;        
    }   

    
    public function indexAction()
    {
        return [];
    }
    
    public function getSuppliersAction()
    {
        $this->aplService->getSuppliers();
        
        return new JsonModel([
            'ok'
        ]);
    }

    public function getClientsAction()
    {
        $this->aplService->uploadUsers();
        
        return new JsonModel([
            'ok'
        ]);
    }
    
    /*
     * Копирование прайсов с autopartslist.ru
     */
    public function aplMirrorAction()
    {
        
    }
    
    /*
     * Сообщения в телеграм
     * $post api_key, chat_id, text
     */
    public function telegramAction()
    {
        $data = [];
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $this->aplService->sendTelegramMessage($data);
        }    
        
        return new JsonModel(
            $data
        );
    }
    
    public function transBankAction()
    {
        $this->aplBankService->sendBankStatement();
        return new JsonModel([
            'ok'
        ]);
    }

    public function producerAplIdAction()
    {
        $producerId = $this->params()->fromRoute('id', -1);
    
        $producer = $this->entityManager->getRepository(\Application\Entity\Producer::class)
                ->findOneById($producerId);  
        	
        if ($producer == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $this->aplService->updateProducerAplId($producer);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }

    public function updateProducerAplIdAction()
    {
        
        $this->aplService->updateProducersAplId();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }

    
    public function goodAplIdAction()
    {
        $goodId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $good = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->findOneById($goodId);  
        	
        if ($good == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $this->aplService->getGoodAplId($good);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }
    
    public function updateGoodAplIdAction()
    {
        
        $this->aplService->updateGoodAplId();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }

    public function groupAplIdAction()
    {
        $goodId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $good = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->findOneById($goodId);  
        	
        if ($good == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $this->aplService->getGroupAplId($good);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }
    
    public function updateGroupAplIdAction()
    {
        
        $this->aplService->updateGroupAplId();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }

    public function updateGoodNameAction()
    {
        $goodId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $good = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->findOneById($goodId);  
        	
        if ($good == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $this->aplService->updateGoodName($good);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }

    public function updateGoodPriceAction()
    {
        $goodId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $good = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->findOneById($goodId);  
        	
        if ($good == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $this->aplService->updateGoodPrice([$good]);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }
    
    public function updateGoodPricesAction()
    {
        
        $this->aplService->updateGoodPrices();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }    
    

    public function makeAplIdAction()
    {
        $makeId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $make = $this->entityManager->getRepository(\Application\Entity\Make::class)
                ->findOneById($makeId);  
        	
        if ($make == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $this->aplService->getMakeAplId($make);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }

    public function updateMakeAplIdAction()
    {
        
        $this->aplService->updateMakeAplId();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }

    public function modelAplIdAction()
    {
        $modelId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $model = $this->entityManager->getRepository(\Application\Entity\Model::class)
                ->findOneById($modelId);  
        	
        if ($model == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $this->aplService->getModelAplId($model);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }

    public function updateModelAplIdAction()
    {
        
        $this->aplService->updateModelAplId();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }

    public function updateCarAplIdAction()
    {
        
        $this->aplService->updateCarAplId();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }

    public function carAplIdAction()
    {
        $carId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $car = $this->entityManager->getRepository(\Application\Entity\Car::class)
                ->findOneById($carId);  
        	
        if ($car == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $this->aplService->getCarAplId($car);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }

    public function updateAcquiringAction()
    {
        
        $this->aplService->updateAcquiringPayments();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }

    public function exRawAction()
    {
        $rawId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                ->findOneById($rawId);  
        	
        if ($raw == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $this->aplService->sendRaw($raw);
        
        return new JsonModel([
            'oke'
        ]);
    }
    
    public function deleteRawAction()
    {
        $rawId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                ->findOneById($rawId);  
        	
        if ($raw == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $this->aplService->deleteRaw($raw);
        
        return new JsonModel([
            'ok',
        ]);                  
    }
    
    public function deleteOldRawAction()
    {
        $raws = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                ->findBy(['statusEx' => \Application\Entity\Raw::EX_TO_DELETE], null, 5);
        
        foreach ($raws as $raw){
            $this->aplService->deleteRaw($raw);
        }    
        
        return new JsonModel([
            'oke',
        ]);                  
    }
    
    public function exRawpriceAction()
    {
        $goodId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $good = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->findOneById($goodId);  
        	
        if ($good == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $this->aplService->sendRawpricesPackage([$good]);
//        $this->aplService->sendRawprices($good);
//        $this->aplService->updateGoodsRawprice($good);
        
        return new JsonModel([
            'oke'
        ]);
    }
    
    public function updateGoodRawpriceAction()
    {
        
        $this->aplService->updateGoodsRawprice();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }        
    
    public function updateRawpricesAction()
    {
        
        $this->aplService->updateRawprices();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }        
    
    public function exOemAction()
    {
        $goodId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $good = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->findOneById($goodId);  
        	
        if ($good == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $this->aplService->sendGoodOem($good);
        
        return new JsonModel([
            'oke'
        ]);
    }
    
    public function updateGoodOemAction()
    {
        
        $this->aplService->updateGoodsOem();
        
        return new JsonModel([
            'ok'
        ]);
    }        
    
    public function exImgAction()
    {
        $goodId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $good = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->findOneById($goodId);  
        	
        if ($good == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $this->aplService->sendGoodImg($good);
        
        return new JsonModel([
            'oke'
        ]);
    }
    
    public function updateGoodImgAction()
    {
        
        $this->aplService->updateGoodsImg();
        
        return new JsonModel([
            'ok'
        ]);
    }        

    public function exCarAction()
    {
        $goodId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $good = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->findOneById($goodId);  
        	
        if ($good == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $this->aplService->sendGoodCar($good);
        
        return new JsonModel([
            'oke'
        ]);
    }
    
    public function updateGoodCarAction()
    {
        
        $this->aplService->updateGoodsCar();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }        

    public function exGroupAction()
    {
        $goodId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $good = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->findOneById($goodId);  
        	
        if ($good == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $this->aplService->sendGroup($good);
        
        return new JsonModel([
            'oke'
        ]);
    }
    
    public function updateGoodGroupAction()
    {
        
        $this->aplService->updateGoodsGroup();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }        

    public function updateAttributeAplIdAction()
    {
        
        $this->aplService->updateAttributeAplId();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }        

    public function updateAttributeValueAplIdAction()
    {
        
        $this->aplService->updateAttributeValueAplId();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }        

    public function exAttrAction()
    {
        $goodId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $good = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->findOneById($goodId);  
        	
        if ($good == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $this->aplService->sendGoodAttribute($good);
        
        return new JsonModel([
            'oke'
        ]);
    }
    
    public function updateGoodAttributeAction()
    {
        
        $this->aplService->updateGoodsAttribute();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }    

    public function updateGoodNamesAction()
    {
        
        $this->aplService->updateGoodNames();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }    
    
    public function updateSupplierLegalAction()
    {
        $supplierId = $this->params()->fromRoute('id', -1);
        $contractId = $this->params()->fromQuery('contract', -1);
    
        // Находим существующий пост в базе данных.    
        $supplier = $this->entityManager->getRepository(\Application\Entity\Supplier::class)
                ->findOneById($supplierId);  
        	
        if ($supplier == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $contract = $this->entityManager->getRepository(\Company\Entity\Contract::class)
                ->findOneById($contractId);  
        	
        if ($contract == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $this->aplService->updateSupplierLegal($supplier, $contract);
        
        return new JsonModel([
            'oke',
        ]);
    }    
    
    public function addSupplierAction()
    {
        $supplierId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $supplier = $this->entityManager->getRepository(\Application\Entity\Supplier::class)
                ->findOneById($supplierId);  
        	
        if ($supplier == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
                
        $this->aplService->addSupplier($supplier);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }    
    
    public function unloadDocAction()
    {                
        $this->aplDocService->unloadDoc();
        
        return new JsonModel([
            'result' => 'ok',
        ]);
    }    
    
    public function unloadDocsAction()
    {                
        $this->aplDocService->unloadDocs();
        
        return new JsonModel([
            'result' => 'ok',
        ]);
    }    
    
    public function sendFillVolumesAction()
    {
        $carId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $car = $this->entityManager->getRepository(\Application\Entity\Car::class)
                ->findOneById($carId);  
        	
        if ($car == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
                
        $this->aplService->sendFillVolumes($car);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }    

    public function updateFillVolumesAction()
    {                
        $this->aplService->updateFillVolumes();
        
        return new JsonModel([
            'result' => 'ok',
        ]);
    }    
    
    public function showRefAction()
    {
        echo 'ref: '.$_SERVER['HTTP_REFERER'];
        exit;
    }
    
    public function rdctAction()
    {
        $redirectUrl = $this->params()->fromQuery('u');
//        var_dump($redirectUrl); exit;
        if ($redirectUrl){
            $response = $this->getResponse();
            $response->getHeaders()->addHeaderLine('Referer', 'ya.ru');
            $response->getHeaders()->addHeaderLine('Location', $redirectUrl);
            $response->setStatusCode(302);
            return $response;
        }
        
        exit;
    }
    
    public function asqrrnAction()
    {
        $aplPaymentId = $this->params()->fromRoute('id', -1);
        
        if ($aplPaymentId <= 0){
            $this->getResponse()->setStatusCode(401);
            goto r;                                    
        }
    
        $aplPayments = $this->entityManager->getRepository(AplPayment::class)
                ->findBy(['aplPaymentId' => $aplPaymentId]);
        	
        if ($aplPayments == null) {
            $this->getResponse()->setStatusCode(401);
            goto r;                        
        } 
        
        $result = [];
        foreach ($aplPayments as $aplPayment){
            $asquirings = $aplPayment->getAcquirings();
            foreach ($asquirings as $asquiring){
                if ($asquiring->getAmount() > 0){
                    $result[] = [
                        'cart' => $asquiring->getCart(),
                        'cartType' => $asquiring->getСartType(),
                        'amount' => $asquiring->getAmount(),
                        'transDate' => $asquiring->getTransDate(),
                        'rrn' => $asquiring->getRrn(),
                    ];
                }    
            }
        }
        
        r:
        return new JsonModel($result);
    }
    
    public function orderAsquiringAction()
    {
        $aplOrderId = $this->params()->fromRoute('id', -1);
        
        $result = [];
        if ($aplOrderId <= 0){
            $this->getResponse()->setStatusCode(401);
            goto r;                                   
        }
    
        $aplPayments = $this->entityManager->getRepository(AplPayment::class)
                ->findBy(['aplPaymentTypeId' => $aplOrderId, 'aplPaymentType' => 'Orders']);
        	
        if ($aplPayments == null) {
            $this->getResponse()->setStatusCode(401);
            goto r;                                   
        } 
        
        foreach ($aplPayments as $aplPayment){
            $asquirings = $aplPayment->getAcquirings();
            foreach ($asquirings as $asquiring){
                if ($asquiring->getAmount() > 0){
                    $result[] = [
                        'cart' => $asquiring->getCart(),
                        'cartType' => $asquiring->getСartType(),
                        'amount' => $asquiring->getAmount(),
                        'transDate' => $asquiring->getTransDate(),
                        'rrn' => $asquiring->getRrn(),
                    ];
                }    
            }
        }
        
        r:
        return new JsonModel($result);
    }
    
}
