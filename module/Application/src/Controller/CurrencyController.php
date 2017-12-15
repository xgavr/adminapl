<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\Currency;
use Application\Entity\Currencyrate;
use Application\Form\CurrencyForm;
use Application\Form\CurrencyrateForm;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class CurrencyController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер товаров.
     * @var Application\Service\CurrencyManager 
     */
    private $currencyManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $currencyManager) 
    {
        $this->entityManager = $entityManager;
        $this->currencyManager = $currencyManager;
    }    
    
    public function indexAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        
        $query = $this->entityManager->getRepository(Currency::class)
                    ->findAllCurrency();
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'currency' => $paginator,
            'currencyManager' => $this->currencyManager
        ]);  
    }
    
    public function addAction() 
    {     
        // Создаем форму.
        $form = new CurrencyForm($this->entityManager);
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер currency для добавления нового good в базу данных.                
                $this->currencyManager->addNewCurrency($data);
                
                // Перенаправляем пользователя на страницу "currency".
                return $this->redirect()->toRoute('currency', []);
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
        $form = new CurrencyForm($this->entityManager);
    
        // Получаем ID tax.    
        $currencyId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $currency = $this->entityManager->getRepository(Currency::class)
                ->findOneById($currencyId);  
        	
        if ($currency == null) {
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
                $this->currencyManager->updateCurrency($currency, $data);
                
                // Перенаправляем пользователя на страницу "currency".
                return $this->redirect()->toRoute('currency', []);
            }
        } else {
            $data = [
               'name' => $currency->getName(),
               'description' => $currency->getDescription(),
            ];
            
            $form->setData($data);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
            'currency' => $currency
        ]);  
    }    
    
    public function deleteAction()
    {
        $currencyId = $this->params()->fromRoute('id', -1);
        
        $currency = $this->entityManager->getRepository(Currency::class)
                ->findOneById($currencyId);        
        if ($currency == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->currencyManager->removeCurrency($currency);
        
        // Перенаправляем пользователя на страницу "currency".
        return $this->redirect()->toRoute('currency', []);
    }    

    public function viewAction() 
    {       
        $currencyId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($currencyId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the tax currency ID
        $currency = $this->entityManager->getRepository(Currency::class)
                ->findOneById($currencyId);
        
        if ($currency == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $form = new CurrencyrateForm();
        // Проверяем, является ли пост POST-запросом.
        if($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if($form->isValid()) {
                                
                // Получаем валадированные данные формы.
                $data = $form->getData();
              
                // Используем менеджер постов для добавления нового комментарий к посту.
                $this->currencyManager->addRateToCurrency($currency, $data);
                
                // Снова перенаправляем пользователя на страницу "view".
                return $this->redirect()->toRoute('currency', ['action'=>'view', 'id'=>$currencyId]);
            }
        }        
        
        // Render the view template.
        return new ViewModel([
            'currency' => $currency,
            'form' => $form,
            'currencyManager' => $this->currencyManager,
        ]);
    }                  

    public function rateDeleteAction()
    {
        $rateId = $this->params()->fromRoute('id', -1);
        
        $rate = $this->entityManager->getRepository(Currencyrate::class)
                ->findOneById($rateId);
        
        if ($rate == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $currency = $rate->getCurrency();
        
        $this->currencyManager->removeRate($rate);
        
        // Перенаправляем пользователя на страницу "currency/view".
        return $this->redirect()->toRoute('currency', ['action' => 'view', 'id' => $currency->getId()]);
    }    

}
