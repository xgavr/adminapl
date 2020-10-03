<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Admin\Entity\Log;
use Admin\Entity\Setting;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Laminas\Paginator\Paginator;

class LogController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Log manager.
     * @var \Admin\Service\LogManager
     */
    private $logManager;    
    
    /**
     * Setting manager.
     * @var \Admin\Service\SettingManager
     */
    private $settingManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $logManager, $settingManager) 
    {
        $this->entityManager = $entityManager;
        $this->logManager = $logManager;        
        $this->settingManager = $settingManager;        
    }   
    
    public function indexAction()
    {
        $ident = $this->params()->fromQuery('ident');
        $id = $this->params()->fromQuery('id');
                
        $query = $this->entityManager->getRepository(Log::class)
                ->queryByDocType($ident, ['id' => $id]);
        
        $page = $this->params()->fromQuery('page', 1);
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(20);        
        $paginator->setCurrentPageNumber($page);        
        
        return [
            'rows' => $paginator,
            'entityManager' => $this->entityManager,
            'ident' => $ident,
            'id' => $id,
        ];
    } 
    
    public function settingAction()
    {
        // Визуализируем шаблон представления.
        return new ViewModel([
         ]);          
    }

    public function settingContentAction()
    {
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $status = $this->params()->fromQuery('status');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        
        $query = $this->entityManager->getRepository(Setting::class)
                        ->findSettings([
                            'status' => $status, 
                            'sort' => $sort,
                            'order' => $order,
                            ]);

        $total = count($query->getResult(2));
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);                  
    }
    
    public function editSettingNameAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $settingId = $data['pk'];
            $setting = $this->entityManager->getRepository(Setting::class)
                    ->findOneById($settingId);
                    
            if ($setting){
                $this->settingManager->editProcessName($setting, $data['value']);
            }    
        }
        
        exit;
    }
    
    public function editSettingStatusAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $settingId = $data['pk'];
            $setting = $this->entityManager->getRepository(Setting::class)
                    ->findOneById($settingId);
                    
            if ($setting){
                $this->settingManager->editProcessStatus($setting, $data['value']);
            }    
        }
        
        exit;
    }
    
    public function settingErrorTextAction()
    {
        $settingId = $this->params()->fromRoute('id', -1);
            
        if ($settingId > 0){
            $setting = $this->entityManager->getRepository(Setting::class)
                    ->findOneById($settingId);
        }    
        
        $errorText = '';
        if ($setting){
            $errorText = $setting->getErrorText();
        }    

        $text = $errorText;
//        return new JsonModel(
//           ['text' => $text]
//        );           
        $this->layout()->setTemplate('layout/terminal');
        return new ViewModel([
            'text' => $text,
        ]);
    }
    
}
