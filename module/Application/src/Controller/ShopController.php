<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\Goods;
use Zend\View\Model\JsonModel;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class ShopController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер товаров.
     * @var Application\Service\GoodsManager 
     */
    private $goodsManager;    
    
    /**
     * Менеджер товаров.
     * @var Application\Service\ShopManager 
     */
    private $shopManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $shopManager, $goodsManager) 
    {
        $this->entityManager = $entityManager;
        $this->goodsManager = $goodsManager;
        $this->shopManager = $shopManager;
    }    
    
    public function indexAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        $q = $this->params()->fromQuery('q', '');
        
        if (strlen($q) > 2){
            $query = $this->entityManager->getRepository(Goods::class)
                        ->searchByName($q);            
        } else {
            $query = $this->entityManager->getRepository(Goods::class)
                        ->findAllGoods();
        }    
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'goods' => $paginator,
            'goodsManager' => $this->goodsManager,
            'search' => $q,    
        ]);  
    }
    
    public function searchAssistantAction()
    {
        $q = $this->params()->fromQuery('q', '');

        $data = $this->shopManager->searchGoodNameAssistant($q);
        
        return new JsonModel(
           $data
        );        
    }
    
    public function addToBagAction()
    {
        
    }
    
}
