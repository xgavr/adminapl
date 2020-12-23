<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;


class PostController extends AbstractActionController
{
    
    /**
     * AplService manager.
     * @var \Admin\Service\PostManager
     */
    private $postManager;    
    
    /**
     * AutoruService manager.
     * @var \Admin\Service\AutoruManager
     */
    private $autoruManager;    
    
    /**
     * HelloService manager.
     * @var \Admin\Service\HelloManager
     */
    private $helloManager;    
    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($postManager, $autoruManager, $helloManager) 
    {
        $this->postManager = $postManager;        
        $this->autoruManager = $autoruManager;
        $this->helloManager = $helloManager;
    }   

    
    public function indexAction()
    {
        
        return [];
    }
    
    public function autoruAction()
    {
        $this->autoruManager->postOrder();
        return new JsonModel([
            'ok'
        ]);
    }    
    
    public function helloAction()
    {
        $this->helloManager->checkingMail();
        
        return new JsonModel([
            'ok'
        ]);
    }    
    
}
