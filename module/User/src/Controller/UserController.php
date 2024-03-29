<?php
namespace User\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use User\Entity\User;
use User\Entity\Role;
use Application\Entity\Phone;
use User\Form\UserForm;
use User\Form\PasswordChangeForm;
use User\Form\PasswordResetForm;
use User\Form\PasswordResetPhoneForm;
use User\Filter\PhoneFilter;
use Laminas\View\Model\JsonModel;
use Company\Entity\Office;
use Application\Entity\Email;
use Cash\Entity\Cash;

/**
 * This controller is responsible for user management (adding, editing, 
 * viewing users and changing user's password).
 */
class UserController extends AbstractActionController 
{
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * User manager.
     * @var \User\Service\UserManager 
     */
    private $userManager;
    
    /**
     * Constructor. 
     */
    public function __construct($entityManager, $userManager)
    {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
    }
    
    /**
     * This is the default "index" action of the controller. It displays the 
     * list of users.
     */
    public function indexAction() 
    {
        // Access control.
        if (!$this->access('user.manage')) {
            $this->getResponse()->setStatusCode(401);
            return;
        }
        
        $currentUser = $this->entityManager->getRepository(User::class)
            ->findOneByEmail($this->identity());
        
        $offices = $this->entityManager->getRepository(Office::class)
                ->findBy([], ['id'=>'ASC']);
        
        return new ViewModel([
            'offices' => $offices,
            'currentUser' => $currentUser,
        ]);
    } 
    
    public function contentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        $status = $this->params()->fromQuery('status');
        $officeId = $this->params()->fromQuery('office');
        
        $params = [
            'q' => $q, 'sort' => $sort, 'order' => $order, 
            'officeId' => $officeId, 'status' => $status,
        ];
        $query = $this->entityManager->getRepository(User::class)
                        ->findAllUser($params);
        
        $total = $this->entityManager->getRepository(User::class)
                        ->findAllUserTotal($params);
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $data = $query->getResult();
        
        $result = [];
        foreach ($data as $user){
            $row = $user->toArray();
            $result[] = $row;
        }
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);          
    }        
    
    /**
     * This action displays a page allowing to add a new user.
     */
    public function addAction()
    {
        // Create user form
        $form = new UserForm('create', $this->entityManager);
        
        // Get the list of all available roles (sorted by name).
        $allRoles = $this->entityManager->getRepository(Role::class)
                ->findBy([], ['name'=>'ASC']);
        $roleList = [];
        foreach ($allRoles as $role) {
            $roleList[$role->getId()] = $role->getName();
        }
        
        $offices = $this->entityManager->getRepository(Office::class)
                ->findBy([]);
        $officeList = [];
        foreach ($offices as $office) {
            $officeList[$office->getId()] = $office->getName();
        }

        $form->get('roles')->setValueOptions($roleList);
        $form->get('office')->setValueOptions($officeList);
        
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            
            // Fill in the form with POST data
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Validate form
            if($form->isValid()) {
                
                // Get filtered and validated data
                $data = $form->getData();
                
                // Add user.
                $user = $this->userManager->addUser($data);
                
                // Redirect to "view" page
                return $this->redirect()->toRoute('users', 
                        ['action'=>'view', 'id'=>$user->getId()]);                
            }               
        } 
        
        return new ViewModel([
                'form' => $form
            ]);
    }
    
    /**
     * The "view" action displays a page allowing to view user's details.
     */
    public function viewAction() 
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find a user with such ID.
        $user = $this->entityManager->getRepository(User::class)
                ->find($id);
        
        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
                
        $balance = $this->entityManager->getRepository(Cash::class)
                ->userBalance($user->getId(), date('Y-m-d'));
        
        return new ViewModel([
            'user' => $user,
            'balance' => $balance,
        ]);
    }
    
    /**
     * The "edit" action displays a page allowing to edit user.
     */
    public function editAction() 
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $user = $this->entityManager->getRepository(User::class)
                ->find($id);
        
        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Create user form
        $form = new UserForm('update', $this->entityManager, $user);
        
        // Get the list of all available roles (sorted by name).
        $allRoles = $this->entityManager->getRepository(Role::class)
                ->findBy([], ['name'=>'ASC']);
        $roleList = [];
        foreach ($allRoles as $role) {
            $roleList[$role->getId()] = $role->getName();
        }
        $offices = $this->entityManager->getRepository(Office::class)
                ->findBy([]);
        $officeList = [];
        foreach ($offices as $office) {
            $officeList[$office->getId()] = $office->getName();
        }
        
        $form->get('roles')->setValueOptions($roleList);
        $form->get('office')->setValueOptions($officeList);
        
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            
            // Fill in the form with POST data
            $data = $this->params()->fromPost();
            
            $form->setData($data);
            
            // Validate form
            if($form->isValid()) {
                
                if ($user->getEmail() == $this->identity() && $user->getEmail() != $data['email']){
                    $logout = true;
                } else {
                    $logout = false;
                } 
                    
                // Get filtered and validated data
                $data = $form->getData();
                // Update the user.
                $result = $this->userManager->updateUser($user, $data);

                if ($logout){
                    return $this->redirect()->toRoute('logout');
                    
                } else {
                    // Redirect to "view" page
                    return $this->redirect()->toRoute('users', 
                        ['action'=>'view', 'id'=>$user->getId()]);
                    
                }
            }               
        } else {
            
            $userRoleIds = [];
            foreach ($user->getRoles() as $role) {
                $userRoleIds[] = $role->getId();
            }
            $form->setData(array(
                    'aplId' => $user->getAplId(),
                    'full_name'=>$user->getFullName(),
                    'birthday' => $user->getBirthday(),
                    'email'=>$user->getEmail(),
                    'sign' => $user->getSign(),
                    'mailPassword' => $this->userManager->userMailPassword($user),
                    'status'=>$user->getStatus(), 
                    'roles' => $userRoleIds,
                    'office' => ($user->getOffice()) ? $user->getOffice()->getId():null,
                ));
        }
        
        return new ViewModel(array(
            'user' => $user,
            'form' => $form
        ));
    }
    
    /**
     * This action displays a page allowing to change user's password.
     */
    public function changePasswordAction() 
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $user = $this->entityManager->getRepository(User::class)
                ->find($id);
        
        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Create "change password" form
        $form = new PasswordChangeForm('change');
        
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            
            // Fill in the form with POST data
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Validate form
            if($form->isValid()) {
                
                // Get filtered and validated data
                $data = $form->getData();
                
                // Try to change password.
                if (!$this->userManager->changePassword($user, $data)) {
                    $this->flashMessenger()->addErrorMessage(
                            'Sorry, the old password is incorrect. Could not set the new password.');
                } else {
                    $this->flashMessenger()->addSuccessMessage(
                            'Changed the password successfully.');
                }
                
                // Redirect to "view" page
                return $this->redirect()->toRoute('users', 
                        ['action'=>'view', 'id'=>$user->getId()]);                
            }               
        } 
        
        return new ViewModel([
            'user' => $user,
            'form' => $form
        ]);
    }
    
    /**
     * This action displays the "Reset Password" page.
     */
    public function resetPasswordAction()
    {
        // Create form
        $form = new PasswordResetForm();
        
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            
            // Fill in the form with POST data
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Validate form
            if($form->isValid()) {
                
                // Look for the user with such email.
                $user = $this->entityManager->getRepository(User::class)
                        ->findOneByEmail($data['email']);                
                if ($user!=null) {
                    // Generate a new password for user and send an E-mail 
                    // notification about that.
                    $this->userManager->generatePasswordResetToken($user);
                    
                    // Redirect to "message" page
                    return $this->redirect()->toRoute('users', 
                            ['action'=>'message', 'id'=>'sent']);                 
                } else {
                    return $this->redirect()->toRoute('users', 
                            ['action'=>'message', 'id'=>'invalid-email']);                 
                }
            }               
        } 
        
        $this->layout()->setTemplate('layout/layout_no_auth');

        return new ViewModel([                    
            'form' => $form
        ]);
    }
    
    /*
     * sms token
     */
    public function smsTokenAction()
    {
        $msg = 'SMS не отправлено';
        
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();            

            $filter = new PhoneFilter();
            $phone = $this->entityManager->getRepository(Phone::class)
                    ->findOneByName($filter->filter($data['phone']));                
            
            if ($phone){            
                $this->userManager->generatePasswordSMSResetToken($phone);
                $msg = 'SMS с кодом отправлено на номер '.$data['phone'];
            } else {
                $msg = 'Такой номер телефона не зарегистрирован';
            }  
            
        }
        
        return new JsonModel([
            'msg' => $msg,
        ]);          
    }
    
    /**
     * This action displays the "Reset Password" page.
     */
    public function resetPasswordByPhoneAction()
    {
        // Create form
        $form = new PasswordResetPhoneForm($this->entityManager);
        
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            
            // Fill in the form with POST data
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Validate form
            if($form->isValid()) {
                                
                return $this->redirect()->toRoute('users', 
                        ['action'=>'set-password'], ['query' => ['token' => $data['token']]]);                 
            }               
        } 
        
        $this->layout()->setTemplate('layout/layout_no_auth');

        return new ViewModel([                    
            'form' => $form
        ]);
    }
    
    /**
     * This action displays an informational message page. 
     * For example "Your password has been resetted" and so on.
     */
    public function messageAction() 
    {
        // Get message ID from route.
        $id = (string)$this->params()->fromRoute('id');
        
        // Validate input argument.
        if($id!='invalid-email' && $id!='invalid-phone' && $id!='sent' && $id!='sms' && $id!='set' && $id!='failed') {
            throw new \Exception('Invalid message ID specified');
        }
        
        return new ViewModel([
            'id' => $id
        ]);
    }
    
    /**
     * This action displays the "Reset Password" page. 
     */
    public function setPasswordAction()
    {
        $token = $this->params()->fromQuery('token', null);
        
        // Validate token length
        if ($token!=null && (!is_string($token) || strlen($token)!=32)) {
            // Validate sms token length
            if ($token!=null && (!is_numeric($token) || strlen($token)!=4)) {
                throw new \Exception('Invalid token type or length');
            }
        }
        
        if($token===null || 
           !$this->userManager->validatePasswordResetToken($token)) {
            return $this->redirect()->toRoute('users', 
                    ['action'=>'message', 'id'=>'failed']);
        }
                
        // Create form
        $form = new PasswordChangeForm('reset');
        
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            
            // Fill in the form with POST data
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Validate form
            if($form->isValid()) {
                
                $data = $form->getData();
                                               
                // Set new password for the user.
                if ($this->userManager->setNewPasswordByToken($token, $data['new_password'])) {
                    
                    // Redirect to "message" page
                    return $this->redirect()->toRoute('users', 
                            ['action'=>'message', 'id'=>'set']);                 
                } else {
                    // Redirect to "message" page
                    return $this->redirect()->toRoute('users', 
                            ['action'=>'message', 'id'=>'failed']);                 
                }
            }               
        } 
        
        return new ViewModel([                    
            'form' => $form
        ]);
    }
    
    public function liveSearchAction()
    {
        $total = 0;
        $result = [];
        if ($this->getRequest()->isPost()) {	   
            $data = $this->params()->fromPost();

            $query = $this->entityManager->getRepository(User::class)
                            ->liveSearch($data);

            $total = count($query->getResult(2));

            $result = $query->getResult(2);
        }    
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);          
    }    
    
    public function ddReportAction()
    {
        $period = $this->params()->fromQuery('report');
        
        return new ViewModel([                    
            'result' => $this->userManager->ddReport($period),
        ]);        
    }
    
    public function updateOrderCountAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $user = $this->entityManager->getRepository(User::class)
                ->find($id);
        
        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $this->userManager->updateOrderCount($user);
        
        return new JsonModel([
            'ok'
        ]);          
    }    
    
    public function statusAction()
    {
        $userId = $this->params()->fromRoute('id', -1);
        $status = $this->params()->fromQuery('status', User::STATUS_ACTIVE);
        $user = $this->entityManager->getRepository(User::class)
                ->find($userId);        

        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->userManager->changeStatus($user, $status);
        
        return new JsonModel(
           $user->toArray()
        );           
    }                

    public function deleteAction()
    {
        $userId = $this->params()->fromRoute('id', -1);
        $user = $this->entityManager->getRepository(User::class)
                ->find($userId);        

        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $result = 'false';
        if ($this->userManager->remove($user)){
            $result = 'ok';
        }
        
        return new JsonModel(
           [$result]
        );           
    }                
}


