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
use Application\Entity\Ring;


class RingController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер.
     * @var \Application\Service\RingManager 
     */
    private $ringManager;    
        
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $ringManager) 
    {
        $this->entityManager = $entityManager;
        $this->ringManager = $ringManager;
    }    
    
    public function indexAction()
    {

        $files = $this->entityManager->getRepository(Cross::class)
                ->getTmpFiles();
        
        return new ViewModel([
            'files' => $files,
            'crossManager' => $this->crossManager,
        ]);  
    }
    
    public function bindAction()
    {
        $crossId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($crossId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $cross = $this->entityManager->getRepository(Cross::class)
                ->findOneById($crossId);
        
        $this->crossManager->bindCross($cross);
        
        return new JsonModel(
           ['ok']
        );                   
    }        

    public function resetAction()
    {
        $crossId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($crossId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $cross = $this->entityManager->getRepository(Cross::class)
                ->findOneById($crossId);
        
        $this->crossManager->resetCross($cross);
        
        return new JsonModel(
           ['ok']
        );                   
    }        
}
