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
use Zp\Form\PersonalReviseForm;
use Zp\Entity\Position;
use Zp\Entity\PersonalRevise;


class ReviseController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    /**
     * Zp calculator.
     * @var \Zp\Service\ZpCalculator
     */
    private $zpCalculator;
        
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $zpCalculator) 
    {
       $this->entityManager = $entityManager;
       $this->zpCalculator = $zpCalculator;
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
        $accrual = $this->params()->fromQuery('accrual');
        $status = $this->params()->fromQuery('status');
        $kind = $this->params()->fromQuery('kind');
        $year_month = $this->params()->fromQuery('month');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        
        $year = $month = null;
        if ($year_month){
            $year = date('Y', strtotime($year_month));
            $month = date('m', strtotime($year_month));
        }

        $params = [
            'q' => $q, 'company' => $company, 'sort' => $sort, 'order' => $order, 
            'user' => $user, 'accrual' => $accrual, 'status' => $status, 'kind' => $kind, 
            'year' => $year, 'month' => $month,
        ];
        
        $query = $this->entityManager->getRepository(PersonalRevise::class)
                        ->findPersonalRevise($params);
        
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
        $reviseId = (int)$this->params()->fromRoute('id', -1);
        $companyId = $this->params()->fromQuery('company');
        $userId = $this->params()->fromQuery('user');
        
        $revise = null;
        if ($reviseId > 0){
            $revise = $this->entityManager->getRepository(PersonalRevise::class)
                    ->find($reviseId);
        }    
                
        $companyList = [];
        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        foreach ($companies as $company){
            $companyList[$company->getId()] = $company->getname();
        }    
        
        $form = new PersonalReviseForm();
        $form->get('company')->setValueOptions($companyList);
        $form->get('company')->setValue($companyId);
        $form->get('user')->setValueOptions($this->entityManager->
                getRepository(User::class)->userListForm(['status' => User::STATUS_ACTIVE, 'all' => 'веберете сотрудника']));
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

                if ($revise){
                    $this->zpCalculator->updatePersonalRevise($revise, $data);
                } else {
                    $revise = $this->zpCalculator->addPersonalRevise($data);
                }    
                                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($revise){
                $data = [
                    'company' => $revise->getCompany()->getId(),
                    'user' => $revise->getUser()->getId(),
                    'accrual' => $revise->getAccrual()->getId(),
                    'docDate' => $revise->getDocDate(),
                    'status' => $revise->getStatus(),
                    'kind' => $revise->getKind(),
                    'docNum' => $revise->getDocNum(),
                    'amount' => $revise->getAmount(),
                    'comment' => $revise->getComment(),
                ];
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'revise' => $revise,
        ]);        
    }        
}
