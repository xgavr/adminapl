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
use User\Entity\User;
use Application\Entity\Contact;
use Application\Entity\Phone;
use Application\Form\PhoneForm;
use Application\Entity\Email;
use User\Form\UserForm;
use Company\Entity\Office;


class IndexController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Contact manager.
     * @var \Application\Srvice\ContactManager
     */
    private $contactManager;

    /**
     * User manager.
     * @var User\Srvice\UserManager
     */
    private $userManager;
    
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $contactManager, $userManager) 
    {
       $this->entityManager = $entityManager;
       $this->contactManager = $contactManager;
       $this->userManager = $userManager;
    }

    
    public function indexAction()
    {
        return new ViewModel();
    }

    public function loginAction()
    {
        return new ViewModel();
    }
    
    /**
     * The "settings" action displays the info about currently logged in user.
     */
    public function settingsAction()
    {
        $user = $this->entityManager->getRepository(User::class)
                ->findOneByEmail($this->identity());
        
        if ($user==null) {
            $email = $this->entityManager->getRepository(Email::class)
                    ->findOneBy(['name' => $this->identity()]);
            $user = $email->getUser();
            if ($user == null){
                throw new \Exception('Not found user with such email '.$this->identity());
            }    
        }
        
        $form = new UserForm('update', $this->entityManager, $user);

        $offices = $this->entityManager->getRepository(Office::class)
                ->findBy([]);
        $officeList = [];
        foreach ($offices as $office) {
            $officeList[$office->getId()] = $office->getName();
        }
        
        $form->get('office')->setValueOptions($officeList);
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();

            if ($user->getEmail() == $this->identity() && $user->getEmail() != $data['email']){
                $logout = true;
            } else {
                $logout = false;
            } 
                    
                // Update the user.
            $result = $this->userManager->updateUser($user, $data);

            if ($logout){
                return $this->redirect()->toRoute('logout');

            } else {
                $this->flashMessenger()->addSuccessMessage(
                        'Настройки сохранены.');
                
                return $this->redirect()->toRoute('application', 
                    ['action'=>'settings']);

            }

            
        }  else {
            $form->setData(array(
                    'aplId' => $user->getAplId(),
                    'full_name'=>$user->getFullName(),
                    'birthday' => $user->getBirthday(),
                    'email'=>$user->getEmail(),
                    'sign' => $user->getSign(),
                    'mailPassword' => $this->userManager->userMailPassword($user),
                    'office' => ($user->getOffice()) ? $user->getOffice()->getId():null,
                ));
            
        }   
        
        return new ViewModel([
            'user' => $user,
            'form' => $form
        ]);
    }    
    
    public function deletePhoneAction()
    {
        $phoneId = $this->params()->fromQuery('id', -1);
        
        $phone = $this->entityManager->getRepository(Phone::class)
                ->findOneById($phoneId);
        
        if ($phone == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->contactManager->removePhone($phone);
        
        // Перенаправляем пользователя на страницу "currency".
        return $this->redirect()->toRoute('application', ['action' => 'settings']);
        
    }
    
    public function checkLoginAction()
    {
        return new JsonModel([
            'ident' => $this->identity(),
            'response' => 'ok',
        ]);
    }
    
}
