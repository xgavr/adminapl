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
use Company\Entity\Legal;
use Application\Entity\Contact;

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
     * Legal manager.
     * @var \Company\Service\LegalManager
     */
    private $legalManager;

    /**
     * Constructor. 
     */
    public function __construct($entityManager, $contractManager, $legalManager)
    {
        $this->entityManager = $entityManager;
        $this->contractManager = $contractManager;
        $this->legalManager = $legalManager;
    }
    
    public function indexAction()
    {
        return new ViewModel([
        ]);
    }
        
    public function selectAction()
    {
        $companyId = (int)$this->params()->fromQuery('company');
        $legalId = (int)$this->params()->fromQuery('legal', -1);
        $officeId = (int)$this->params()->fromQuery('office', -1);

        $result = [];
        if ($legalId>0) {
            $params = [
                'legal' => $legalId,
            ];
            
            if (!empty($companyId)){
                if (is_numeric($companyId)){
                    $params['company'] = $companyId;
                }    
            }
            
            $contracts = $this->entityManager->getRepository(Contract::class)
                    ->findBy($params, ['dateStart' => 'DESC']);

            if ($contracts){
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
    
    public function contactSelectAction()
    {
        $contactId = (int) $this->params()->fromRoute('id', -1);
        $kind = (int)$this->params()->fromQuery('kind');

        $result = [];
        if ($contactId>0) {
            $contact = $this->entityManager->getRepository(Contact::class)
                    ->find($contactId);            
            $contracts = $this->entityManager->getRepository(Contract::class)
                    ->contactSelect($contact, $kind);
            
            foreach ($contracts as $contract){
                $result[$contract->getId()] = [
                    'id' => $contract->getId(),
                    'name' => $contract->getName(),                
                ];
            }                
        }    
        
        return new JsonModel([
            'rows' => $result,
        ]);                  
    }
    
    public function unionAction()
    {
        $contactId = (int) $this->params()->fromRoute('id', -1);
        if ($contactId>0) {
            $contact = $this->entityManager->getRepository(Contract::class)
                    ->find($contactId);            
            
            $this->legalManager->contractUnion($contact);
        }    
        
        return new JsonModel(
           ['ok']
        );                          
    }
    
    public function updateBalanceAction()
    {
        $contactId = (int) $this->params()->fromRoute('id', -1);
        if ($contactId>0) {
            $contact = $this->entityManager->getRepository(Contract::class)
                    ->find($contactId);            
            
            $this->legalManager->updateContractBalance($contact);
        }    
        
        return new JsonModel(
           ['ok']
        );                          
    }
}
