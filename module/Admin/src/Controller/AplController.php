<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AplController extends AbstractActionController
{
    
    /**
     * AplService manager.
     * @var Admin\Service\AplService
     */
    private $aplService;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($aplService) 
    {
        $this->aplService = $aplService;        
    }   

    
    public function indexAction()
    {
        return [];
    }
    
    public function getStaffsAction()
    {
        $this->aplService->getStaffs();
        exit;
    }
}
