<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Bank\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Bank\Entity\Statement;
use Company\Entity\BankAccount;
use Bank\Entity\Acquiring;
use Bank\Entity\AplPayment;

class IndexController extends AbstractActionController
{

    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Менеджер банк.
     * @var \Bank\Service\BankManager
     */
    private $bankManager;

    /**
     * Менеджер ml.
     * @var \Bank\Service\MlManager
     */
    private $mlManager;

    public function __construct($entityManager, $bankManager, $mlManager) 
    {
        $this->entityManager = $entityManager;
        $this->bankManager = $bankManager;
        $this->mlManager = $mlManager;
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
            'numberFormatFilter' => new \Laminas\I18n\Filter\NumberFormat('ru-RU'),
            'avatar' => new \LasseRafn\InitialAvatarGenerator\InitialAvatar(),
            'allowDate' => date('Y-m-d', strtotime($this->bankManager->getAllowDate().' + 1 day')),
        ]);
    }
    
    public function avatarAccountAction()
    {
        $account = $this->params()->fromRoute('id');
        $imageSize = $this->params()->fromQuery('size', 24);

        $name = '--';
        if ($account){
            $bankAccount = $this->entityManager->getRepository(BankAccount::class)
                    ->findOneBy(['rs' => $account]);
            if ($bankAccount){
                $name = substr($bankAccount->getRs(), -2);
            }
        }
        
        $avatar = new \LasseRafn\InitialAvatarGenerator\InitialAvatar();
        $colorFilter = new \Application\Filter\GenerateColorFromText();
//        $inverseColorFilter = new \Application\Filter\InverseColor();
        $background = $colorFilter->filter($name);
        $color = \InvertColor\Color::fromHex($background)->invert(true);
        $image = $avatar->name($name)
                        ->size($imageSize * 2)
                        ->length(2)
                        ->fontSize(0.5)
                        ->background($background)
                        ->color($color)
                        ->generate()
                        ;
        
        header("Content-Type: image/png");
        echo $image->stream('png', 100);
        exit;
    }

    public function statementContentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $rs = $this->params()->fromQuery('rs');
        $date = $this->params()->fromQuery('date');        
        $pay = $this->params()->fromQuery('pay');        
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $dateStart = $this->params()->fromQuery('dateStart');
        $period = $this->params()->fromQuery('period');
        
        $startDate = '2012-01-01';
        $endDate = '2199-01-01';
        if (!empty($dateStart)){
            $startDate = date('Y-m-d', strtotime($dateStart));
            $endDate = $startDate;
            if ($period == 'week'){
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 week - 1 day', strtotime($startDate)));
            }    
            if ($period == 'month'){
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 month - 1 day', strtotime($startDate)));
            }    
            if ($period == 'number'){
                $startDate = $dateStart.'-01-01';
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 year - 1 day', strtotime($startDate)));
            }    
        }    
        
        $query = $this->entityManager->getRepository(Statement::class)
                        ->findStatement($q, $rs, ['start' => $startDate, 'end' => $endDate, 'pay' => $pay]);
        
//        $total = count($query->getResult());
        $totalResult = $this->entityManager->getRepository(Statement::class)
                        ->findStatement($q, $rs, ['start' => $startDate, 'end' => $endDate, 'count' => true, 'pay' => $pay]);
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);
        
        $startTotal = 0;
        $balanceQuery = $this->entityManager->getRepository(Statement::class)
                        ->findBalance($q, $rs, $startDate);
        $balanceResult = $balanceQuery->getResult();
        foreach ($balanceResult as $balance){
            $startTotal += $balance->getBalance();
        }
        
        return new JsonModel([
            'total' => $totalResult['totalCount'],
            'inTotal' => $totalResult['inTotal'],
            'outTotal' => $totalResult['outTotal'],
            'startTotal' => $startTotal,
            'rows' => $result,
        ]);          
    }
    
    public function updateStatementSwapAction()
    {
       if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $statementId = $data['pk'];
            $statement = $this->entityManager->getRepository(Statement::class)
                    ->findOneById($statementId);
//            var_dump($data); exit;
            $swap = ($data['value'] == 'true') ? Statement::SWAP1_TRANSFERED:Statement::SWAP1_TO_TRANSFER;
                    
            if ($statement){
                $this->bankManager->updateStatementSwap($statement, $swap);                    
            }    
        }     
        
        exit;
    }

    public function tochkaStatementUpdateAction()
    {
        $date = $this->params()->fromQuery('date', date('Y-m-d'));
        
        $result = $this->bankManager->tochkaStatementV2($date, $date);

        $message = 'ok!';
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
    
    public function loadStatementFileAction()
    {
        $this->bankManager->checkStatementFolder();
        
        return new JsonModel([
            'result' => 'ok',
        ]);                  
    }
    
    public function acquiringIntersectAction()
    {
        $this->bankManager->findAcquiringIntersect();
        $this->bankManager->findAcquiringIntersectSum();
        
        return new JsonModel([
            'result' => 'ok',
        ]);                  
    }
    
    public function updateAcquiringStatusAction()
    {
        $acquiringId = $this->params()->fromRoute('id');
        $status = $this->params()->fromQuery('status');
        
        if ($acquiringId){
            $acquiring = $this->entityManager->getRepository(Acquiring::class)
                    ->findOneById($acquiringId);
            
            if ($acquiring){
                if ($acquiring->getStatus() == Acquiring::STATUS_NO_MATCH){
                    $status = Acquiring::STATUS_MATCH;
                } else {
                    $status = Acquiring::STATUS_NO_MATCH;
                }
                
                $this->bankManager->updateAcquiringStatus($acquiring, $status);
            }
        }
        
        return new JsonModel([
            'ok',
        ]);                  
    }
    
    public function updateAplPaymentStatusAction()
    {
        $aplPaymentId = $this->params()->fromRoute('id');
        
        if ($aplPaymentId){
            $aplPayment = $this->entityManager->getRepository(AplPayment::class)
                    ->findOneById($aplPaymentId);
            
            if ($aplPayment){
                if ($aplPayment->getStatus() == AplPayment::STATUS_NO_MATCH){
                    $status = AplPayment::STATUS_MATCH;
                } else {
                    $status = AplPayment::STATUS_NO_MATCH;
                }
                
                $this->bankManager->updateAplPaymentStatus($aplPayment, $status);
            }
        }
        
        return new JsonModel([
            'ok',
        ]);                  
    }
    
    public function deleteAplPaymentAction()
    {
        $aplPaymentId = $this->params()->fromRoute('id');
        
        if ($aplPaymentId){
            $aplPayment = $this->entityManager->getRepository(AplPayment::class)
                    ->findOneById($aplPaymentId);
            
            if ($aplPayment){                
                $this->bankManager->removeAplPayment($aplPayment);
            }
        }
        
        return new JsonModel([
            'ok',
        ]);                  
    }

    public function balanceContentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $rs = $this->params()->fromQuery('rs');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        
        $query = $this->entityManager->getRepository(Statement::class)
                        ->findBalance($q, $rs);
        
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

    public function acquiringContentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $status = $this->params()->fromQuery('status', Acquiring::STATUS_NO_MATCH);
        $search = $this->params()->fromQuery('search');
        $date = $this->params()->fromQuery('date');
        if ($search || $date){
            $status = null;
        }
        
        $query = $this->entityManager->getRepository(Acquiring::class)
                        ->findAcquiring(['status' => $status, 'search' => $search, 'date' => $date]);
        
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
    
    public function aplPaymentContentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $status = $this->params()->fromQuery('status', AplPayment::STATUS_NO_MATCH);
        $search = $this->params()->fromQuery('search');
        $date = $this->params()->fromQuery('date');
        if ($search || $date){
            $status = null;
        }
        
        $query = $this->entityManager->getRepository(AplPayment::class)
                        ->findAplPayment(['status' => $status, 'search' => $search, 'date' => $date]);
        
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
    
    public function compressAcquiringAction()
    {
        $this->bankManager->compressAcquiring();
        
        return new JsonModel([
            'result' => 'ok',
        ]);                  
    }
    
    public function compressAplPaymentAction()
    {
        $this->bankManager->compressAplPayment();
        
        return new JsonModel([
            'result' => 'ok',
        ]);                  
    }
    
    public function tochkaStatementsAction()
    {
        $date = $this->params()->fromQuery('date', date('Y-m-d'));
        
        $result = $this->bankManager->tochkaStatementV2($date, $date);

        $message = 'ok!';
        $ok = 'ok-reload';
//        if ($result !== true){
//            $message = '<p>'.$result.'</p><p><a href="/bankapi/tochka-access">Проверить доступ к api</a></p>';
//            $ok = 'error';
//        }
        
        return new JsonModel([
            'result' => $ok,
            'message' => $message,
        ]);          
    }    
    
    public function accountListAction()
    {
        $result = $this->bankManager->accountListV2();
        
        return new JsonModel($result);                  
    }

    public function statementTokensAction()
    {
        $date = $this->params()->fromQuery('date', date('Y-m-d'));
        
        $result = $this->bankManager->tochkaStatementV2($date, $date);

        $message = 'ok!';
        $ok = 'ok-reload';
//        if ($result !== true){
//            $message = '<p>'.$result.'</p><p><a href="/bankapi/tochka-access">Проверить доступ к api</a></p>';
//            $ok = 'error';
//        }
        
        return new JsonModel([
            'result' => $ok,
            'message' => $message,
        ]);          
    }    
    
    public function purposeTokensAction()
    {
        $statementId = $this->params()->fromRoute('id');
        
        $statement = $this->entityManager->getRepository(Statement::class)
                ->find($statementId);
        
        $this->mlManager->statementLemms($statement);
        
        return new JsonModel([ 'result' => 'ok']);                  
    }
    
    public function addStatementTokensAction()
    {
        $this->mlManager->statementTokens();
        
        return new JsonModel([ 'result' => 'ok']);                  
    }
    
    public function statementTokensCountAction()
    {
        $this->mlManager->updateStatementTokensCount();
        
        return new JsonModel([ 'result' => 'ok']);                  
    }
    
    public function updateKindAction()
    {
        $statementId = $this->params()->fromRoute('id', -1);
        $kind = $this->params()->fromQuery('kind', Statement::KIND_UNKNOWN);
        
        if ($statementId > 0){
            $statement = $this->entityManager->getRepository(Statement::class)
                    ->find($statementId);
            if ($statement){
                $this->bankManager->updateStatementKind($statement, $kind);

                $query = $this->entityManager->getRepository(Statement::class)
                                ->findStatement(null, null, ['statementId' => $statementId]);
                
                $result = $query->getOneOrNullResult(2);
                return new JsonModel([
                    'id' => $statement->getId(),
                    'row' => $result,
                ]);
            }
        }
    }    
}
