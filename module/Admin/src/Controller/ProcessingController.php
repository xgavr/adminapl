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


class ProcessingController extends AbstractActionController
{
    
    /**
     * PostManager manager.
     * @var Admin\Service\PostManager
     */
    private $postManager;    
    
    /**
     * AutoruManager manager.
     * @var Admin\Service\AutoruManager
     */
    private $autoruManager;    
    
    /**
     * TelegramManager manager.
     * @var Admin\Service\TelegrammManager
     */
    private $telegramManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($postManager, $autoruManager, $telegramManager) 
    {
        $this->postManager = $postManager;        
        $this->autoruManager = $autoruManager;
        $this->telegramManager = $telegramManager;
    }   

    
    public function indexAction()
    {
        $this->autoruManager->postOrder();
        
        return [];
    }
    
    /*
     * Сообщения в телеграм
     * $post api_key, chat_id, text
     */
    public function telegramAction()
    {
        $data = [];
        if ($this->getRequest()->isPost()) {
            $data = json_decode($this->params()->fromPost());
            var_dump($data[0]);
            $this->aplService->sendTelegramMessage($data);
        }    
        
        return new JsonModel(
            $data
        );
    }
    
}
