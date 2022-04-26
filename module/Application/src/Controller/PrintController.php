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
        $torg2file = $this->printManager->torg2($vtp);
        
//        var_dump($torg2); exit;
        
        // Render the view template.
        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($torg2file) . '"');
        header('Content-Transfer-Encoding: binary');  
        header('Accept-Ranges: bytes');
  
        // Read the file
        @readfile($torg2file);
    }      
    
}
