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
use Application\Entity\Comment;
use Application\Entity\Order;
use Application\Entity\Client;
use Stock\Entity\Vtp;
use Company\Entity\Legal;
use Company\Entity\Contract;

class PrintController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер.
     * @var \Application\Service\PrintManager 
     */
    private $printManager;    
            
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $printManager) 
    {
        $this->entityManager = $entityManager;
        $this->printManager = $printManager;
    }    
    
    public function indexAction()
    {
        return new ViewModel([
            'printManager' => $this->printManager,
        ]);  
    }
    
    public function vtpTorg2Action() 
    {       
        $vtpId = (int)$this->params()->fromRoute('id', -1);
        $ext = $this->params()->fromQuery('ext', 'Pdf');

        if ($vtpId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $vtp = $this->entityManager->getRepository(Vtp::class)
                ->find($vtpId);
        
        if ($vtp == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }        
        $torg2file = $this->printManager->torg2($vtp, $ext);
        
//        var_dump($torg2); exit;
        
        // Render the view template.
        header('Content-type: application/'. strtolower($ext));
        header('Content-Disposition: inline; filename="' . basename($torg2file) . '"');
        header('Content-Transfer-Encoding: binary');  
        header('Accept-Ranges: bytes');
  
        // Read the file
        @readfile($torg2file);
    }      
    
    public function vtpUpdAction() 
    {       
        $vtpId = (int)$this->params()->fromRoute('id', -1);
        $ext = $this->params()->fromQuery('ext', 'Pdf');

        if ($vtpId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $vtp = $this->entityManager->getRepository(Vtp::class)
                ->find($vtpId);
        
        if ($vtp == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }        
        $updfile = $this->printManager->updVtp($vtp, $ext);
        
//        var_dump($torg2); exit;
        
        // Render the view template.
        header('Content-type: application/'. strtolower($ext));
        header('Content-Disposition: inline; filename="' . basename($updfile) . '"');
        header('Content-Transfer-Encoding: binary');  
        header('Accept-Ranges: bytes');
  
        // Read the file
        @readfile($updfile);
    }      
    
    public function vtpTorg12Action() 
    {       
        $vtpId = (int)$this->params()->fromRoute('id', -1);
        $ext = $this->params()->fromQuery('ext', 'Pdf');

        if ($vtpId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $vtp = $this->entityManager->getRepository(Vtp::class)
                ->find($vtpId);
        
        if ($vtp == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }        
        $updfile = $this->printManager->vtpTorg12($vtp, $ext, true);
        
//        var_dump($torg2); exit;
        
        // Render the view template.
        header('Content-type: application/'. strtolower($ext));
        header('Content-Disposition: inline; filename="' . basename($updfile) . '"');
        header('Content-Transfer-Encoding: binary');  
        header('Accept-Ranges: bytes');
  
        // Read the file
        @readfile($updfile);
    }          
    
    public function orderBillAction() 
    {       
        $orderId = (int)$this->params()->fromRoute('id', -1);
        $ext = $this->params()->fromQuery('ext', 'Pdf');
        $stamp = $this->params()->fromQuery('stamp', false);
        $code = $this->params()->fromQuery('code', true);
        $edo = $this->params()->fromQuery('edo', false);

        if ($orderId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);
        
        if ($order == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }        
        $updfile = $this->printManager->bill($order, $ext, boolval($stamp), boolval($code), boolval($edo));
        
//        var_dump($torg2); exit;
        
        // Render the view template.
        header('Content-type: application/'. strtolower($ext));
        header('Content-Disposition: inline; filename="' . basename($updfile) . '"');
        header('Content-Transfer-Encoding: binary');  
        header('Accept-Ranges: bytes');
  
        // Read the file
        @readfile($updfile);
    }      
    
    public function torg12Action() 
    {       
        $orderId = (int)$this->params()->fromRoute('id', -1);
        $ext = $this->params()->fromQuery('ext', 'Pdf');
        $code = $this->params()->fromQuery('code', true);

        if ($orderId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);
        
        if ($order == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }        
        $updfile = $this->printManager->torg12($order, $ext, boolval($code));
        
//        var_dump($torg2); exit;
        
        // Render the view template.
        header('Content-type: application/'. strtolower($ext));
        header('Content-Disposition: inline; filename="' . basename($updfile) . '"');
        header('Content-Transfer-Encoding: binary');  
        header('Accept-Ranges: bytes');
  
        // Read the file
        @readfile($updfile);
    }          
    
    public function actAction() 
    {       
        $orderId = (int)$this->params()->fromRoute('id', -1);
        $ext = $this->params()->fromQuery('ext', 'Pdf');

        if ($orderId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);
        
        if ($order == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }        
        $updfile = $this->printManager->act($order, $ext);
        
//        var_dump($torg2); exit;
        
        // Render the view template.
        header('Content-type: application/'. strtolower($ext));
        header('Content-Disposition: inline; filename="' . basename($updfile) . '"');
        header('Content-Transfer-Encoding: binary');  
        header('Accept-Ranges: bytes');
  
        // Read the file
        @readfile($updfile);
    }              
    
    public function preorderAction() 
    {       
        $orderId = (int)$this->params()->fromRoute('id', -1);
        $ext = $this->params()->fromQuery('ext', 'Pdf');
        $code = $this->params()->fromQuery('code', false);

        if ($orderId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);
        
        if ($order == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }        
        $updfile = $this->printManager->preorder($order, $ext, $code);
        
//        var_dump($torg2); exit;
        
        // Render the view template.
        header('Content-type: application/'. strtolower($ext));
        header('Content-Disposition: inline; filename="' . basename($updfile) . '"');
        header('Content-Transfer-Encoding: binary');  
        header('Accept-Ranges: bytes');
  
        // Read the file
        @readfile($updfile);
    }              
    
   
    public function offerAction() 
    {       
        $orderId = (int)$this->params()->fromRoute('id', -1);
        $ext = $this->params()->fromQuery('ext', 'Pdf');
        $stamp = $this->params()->fromQuery('stamp', true);
        $code = $this->params()->fromQuery('code', false);

        if ($orderId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);
        
        if ($order == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }        
        $updfile = $this->printManager->offer($order, $ext, $stamp, $code);
        
//        var_dump($torg2); exit;
        
        // Render the view template.
        header('Content-type: application/'. strtolower($ext));
        header('Content-Disposition: inline; filename="' . basename($updfile) . '"');
        header('Content-Transfer-Encoding: binary');  
        header('Accept-Ranges: bytes');
  
        // Read the file
        @readfile($updfile);
    }              
    
    public function checkAction() 
    {       
        $orderId = (int)$this->params()->fromRoute('id', -1);
        $ext = $this->params()->fromQuery('ext', 'Html');

        if ($orderId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);
        
        if ($order == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }        
        $updfile = $this->printManager->check($order, $ext);
        
//        var_dump($torg2); exit;
        
        // Render the view template.
        switch ($ext){
            case 'Html':
                $this->layout()->setTemplate('layout/terminal');
                return new ViewModel([
                    'content' => $updfile,
                ]);        
            default:
                header('Content-type: application/'. strtolower($ext));
                header('Content-Disposition: inline; filename="' . basename($updfile) . '"');
                header('Content-Transfer-Encoding: binary');  
                header('Accept-Ranges: bytes');

                // Read the file
                @readfile($updfile);
        }
    }              
    
    public function reviseAction() 
    {       
        $dateStart = $this->params()->fromQuery('dateStart');
//        $dateEnd = $this->params()->fromQuery('dateEnd');
        $period = $this->params()->fromQuery('period');
        $companyId = $this->params()->fromQuery('company', -1);
        $legalId = $this->params()->fromQuery('legal', -1);
        $contractId = $this->params()->fromQuery('contract', -1);
        $ext = $this->params()->fromQuery('ext', 'Pdf');
        $stamp = $this->params()->fromQuery('stamp');
//        $range = $this->params()->fromQuery('dateRange');
        
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

        if ($companyId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $company = $this->entityManager->getRepository(Legal::class)
                ->find($companyId);

        if ($legalId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $legal = $this->entityManager->getRepository(Legal::class)
                ->find($legalId);
        
        if ($legal == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }        
        
        $contract = null;
        if ($contractId > 0){
            $contract = $this->entityManager->getRepository(Contract::class)
                    ->find($contractId);
        }
        
        var_dump($startDate, $endDate);
        $updfile = $this->printManager->revise($startDate, $endDate, $company, $legal, $contract, $ext, $stamp);
        
        // Render the view template.
        header('Content-type: application/'. strtolower($ext));
        header('Content-Disposition: inline; filename="' . basename($updfile) . '"');
        header('Content-Transfer-Encoding: binary');  
        header('Accept-Ranges: bytes');
  
        // Read the file
        @readfile($updfile);
    }          
    
}
