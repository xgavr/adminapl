<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Application\Entity\Client;
use Application\Entity\Contact;
use User\Entity\User;
use Application\Form\ClientForm;
use Application\Form\ContactForm;
use Laminas\View\Model\JsonModel;
use Stock\Entity\Retail;
use Application\Entity\Order;
use Stock\Entity\Comiss;
use Company\Entity\Legal;
use Company\Entity\Contract;
use Stock\Entity\Movement;

class ClientController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер товаров.
     * @var \Application\Service\ClientManager 
     */
    private $clientManager;    
    
    /**
     * Менеджер.
     * @var \Application\Service\ContactManager 
     */
    private $contactManager;    
    
    /*
     * Менеджер сессий
     * @var Zend\Seesion
     */
    private $sessionContainer;
    
    /**
     * RBAC manager.
     * @var \User\Service\RbacManager
     */
    private $rbacManager;    
    
    /**
     * Admin manager.
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $clientManager, $contactManager, 
            $sessionContainer, $rbacManger, $adminManager) 
    {
        $this->entityManager = $entityManager;
        $this->clientManager = $clientManager;
        $this->contactManager = $contactManager; 
        $this->sessionContainer = $sessionContainer;
        $this->rbacManager = $rbacManger;
        $this->adminManager = $adminManager;
    }   
    
    public function setCurrentClientAction()
    {
        $clientId = $this->params()->fromRoute('id', -1);
        $this->sessionContainer->currentClient = $clientId;
        return $this->redirect()->toRoute('client', []);        
    }
    
    public function indexAction()
    {
        $total = $this->entityManager->getRepository(Client::class)
                ->count([]);
        
        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        // Визуализируем шаблон представления.
        return new ViewModel([
            'total' => $total,
            'companies' => $companies,
        ]);  
    }
    
    public function contentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit', 10);
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'ASC');
        $pricecol = $this->params()->fromQuery('pricecol');
        $legal = $this->params()->fromQuery('legal');
        $company = $this->params()->fromQuery('company');
        
        $params = ['search' => $q, 'sort' => $sort, 'order' => $order,
                    'pricecol' => $pricecol, 'legal' => $legal, 'company' => $company];
        
        $query = $this->entityManager->getRepository(Client::class)
                        ->findAllClient($params);
        
        $totalResult = $this->entityManager->getRepository(Client::class)
                        ->totalAllClient($params);
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $totalResult['countC'],
            'balanceIn' => $totalResult['balanceIn'],
            'balanceOut' => $totalResult['balanceOut'],
            'contractBalanceIn' => (empty($totalResult['contractBalanceIn'])) ? 0:$totalResult['contractBalanceIn'],
            'contractBalanceOut' => (empty($totalResult['contractBalanceOut'])) ? 0:$totalResult['contractBalanceOut'],
            'rows' => $result,
        ]);          
    }    
    
    public function revenueContentAction()
    {
        $clientId = (int)$this->params()->fromRoute('id', -1);
        if ($clientId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the client ID
        $client = $this->entityManager->getRepository(Client::class)
                ->find($clientId);
        
        if ($client == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }      
        
        $movement = $this->entityManager->getRepository(Movement::class)
                ->findOneBy(['client' => $client->getId()], ['docStamp' => 'ASC']);
        	        
        $dateStart = ($movement) ? $movement->getDateOper():date('Y-01-01');
        $endDate = $client->getBalanceDateTimeOrNow();
        $period = 'year';
        
        $dateDiff = (new \DateTime($endDate))->diff(new \DateTime($dateStart));
        if ($dateDiff->y > 2){
            $dateStart = date('Y-m-d', strtotime($endDate.' -2 years'));
        }

//        var_dump($dateStart, $endDate, $period);
        $params = ['client' => $client->getId(), 'period' => $period, 
            'startDate' => $dateStart, 'endDate' => $endDate];
        
        $result = $this->entityManager->getRepository(Order::class)
                        ->revenueByYears($params)
                        ->getResult(2);
        
        return new JsonModel([
            'rows' => $result,
        ]);          
    }    
    

    
    public function addAction() 
    {     
        // Создаем форму.
        $form = new ClientForm($this->entityManager);
        
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер client для добавления нового good в базу данных.                
                $client = $this->clientManager->addNewClient($data);
                
                // Перенаправляем пользователя на страницу "client".
                return $this->redirect()->toRoute('client', ['action' => 'view', 'id' => $client->getId()]);
            }
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
        ]);
    }   
    
   public function editAction()
   {
        // Создаем форму.
        $form = new ClientForm($this->entityManager);
    
        // Получаем ID tax.    
        $clientId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $client = $this->entityManager->getRepository(Client::class)
                ->findOneById($clientId);  
        	
        if ($client == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер постов, чтобы добавить новый пост в базу данных.                
                $this->clientManager->updateClient($client, $data);
                
                // Перенаправляем пользователя на страницу "client".
                return $this->redirect()->toRoute('client', []);
            }
        } else {
            $data = [
               'name' => $client->getName(),
               'status' => $client->getStatus(),
            ];
            
            $form->setData($data);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
            'client' => $client
        ]);  
    }    
    
    public function editFormAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $client = $this->entityManager->getRepository(Client::class)
                ->find($id);
        
        if ($client == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
                

        // Create form
        $form = new ClientForm($this->entityManager);
                
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            // Fill in the form with POST data
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Validate form
            if($form->isValid()) {
                
                // Get filtered and validated data
                $data = $form->getData();
                
                $this->clientManager->updateClient($client, $data);
                
                return new JsonModel(
                   ['ok']
                );           
            }               
        } else {
            if ($client){
                $form->setData(array(
                        'name'=>$client->getName(),
                        'aplId'=>$client->getAplId(),     
                        'status'=>$client->getStatus(),     
                        'pricecol' => $client->getPricecol(),
                    ));
            }    
        }
        
        $this->layout()->setTemplate('layout/terminal');
        
        return new ViewModel([
                'form' => $form,
                'client' => $client
            ]);
    }    
    
    public function deleteAction()
    {
        $clientId = $this->params()->fromRoute('id', -1);
        
        $client = $this->entityManager->getRepository(Client::class)
                ->findOneById($clientId);        
        if ($client == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->clientManager->removeClient($client);
        
        // Перенаправляем пользователя на страницу "rb/tax".
        return $this->redirect()->toRoute('client', []);
    }    

    public function deleteContactAction()
    {
        $contactId = $this->params()->fromRoute('id', -1);
        
        $contact = $this->entityManager->getRepository(Contact::class)
                ->findOneById($contactId);
        
        if ($contact == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $clientId = $contact->getClient()->getId();
        
        $this->contactManager->removeContact($contact);
        
        // Перенаправляем пользователя на страницу "supplier/view".
        return $this->redirect()->toRoute('client', ['action' => 'view', 'id' => $clientId]);
    }    
    
    public function viewAction() 
    {       
        $clientId = (int)$this->params()->fromRoute('id', -1);
        $orderId = (int)$this->params()->fromQuery('order', -1);

        if ($clientId<0 && $orderId>0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
            if ($order){
                $clientId = $order->getClient()->getId();
            }
        }
        
        // Validate input parameter
        if ($clientId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the client ID
        $client = $this->entityManager->getRepository(Client::class)
                ->find($clientId);
        
        if ($client == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }      
        
        if (!$client->getLegalContact()){
            $data = [
                'name' => $client->getName(),
                'status' => Contact::STATUS_LEGAL,
            ];
            $this->contactManager->addNewContact($client, $data);
            $this->entityManager->refresh($client);
        }
        
        $legalList = [
            Client::RETAIL_ID => 'Розничная покупка',            
            ];        
        $legals = $this->entityManager->getRepository(Client::class)
                ->clientLegals($client);
        foreach ($legals as $legal){
            $legalList[$legal->getId()] = $legal->getName();
        }
        
        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        
        $revenue = $this->entityManager->getRepository(Order::class)
                ->revenueByClientCount(['client' => $client->getId()])
                ->getOneOrNullResult();
        
        $movement = $this->entityManager->getRepository(Movement::class)
                ->findOneBy(['client' => $client->getId()], ['docStamp' => 'ASC']);
        	        
        $dateStart = ($movement) ? date('d.m.Y', strtotime($movement->getDateOper())):'';
//        var_dump($revenue);
//        var_dump($client->getLegalContact()->getId());
        // Render the view template.
        return new ViewModel([
            'client' => $client,
            'allowDate' => $this->adminManager->getAllowDate(),
            'legals' => $legalList,
            'companies' => $companies,
            'revenue' => $revenue,
            'dateStart' => $dateStart,
        ]);
    }      
    
    public function managerTransferAction()
    {
        $clientId = (int) $this->params()->fromQuery('clientId', -1);
        $userId = (int) $this->params()->fromRoute('id', -1);
        
        if ($clientId<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        if ($userId<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Access control.
        if (!$this->access('member.transfer.manage')) {
            $this->getResponse()->setStatusCode(401);
            return;
        }
        
        $client = $this->entityManager->getRepository(Client::class)
                ->findOneById($clientId);
        
        if ($client == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $user = $this->entityManager->getRepository(User::class)
                ->findOneById($userId);
        
        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $this->clientManager->transferToManager([$client], $user);
        
        // Снова перенаправляем пользователя на страницу "index".
        return $this->redirect()->toRoute('client');
                
        return new ViewModel([]);
                
    }
    
    public function deleteEmptyClientsAction()
    {
        $deleted = $this->clientManager->cleanClients();
                
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => $deleted.' удалено!',
        ]);          
    }
    
    public function aplUnionAction()
    {
        $clientId = (int) $this->params()->fromRoute('id', -1);
        
        if ($clientId<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $client = $this->entityManager->getRepository(Client::class)
                ->findOneById($clientId);
        if (!$client) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
//        if (empty($client->getContactPhone())){
//            $this->clientManager->correctByPhone($client);
//        }
        $this->clientManager->aplUnion($client);
        $this->clientManager->updateBalance($client);
        
        return new JsonModel([
            'result' => 'ok-reload',
//            'message' => $deleted.' удалено!',
        ]);          
    }
    
    public function clearDoubleAplAction()
    {
        
        $this->clientManager->clearDoubleApl();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }
    
    public function retailsAction()
    {        
        $clientId = (int)$this->params()->fromRoute('id', -1);

        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $search = $this->params()->fromQuery('search');
        $source = $this->params()->fromQuery('source');
        $company = $this->params()->fromQuery('company');
        $legal = $this->params()->fromQuery('legal');
        $contract = $this->params()->fromQuery('contract');
        $sort = $this->params()->fromQuery('sort', 'dateOper');
        $order = $this->params()->fromQuery('order', 'ASC');
        $dateStart = $this->params()->fromQuery('dateStart');
        $period = $this->params()->fromQuery('period');
//        $range = $this->params()->fromQuery('dateRange');
        
        $startDate = '2012-01-01';
        $endDate = '2199-01-01';
        if (!empty($dateStart)){
            $startDate = date('Y-m-d', strtotime($dateStart));
            $endDate = $startDate;
            if ($period == 'week'){
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 week - 1 day', strtotime($startDate)));
            }    
            if ($period == 'month'){
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 month - 1 day', strtotime($startDate)));
            }    
            if ($period == 'number'){
                $startDate = $dateStart.'-01-01';
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 year - 1 day', strtotime($startDate)));
            }    
        }    
        
//        var_dump($startDate, $endDate);
        
        // Validate input parameter
        if ($clientId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $client = $this->entityManager->getRepository(Client::class)
                ->find($clientId);

        if ($client == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        $params = ['q' => $search, 'source' => $source, 
                    'sort' => $sort, 'order' => $order, 'company' => $company,
                    'legal' => $legal, 'contract' => $contract,
                    'startDate' => $startDate, 'endDate' => $endDate];
        
        $query = $this->entityManager->getRepository(Client::class)
                        ->retails($client, $params);

        $total = count($query->getResult(2));
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);
        foreach ($result as $key=>$value){
            $result[$key]['rest'] = $this->entityManager->getRepository(Retail::class)
                ->clientStampRest($clientId, $value['docKey'], 
                        $company, $legal, $contract);
        }
        
        $params['restDate'] = $startDate;
        $restQuery = $this->entityManager->getRepository(Client::class)
                        ->restRetails($client, $params);
        $rest = $restQuery->getOneOrNullResult();
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
            'startBalance' => empty($rest['amount']) ? 0:$rest['amount'],
        ]);                  
    }   
    
    public function comissAction()
    {        
        $clientId = (int)$this->params()->fromRoute('id', -1);

        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $search = $this->params()->fromQuery('search');
        $source = $this->params()->fromQuery('source');
        $company = $this->params()->fromQuery('company');
        $sort = $this->params()->fromQuery('sort', 'dateOper');
        $order = $this->params()->fromQuery('order', 'ASC');
        $year_month = $this->params()->fromQuery('month');
        
        $year = $month = null;
        if ($year_month){
            $year = date('Y', strtotime($year_month));
            $month = date('m', strtotime($year_month));
        }        
        
        // Validate input parameter
        if ($clientId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $client = $this->entityManager->getRepository(Client::class)
                ->find($clientId);

        if ($client == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $query = $this->entityManager->getRepository(Client::class)
                        ->comiss($client, ['q' => $search, 'source' => $source, 
                            'sort' => $sort, 'order' => $order, 'company' => $company,
                            'month' => $month, 'year' => $year]);

        $total = count($query->getResult(2));
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);
        foreach ($result as $key=>$value){
            $result[$key]['rest'] = $this->entityManager->getRepository(Comiss::class)
                ->clientStampRest($clientId, $value['docKey'], $company);
        }
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);                  
    }   

    public function legalsAction()
    {
        $clientId = (int) $this->params()->fromRoute('id', '');
        
        if ($clientId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $client = $this->entityManager->getRepository(Client::class)
                ->find($clientId);

        if ($client == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }
        
        $result = [];
        $orderLegals = $this->entityManager->getRepository(Client::class)
                ->findClientLegals($client);
        foreach ($orderLegals as $orderLegal){
            $legalId = $recipientId = $bankAccountId = 0;
            if (isset($orderLegal['legal'])){
                $legalId = $orderLegal['legal']['id'];
            }
            if (isset($orderLegal['recipient'])){
                if ($orderLegal['recipient']['id'] != $legalId){
                    $recipientId = $orderLegal['recipient']['id'];
                }    
            }
            if (isset($orderLegal['bankAccount'])){
                $bankAccountId = $orderLegal['bankAccount']['id'];
            }
            $result[implode('k', [$legalId, $recipientId, $bankAccountId])] = [
                'legal' => $orderLegal['legal'],
                'recipient' => $orderLegal['recipient'],
                'bankAccount' => $orderLegal['bankAccount'],
            ];
        }
        
        return new JsonModel(
            array_values($result)
        );           
    }    
    
    public function clientLegalsAction()
    {
        $clientId = (int)$this->params()->fromRoute('id', -1);
        if ($clientId<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $client = $this->entityManager->getRepository(Client::class)
                ->find($clientId);
        
        if ($client == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $result = [];
        $legals = $this->entityManager->getRepository(Client::class)
                ->findLegals($client);
        foreach ($legals as $legal){
            $result[$legal->getId()] = [
                'id' => $legal->getId(),
                'name' => $legal->getName(),                
            ];
        }
        
        return new JsonModel([
            'rows' => $result,
        ]);                  
    }

    public function clientContractsAction()
    {
        $clientId = (int)$this->params()->fromRoute('id', -1);
        $legalId = (int)$this->params()->fromQuery('legal', -1);
        
        if ($clientId<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $client = $this->entityManager->getRepository(Client::class)
                ->find($clientId);
        
        if ($client == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $result = [];
        
        if ($legalId > 0){
            $legal = $this->entityManager->getRepository(Legal::class)
                    ->find($legalId);
            if ($legal){
                $contracts = $this->entityManager->getRepository(Client::class)
                        ->clientContracts($client, $legal);
                foreach ($contracts as $contract){
                    $result[$contract->getId()] = [
                        'id' => $contract->getId(),
                        'name' => $contract->getContractPresentPay(),                
                    ];
                }
            }    
        }
        
        return new JsonModel([
            'rows' => $result,
        ]);                  
    }

    public function updateBalanceAction()
    {
        $clientId = (int)$this->params()->fromRoute('id', -1);
        if ($clientId<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $client = $this->entityManager->getRepository(Client::class)
                ->find($clientId);
        
        if ($client == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $this->clientManager->updateBalance($client);
        
        return new JsonModel(
           ['result' => 'ok-reload']
        );                  
    }

    public function updateBalancesAction()
    {
        $this->clientManager->updateBalances();        
        
        return new JsonModel(
           ['result' => 'ok']
        );                   
    }
    
    public function updateClientDatesAction()
    {
        $this->clientManager->updateDates();        
        
        return new JsonModel(
           ['result' => 'ok']
        );                   
    }
    
}
