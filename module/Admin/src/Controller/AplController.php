<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;


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

    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $aplService, $aplBankService) 
    {
        $this->entityManager = $entityManager;
        $this->aplService = $aplService;        
        $this->aplBankService = $aplBankService;        
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

    public function getStaffsAction()
    {
        $this->aplService->getStaffs();
        
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

    public function updateAcquiringAction()
    {
        
        $this->aplService->updateAcquiringPayments();
        
        return new JsonModel([
            'result' => 'ok-reload',
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
        
        $this->aplService->updateGoodRawprice($good);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }
    
}
