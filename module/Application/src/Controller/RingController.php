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
                $ring = $this->ringManager->addRing($data);
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($ring){
                $data = [
                    'office_id' => $ptu->getContract()->getOffice()->getId(),
                    'company' => $ptu->getContract()->getCompany()->getId(),
                    'supplier' => $ptu->getSupplier()->getId(),
                    'legal_id' => $ptu->getLegal()->getId(),  
                    'contract_id' => $ptu->getContract()->getId(),  
                    'doc_date' => $ptu->getDocDate(),  
                    'doc_no' => $ptu->getDocNo(),
                    'comment' => $ptu->getComment(),
                    'status' => $ptu->getStatus(),
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
        $contactName = $cars = $orders = null;
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
                    $contactCars = $this->entityManager->getRepository(ContactCar::class)
                            ->findByContact($phone->getContact()->getId(), ['id' => 'DESC']);                    
                    foreach ($contactCars as $contactCar){
                        $cars[] = [
                            'makeName' => ($contactCar->getMake()) ? $contactCar->getMake()->getName():'',
                            'vin' => $contactCar->getVin(),
                            ];
                    }                    
                    $orders = $this->entityManager->getRepository(Order::class)
                            ->findBy(['contact' => $phone->getContact()->getId()], ['id' => 'DESC']);
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
        ]);                  
    }
}
