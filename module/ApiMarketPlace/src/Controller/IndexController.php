<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ApiMarketPlace\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use ApiMarketPlace\Entity\Marketplace;
use ApiMarketPlace\Form\MarketplaceSetting;
use Application\Entity\Goods;
use Application\Entity\MarketPriceSetting;


class IndexController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    /**
     * Request manager.
     * @var \ApiMarketPlace\Service\SberMarket
     */
    private $sbermarketManager;
        
    /**
     * Market place manager.
     * @var \ApiMarketPlace\Service\MarketplaceService
     */
    private $marketplaceService;
        
    /**
     * Ozon manager.
     * @var \ApiMarketPlace\Service\OzonService
     */
    private $ozonService;
        
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $sbermarketManager, $marketplaceService, $ozonService) 
    {
       $this->entityManager = $entityManager;
       $this->sbermarketManager = $sbermarketManager;
       $this->marketplaceService = $marketplaceService;
       $this->ozonService = $ozonService;
    }

    
    public function indexAction()
    {
        $marketplaces = $this->entityManager->getRepository(Marketplace::class)
                ->findAll();
        return new ViewModel([
            'marketplaces' =>  $marketplaces,
        ]);
    }

    public function editFormAction()
    {
        $marketplaceId = (int)$this->params()->fromRoute('id', -1);
        
        $marketplace = null;
        
        if ($marketplaceId > 0){
            $marketplace = $this->entityManager->getRepository(Marketplace::class)
                    ->find($marketplaceId);
        }    
        
        $form = new MarketplaceSetting($this->entityManager);
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();            
            $form->setData($data);

            if ($form->isValid()) {
                
                if ($marketplace){
                    $this->marketplaceService->update($marketplace, $data);
                } else {
                    $marketplace = $this->marketplaceService->add($data);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
                //var_dump($form->getMessages());
            }
        } else {
            if ($marketplace){
                $data = $marketplace->toArray();
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'marketplace' => $marketplace,
        ]);        
    }    
    
    public function sbermarketOrderNewAction()
    {
        $this->sbermarketManager->handle();
        //{"success":1,"meta":{"source":"merchant_name"}}
        return new JsonModel([
            'success' => 1,
            'meta' => [
                'source' => 'APL',
            ],
        ]);
    }
    
    public function sbermarketOrderCancelAction()
    {
        $this->sbermarketManager->handle();
        return new JsonModel([
            'success' => 1,
            'meta' => [
                'source' => 'APL',
            ],
        ]);        
    }

    public function yandexOrderAcceptAction()
    {
        $updId = $this->sbermarketManager->handle();
        return new JsonModel([
            'order' => [
                'accepted' => true,
                'id' => $updId,
                'reason' => '',
            ],
        ]);
    }
    
    public function ozonCategoryTreeAction()
    {
        $result = $this->ozonService->сategoryTree();
        return new JsonModel($result);
    }
    
    public function ozonUpdatePriceAction()
    {
        $goodId = (int)$this->params()->fromRoute('id', -1);
        
        $good = null;
        $resultPrice = []; $resultStock = [];
        if ($goodId > 0){
            $good = $this->entityManager->getRepository(Goods::class)
                    ->find($goodId);
        }    
        
        if ($good){
            $resultPrice = $this->ozonService->updateGoodPrice($good);
            $resultStock = $this->ozonService->updateGoodStock($good);
        }

        return new JsonModel([
            'price' => $resultPrice,
            'stock' => $resultStock,
        ]);
    }

    public function ozonUpdateMarketAction()
    {
        $marketId = (int)$this->params()->fromRoute('id', -1);
        
        $market = null;
        $result = [];
        if ($marketId > 0){
            $market = $this->entityManager->getRepository(MarketPriceSetting::class)
                    ->find($marketId);
        }    
        
        if ($market){
            $result = $this->ozonService->marketUpdate($market);
        }

        return new JsonModel($result);
    }
    
    public function downloadLogAction()
    {
        setlocale(LC_ALL,'ru_RU.UTF-8');
        $logName = $this->params()->fromQuery('log', 0);

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
        
        $file = realpath($this->ozonService->logFile($market, $logName));
        
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
