<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Search\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Entity\Goods;
use Search\Entity\SearchTitle;


class IndexController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    /**
     * Search manager.
     * @var \Search\Service\SearchManager
     */
    private $searchManager;
        
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $searchManager) 
    {
       $this->entityManager = $entityManager;
       $this->searchManager = $searchManager;    }

    
    public function indexAction()
    {
        $q = $this->params()->fromQuery('search');
        
        return new ViewModel([
            'search' => $q,
        ]);
    }

    public function contentAction()
    {
        	        
        ini_set('memory_limit', '512M');
        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        $producer = $this->params()->fromQuery('producer');
        $group = $this->params()->fromQuery('group');
        $accurate = $this->params()->fromQuery('accurate');
        
        $params = [
            'sort' => $sort, 
            'order' => $order, 
            'producerId' => $producer,
            'groupId' => $group,
            'groupId' => $group,
            'accurate' => $accurate,            
        ];
        
        $query = $this->entityManager->getRepository(SearchTitle::class)
                        ->queryGoodsBySearchStr($q, $params);
        
        $params['total'] = 1;
        $totalQuery = $this->entityManager->getRepository(SearchTitle::class)
                        ->queryGoodsBySearchStr($q, $params);
        $total = count($totalQuery->getResult());
        
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
