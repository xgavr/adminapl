<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Fasade\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Entity\Goods;



class IndexController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    /**
     * Fasade manager.
     * @var \Fasade\Service\FasadeManager
     */
    private $fasadeManager;
        
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $fasadeManager) 
    {
       $this->entityManager = $entityManager;
       $this->fasadeManager = $fasadeManager;    }

    
    public function indexAction()
    {

        return new ViewModel([

        ]);
    }

    public function catalogAction()
    {

        return new ViewModel([

        ]);
    }

    public function catalogContentAction()
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
        $retailCount = $this->params()->fromQuery('retailCount');
        
        $params = [
            'q' => $q, 
            'sort' => $sort, 
            'order' => $order, 
            'producerId' => $producer,
            'groupId' => $group,
            'accurate' => $accurate,            
            'retailCount' => $retailCount,            
        ];
        
        $query = $this->entityManager->getRepository(Goods::class)
                        ->catalog($params);  
        
        $totalCount = $this->entityManager->getRepository(Goods::class)
                        ->catalogTotal($params);   
        
        $total = $totalCount['totalCount'];
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);

        return new JsonModel([
            'total' => $total,
//            'total' => 10000,
            'rows' => $result,
        ]);          
    }    
}
