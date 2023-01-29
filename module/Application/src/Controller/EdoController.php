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


class EdoController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер.
     * @var \Application\Service\EdoManager 
     */
    private $edoManager;    
    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $edoManager) 
    {
        $this->entityManager = $entityManager;
        $this->edoManager = $edoManager;
    }    
    
    public function indexAction()
    {

        return new ViewModel([
        ]);  
    }
    
    public function orderBillAction() 
    {       
        $orderId = (int)$this->params()->fromRoute('id', -1);
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
        $file = $this->edoManager->bill($order, boolval($code));
        
        // Render the view template.
        if (file_exists($file)){
            if (ob_get_level()) {
              ob_end_clean();
            }
            // заставляем браузер показать окно сохранения файла
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            // читаем файл и отправляем его пользователю
            readfile($file);
        }
        exit;          
    }      
    
    public function torg12Action() 
    {       
        $orderId = (int)$this->params()->fromRoute('id', -1);
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
        $file = $this->edoManager->torg12($order, boolval($code));
        
        // Render the view template.
        if (file_exists($file)){
            if (ob_get_level()) {
              ob_end_clean();
            }
            // заставляем браузер показать окно сохранения файла
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            // читаем файл и отправляем его пользователю
            readfile($file);
        }
        exit;          
    }              
}
