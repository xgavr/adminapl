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
        
        return new ViewModel([
            'files' => $files,
        ]);
    }
    
    public function checkMailAction()
    {
        $this->imageManager->getImageByMail();
        
        return new JsonModel([
            'ok',
        ]);
    }
}
