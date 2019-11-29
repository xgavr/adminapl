<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;


class PostController extends AbstractActionController
{
    
    /**
     * AplService manager.
     * @var \Admin\Service\PostManager
     */
    private $postManager;    
    
    /**
     * AplService manager.
     * @var \Admin\Service\AutoruManager
     */
    private $autoruManager;    
    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($postManager, $autoruManager) 
    {
        $this->postManager = $postManager;        
        $this->autoruManager = $autoruManager;
    }   

    
    public function indexAction()
    {
        
        $this->autoruManager->postOrder();
        return [];
    }
    
}
