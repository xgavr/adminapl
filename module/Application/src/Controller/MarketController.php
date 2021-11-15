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
use Application\Entity\Rate;
use Application\Entity\Supplier;
use Application\Entity\Shipping;
use Company\Entity\Office;

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
        
        $allRates = $this->entityManager->getRepository(Rate::class)
                ->findBy(['status' => Rate::STATUS_ACTIVE], ['id' => 'ASC']);
        //$rateList = ['0' => '--все--'];
        foreach ($allRates as $rate) {
            $rateList[$rate->getId()] = $rate->getName();
        }

        $suppliers = $this->entityManager->getRepository(Supplier::class)
                ->findBy(['status' => Supplier::STATUS_ACTIVE], ['name' => 'ASC']);
        $supplierList = ['--все--'];
        foreach ($suppliers as $supplier) {
            $supplierList[$supplier->getId()] = $supplier->getName();
        }
        
        $form = new MarketForm($this->entityManager);
        
        $form->get('rates')->setValueOptions($rateList);
        $form->get('supplier')->setValueOptions($supplierList);
        if ($market){
            $form->get('shipping')->setValueOptions($this->marketManager->regionShipping($market->getRegion()));            
        } else {
            $defaultOffice = $this->entityManager->getRepository(Office::class)
                    ->findDefaultOffice();
            $form->get('shipping')->setValueOptions($this->marketManager->regionShipping($defaultOffice->getRegion()));            
        }

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();            
            if (empty($data['rates'])){
                $data['rates'] = [];
            }
                        
            $region = $this->entityManager->getRepository(Region::class)
                    ->find($data['region']);                
            $supplier = $this->entityManager->getRepository(Supplier::class)
                    ->find($data['supplier']);                
            $shipping = $this->entityManager->getRepository(Shipping::class)
                    ->find($data['shipping']);
            if ($region){
                $form->get('shipping')->setValueOptions($this->marketManager->regionShipping($region));                            
            }

            $form->setData($data);

            if ($form->isValid()) {
                $data['region'] = $region; 
                $data['supplier'] = $supplier; 
                $data['shipping'] = $shipping; 
                
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
    
    public function regionShippingAction()
    {
        $regionId = (int)$this->params()->fromRoute('id', -1);
        if ($regionId<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $region = $this->entityManager->getRepository(Region::class)
                ->find($regionId);
        
        if ($region == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $shippings = $this->marketManager->regionShipping($region);
        foreach ($shippings as $key=>$value){
            $result[$key] = [
                'id' => $key,
                'name' => $value,                
            ];
        }
        
        return new JsonModel([
            'rows' => $result,
        ]);                                  
    }
    
    public function unloadMarketAction()
    {
        $marketId = (int)$this->params()->fromRoute('id', -1);
        if ($marketId<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $market = $this->entityManager->getRepository(MarketPriceSetting::class)
                ->find($marketId);
        
        if ($market == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $this->marketManager->unload($market);

        return new JsonModel([
            'ok'
        ]);        
    }
    
    public function aplToZzapAction()
    {
        $this->marketManager->aplToZzap();

        return new JsonModel([
            'ok'
        ]);        
    }
 
    public function downloadPriceAction()
    {
        setlocale(LC_ALL,'ru_RU.UTF-8');
        $zip = $this->params()->fromQuery('zip', 0);

        $marketId = (int)$this->params()->fromRoute('id', -1);
        if ($marketId<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $market = $this->entityManager->getRepository(MarketPriceSetting::class)
                ->find($marketId);
        
        if ($market == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $file = realpath($this->marketManager->filenamePath($market, $zip));
        
        if (file_exists($file)){
            if (ob_get_level()) {
              ob_end_clean();
            }
            // заставляем браузер показать окно сохранения файла
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            // читаем файл и отправляем его пользователю
            readfile($file);
        }
        exit;          
    }      
}
