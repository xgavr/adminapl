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
        return new ViewModel();
    }
    
    public function editFormAction()
    {
        $marketId = (int)$this->params()->fromRoute('id', -1);
        
        $market = null;
        
        if ($marketId > 0){
            $market = $this->entityManager->getRepository(MarketPriceSetting::class)
                    ->find($marketId);
        }    

        $form = new MarketForm();

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();            
            $form->setData($data);

            if ($form->isValid()) {
                $market = $this->marketManager->addMarketSetting($data);
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
                var_dump($form->getMessages());
            }
        } else {
            if ($market){
                $data = [
                ];
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
    
    
    public function aplToZzapAction()
    {
        $this->marketManager->aplToZzap();

        return new JsonModel([
            'ok'
        ]);        
    }
    
}
