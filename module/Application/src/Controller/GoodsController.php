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
use Application\Form\GoodsForm;
use Application\Form\GoodSettingsForm;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class GoodsController extends AbstractActionController
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
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $goodsManager) 
    {
        $this->entityManager = $entityManager;
        $this->goodsManager = $goodsManager;
    }    
    
    public function settingsAction()
    {
        $form = new GoodSettingsForm($this->entityManager);
    
        $settings = $this->goodsManager->getSettings();
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер постов, чтобы добавить новый пост в базу данных.                
                $this->goodsManager->setSettings($data);
                
                // Перенаправляем пользователя на страницу "goods".
                return $this->redirect()->toRoute('goods', []);
            }
        } else {
            $form->setData($settings);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
        ]);  
        
    }
    
    public function indexAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        
        $query = $this->entityManager->getRepository(Goods::class)
                    ->findAllGoods();
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'goods' => $paginator,
            'goodsManager' => $this->goodsManager
        ]);  
    }
    
    public function addAction() 
    {     
        // Создаем форму.
        $form = new GoodsForm($this->entityManager);
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер goods для добавления нового good в базу данных.                
                $this->goodsManager->addNewGoods($data);
                
                // Перенаправляем пользователя на страницу "goods".
                return $this->redirect()->toRoute('goods', []);
            }
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form
        ]);
    }   
    
   public function editAction()
   {
        // Создаем форму.
        $form = new GoodsForm($this->entityManager);
    
        // Получаем ID tax.    
        $goodsId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);  
        	
        if ($goods == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер постов, чтобы добавить новый пост в базу данных.                
                $this->goodsManager->updateGoods($goods, $data);
                
                // Перенаправляем пользователя на страницу "goods".
                return $this->redirect()->toRoute('goods', []);
            }
        } else {
            $data = [
               'name' => $goods->getName(),
               'code' => $goods->getCode(),
               'producer' => $goods->getProducer(),
               'tax' => $goods->getTax(),
               'available' => $goods->getAvailable(),
               'description' => $goods->getDescription(),
            ];
            
            $form->setData($data);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
            'goods' => $goods
        ]);  
    }    
    
    public function deleteAction()
    {
        $goodsId = $this->params()->fromRoute('id', -1);
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->goodsManager->removeGoods($goods);
        
        // Перенаправляем пользователя на страницу "rb/tax".
        return $this->redirect()->toRoute('goods', []);
    }    

    public function viewAction() 
    {       
        $goodsId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($goodsId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the tax by ID
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);
        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        // Render the view template.
        return new ViewModel([
            'goods' => $goods,
        ]);
    }      
}
