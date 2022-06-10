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
    
    public function orderBillAction() 
    {       
        $orderId = (int)$this->params()->fromRoute('id', -1);
        $ext = $this->params()->fromQuery('ext', 'Pdf');
        $stamp = $this->params()->fromQuery('stamp', false);
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
        $updfile = $this->printManager->bill($order, $ext, boolval($stamp), boolval($code));
        
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
    
}
