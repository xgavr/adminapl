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


class AplController extends AbstractActionController
{
    
    /**
     * AplService manager.
     * @var Admin\Service\AplService
     */
    private $aplService;    
    
    /**
     * AplBankService manager.
     * @var Admin\Service\AplBankService
     */
    private $aplBankService;    

    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($aplService, $aplBankService) 
    {
        $this->aplService = $aplService;        
        $this->aplBankService = $aplBankService;        
    }   

    
    public function indexAction()
    {
        return [];
    }
    
    public function getSuppliersAction()
    {
        $this->aplService->getSuppliers();
        
        return new JsonModel([
            'ok'
        ]);
    }

    public function getStaffsAction()
    {
        $this->aplService->getStaffs();
        
        return new JsonModel([
            'ok'
        ]);
    }
    
    /*
     * Копирование прайсов с autopartslist.ru
     */
    public function aplMirrorAction()
    {
        
    }
    
    /*
     * Сообщения в телеграм
     * $post api_key, chat_id, text
     */
    public function telegramAction()
    {
        $data = [];
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $this->aplService->sendTelegramMessage($data);
        }    
        
        return new JsonModel(
            $data
        );
    }
    
    public function transBankAction()
    {
        $this->aplService->sendBankStatement();
        return new JsonModel([
            'ok'
        ]);
    }
}
