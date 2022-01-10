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
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $mskManager) 
    {
       $this->entityManager = $entityManager;
       $this->mskManager = $mskManager;
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
            'content' => iconv('cp-1251', 'utf-8', $content),
        ]);
    }    
}
