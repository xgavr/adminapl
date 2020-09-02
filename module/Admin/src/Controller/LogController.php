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

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Laminas\Paginator\Paginator;

class LogController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Log manager.
     * @var \Admin\Service\LogManager
     */
    private $logManager;    
    
    /**
     * Setting manager.
     * @var \Admin\Service\SettingManager
     */
    private $settingManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $logManager, $settingManager) 
    {
        $this->entityManager = $entityManager;
        $this->logManager = $logManager;        
        $this->settingManager = $settingManager;        
    }   
    
    public function indexAction()
    {
        $ident = $this->params()->fromQuery('ident');
        $id = $this->params()->fromQuery('id');
                
        $query = $this->entityManager->getRepository(Log::class)
                ->queryByDocType($ident, ['id' => $id]);
        
        $page = $this->params()->fromQuery('page', 1);
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(20);        
        $paginator->setCurrentPageNumber($page);        
        
        return [
            'rows' => $paginator,
            'entityManager' => $this->entityManager,
            'ident' => $ident,
            'id' => $id,
        ];
    } 
    
    public function settingAction()
    {
        // Визуализируем шаблон представления.
        return new ViewModel([
         ]);          
    }

    public function settingContentAction()
    {
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $status = $this->params()->fromQuery('status');
        
        $query = $this->entityManager->getRepository(Goods::class)
                        ->findSettings(['status' => $status]);

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
}
