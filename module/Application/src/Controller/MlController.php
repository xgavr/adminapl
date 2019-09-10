<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

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
        
        return new ViewModel();
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
        $paginator->setDefaultItemCountPerPage(5);        
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
    
}
