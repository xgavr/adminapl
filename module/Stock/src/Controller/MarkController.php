<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Stock\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Stock\Entity\Mark;

class MarkController extends AbstractActionController
{

    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Менеджер пту.
     * @var \Stock\Service\MarkManager
     */
    private $markManager;

    public function __construct($entityManager, $markManager) 
    {
        $this->entityManager = $entityManager;
        $this->markManager = $markManager;
    }   

    public function indexAction()
    {
 
        return new ViewModel([
           
        ]);  
    }
        
    public function contentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        $search = $this->params()->fromQuery('search');
        $markStatus = $this->params()->fromQuery('markStatus');


        
        $params = [
            'q' => $q, 'sort' => $sort, 'order' => $order, 
            'markStatus' => $markStatus, 'search' => $search,
        ];
        $query = $this->entityManager->getRepository(Mark::class)
                        ->queryAllMark($params);
        
        $total = $this->entityManager->getRepository(Mark::class)
                        ->queryAllMarkTotal($params);
        
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
    
        
    public function markStatusAction()
    {
        $markId = $this->params()->fromRoute('id', -1);
        
        $mark = $this->entityManager->getRepository(Mark::class)
                ->find($markId);        

        if ($mark == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $data = $this->markManager->signQr($mark->getMark());
        
        $query = $this->entityManager->getRepository(Mark::class)
                ->queryAllMark(['markId' => $mark->getId()]);
        $result = $query->getOneOrNullResult(2);
        
        return new JsonModel(
           $result
        );           
    }        
    
    
}
