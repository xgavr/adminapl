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


class IndexController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Contact manager.
     * @var Application\Srvice\ContactManager
     */
    private $contactManager;
    
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $contactManager) 
    {
       $this->entityManager = $entityManager;
       $this->contactManager = $contactManager;
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
            throw new \Exception('Not found user with such email');
        }
        
        $phoneform = new PhoneForm($this->entityManager);
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $phoneform->setData($data);
            
            if ($phoneform->isValid()) {

                $data['phone'] = $data['name'];
                unset($data['name']);

                $contacts = $user->getContacts();
                if (count($contacts)){
                    foreach ($contacts as $contact){
                        $this->contactManager->addPhone($contact, $data, true);
                    }                    
                } else {
                    $data['full_name'] = $data['name'] = $user->getFullName();
                    $data['status'] = Contact::STATUS_ACTIVE;
                    $this->contactManager->addNewContact($user, $data);
                }
                
                $phoneform->setData(['name' => null]);
            }
        }    
        
        return new ViewModel([
            'user' => $user,
            'phoneForm' => $phoneform,
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
