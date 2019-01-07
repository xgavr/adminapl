<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class MlController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер ml.
     * @var Application\Service\MlManager 
     */
    private $mlManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $mlManager) 
    {
        $this->entityManager = $entityManager;
        $this->mlManager = $mlManager;
    }    
    
    public function indexAction()
    {
        
        return new ViewModel();
    }
    
    public function matchingRawpriceTrainAction()
    {
        $this->mlManager->matchingRawpriceTrain();
        
        return new JsonModel([
            'ok'
        ]);  
    }
    
    public function nameMatrixAction()
    {
        $this->mlManager->featureNameMatrix();
        
        return new JsonModel([
            'result' => 'ok',
        ]);  
    }
    
    public function clusterNameAction()
    {
        $this->mlManager->clusterName();
        
        return new JsonModel([
            'result' => 'ok',
        ]);  
    }
    
}
