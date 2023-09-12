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
use Admin\Entity\PostLog;
use Application\Entity\Order;
use Admin\Form\PostForm;
use User\Entity\User;
use Application\Entity\Email;


class PostController extends AbstractActionController
{
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;    
    
    /**
     * AplService manager.
     * @var \Admin\Service\PostManager
     */
    private $postManager;    
    
    /**
     * AutoruService manager.
     * @var \Admin\Service\AutoruManager
     */
    private $autoruManager;    
    
    /**
     * HelloService manager.
     * @var \Admin\Service\HelloManager
     */
    private $helloManager;    
    
    /**
     * AdminManager manager.
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;        
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $postManager, $autoruManager, $helloManager, $adminManager) 
    {
        $this->postManager = $postManager;        
        $this->autoruManager = $autoruManager;
        $this->helloManager = $helloManager;
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
    }   

    
    public function indexAction()
    {
        
        return [];
    }
    
    public function checkConnAction()
    {
        var_dump(fsockopen("ssl://smtp.yandex.ru",465));
        exit;
    }
    
    public function sendAction()
    {
        if ($this->getRequest()->isPost()) {
            $result = 'Не ушло! Проверте данные';
            $data = $this->params()->fromPost();
            if (!empty($data['fromEmail']) && !empty($data['toEmail'])){
                $user = $this->entityManager->getRepository(User::class)
                        ->findOneByEmail($data['fromEmail']);
                $email = $this->entityManager->getRepository(Email::class)
                        ->findOneByName($data['fromEmail']);
                if ($user && $email){
                    $settings = $this->adminManager->getSettings();
                    $options['to'] = $data['toEmail'];
                    $options['from'] = $data['fromEmail'];
                    $options['copyMe'] = $data['copyMe'];
                    $options['bill'] = $data['bill'];
                    $options['offer'] = $data['offer'];
                    $options['showCode'] = (empty($data['showCode'])) ? 0:1;
                    $options['orderId'] = $data['orderId'];
                    $options['subject'] = $data['subject'];
                    $options['body'] = $data['message'];
                    $options['username'] = $data['fromEmail'];
                    $options['password'] = $email->getMailPassword($settings['turbo_passphrase']);
                    $result = $this->postManager->send($options);
                }    
            }    

            return new JsonModel([
                'result' => $result
            ]);        
        }    
        exit;            
    }
    
    public function postFormAction()
    {        
        $orderId = (int)$this->params()->fromRoute('id', -1);
        $email = $this->params()->fromQuery('email');
        
        $order = null;
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);                    
        }    
        
        $currentUser = $this->postManager->currentUser();
//        $settings = $this->adminManager->getSettings();
//        $turbo_passphrase = $settings['turbo_passphrase'];

        $form = new PostForm();
        $form->get('toEmail')->setValue($email);
        $form->get('fromEmail')->setValue($currentUser->getEmail());

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
//            'turbo' => $order->getAplTurboId($turbo_passphrase),
            'currentUser' => $currentUser,
        ]);                        
    }
    
    public function orderBodyAction()
    {
        $orderId = (int)$this->params()->fromRoute('id', -1);
        $showCode = $this->params()->fromQuery('showCode');
        $result = [];
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
            $result['body'] = $order->getBidsAsHtml($showCode === 'true');
        }
        $result['sign'] = $this->currentUser()->getSign();
        
        return new JsonModel($result);                   
    }
    
    public function sberFormAction()
    {
        $orderId = (int)$this->params()->fromRoute('id', -1);
        $prepay = $this->params()->fromQuery('prepay', 0);
        
        $result = [];
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
        }
        
        $this->layout()->setTemplate('layout/terminal');

        return new ViewModel([
            'orderAplId' => $order->getAplId(),
            'prepay' => $prepay,
        ]);                        
    }

    public function autoruAction()
    {
        $this->autoruManager->postOrder();
        return new JsonModel([
            'ok'
        ]);
    }    
    
    public function helloAction()
    {
        $this->helloManager->checkingMail();
        
        return new JsonModel([
            'ok'
        ]);
    }    
    
    public function logToTokensAction()
    {
        $logId = $this->params()->fromRoute('id', -1);
            
        if ($logId > 0){
            $log = $this->entityManager->getRepository(PostLog::class)
                    ->findOneById($logId);
        }    
        

        $this->helloManager->toTokens($log);

        return new JsonModel([
            'ok-reload'
        ]);
    }
    
    public function logsToTokensAction()
    {
        $this->helloManager->logsToTokens();

        return new JsonModel([
            'ok-reload'
        ]);
    }
    
}
