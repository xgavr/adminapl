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
use Application\Entity\Images;
use Application\Form\UploadForm;

class ImageController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер картинок.
     * @var \Application\Service\ImageManager
     */
    private $imageManager;
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $imageManager) 
    {
        $this->entityManager = $entityManager;
        $this->imageManager = $imageManager;
    }    
    
    public function indexAction()
    {
        $files = $this->entityManager->getRepository(Images::class)
                ->getTmpImages();
        shuffle($files);
        
        return new ViewModel([
            'files' => array_slice($files, 0, 100),
            'imageManager' => $this->imageManager,
        ]);
    }
    
    public function checkMailAction()
    {
        $this->imageManager->getImageByMail();
        
        return new JsonModel([
            'ok',
        ]);
    }

    public function uploadTmpAction()
    {
        $this->imageManager->getImageByMail();
        
        return new JsonModel([
            'ok',
        ]);
    }

    public function uploadTmpFilesAction()
    {
        $this->entityManager->getRepository(Images::class)
                ->uploadImageFromTmpFolder(Images::STATUS_SUP);
        
        return new JsonModel([
            'ok',
        ]);
    }
    
    public function uploadTmpFileAction()
    {
        $filename = $this->params()->fromQuery('file');
        $goodId = $this->params()->fromQuery('good');

        if (file_exists($filename)){
            $good = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                    ->find($goodId);
            if ($good){
                $this->entityManager->getRepository(Images::class)
                        ->addImageToGood($filename, $good, Images::STATUS_SUP);
            } else {
                $this->entityManager->getRepository(Images::class)
                        ->findGoodByImageFileName($filename, Images::STATUS_SUP);
            }    
        }
        
        return new JsonModel([
            'ok',
        ]);
    }
    
    public function decompressTmpFileAction()
    {
        $filename = $this->params()->fromQuery('file');

        if (file_exists($filename)){
            $this->imageManager->decompress($filename);
        }
        
        return new JsonModel([
            'ok',
        ]);
    }
    
    public function convertToJpgAction()
    {
        $filename = $this->params()->fromQuery('file');

        if (file_exists($filename)){
            $this->imageManager->tiff2jpg($filename);
        }
        
        return new JsonModel([
            'ok',
        ]);
    }
    
    public function uploadTmpImageFormAction()
    {

        $imageFolder = $this->entityManager->getRepository(Images::class)
                ->getTmpImageFolder();
        
        $form = new UploadForm($imageFolder);

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
    
    
    public function deleteTmpFileAction()
    {
        $filename = $this->params()->fromQuery('file');

        if (file_exists($filename)){
            unlink($filename);
        }
        
        return new JsonModel([
            'ok',
        ]);
    }
    
}
