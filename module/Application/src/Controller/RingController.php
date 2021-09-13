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
use Application\Entity\Ring;
use Application\Form\RingForm;
use User\Filter\PhoneFilter;
use Application\Entity\Phone;
use Application\Entity\ContactCar;
use Application\Entity\Order;
use Application\Entity\RingHelpGroup;
use Application\Entity\RingHelp;
use Application\Form\RingHelpGroupForm;
use Application\Form\RingHelpForm;


class RingController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер.
     * @var \Application\Service\RingManager 
     */
    private $ringManager;    
        
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $ringManager) 
    {
        $this->entityManager = $entityManager;
        $this->ringManager = $ringManager;
    }    
    
    public function indexAction()
    {        
        return new ViewModel([
        ]);  
    }
    
    public function contentAction()
    {
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        $limit = $this->params()->fromQuery('limit');
        $status = $this->params()->fromQuery('status', Ring::STATUS_ACTIVE);
        
        $query = $this->entityManager->getRepository(Ring::class)
                        ->findAllRing(['q' => $q, 'sort' => $sort, 'order' => $order, 'status' => $status]);

        $total = count($query->getResult(2));
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);                  
    }
    
    public function editFormAction()
    {
        $ringId = (int)$this->params()->fromRoute('id', -1);
        
        $ring = null;
        
        if ($ringId > 0){
            $ring = $this->entityManager->getRepository(Ring::class)
                    ->find($ringId);
        }    

        $form = new RingForm();

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();            
            $form->setData($data);

            if ($form->isValid()) {
                $phoneFilter = new PhoneFilter();
                $data['phone'] = $phoneFilter->filter($data['phone1']);
                $ring = $this->ringManager->addRing($data);
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
                var_dump($form->getMessages());
            }
        } else {
            if ($ring){
                $data = [
                ];
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'ring' => $ring,
        ]);        
    }    
    
    public function findPhoneAction()
    {
        $contactName = $cars = $orders = $contact = null;
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $phonePost = $data['phone'];
            $phoneFilter = new PhoneFilter();
            $phoneNum = $phoneFilter->filter($phonePost);

            if (strlen($phoneNum) == 10){

                $phone = $this->entityManager->getRepository(Phone::class)
                                ->findOneByName(['name' => $phoneNum]);
                if ($phone){
                    $contactName = $phone->getContact()->getName();
                    $contact = $phone->getContact()->getId();
                    $contactCars = $this->entityManager->getRepository(ContactCar::class)
                            ->findByContact($contact, ['id' => 'DESC']);                    
                    foreach ($contactCars as $contactCar){
                        $cars[] = [
                            'makeName' => ($contactCar->getMake()) ? $contactCar->getMake()->getName():'',
                            'vin' => $contactCar->getVin(),
                            'id' => $contactCar->getId(),
                            ];
                    }                    
                    $orders = $this->entityManager->getRepository(Order::class)
                            ->findBy(['contact' => $contact], ['id' => 'DESC']);
                    foreach ($orders as $order){
                        $orders[] = [
                            'aplId' => $order->getAplId(),
                            'id' => $order->getId(),
                            'status' => $order->getStatus(),
                            'user' => ($order->getUser()) ? $order->getUser()->getId():null,
                            'skiper' => ($order->getSkiper()) ? $order->getSkiper()->getId():null,
                            ];
                    }                    
                }
            }    
        }    
        return new JsonModel([
            'name' => $contactName,
            'cars' => $cars,
            'orders' => $orders,
            'contact' => $contact,
        ]);                  
    }
    
    public function findHelpAction()
    {
        $helps = [];
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $mode = $data['mode'];
            
            $groups = $this->entityManager->getRepository(RingHelpGroup::class)
                            ->findBy(['mode' => $mode, 'status' => RingHelpGroup::STATUS_ACTIVE], ['sort' => 'ASC']);
            if ($groups){                
                foreach ($groups as $helpGroup){                    
                    $ringHelps = $this->entityManager->getRepository(RingHelp::class)
                                ->findBy(['mode' => $mode, 
                                    'status' => RingHelp::STATUS_ACTIVE,
                                    'ringHelpGroup' => $helpGroup->getId()], ['sort' => 'ASC']);
                    foreach ($ringHelps as $help){
                        $helps[$helpGroup->getName()][$help->getId()] = (array) $help;
                    }
                }                
            }    
        }    
        return new JsonModel([
            'helps' => $helps,
        ]);                  
    }

    public function helpGroupsAction()
    {        
        return new ViewModel([
        ]);  
    }
    
    public function helpGroupsContentAction()
    {
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        $limit = $this->params()->fromQuery('limit');
        $mode = $this->params()->fromQuery('mode');
        
        $query = $this->entityManager->getRepository(Ring::class)
                        ->findAllRingHelpGroup(['q' => $q, 'sort' => $sort, 'order' => $order, 'mode' => $mode]);

        $total = count($query->getResult(2));
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);                  
    }
    
    public function helpGroupFormAction()
    {
        $helpGroupId = (int)$this->params()->fromRoute('id', -1);
        $mode = (int) $this->params()->fromQuery('mode', -1);
        
        $helpGroup = null;
        
        if ($helpGroupId > 0){
            $helpGroup = $this->entityManager->getRepository(RingHelpGroup::class)
                    ->find($helpGroupId);
        }    

        $form = new RingHelpGroupForm();
        if ($mode>0){
            $form->setData(['mode' => $mode]);
        }

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();            
            $form->setData($data);

            if ($form->isValid()) {
                if ($helpGroup){
                    $this->ringManager->updateHelpGroup($helpGroup, $data);
                } else {
                    $helpGroup = $this->ringManager->addHelpGroup($data);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
                var_dump($form->getMessages());
            }
        } else {            
            if ($helpGroup){
                $data = [
                    'info' => $helpGroup->getInfo(),
                    'mode' => $helpGroup->getMode(),
                    'name' => $helpGroup->getName(),
                    'sort' => $helpGroup->getSort(),
                    'status' => $helpGroup->getStatus(),
                ];
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'helpGroup' => $helpGroup,
        ]);        
    }    
    
    public function deleteHelpGroupAction()
    {
        $helpGroupId = (int)$this->params()->fromRoute('id', -1);
        
        if ($helpGroupId > 0){
            $helpGroup = $this->entityManager->getRepository(RingHelpGroup::class)
                    ->find($helpGroupId);
        }    

        if ($helpGroup){
            $this->ringManager->removeHelpGroup($helpGroup);
        }
        
        return new JsonModel(
           ['ok']
        );           
    }        
    
    public function helpGroupSelectAction()
    {
        $modeId = (int)$this->params()->fromQuery('mode', -1);

        $result[0] = ['id' => null, 'name' => 'все'];
        if ($modeId>0) {

            $helpGroups = $this->entityManager->getRepository(RingHelpGroup::class)
                    ->findBy(['mode' => $modeId, 'status' => RingHelpGroup::STATUS_ACTIVE]);

            if ($helpGroups){
                foreach ($helpGroups as $helpGroup){
                    $result[$helpGroup->getId()] = [
                        'id' => $helpGroup->getId(),
                        'name' => $helpGroup->getName(),                
                    ];
                }
            }    
        }    
        
        return new JsonModel([
            'rows' => $result,
        ]);                  
    }
    
    public function helpAction()
    {        
//        $helpGroups = $this->entityManager->getRepository(RingHelpGroup::class)
//                ->findBy(['status' => RingHelpGroup::STATUS_ACTIVE], ['sort' => 'ASC']);
        return new ViewModel([
           // 'helpGroups' => $helpGroups,
        ]);  
    }
    
    public function helpContentAction()
    {
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        $limit = $this->params()->fromQuery('limit');
        $mode = $this->params()->fromQuery('mode');
        $helpGroup = $this->params()->fromQuery('helpGroup');
        
        $query = $this->entityManager->getRepository(Ring::class)
                        ->findAllRingHelp([
                            'q' => $q, 
                            'sort' => $sort, 
                            'order' => $order, 
                            'mode' => $mode,
                            'helpGroup' => $helpGroup,
                        ]);

        $total = count($query->getResult(2));
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);                  
    }
    
    public function helpFormAction()
    {
        $helpId = (int)$this->params()->fromRoute('id', -1);
        $modeId = (int) $this->params()->fromQuery('mode');
        $helpGroupId = (int) $this->params()->fromQuery('helpGroup');
        
        $help = null;
        
        if ($helpId > 0){
            $help = $this->entityManager->getRepository(RingHelp::class)
                    ->find($helpId);
        }    

        $form = new RingHelpForm($this->entityManager);
        $form->setData(['mode' => $modeId, 'helpGroup' => $helpGroupId]);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();            
            $form->setData($data);

            if ($form->isValid()) {
                $helpGroup = $this->entityManager->getRepository(RingHelpGroup::class)
                        ->find($data['helpGroup']);
                $data['helpGroup'] = $helpGroup;
                if ($help){
                    $this->ringManager->updateHelp($help, $data);
                } else {
                    $help = $this->ringManager->addHelp($data);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
                var_dump($form->getMessages());
            }
        } else {            
            if ($help){
                $data = [
                    'info' => $help->getInfo(),
                    'mode' => $help->getMode(),
                    'helpGroup' => $help->getRingHelpGroup()->getId(),
                    'name' => $help->getName(),
                    'sort' => $help->getSort(),
                    'status' => $help->getStatus(),
                ];
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'help' => $help,
        ]);        
    }    
    
    public function deleteHelpAction()
    {
        $helpId = (int)$this->params()->fromRoute('id', -1);
        
        if ($helpId > 0){
            $help = $this->entityManager->getRepository(RingHelp::class)
                    ->find($helpId);
        }    

        if ($help){
            $this->ringManager->removeHelp($help);
        }
        
        return new JsonModel(
           ['ok']
        );           
    }            
}
