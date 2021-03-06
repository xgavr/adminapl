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

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Laminas\Paginator\Paginator;

class MlController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер ml.
     * @var \Application\Service\MlManager 
     */
    private $mlManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $mlManager) 
    {
        $this->entityManager = $entityManager;
        $this->mlManager = $mlManager;
    }    
    
    public function indexAction()
    {
        //$trains = $this->mlManager->trainPrimaryScale();
        
        return new ViewModel([
//            'testTreshold' => $testTreshold,
//            'predictRate' => $predictRate,
            //'trains' => $trains,
            'mlManager' => $this->mlManager,
        ]);
    }
    
    public function primaryScaleAction()
    {
        $trains = $this->mlManager->trainPrimaryScale();
        
        return new ViewModel([
            'trains' => $trains,
            'mlManager' => $this->mlManager,
        ]);
    }

    public function trainPrimaryScaleAction()
    {
        $this->mlManager->trainPrimaryScale();
        
        return new JsonModel([
            'ok'
        ]);          
    }
    
    public function matchingRawpriceTrainAction()
    {
        $this->mlManager->matchingRawpriceTrain();
        
        return new JsonModel([
            'ok'
        ]);  
    }
    
    public function nameMatrixAction()
    {
        $this->mlManager->featureNameMatrix();
        
        return new JsonModel([
            'result' => 'ok',
        ]);  
    }
    
    public function clusterNameAction()
    {
        $this->mlManager->clusterName();
        
        return new JsonModel([
            'result' => 'ok',
        ]);  
    }
    
    public function fillMlTitlesAction()
    {
        $this->entityManager->getRepository(\Application\Entity\Rawprice::class)
                ->fillMlTitles();
        
        return new JsonModel([
            'result' => 'ok',
        ]);  
    }
    
    public function mlTitlesAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        
        $query = $this->entityManager->getRepository(\Application\Entity\Token::class)
                    ->findMlTitles();
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(3);        
        $paginator->setCurrentPageNumber($page);

        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'mlTitles' => $paginator,
            'mlManager' => $this->mlManager,            
        ]);  
        
    }
            
    public function mlRawpricesAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        
        $query = $this->entityManager->getRepository(\Application\Entity\Token::class)
                    ->findMlTitles();
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(3);        
        $paginator->setCurrentPageNumber($page);

        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'mlTitles' => $paginator,
            'mlManager' => $this->mlManager,            
        ]);  
        
    }
    
    public function mlTitlesToCsvAction()
    {
        $this->mlManager->mlTitlesToCsv();
        
        return new JsonModel([
            'result' => 'ok',
        ]);          
    }

    public function updateMlTitleStatusAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $mlTitleId = $data['pk'];
            $status = $data['value'];

            $mlTitle = $this->entityManager->getRepository(\Application\Entity\MlTitle::class)
                    ->findOneById($mlTitleId);
            
            if ($mlTitle) {
                $this->mlManager->updateMlTitleStatus($mlTitle, $status);
            }        
        }
        exit;
    }    

    public function mlTitlePredictAction()
    {
        $this->mlManager->mlTitlePredict();

        return new JsonModel([
            'result' => 'ok',
        ]);          
    }
    
    public function tokenGroupsToCsvAction()
    {
        $this->mlManager->tokenGroupsToCsv();
        
        return new JsonModel([
            'result' => 'ok',
        ]);          
    }
    
    public function clusterTokenGroupAction()
    {
        $this->mlManager->clusteringTokenGroup();

        return new JsonModel([
            'result' => 'ok',
        ]);          
    }
    
    public function postLogAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        
        $query = $this->entityManager->getRepository(\Admin\Entity\PostLog::class)
                    ->findLogs();
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);

        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'logs' => $paginator,
            'mlManager' => $this->mlManager, 
            'emailRepository' => $this->entityManager->getRepository(\Application\Entity\Email::class),
        ]);  
        
    }                
    
}
