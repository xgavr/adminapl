<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Bank\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Bank\Entity\Statement;
use Company\Entity\BankAccount;

class IndexController extends AbstractActionController
{

    /**
     * Менеджер сущностей.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Менеджер банк.
     * @var Bank\Service\BankManager
     */
    private $bankManager;

    public function __construct($entityManager, $bankManager) 
    {
        $this->entityManager = $entityManager;
        $this->bankManager = $bankManager;
    }   

    public function indexAction()
    {
        return [];
    }
    
    public function statementAction()
    {
        return new ViewModel([]);
    }

    public function statementContentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        
        $query = $this->entityManager->getRepository(Statement::class)
                        ->findStatement($q);
        
        $total = count($query->getResult(2));
        
        if ($offset) $query->setFirstResult( $offset );
        if ($limit) $query->setMaxResults( $limit );
        
        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);          
    }

    public function tochkaStatementUpdateAction()
    {
        	        
        $result = $this->bankManager->tochkaStatement(date('Y-m-d', strtotime("-1 days")), date('Y-m-d'));
        
        return new JsonModel([
            'ok',
        ]);          
    }
}
