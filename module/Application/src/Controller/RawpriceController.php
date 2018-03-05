<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\Raw;
use Application\Entity\Rawprice;
use Application\Form\PricesettingsForm;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class RawpriceController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер.
     * @var Application\Service\SupplierManager 
     */
    private $supplierManager;    
    
    /**
     * Менеджер.
     * @var Application\Service\RawManager 
     */
    private $rawManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $supplierManager, $rawManager) 
    {
        $this->entityManager = $entityManager;
        $this->supplierManager = $supplierManager;
        $this->rawManager = $rawManager;
    }    
    
    public function indexAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        
        $query = $this->entityManager->getRepository(Raw::class)
                    ->findAllRaw();
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'raws' => $paginator,
        ]);  
    }
    
    public function checkAction()
    {
        $this->rawManager->checkSupplierPrice();

        return $this->redirect()->toRoute('raw', []);
        
    }
        
    public function deleteAction()
    {
        $rawId = $this->params()->fromRoute('id', -1);
        
        $raw = $this->entityManager->getRepository(Raw::class)
                ->findOneById($rawId);        
        if ($supplier == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->rawManager->removeRaw($raw);
        
        // Перенаправляем пользователя на страницу "raw".
        return $this->redirect()->toRoute('raw', []);
    }    
    
    public function parseAction()
    {
        $rawpriceId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($rawpriceId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $rawprice = $this->entityManager->getRepository(Rawprice::class)
                ->findOneById($rawpriceId);
        
        $this->rawManager->parseRawprice($rawprice);
        
        return $this->redirect()->toRoute('rawprice', ['action' => 'view', 'id' => $rawprice->getId()]);
        
    }        

    public function unknownProducerAction()
    {
        $rawpriceId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($rawpriceId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $rawprice = $this->entityManager->getRepository(Rawprice::class)
                ->findOneById($rawpriceId);
        
        $this->rawManager->unknownProducerRawprice($rawprice);
        
        return $this->redirect()->toRoute('rawprice', ['action' => 'view', 'id' => $rawprice->getId()]);
        
    }        

    public function goodAction()
    {
        $rawpriceId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($rawpriceId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $rawprice = $this->entityManager->getRepository(Rawprice::class)
                ->findOneById($rawpriceId);
        
        $this->rawManager->addGoodRawprice($rawprice);
        
        return $this->redirect()->toRoute('rawprice', ['action' => 'view', 'id' => $rawprice->getId()]);
        
    }        

    public function priceAction()
    {
        $rawpriceId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($rawpriceId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $rawprice = $this->entityManager->getRepository(Rawprice::class)
                ->findOneById($rawpriceId);
        
        $this->rawManager->setPriceRawprice($rawprice);
        
        return $this->redirect()->toRoute('rawprice', ['action' => 'view', 'id' => $rawprice->getId()]);
        
    }        

    public function viewAction() 
    {       
        $rawpriceId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($rawpriceId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the rawprice ID
        $rawprice = $this->entityManager->getRepository(Rawprice::class)
                ->findOneById($rawpriceId);
        
        if ($rawprice == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $form = new PricesettingsForm();
        
        $priceSettingOptions = [];
        foreach ($form->getElements() as $element){
            if ($element->getLabel()){
                $priceSettingOptions[$element->getName()] = $element->getLabel();
            }    
        }
        
        $priceSettings = $rawprice->getRaw()->getSupplier()->getPricesettings();
        if (count($priceSettings)){
            $priceSetting = $priceSettings[0];
        } else {
            $priceSetting = null;
        }    
                
        //сохранение данных настройки прайса
        if($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);            

            if ($form->isValid()) {

                if ($priceSetting){
                    $this->supplierManager->updatePricesettings($priceSetting, $data, true);                    
                } else{
                    $this->supplierManager->addNewPricesettings($supplier, $data, true);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            }
            
        }
        
        // Render the view template.
        return new ViewModel([
            'rawprice' => $rawprice,
            'rawManager' => $this->rawManager,
            'priceSettingOptions' => $priceSettingOptions,
            'priceSetting' => $priceSetting,
            'form' => $form,
        ]);
    }      
}
