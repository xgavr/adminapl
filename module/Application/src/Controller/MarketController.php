<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Entity\MarketPriceSetting;
use Application\Form\MarketForm;
use Company\Entity\Region;

class MarketController extends AbstractActionController
{
   
    /**
    * Менеджер сущностей.
    * @var \Doctrine\ORM\EntityManager
    */
    private $entityManager;
    
    /**
     * Менеджер.
     * @var \Application\Service\MarketManager 
     */
    private $marketManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $marketManager) 
    {
        $this->entityManager = $entityManager;
        $this->marketManager = $marketManager;
    }    
    
    public function indexAction()
    {
        $markets = $this->entityManager->getRepository(MarketPriceSetting::class)
                ->findAll();
        
        return new ViewModel([
            'markets' => $markets,
        ]);
    }
    
    public function contentAction()
    {
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        $limit = $this->params()->fromQuery('limit');
//        $status = $this->params()->fromQuery('status', MarketPriceSetting::STATUS_ACTIVE);
        
        $query = $this->entityManager->getRepository(MarketPriceSetting::class)
                        ->findAllMarket(['q' => $q, 'sort' => $sort, 'order' => $order]);

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
    
    public function editFormAction()
    {
        $marketId = (int)$this->params()->fromRoute('id', -1);
        
        $market = null;
        
        if ($marketId > 0){
            $market = $this->entityManager->getRepository(MarketPriceSetting::class)
                    ->find($marketId);
        }    

        $form = new MarketForm($this->entityManager);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();            
            if (!is_array($data['rates'])){
                $data['rates'] = [];
            }
            $form->setData($data);

            if ($form->isValid()) {
                $region = $this->entityManager->getRepository(Region::class)
                        ->find($data['region']);
                
                $data['region'] = $region; 
                if ($market){
                    $this->marketManager->updateMarketSetting($market, $data);
                } else {
                    $market = $this->marketManager->addMarketSetting($data);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
                //var_dump($form->getMessages());
            }
        } else {
            if ($market){
                $data = $market->toArray();
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'market' => $market,
        ]);        
    }    
    
    public function deleteAction()
    {
        $marketId = (int)$this->params()->fromRoute('id', -1);
        
        $market = null;
        
        if ($marketId > 0){
            $market = $this->entityManager->getRepository(MarketPriceSetting::class)
                    ->find($marketId);
        }    
        
        if ($market){
            $this->marketManager->removeMarketPriceSetting($market);
        }
        
        echo 'ok';
        exit;
    }    
    
    
    public function aplToZzapAction()
    {
        $this->marketManager->aplToZzap();

        return new JsonModel([
            'ok'
        ]);        
    }
    
}
