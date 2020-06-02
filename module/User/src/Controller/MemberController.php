<?php
namespace User\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use User\Entity\User;
use User\Entity\Role;
use Application\Entity\Client;
use User\Form\UserForm;
use User\Form\PasswordChangeForm;
use User\Form\PasswordResetForm;

/**
 * This controller is responsible for user management (adding, editing, 
 * viewing users and changing user's password).
 */
class MemberController extends AbstractActionController 
{
    /*
     * Id роли представителя
     */
    const MEMBER_ROLE_ID = 4;
    
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * User manager.
     * @var User\Service\UserManager 
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
        if (!$this->access('member.manage')) {
            $this->getResponse()->setStatusCode(401);
            return;
        }
        
        $currentUser = $this->entityManager->getRepository(User::class)
            ->findOneByEmail($this->identity());
        
        $users = $this->entityManager->getRepository(User::class)
                ->findUsersByRole(self::MEMBER_ROLE_ID);
        
        return new ViewModel([
            'users' => $users,
        ]);
    } 
    
    public function clientManagerTransferAction()
    {
        $clientId = (int) $this->params()->fromRoute('id', -1);
        
        if ($clientId<1) {
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
        
        $users = $this->entityManager->getRepository(User::class)
                ->findUsersByRole(self::MEMBER_ROLE_ID);
        
        return new ViewModel([
            'users' => $users,
            'client' => $client,
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
                ->findBy(['id' => self::MEMBER_ROLE_ID], ['name'=>'ASC']);
        $roleList = [];
        foreach ($allRoles as $role) {
            $roleList[$role->getId()] = $role->getName();
        }
        
        $form->get('roles')->setValueOptions($roleList);                
        // Check if user has submitted the form
        
        if ($this->getRequest()->isPost()) {
            
            // Fill in the form with POST data
            $data = $this->params()->fromPost(); 
            $data['roles'][] = self::MEMBER_ROLE_ID;
            
            $form->setData($data);
            
            // Validate form
            if($form->isValid()) {
                
                // Get filtered and validated data
                $data = $form->getData();
                
                // Add user.
                $user = $this->userManager->addUser($data);
                
                // Redirect to "view" page
                return $this->redirect()->toRoute('members', 
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
                
        return new ViewModel([
            'user' => $user
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
        
        if (!$this->access('member.manage')) {
            if (!$this->access('member.own.manage', ['user'=>$user])) {
                return $this->redirect()->toRoute('not-authorized');
            }    
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
        
        $form->get('roles')->setValueOptions($roleList);
        
        $userRoles = $user->getRoles();
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            
            // Fill in the form with POST data
            $data = $this->params()->fromPost();
            
            foreach ($userRoles as $role){
                $data['roles'][] = $role->getId();
            }    

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
                    if (!$this->access('member.manage')) {
                        if ($this->access('member.own.manage', ['user'=>$user])) {
                            return $this->redirect()->toRoute('application', ['action' => 'settings']);
                        }    
                    }
                    // Redirect to "view" page
                    return $this->redirect()->toRoute('members', 
                        ['action'=>'view', 'id'=>$user->getId()]);
                    
                }
            }               
        } else {
            
            $userRoleIds = [];
            foreach ($user->getRoles() as $role) {
                $userRoleIds[] = $role->getId();
            }
            
            $form->setData(array(
                    'full_name'=>$user->getFullName(),
                    'email'=>$user->getEmail(),
                    'status'=>$user->getStatus(), 
                    'roles' => $userRoleIds
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
        
        if (!$this->access('member.manage')) {
            if (!$this->access('member.own.manage', ['user'=>$user])) {
                return $this->redirect()->toRoute('not-authorized');
            }    
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
                            'Извините, старый пароль неверен. Не удалось установить новый пароль.');
                } else {
                    $this->flashMessenger()->addSuccessMessage(
                            'Пароль успешно изменен.');
                }
                
                if (!$this->access('member.manage')) {
                    if ($this->access('member.own.manage', ['user'=>$user])) {
                        return $this->redirect()->toRoute('application', ['action' => 'settings']);
                    }    
                }
                
                // Redirect to "view" page
                return $this->redirect()->toRoute('members', 
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
                    return $this->redirect()->toRoute('members', 
                            ['action'=>'message', 'id'=>'sent']);                 
                } else {
                    return $this->redirect()->toRoute('members', 
                            ['action'=>'message', 'id'=>'invalid-email']);                 
                }
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
        if($id!='invalid-email' && $id!='sent' && $id!='set' && $id!='failed') {
            throw new \Exception('Указан неверный идентификатор сообщения');
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
            throw new \Exception('Недопустимый тип или длина токена');
        }
        
        if($token===null || 
           !$this->userManager->validatePasswordResetToken($token)) {
            return $this->redirect()->toRoute('members', 
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
                    return $this->redirect()->toRoute('members', 
                            ['action'=>'message', 'id'=>'set']);                 
                } else {
                    // Redirect to "message" page
                    return $this->redirect()->toRoute('members', 
                            ['action'=>'message', 'id'=>'failed']);                 
                }
            }               
        } 
        
        return new ViewModel([                    
            'form' => $form
        ]);
    }
}


