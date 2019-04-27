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
use Application\Entity\Images;

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
        
        $tmpDir = $this->entityManager->getRepository(Images::class)
                ->getTmpImageFolder();
        
        return new ViewModel([
            'files' => $files,
            'tmpDir' => Images::publicPath($tmpDir),
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

        $tmpDir = $this->entityManager->getRepository(Images::class)
                ->getTmpImageFolder();
        
        if (file_exists($tmpDir.'/'.$filename)){
            $this->entityManager->getRepository(Images::class)
                    ->findGoodByImageFileName($tmpDir.'/'.$filename, Images::STATUS_SUP);
        }
        
        return new JsonModel([
            'ok',
        ]);
    }
    
    public function deleteTmpFileAction()
    {
        $filename = $this->params()->fromQuery('file');

        $tmpDir = $this->entityManager->getRepository(Images::class)
                ->getTmpImageFolder();
        
        if (file_exists($tmpDir.'/'.$filename)){
            unlink($tmpDir.'/'.$filename);
        }
        
        return new JsonModel([
            'ok',
        ]);
    }
    
}
