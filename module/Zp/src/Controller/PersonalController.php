<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zp\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Zp\Entity\Personal;
use Zp\Form\PersonalForm;
use Company\Entity\Legal;
use User\Entity\User;
use Zp\Entity\Accrual;
use Zp\Form\PersonalAccrualForm;
use Zp\Entity\Position;


class PersonalController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    /**
     * Zp manager.
     * @var \Zp\Service\ZpManager
     */
    private $zpManager;
        
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $zpManager) 
    {
       $this->entityManager = $entityManager;
       $this->zpManager = $zpManager;
    }

    
    public function indexAction()
    {
        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        $users = $this->entityManager->getRepository(User::class)
                ->findBy(['status' => User::STATUS_ACTIVE]);
        
        return new ViewModel([
            'companies' => $companies,
            'users' => $users,
        ]);
    }
    
    public function contentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $company = $this->params()->fromQuery('company');
        $user = $this->params()->fromQuery('user');
        $position = $this->params()->fromQuery('position');
        $status = $this->params()->fromQuery('status');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        
        $params = [
            'q' => $q, 'company' => $company, 'sort' => $sort, 'order' => $order, 
            'user' => $user, 'position' => $position, 'status' => $status, 
        ];
        
        $query = $this->entityManager->getRepository(Personal::class)
                        ->findPersonal($params);
        
        $total = count($query->getResult());
        
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
        $personalId = (int)$this->params()->fromRoute('id', -1);
        $companyId = $this->params()->fromQuery('company');
        $userId = $this->params()->fromQuery('user');
        
        $personal = null;
        if ($personalId > 0){
            $personal = $this->entityManager->getRepository(Personal::class)
                    ->find($personalId);
        }    
                
        $companyList = [];
        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        foreach ($companies as $company){
            $companyList[$company->getId()] = $company->getname();
        }    
        
        $form = new PersonalForm();
        $form->get('company')->setValueOptions($companyList);
        $form->get('company')->setValue($companyId);
        $form->get('user')->setValueOptions($this->entityManager->
                getRepository(User::class)->userListForm(['status' => User::STATUS_ACTIVE, 'all' => 'веберете сотрудника']));
        $form->get('position')->setValueOptions($this->entityManager->
                getRepository(Position::class)->positionListForm(['status' => Position::STATUS_ACTIVE, 'company' => $companyId, 'all' => 'веберете должность']));
        $form->get('user')->setValue($userId);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                
                if (is_numeric($data['company'])){
                    $data['company'] = $this->entityManager->getRepository(Legal::class)
                            ->find($data['company']);
                }
                if (is_numeric($data['user'])){
                    $data['user'] = $this->entityManager->getRepository(User::class)
                            ->find($data['user']);
                }
                if (is_numeric($data['position'])){
                    $data['position'] = $this->entityManager->getRepository(Position::class)
                            ->find($data['position']);
                }

                if ($personal){
                    $this->zpManager->updatePersonal($personal, $data);
                } else {
                    $personal = $this->zpManager->addPersonal($data);
                }    
                                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($personal){
                $data = [
                    'aplId' => $personal->getAplId(),
                    'company' => $personal->getCompany()->getId(),
                    'user' => $personal->getUser()->getId(),
                    'position' => $personal->getPosition()->getId(),
                    'docDate' => $personal->getDocDate(),
                    'status' => $personal->getStatus(),
                    'positionNum' => $personal->getPositionNum(),
                ];
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'personal' => $personal,
        ]);        
    }    
    
    public function accrualContentAction()
    {
        	        
        $personalId = $this->params()->fromRoute('id', -1);
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        
        $query = $this->entityManager->getRepository(Accrual::class)
                        ->findPersonalAccruals($personalId, ['q' => $q, 'sort' => $sort, 'order' => $order]);
        
        //$total = count($query->getResult(2));
        
        $result = $query->getResult(2);
        
        return new JsonModel($result);          
    }            
    
    public function accrualEditFormAction()
    {        
        $params = $this->params()->fromQuery();
//        var_dump($params); exit;
        $accrual = $rowNo = $result = null;
        
        if (isset($params['accrual'])){
            $accrual = $this->entityManager->getRepository(Accrual::class)
                    ->find($params['accrual']['id']);            
        }
        
        if (isset($params['rowNo'])){
            $rowNo = $params['rowNo'];
        }
        
        $form = new PersonalAccrualForm();
                
        $form->get('accrual')->setValueOptions($this->entityManager->
                getRepository(Accrual::class)->accrualListForm(['status' => Accrual::STATUS_ACTIVE]))
                ->setValue(($accrual) ? $accrual->getId():null);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();

            $form->setData($data);
            
            if (isset($data['accrual'])){
                $accrual = $this->entityManager->getRepository(Accrual::class)
                        ->find($data['accrual']);            
            }

            if ($form->isValid()) {
                $result = 'ok';
                return new JsonModel([
                    'result' => $result,
                    'accrual' => [
                        'id' => $accrual->getId(),
                        'name' => $accrual->getName(),
                    ],
                ]);        
            }
        } else {
            if ($accrual){
                $data = [
                    'accrual' => $accrual->getId(),
                    'status' => $params['status'],
                    'rate' => $params['rate'],
                ];
                $form->setData($data);
            }    
        }        

        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'rowNo' => $rowNo,
            'accrual' => $accrual,
        ]);        
    }
    
    public function mutualAction()
    {
        $user = $this->params()->fromQuery('user');
        
        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        $users = $this->entityManager->getRepository(User::class)
                ->findBy(['status' => User::STATUS_ACTIVE]);
        
        return new ViewModel([
            'companies' => $companies,
            'users' => $users,
            'userId' => $user
        ]);
    }    
    
    public function mutualContentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $company = $this->params()->fromQuery('company');
        $user = $this->params()->fromQuery('user');
        $position = $this->params()->fromQuery('position');
        $status = $this->params()->fromQuery('status');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        
        $params = [
            'q' => $q, 'company' => $company, 'sort' => $sort, 'order' => $order, 
            'user' => $user, 'position' => $position, 'status' => $status, 
        ];
        
        $query = $this->entityManager->getRepository(Personal::class)
                        ->findPersonal($params);
        
        $total = count($query->getResult());
        
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
}
