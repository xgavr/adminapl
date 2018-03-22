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
     * @var Admin\Service\PostManager
     */
    private $postManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($postManager) 
    {
        $this->postManager = $postManager;        
    }   

    
    public function indexAction()
    {
        $box = [
            'host' => 'imap.yandex.ru',
            'user' => 'autoru@autopartslist.ru',
            'password' => 'kjdrf4',
        ];
        
        $this->postManager->read($box);
        
        return [];
    }
    
}
