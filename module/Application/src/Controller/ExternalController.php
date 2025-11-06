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
use Application\Entity\Goods;

class ExternalController extends AbstractActionController
{
   
    /**
    * Менеджер сущностей.
    * @var \Doctrine\ORM\EntityManager
    */
    private $entityManager;
    
    /**
     * Менеджер справочников.
     * @var \Application\Service\RbManager 
     */
    private $rbManager;    
    
    /**
     * Менеджер внешних баз.
     * @var \Application\Service\ExternalManager 
     */
    private $externalManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $rbManager, $externalManager) 
    {
        $this->entityManager = $entityManager;
        $this->rbManager = $rbManager;
        $this->externalManager = $externalManager;
    }    
    
    public function indexAction()
    {
        return new ViewModel();
    }

    public function autoDbAction()
    {
        $action = $this->params()->fromQuery('action');
        
        if ($action == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $result = $this->externalManager->autoDb($action);
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'message' => $result,
        ]);           
        
    }
    
    public function abcpAction()
    {
        $action = $this->params()->fromQuery('action');
        
        if ($action == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $result = $this->externalManager->abcp($action);
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'message' => $result,
        ]);           
        
    }
    
    public function updateGenericGroupAction()
    {

        $result = $this->externalManager->updateGenericGroup($action);
        
        return new JsonModel([
            'result' => 'ok',
        ]);           
        
    }
    
    public function partsApiAction()
    {
        $action = $this->params()->fromQuery('action');
        
        if ($action == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $result = $this->externalManager->partsApi($action);
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'message' => $result,
        ]);           
        
    }
    
    public function zetasoftAction()
    {
        $action = $this->params()->fromQuery('action');
        
        if ($action == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $result = $this->externalManager->zetasoft($action);
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'message' => $result,
        ]);                   
    }
    
    public function laximoAction()
    {
        $action = $this->params()->fromQuery('action');
        
        if ($action == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $result = $this->externalManager->laximo($action);
        
        //.
        return new JsonModel([
            'message' => $result,
        ]);                   
    }
    
    public function laximoFindOemAction()
    {
        $goodId = $this->params()->fromRoute('id', -1);
        
        if ($goodId < 0) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $good = $this->entityManager->getRepository(Goods::class)
                ->find($goodId);
        
        if (!$good) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        

        $result = $this->externalManager->laximo('findOem', [
            'code' => $good->getCode(),
            'brand' => $good->getProducer()->getName(),
        ]);
        
        //.
        return new JsonModel([
            'message' => $result,
        ]);                   
    }
    
}
