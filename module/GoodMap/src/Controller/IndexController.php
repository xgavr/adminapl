<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace GoodMap\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Company\Entity\Office;
use GoodMap\Entity\Rack;
use GoodMap\Entity\Shelf;
use GoodMap\Entity\Cell;


class IndexController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    /**
     * GoodMap manager.
     * @var \GoodMap\Service\GoodMapManager
     */
    private $goodMapManager;
        
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $goodMapManager) 
    {
       $this->entityManager = $entityManager;
       $this->goodMapManager = $goodMapManager;    }

    
    public function indexAction()
    {
        $offices = $this->entityManager->getRepository(Office::class)
                ->findAll();
        
        return new ViewModel([
            'offices' => $offices,
            'currentOfficeId' => $this->goodMapManager->currentUser()->getOffice()->getId(),
        ]);
    }

    public function contentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        $officeId = $this->params()->fromQuery('office');
        
        $params = [
            'q' => $q, 'sort' => $sort, 'order' => $order, 
            'officeId' => $officeId,
        ];
        
        $result = $this->entityManager->getRepository(Rack::class)
                        ->findAllRack($params);
        
        $total = count($result);
        
//        if ($offset) {
//            $query->setFirstResult($offset);
//        }
//        if ($limit) {
//            $query->setMaxResults($limit);
//        }

//        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);          
    }        
    
    public function statusesAction()
    {
        return new JsonModel(Rack::getStatusList());                  
    }
    
    public function addAction()
    {
        $pid = $this->params()->fromQuery('pid');
        $table = $this->params()->fromQuery('table');        
        $officeId = $this->params()->fromQuery('office');        
        
        switch ($table){
            case 'shelf':
                $rack = $this->entityManager->getRepository(Rack::class)
                        ->find($pid);
                if ($rack){
                    $this->goodMapManager->addShelf($rack, [
//                        'name' => 'Новая полка',
                        'status' => Shelf::STATUS_ACTIVE,                        
                    ]);
                }
                break;
            case 'cell':
                $shelf = $this->entityManager->getRepository(Shelf::class)
                        ->find($pid);
                if ($shelf){
                    $this->goodMapManager->addCell($shelf, [
//                        'name' => 'Новая ячейка',
                        'status' => Cell::STATUS_ACTIVE,                                                
                    ]);
                }
                break;
            default:    
                $office = $this->entityManager->getRepository(Office::class)
                        ->find($officeId);
                if ($office){
                    $this->goodMapManager->addRack($office, [
//                        'name' => 'Новый стелаж',
                        'status' => Rack::STATUS_ACTIVE,
                    ]);
                }
        }
        
        return new JsonModel(
           ['ok']
        );           
    }
    
    public function deleteAction()
    {
        $pid = $this->params()->fromQuery('pid');
        $table = $this->params()->fromQuery('table');        
        
        switch ($table){
            case 'rack':
                $rack = $this->entityManager->getRepository(Rack::class)
                        ->find($pid);
                if ($rack){
                    $this->goodMapManager->removeRack($rack);
                }
                break;
            case 'shelf':
                $shelf = $this->entityManager->getRepository(Shelf::class)
                        ->find($pid);
                if ($shelf){
                    $this->goodMapManager->removeShelf($shelf);
                }
                break;
            case 'cell':
                $cell = $this->entityManager->getRepository(Cell::class)
                        ->find($pid);
                if ($cell){
                    $this->goodMapManager->removeCell($cell);
                }
                break;
        }
        
        return new JsonModel(
           ['ok']
        );           
    }
    
    public function changeNameAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $name = $this->params()->fromQuery('name');
        $table = $this->params()->fromQuery('table');
        
        switch ($table){
            case 'rack':
                $rack = $this->entityManager->getRepository(Rack::class)
                        ->find($id);
                if ($rack){
                    $this->goodMapManager->updateRackName($rack, $name);
                }
                break;
            case 'shelf':
                $shelf = $this->entityManager->getRepository(Shelf::class)
                        ->find($id);
                if ($shelf){
                    $this->goodMapManager->updateShelfName($shelf, $name);
                }
                break;
            case 'cell':
                $cell = $this->entityManager->getRepository(Cell::class)
                        ->find($id);
                if ($cell){
                    $this->goodMapManager->updateCellName($cell, $name);
                }
                break;
        }

        return new JsonModel(
           ['ok']
        );           
        
    }    
    
    public function changeCommentAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $comment = $this->params()->fromQuery('comment');
        $table = $this->params()->fromQuery('table');
        
        switch ($table){
            case 'rack':
                $rack = $this->entityManager->getRepository(Rack::class)
                        ->find($id);
                if ($rack){
                    $this->goodMapManager->updateRackComment($rack, $comment);
                }
                break;
            case 'shelf':
                $shelf = $this->entityManager->getRepository(Shelf::class)
                        ->find($id);
                if ($shelf){
                    $this->goodMapManager->updateShelfComment($shelf, $comment);
                }
                break;
            case 'cell':
                $cell = $this->entityManager->getRepository(Cell::class)
                        ->find($id);
                if ($cell){
                    $this->goodMapManager->updateCellComment($cell, $comment);
                }
                break;
        }

        return new JsonModel(
           ['ok']
        );           
        
    }    
    
    public function changeCodeAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $code = $this->params()->fromQuery('code');
        $table = $this->params()->fromQuery('table');
        
        switch ($table){
            case 'rack':
                $rack = $this->entityManager->getRepository(Rack::class)
                        ->find($id);
                if ($rack){
                    $this->goodMapManager->updateRackCode($rack, $code);
                }
                break;
            case 'shelf':
                $shelf = $this->entityManager->getRepository(Shelf::class)
                        ->find($id);
                if ($shelf){
                    $this->goodMapManager->updateShelfCode($shelf, $code);
                }
                break;
            case 'cell':
                $cell = $this->entityManager->getRepository(Cell::class)
                        ->find($id);
                if ($cell){
                    $this->goodMapManager->updateCellCode($cell, $code);
                }
                break;
        }

        return new JsonModel(
           ['ok']
        );                   
    }
    
    public function changeStatusAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $status = $this->params()->fromQuery('status');
        $table = $this->params()->fromQuery('table');
        
        switch ($table){
            case 'rack':
                $rack = $this->entityManager->getRepository(Rack::class)
                        ->find($id);
                if ($rack){
                    $this->goodMapManager->updateRackStatus($rack, $status);
                }
                break;
            case 'shelf':
                $shelf = $this->entityManager->getRepository(Shelf::class)
                        ->find($id);
                if ($shelf){
                    $this->goodMapManager->updateShelfStatus($shelf, $status);
                }
                break;
            case 'cell':
                $cell = $this->entityManager->getRepository(Cell::class)
                        ->find($id);
                if ($cell){
                    $this->goodMapManager->updateCellStatus($cell, $status);
                }
                break;
        }

        return new JsonModel(
           ['ok']
        );                   
    }    
    
    public function printBarcodeAction()
    {
        $code = $this->params()->fromQuery('code');
        $officeId = (int)$this->params()->fromQuery('office', -1);

        $decodedCode = $this->goodMapManager->decodeCode($code);
        
        $params = array_filter([
            'officeId' => $officeId, 
            'sort' => 'code', 
            'order' => 'asc',
            'rack' => $decodedCode['rack'],
            'shelf' => $decodedCode['shelf'],
            'cell' => $decodedCode['cell'],
            'code' => $code,
        ]);
        
        $result = $this->entityManager->getRepository(Rack::class)
                ->findAllRack($params);
        
        $this->layout()->setTemplate('layout/terminal');
        
        return new ViewModel([
            'rows' => $result,
        ]);        
    }        
}
