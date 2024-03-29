<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ApiMarketPlace\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use ApiMarketPlace\Entity\Marketplace;
use ApiMarketPlace\Form\MarketplaceSetting;
use Application\Entity\Goods;
use Application\Entity\MarketPriceSetting;
use Application\Entity\Order;
use ApiMarketPlace\Entity\MarketplaceOrder;
use ApiMarketPlace\Form\MarketplaceOrderForm;
use Application\Entity\Contact;
use Company\Entity\Contract;
use Stock\Entity\RegisterVariable;


class IndexController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    /**
     * Request manager.
     * @var \ApiMarketPlace\Service\SberMarket
     */
    private $sbermarketManager;
        
    /**
     * Market place manager.
     * @var \ApiMarketPlace\Service\MarketplaceService
     */
    private $marketplaceService;
        
    /**
     * Ozon manager.
     * @var \ApiMarketPlace\Service\OzonService
     */
    private $ozonService;
        
    /**
     * Report manager.
     * @var \ApiMarketPlace\Service\ReportManager
     */
    private $reportManager;

    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $sbermarketManager, $marketplaceService, 
            $ozonService, $reportManager) 
    {
       $this->entityManager = $entityManager;
       $this->sbermarketManager = $sbermarketManager;
       $this->marketplaceService = $marketplaceService;
       $this->ozonService = $ozonService;
       $this->reportManager = $reportManager;
    }

    /**
     * Дата запрета редактирования
     * @return date
     */    
    private function getAllowDate()
    {
        $var = $this->entityManager->getRepository(RegisterVariable::class)
                ->findOneBy([]);
        return $var->getAllowDate();
    }
    
    public function indexAction()
    {
        $marketplaces = $this->entityManager->getRepository(Marketplace::class)
                ->findAll();
        return new ViewModel([
            'marketplaces' =>  $marketplaces,
        ]);
    }
    
    public function viewAction() 
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find a role with such ID.
        $marketplace = $this->entityManager->getRepository(Marketplace::class)
                ->find($id);
        
        if ($marketplace == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
                        
        return new ViewModel([
            'marketplace' => $marketplace,
            'allowDate' => date('Y-m-d', strtotime($this->getAllowDate().' +1 day')),
        ]);
    }    

    public function editFormAction()
    {
        $marketplaceId = (int)$this->params()->fromRoute('id', -1);
        
        $marketplace = $contact = null;
        $contractList = [];
        
        if ($marketplaceId > 0){
            $marketplace = $this->entityManager->getRepository(Marketplace::class)
                    ->find($marketplaceId);
            $contact = $marketplace->getContact();
        }    
        
        if ($this->getRequest()->isPost()){
            $data = $this->params()->fromPost();
            $contactId = $data['contact'];
            $contact = $this->entityManager->getRepository(Contact::class)
                    ->find($contactId);
        }    
        
        if ($contact){
            $contracts = $this->entityManager->getRepository(Contract::class)
                    ->contactSelect($contact, Contract::KIND_COMITENT);
            foreach ($contracts as $contract){
                $contractList[$contract->getId()] = $contract->getName();
            }            
        }
        
        $form = new MarketplaceSetting($this->entityManager);
        $form->get('contract')->setValueOptions($contractList);
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();            
            $form->setData($data);

            if ($form->isValid()) {
                
                if ($marketplace){
                    $this->marketplaceService->update($marketplace, $data);
                } else {
                    $marketplace = $this->marketplaceService->add($data);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
                //var_dump($form->getMessages());
            }
        } else {
            if ($marketplace){
                $data = $marketplace->toArray();
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'marketplace' => $marketplace,
        ]);        
    }    
    
    public function marketplaceOrderFormAction()
    {
        $marketplaceOrderId = (int)$this->params()->fromRoute('id', -1);
        $orderId = (int) $this->params()->fromQuery('order', -1);

        $marketplaceOrder = $order = NULL;
        $message = ['message' => []];
        
        if ($marketplaceOrderId>0) {
            $marketplaceOrder = $this->entityManager->getRepository(MarketplaceOrder::class)
                    ->find($marketplaceOrderId);
            $order = $marketplaceOrder->getOrder();
        }        

        if ($orderId>0) {
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
        }        
        
        $form = new MarketplaceOrderForm();
        
        $marketplaceList = [];
        $marketplaces = $this->entityManager->getRepository(Marketplace::class)
                ->findBy(['status' => Marketplace::STATUS_ACTIVE]);
        foreach ($marketplaces as $marketplace){
            $marketplaceList[$marketplace->getId()] = $marketplace->getName();
        }
        
        $form->get('marketplace')->setValueOptions($marketplaceList);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                
                $data['order'] = $order;
                
                if ($marketplaceOrder){
                    $this->marketplaceService->updateMarketplaceOrder($marketplaceOrder, $data);
                } else {
                    $message = $this->marketplaceService->addOrUpdateMarketplaceOrder($data);                    
                }    
                
                if (count($message['message']) == 0){
                    $query = $this->entityManager->getRepository(Order::class)
                            ->findAllOrder(['orderId' => $order->getId()]);
                    $result = $query->getOneOrNullResult(2);
                    
                    return new JsonModel([
                       'message' => implode(' ', $message['message']),   
                       'result' => $result,
                    ]);           
                }    
                $form->get('orderNumber')->setMessages($message);
            }
        } else {
            if ($marketplaceOrder){
                $data = [
                    'marketplace' => $marketplaceOrder->getMarketplace()->getId(),
                    'orderNumber' => $marketplaceOrder->getOrderNumber(),
                    'postingNumber' => $marketplaceOrder->getPostingNumber(),
                ];
                $form->setData($data);
            }  
        }    
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'order' => $order,
            'marketplaceOrder' => $marketplaceOrder,
        ]);                
    }
     
    public function orderFormAction()
    {
        $orderId = (int)$this->params()->fromRoute('id', -1);
        
        if ($orderId <= 0){
            $this->getResponse()->setStatusCode(404);
            return;
        }    
        
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);
        
        if ($order == null){
            $this->getResponse()->setStatusCode(404);
            return;            
        }
        $marketplaceOrders = $this->entityManager->getRepository(MarketplaceOrder::class)
                ->findBy(['order' => $order->getId()]);
        
        $this->layout()->setTemplate('layout/terminal');
        
        return new ViewModel([
            'order' => $order,
            'marketplaceOrders' => $marketplaceOrders,
        ]);                
    }    
    
    public function orderFormContentAction()
    {
        $orderId = (int)$this->params()->fromRoute('id', -1);
        
        if ($orderId <= 0){
            $this->getResponse()->setStatusCode(404);
            return;
        }    
        
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);
        
        if ($order == null){
            $this->getResponse()->setStatusCode(404);
            return;            
        }
        $marketplaceOrders = $this->entityManager->getRepository(MarketplaceOrder::class)
                ->findBy(['order' => $order->getId()]);
        
        $result = [];
        foreach ($marketplaceOrders as $marketplaceOrder){
            $result[] = $marketplaceOrder->toLog();
        }
        
        return new JsonModel([
            'total' => count($result),
            'rows' => $result,
        ]);                
    }    
    
    public function marketplaceOrderDeleteAction()
    {
        $marketplaceOrderId = $this->params()->fromRoute('id', -1);
        
        $marketplaceOrder = $this->entityManager->getRepository(MarketplaceOrder::class)
                ->find($marketplaceOrderId);
        
        if ($marketplaceOrder == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->marketplaceService->removeMarketplaceOrder($marketplaceOrder);
        
        $result = null;
        if ($marketplaceOrder->getOrder()){
            $query = $this->entityManager->getRepository(Order::class)
                    ->findAllOrder(['orderId' => $marketplaceOrder->getOrder()->getId()]);
            $result = $query->getOneOrNullResult(2);
        }
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'message' => 'ok',
            'result' => $result,
        ]);           
    }        

    public function orderNumberToOrderAction()
    {
        $marketplaceId = (int) $this->params()->fromRoute('id', -1);
        $orderId = (int)$this->params()->fromQuery('order', -1);
        $orderNumber = (int)$this->params()->fromQuery('orderNumber');
        
        $marketplace = $order = null;
        if ($marketplaceId > 0){
            $marketplace = $this->entityManager->getRepository(Marketplace::class)
                    ->find($marketplaceId);
        }
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
        }
        
        if ($marketplace && $order){
            $marketplaceOrder = $this->marketplaceService->addMarketplaceOrder($marketplace, ['order' => $order, 'orderNumber' => $orderNumber]);
        }    

        $query = $this->entityManager->getRepository(Order::class)
                ->findAllOrder(['orderId' => $order->getId()]);
        $result = $query->getOneOrNullResult(2);
        
        return new JsonModel([
            'maessage' => $message,
            'result' => $result,
        ]);           
    }
    
    public function sbermarketOrderNewAction()
    {
        $this->sbermarketManager->handle();
        //{"success":1,"meta":{"source":"merchant_name"}}
        return new JsonModel([
            'success' => 1,
            'meta' => [
                'source' => 'APL',
            ],
        ]);
    }
    
    public function sbermarketOrderCancelAction()
    {
        $this->sbermarketManager->handle();
        return new JsonModel([
            'success' => 1,
            'meta' => [
                'source' => 'APL',
            ],
        ]);        
    }

    public function yandexOrderAcceptAction()
    {
        $updId = $this->sbermarketManager->handle();
        return new JsonModel([
            'order' => [
                'accepted' => true,
                'id' => $updId,
                'reason' => '',
            ],
        ]);
    }
    
    public function ozonCategoryTreeAction()
    {
        $result = $this->ozonService->сategoryTree();
        return new JsonModel($result);
    }
    
    public function ozonZeroingAction()
    {
        $result = $this->ozonService->zeroing();
        return new JsonModel(['ok']);
    }

    public function ozonPostingListAction()
    {
        $result = $this->ozonService->postingList();
        var_dump($result); exit;
        return new JsonModel($result);
    }

    public function ozonUpdatePriceAction()
    {
        $goodId = (int)$this->params()->fromRoute('id', -1);
        
        $good = null;
        $resultPrice = []; $resultStock = [];
        if ($goodId > 0){
            $good = $this->entityManager->getRepository(Goods::class)
                    ->find($goodId);
        }    
        
        if ($good){
            $resultPrice = $this->ozonService->updateGoodPrice($good);
            $resultStock = $this->ozonService->updateGoodStock($good);
        }

        return new JsonModel([
            'price' => $resultPrice,
            'stock' => $resultStock,
        ]);
    }

    public function ozonUpdateMarketAction()
    {
        $marketId = (int)$this->params()->fromRoute('id', -1);
        
        $market = null;
        $result = [];
        if ($marketId > 0){
            $market = $this->entityManager->getRepository(MarketPriceSetting::class)
                    ->find($marketId);
        }    
        
        if ($market){
            $result = $this->ozonService->marketUpdate($market);
        }

        return new JsonModel($result);
    }
    
    public function ozonRealizationAction()
    {
        $marketplaceId = $this->params()->fromRoute('id', -1);
        $month = $this->params()->fromQuery('date', '2023-03-01');
        
        if ($marketplaceId <= 0){
            $this->getResponse()->setStatusCode(404);
            return;
        }    
        
        $marketplace = $this->entityManager->getRepository(Marketplace::class)
                ->find($marketplaceId);
        
        if ($marketplace == null){
            $this->getResponse()->setStatusCode(404);
            return;            
        }
        
        $result = $this->reportManager->ozonRealization($marketplace, $month);

        return new JsonModel($result);
    }

    public function realizationReportsAction()
    {
        $result = $this->reportManager->monthReports();

        return new JsonModel($result);
    }

    public function downloadLogAction()
    {
        setlocale(LC_ALL,'ru_RU.UTF-8');
        $logName = $this->params()->fromQuery('log', 0);

        $marketId = (int)$this->params()->fromRoute('id', -1);
        if ($marketId<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $market = $this->entityManager->getRepository(MarketPriceSetting::class)
                ->find($marketId);
        
        if ($market == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $file = realpath($this->ozonService->logFile($market, $logName));
        
        if (file_exists($file)){
            if (ob_get_level()) {
              ob_end_clean();
            }
            // заставляем браузер показать окно сохранения файла
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            // читаем файл и отправляем его пользователю
            readfile($file);
        }
        exit;          
    }          
}
