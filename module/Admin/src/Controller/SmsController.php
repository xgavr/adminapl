<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Entity\Order;
use Admin\Form\SmsForm;
use User\Filter\PhoneFilter;
use Admin\Filter\ClickFilter;
use Laminas\Filter\ToFloat;


class SmsController extends AbstractActionController
{
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;    
    
    /**
     * AplService manager.
     * @var \Admin\Service\smsManager
     */
    private $smsManager;    
    
    /**
     * AdminManager manager.
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;        

    /**
     * AplService manager.
     * @var \Admin\Service\AplService
     */
    private $aplService;        
    
    /**
     * SbpManager manager.
     * @var \Bank\Service\SbpManager
     */
    private $sbpManager;        

    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $smsManager, $adminManager, 
            $aplService, $sbpManager) 
    {
        $this->smsManager = $smsManager;        
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->aplService = $aplService;
        $this->sbpManager = $sbpManager;
    }   

    
    public function indexAction()
    {
        
        return [];
    }
    
    public function smsAction()
    {
        if ($this->getRequest()->isPost()) {
            $result = 'Не ушло! Проверте данные';
            $data = $this->params()->fromPost();
            if (!empty($data['phone']) && !empty($data['message']) && !empty($data['mode'])){
                $filter = new PhoneFilter(['filter' => PhoneFilter::PHONE_FORMAT_DB]);
                $phone = '7'.$filter->filter($data['phone']);
                
                if ($data['mode'] == 1){
                    $result = $this->smsManager->send(['phone' => $phone, 'text' => $data['message']]);
                }    
                if ($data['mode'] == 2){
                    $result = $this->smsManager->wamm(['phone' => $phone, 'text' => $data['message'], 'name' => $data['orderId'], 'attachment' => $data['attachment']]);
                }    
            }    

            return new JsonModel([
                'result' => $result
            ]);        
        }    
        exit;    
    }
    
    public function smsFormAction()
    {        
        $orderId = (int)$this->params()->fromRoute('id', -1);
        $phone = $this->params()->fromQuery('phone');
        
        $order = null;
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);                    
        }    
        
        $settings = $this->adminManager->getSettings();
        $turbo_passphrase = $settings['turbo_passphrase'];

        $form = new SmsForm();
        $form->get('phone')->setValue($phone);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {

                return new JsonModel(
                   ['ok']
                );           
            }
        }    
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'order' => $order,
            'currentUser' => $this->smsManager->currentUser(),
        ]);                        
    }    
    
    public function orderPrepayAction()
    {
        $orderId = (int)$this->params()->fromRoute('id', -1);
        $prepay = $this->params()->fromQuery('prepay', 0);
        
        $result = [];
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
            
            if ($order){
                $clickFilter = new ClickFilter();
                $result['prepayLink'] = $clickFilter->filter($order->getAplPaymentLink($prepay));
            }    
        }
        
        return new JsonModel($result);                   
    }    

    public function qrPrepayAction()
    {
        $orderId = (int)$this->params()->fromRoute('id', -1);
        $prepay = $this->params()->fromQuery('prepay', 0);
        
        $result = [];
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
            
            if ($order && !empty($prepay)){
                
                if ($order->getAplId()){
                    $toFloat = new ToFloat();
                    $qrCode = $this->sbpManager->registerQrCode([
                        'orderAplId' => $order->getAplId(),
                        'amount' => $toFloat->filter($prepay),
                    ]);
    //                var_dump($qrCode->getId());
                    if ($qrCode){
                        $qrCodeInfo = $qrCode->toMsg();

                        $clickFilter = new ClickFilter();
                        $result['prepayLink'] = $clickFilter->filter($order->getAplPaymentLink($qrCodeInfo['payload']));
                    }
                }    
                
            }    
        }
        
        return new JsonModel($result);                   
    }    

    public function turboLinkAction()
    {
        $orderId = (int)$this->params()->fromRoute('id', -1);
        
        $result = [];
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
            
            if ($order){
                $turboLink = file_get_contents($order->getAplTurboLink());
                if ($turboLink){
                    $clickFilter = new ClickFilter();
                    $result['turboLink'] = $clickFilter->filter($turboLink);
                }    
            }    
        }
        
        return new JsonModel($result);                   
    }    

    public function profileAction()
    {
        $orderId = (int)$this->params()->fromRoute('id', -1);
        
        $result = [];
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
            
            if ($order){
                $result['login'] = $order->getAplLogin();
                $result['password'] = $this->aplService->getOrderBayerInfo($order);
            }    
        }
        
        return new JsonModel($result);                   
    }    
}
