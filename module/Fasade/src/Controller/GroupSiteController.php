<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Fasade\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Fasade\Entity\GroupSite;
use Fasade\Form\GroupSiteForm;
use Application\Entity\Goods;


class GroupSiteController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер групп сайта.
     * @var \Fasade\Service\GroupSiteManager
     */
    private $grouSiteManager;
    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $groupSiteManager) 
    {
        $this->entityManager = $entityManager;
        $this->grouSiteManager = $groupSiteManager;
    }    
    
    public function indexAction()
    {
        return new ViewModel([
        ]);  
    }
    
    public function contentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $hasChild = $this->params()->fromQuery('hasChild');
        $offset = $this->params()->fromQuery('offset');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        $limit = $this->params()->fromQuery('limit');
        $status = $this->params()->fromQuery('status', GroupSite::STATUS_ACTIVE);
        
        $query = $this->entityManager->getRepository(GroupSite::class)
                        ->queryAllGroupSite(['q' => $q, 'hasChild' => $hasChild, 'sort' => $sort, 
                            'order' => $order, 'status' => $status]);

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
    
    public function editFormAction()
    {
        $groupSiteId = (int)$this->params()->fromRoute('id', -1);
        $parentId = (int)$this->params()->fromQuery('parent', -1);
        
        $groupSite = $parentGroupSite = null;

        if ($groupSiteId > 0){
            $groupSite = $this->entityManager->getRepository(GroupSite::class)
                    ->find($groupSiteId);
        }    
        
        if ($parentId > 0){
            $parentGroupSite = $this->entityManager->getRepository(GroupSite::class)
                    ->find($parentId);
        }    
        
        $form = new GroupSiteForm($this->entityManager);
        
        $siteGroupList = [0 => 'первый уровень'];
        $siteGroups = $this->entityManager->getRepository(GroupSite::class)
                ->findBy(['status' => GroupSite::STATUS_ACTIVE]);
        foreach ($siteGroups as $siteGroup){
            $siteGroupList[$siteGroup->getId()] = $siteGroup->getName();
        }

        $form->get('groupSite')->setValueOptions($siteGroupList);
        
        if ($parentGroupSite){
            $form->get('groupSite')->setValue($parentGroupSite->getId());
        }
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();   

            $form->setData($data);
            
            if ($form->isValid()) {

                        
                if ($groupSite){
                    $this->grouSiteManager->updateGroupSite($groupSite, $data);
                } else {
                    $this->grouSiteManager->addGroupSite($data);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($groupSite){
                $form->setData($groupSite->toArray());
            }    
        }
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'groupSite' => $groupSite,
        ]);        
    }        
    
    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id');
        
        $groupSite = $this->entityManager->getRepository(GroupSite::class)
                ->find($id);
        if ($groupSite){
            $this->grouSiteManager->removeGroupSite($groupSite);
        }
        
        return new JsonModel(
           ['ok']
        );           
    }
    
    public function deleteGoodFromCategoryAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $goodId = $this->params()->fromQuery('good', -1);
        
        $groupSite = $this->entityManager->getRepository(GroupSite::class)
                ->find($id);

        $good = $this->entityManager->getRepository(Goods::class)
                ->find($goodId);
        
        if ($good && $groupSite){
            $groupSite->removeGoodAssociation($good);
        }
        
        return new JsonModel(
           ['ok']
        );           
    }
    
    public function includeGoodToCategoryAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $goodId = $this->params()->fromQuery('good', -1);
        
        $groupSite = $this->entityManager->getRepository(GroupSite::class)
                ->find($id);

        $good = $this->entityManager->getRepository(Goods::class)
                ->find($goodId);
        
        if ($good && $groupSite){
            $this->entityManager->getRepository(Goods::class)
                    ->addGoodCategory($good, $groupSite);
        }
        
        return new JsonModel(
           ['ok']
        );                   
    }
}
