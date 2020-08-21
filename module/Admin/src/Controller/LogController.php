<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Admin\Entity\Log;
use Laminas\Paginator\Adapter;
use Laminas\Paginator\Paginator;

class LogController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Log manager.
     * @var \Admin\Service\LogManager
     */
    private $logManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $logManager) 
    {
        $this->entityManager = $entityManager;
        $this->logManager = $logManager;        
    }   
    
    public function indexAction()
    {
        $ident = $this->params()->fromQuery('ident');
        $id = $this->params()->fromQuery('id');
                
        $logs = $this->entityManager->getRepository(Log::class)
                ->findByDocType($ident, ['id' => $id]);
        
        $page = $this->params()->fromQuery('page', 1);
        $adapter = new DoctrineAdapter(new Adapter\ArrayAdapter($logs));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);                
        
        return [];
    }    
}
