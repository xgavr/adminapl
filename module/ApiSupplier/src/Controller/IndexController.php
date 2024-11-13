<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ApiSupplier\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Entity\SupplierApiSetting;


class IndexController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    /**
     * Request manager.
     * @var \ApiSupplier\Service\MskManager
     */
    private $mskManager;
        
    /**
     * Mikado manager.
     * @var \ApiSupplier\Service\MikadoManager
     */
    private $mikadoManager;
        
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $mskManager, $mikadoManager) 
    {
       $this->entityManager = $entityManager;
       $this->mskManager = $mskManager;
       $this->mikadoManager = $mikadoManager;
    }

    
    public function indexAction()
    {
        return new ViewModel([
        ]);
    }
    
    public function mskLoginAction()
    {
        $content = $this->mskManager->curlLogin();
        
        //$this->layout()->setTemplate('layout/terminal');
        return new ViewModel([
            'content' => iconv('windows-1251', 'utf-8', $content),
        ]);
    }    
    
    public function testAction()
    {
        $api = $this->params()->fromQuery('api');
        
        $content = 'Привет мир!';
        
        if ($api == SupplierApiSetting::NAME_API_MIKADO){
            $content = $this->mikadoManager->deliveriesToPtu();
        }    
        
        $this->layout()->setTemplate('layout/terminal');
        return new JsonModel([
            'content' => $content,
        ]);
    }    
}
