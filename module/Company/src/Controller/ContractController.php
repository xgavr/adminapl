<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Company\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Company\Entity\Contract;
use Company\Form\ContractForm;

class ContractController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Contract manager.
     * @var \Company\Service\ContractManager
     */
    private $contractManager;

    /**
     * Constructor. 
     */
    public function __construct($entityManager, $contractManager)
    {
        $this->entityManager = $entityManager;
        $this->contractManager = $contractManager;
    }
    
    public function indexAction()
    {
        return new ViewModel([
        ]);
    }
    
    /**
     * This action displays a page allowing to add a new role.
     */
    public function addAction()
    {
        // Create form
        $form = new RegionForm();
                
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            
            // Fill in the form with POST data
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Validate form
            if($form->isValid()) {
                
                // Get filtered and validated data
                $data = $form->getData();
                
                // Add role.
                $this->regionManager->addRegion($data);
                
                // Add a flash message.
                $this->flashMessenger()->addSuccessMessage('Добавлен новй регион.');
                
                // Redirect to "index" page
                return $this->redirect()->toRoute('regions', ['action'=>'index']);                
            }               
        } 
        
        return new ViewModel([
                'form' => $form
            ]);
    }    
    
    /**
     * The "view" action displays a page allowing to view role's details.
     */
    public function viewAction() 
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find a role with such ID.
        $region = $this->entityManager->getRepository(Region::class)
                ->find($id);
        
        if ($region == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
                        
        return new ViewModel([
            'region' => $region,
        ]);
    }
    
    /**
     * This action displays a page allowing to edit an existing region.
     */
    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $region = $this->entityManager->getRepository(Region::class)
                ->find($id);
        
        if ($region == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Create form
        $form = new RegionForm();
        
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            
            // Fill in the form with POST data
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Validate form
            if($form->isValid()) {
                
                // Get filtered and validated data
                $data = $form->getData();
                
                // Update permission.
                $this->regionManager->updateRegion($region, $data);
                
                // Add a flash message.
                $this->flashMessenger()->addSuccessMessage('Регион изменен.');
                
                // Redirect to "index" page
                return $this->redirect()->toRoute('regions', ['action'=>'index']);                
            }               
        } else {
            $form->setData(array(
                    'name'=>$region->getName(),
                    'fullName'=>$region->getFullName()     
                ));
        }
        
        return new ViewModel([
                'form' => $form,
                'region' => $region
            ]);
    }
    
    /**
     * This action deletes a region.
     */
    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $region = $this->entityManager->getRepository(Region::class)
                ->find($id);
        
        if ($region == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Delete role.
        $this->regionManager->deleteRegion($region);
        
        // Add a flash message.
        $this->flashMessenger()->addSuccessMessage('Регион удален.');

        // Redirect to "index" region
        return $this->redirect()->toRoute('regions', ['action'=>'index']); 
    }
    
    public function selectAction()
    {
        $companyId = (int)$this->params()->fromQuery('company', -1);
        $legalId = (int)$this->params()->fromQuery('legal', -1);

        $result = [];
        if ($companyId>0 && $legalId>0) {

            $contracts = $this->entityManager->getRepository(Contract::class)
                    ->findBy(['company' => $companyId, 'legal' => $legalId]);

            if ($contracts){
                foreach ($contracts as $contract){
                    $result[$contract->getId()] = [
                        'id' => $contract->getId(),
                        'name' => $contract->getName(),                
                    ];
                }
            }    
        }    
        
        return new JsonModel([
            'rows' => $result,
        ]);                  
    }
    
}
