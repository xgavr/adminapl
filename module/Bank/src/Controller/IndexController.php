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
        $account = $this->params()->fromQuery('account');
        
        $bankAccounts = $this->entityManager->getRepository(BankAccount::class)
                ->findBy(['statement' => BankAccount::STATEMENT_ACTIVE, 'status' => BankAccount::STATUS_ACTIVE]);
        
        $curentBalances = [];
        foreach ($bankAccounts as $bankAccount){
            $curentBalances[$bankAccount->getRs()] = $this->entityManager->getRepository(Statement::class)
                    ->currentBalance($bankAccount->getRs());
        }
                
        return new ViewModel([
            'bankAccounts' => $bankAccounts,
            'account' => $account,
            'currentBalances' => $curentBalances,
            'numberFormatFilter' => new \Zend\I18n\Filter\NumberFormat('ru-RU'),
        ]);
    }

    public function statementContentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $rs = $this->params()->fromQuery('rs');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        
        $query = $this->entityManager->getRepository(Statement::class)
                        ->findStatement($q, $rs);
        
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

        $message = '';
        $ok = 'ok-reload';
        if ($result !== true){
            $message = '<p>'.$result.'</p><p><a href="/bankapi/tochka-access">Проверить доступ к api</a></p>';
            $ok = 'error';
        }
        
        return new JsonModel([
            'result' => $ok,
            'message' => $message,
        ]);          
    }
}
