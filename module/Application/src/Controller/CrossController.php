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
use Application\Entity\Cross;
use Application\Entity\CrossList;


class CrossController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер.
     * @var \Application\Service\SupplierManager 
     */
    private $supplierManager;    
    
    /**
     * Менеджер.
     * @var \Application\Service\CrossManager 
     */
    private $crossManager;    
    
    /**
     * Менеджер.
     * @var \Application\Service\ParseManager 
     */
    private $parseManager;  
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $supplierManager, $crossManager, $parseManager) 
    {
        $this->entityManager = $entityManager;
        $this->supplierManager = $supplierManager;
        $this->crossManager = $crossManager;
        $this->parseManager = $parseManager;
    }    
    
    public function indexAction()
    {

        $files = $this->entityManager->getRepository(Cross::class)
                ->getTmpFiles();
        
        return new ViewModel([
            'files' => $files,
            'crossManager' => $this->crossManager,
        ]);  
    }
    
    public function checkMailAction()
    {
        $this->crossManager->getCrossByMail();
        
        return new JsonModel([
            'ok',
        ]);
    }

    public function uploadTmpCrossFormAction()
    {

        $crossFolder = $this->entityManager->getRepository(Cross::class)
                ->getTmpCrossFolder();
        
        $form = new \Application\Form\UploadForm($crossFolder);

        if($this->getRequest()->isPost()) {
            
            $data = array_merge_recursive(
                $this->params()->fromPost(),
                $this->params()->fromFiles()
            );            
            //var_dump($data); exit;

            // Заполняем форму данными.
            $form->setData($data);
            if($form->isValid()) {
                                
                // Получаем валадированные данные формы.
                $data = $form->getData();
                //$this->imageManager->decompress($data['name']['tmp_name']);
              
                return new JsonModel(
                   ['ok']
                );           
            }
            
        }
        
        $this->layout()->setTemplate('layout/terminal');
        
        return new ViewModel([
            'form' => $form,
        ]);
        
    }    
    
    public function decompressTmpFileAction()
    {
        $filename = $this->params()->fromQuery('file');

        if (file_exists($filename)){
            $this->crossManager->decompress($filename);
        }
        
        return new JsonModel([
            'ok',
        ]);
    }
    
    public function uploadTmpFileAction()
    {
        $filename = rawurldecode($this->params()->fromQuery('file'));

        if (file_exists($filename)){
            $this->crossManager->uploadCross($filename);
        }
        
        return new JsonModel([
            'ok',
        ]);
    }
        
    public function deleteTmpFileAction()
    {
        $filename = rawurldecode($this->params()->fromQuery('file'));

        if (file_exists($filename)){
            unlink($filename);
        }
        
        return new JsonModel([
            'ok',
        ]);
    }    
    
    public function contentAction()
    {
        
        $status = $this->params()->fromQuery('status');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        
        $query = $this->entityManager->getRepository(Cross::class)
                    ->findAllCross($status);            
        
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


    public function viewAction() 
    {       
        $crossId = (int)$this->params()->fromRoute('id', -1);

        if ($crossId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $cross = $this->entityManager->getRepository(Cross::class)
                ->findOneById($crossId);
        
        if ($cross == null) {
            return $this->redirect()->toRoute('cross');
        }        
        // Render the view template.
        return new ViewModel([
            'cross' => $cross,
            'crossManager' => $this->crossManager,
        ]);
    }      
    
    public function listContentAction()
    {
        $crossId = (int)$this->params()->fromRoute('id', -1);

        if ($crossId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $cross = $this->entityManager->getRepository(Cross::class)
                ->findOneById($crossId);
        
        if ($cross == null) {
            return $this->redirect()->toRoute('cross');
        }        
        
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        
        $query = $this->entityManager->getRepository(CrossList::class)
                    ->crossList($cross);            
        
        $total = $cross->getRowCount();
        
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
        
    public function deleteAction()
    {
        $crossId = $this->params()->fromRoute('id', -1);
        
        $cross = $this->entityManager->getRepository(Cross::class)
                ->findOneById($crossId);        
        if ($cross == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->crossManager->removeCross($cross);
        
        // Перенаправляем пользователя на страницу "raw".
        return $this->redirect()->toRoute('cross', []);
    }    
    
    public function deleteFormAction()
    {
        $crossId = $this->params()->fromRoute('id', -1);
        
        $cross = $this->entityManager->getRepository(Cross::class)
                ->findOneById($crossId);
        
        if ($cross == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->crossManager->removeCross($cross);
        
        return new JsonModel(
           ['ok']
        );           
    }    

    public function deleteLineAction()
    {
        $lineId = $this->params()->fromRoute('id', -1);
        
        $line = $this->entityManager->getRepository(CrossList::class)
                ->findOneById($lineId);        
        
        if ($line == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->crossManager->removeLine($line);
        
        return new JsonModel(
           ['ok']
        );           
    }    

    public function exploreAction()
    {
        $crossId = $this->params()->fromRoute('id', -1);
        
        $cross = $this->entityManager->getRepository(Cross::class)
                ->findOneById($crossId);
        
        if ($cross == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->crossManager->exploreCross($cross);
        
        return new JsonModel(
           ['ok']
        );           
        
    }
    
    public function exploreLineAction()
    {
        $lineId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($lineId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $line = $this->entityManager->getRepository(CrossList::class)
                ->findOneById($lineId);
        
        $description = $this->crossManager->exploreLine($line);
        
        $message = '<ul>';
        if (is_array($description)){
            if (isset($description['producerArticle'])){
                $message .= '<li>Артикул: ';                
                $message .= $description['producerArticle'];                
            }                
            if (isset($description['producerArticleName'])){
                $message .= '<li>Производитель: ';                
                $message .= $description['producerArticleName'];                
            }                
            if (isset($description['brandArticle'])){
                $message .= '<li>ОЕ артикул: ';                
                $message .= $description['brandArticle'];                
            }                
            if (isset($description['brandArticleName'])){
                $message .= '<li>ОЕ производитель: ';                
                $message .= $description['brandArticleName'];                
            }                
            if (isset($description['brandName'])){
                $message .= '<li>ОЕ brandName: ';                
                $message .= $description['brandName'];                
            }                
            if (isset($description['articleBy'])){
                $message .= '<li>articleBy: ';                
                $message .= $description['articleBy'];                
            }                
        }
        $message .= '</ul>';
        
        echo $message;
        exit;
    }
    
    public function parseAction()
    {
        $crossId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($crossId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $cross = $this->entityManager->getRepository(Cross::class)
                ->findOneById($crossId);
        
        $this->crossManager->parseCross($cross);
        
        return new JsonModel(
           ['ok']
        );                   
    }        

    public function bindAction()
    {
        $crossId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($crossId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $cross = $this->entityManager->getRepository(Cross::class)
                ->findOneById($crossId);
        
        $this->crossManager->bindCross($cross);
        
        return new JsonModel(
           ['ok']
        );                   
    }        

    public function resetAction()
    {
        $crossId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($crossId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $cross = $this->entityManager->getRepository(Cross::class)
                ->findOneById($crossId);
        
        $this->crossManager->resetCross($cross);
        
        return new JsonModel(
           ['ok']
        );                   
    }        
}
