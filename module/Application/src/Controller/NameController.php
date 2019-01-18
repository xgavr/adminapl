<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\Rawprice;
use Application\Entity\Token;
use Application\Entity\TokenGroup;
use Zend\View\Model\JsonModel;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class NameController extends AbstractActionController
{
   
    /**
    * Менеджер сущностей.
    * @var Doctrine\ORM\EntityManager
    */
    private $entityManager;
    
    /**
     * Менеджер производителей.
     * @var Application\Service\ProducerManager 
     */
    private $producerManager;    
    
    /**
     * Менеджер артикулов производителей.
     * @var Application\Service\ArticleManager 
     */
    private $articleManager;    
    
    /**
     * Менеджер наименований товаров.
     * @var Application\Service\NameManager 
     */
    private $nameManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $producerManager, $articleManager, $nameManager) 
    {
        $this->entityManager = $entityManager;
        $this->producerManager = $producerManager;
        $this->articleManager = $articleManager;
        $this->nameManager = $nameManager;
    }    
        
    public function indexTokenAction()
    {
        $stages = $this->entityManager->getRepository(\Application\Entity\Article::class)
                ->findParseStageRawpriceCount(\Application\Entity\Raw::STAGE_TOKEN_PARSED);
        $total = $this->entityManager->getRepository(Token::class)
                ->count([]);
                
        return new ViewModel([
            'stages' => $stages,
            'total' => $total,
        ]);  
    }
    
    public function contentTokenAction()
    {
        ini_set('memory_limit', '512M');
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        
        $query = $this->entityManager->getRepository(Token::class)
                        ->findAllToken(['q' => $q]);

        $total = count($query->getResult(2));
        
        if ($offset) $query->setFirstResult( $offset );
        if ($limit) $query->setMaxResults( $limit );

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);          
    }    
        
    public function viewTokenAction() 
    {       
        $tokenId = (int)$this->params()->fromRoute('id', -1);
        $page = $this->params()->fromQuery('page', 1);

        if ($tokenId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $token = $this->entityManager->getRepository(Token::class)
                ->findOneById($tokenId);
        
        if ($token == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $prevQuery = $this->entityManager->getRepository(Token::class)
                        ->findAllToken(['prev1' => $token->getLemma()]);
        $nextQuery = $this->entityManager->getRepository(Token::class)
                        ->findAllToken(['next1' => $token->getLemma()]); 
        
        
        $rawpriceQuery = $this->entityManager->getRepository(Token::class)
                        ->findTokenRawprice($token);

        $adapter = new DoctrineAdapter(new ORMPaginator($rawpriceQuery, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);

        $totalRawpriceCount = $paginator->getTotalItemCount();
        

        // Render the view template.
        return new ViewModel([
            'token' => $token,
            'rawprices' => $paginator,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
            'nameManager' => $this->nameManager,
            'totalRawpriceCount' => $totalRawpriceCount,
        ]);
    }
    
    public function tokenFlagAction()
    {
        $tokenId = (int)$this->params()->fromRoute('id', -1);

        if ($tokenId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $token = $this->entityManager->getRepository(Token::class)
                ->findOneById($tokenId);
        
        if ($token == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $flag = $this->params()->fromQuery('flag', 1);
        
        $this->nameManager->updateTokenFlag($token, $flag);
        
        return new JsonModel([
            'ok',
        ]);          
    }

    public function viewAction() 
    {       
        $oemId = (int)$this->params()->fromRoute('id', -1);

        if ($oemId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $oem = $this->entityManager->getRepository(OemRaw::class)
                ->findOneById($oemId);
        
        if ($oem == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $rawpriceCountBySupplier = $this->entityManager->getRepository(OemRaw::class)
                ->rawpriceCountBySupplier($oem);
        
        $prevQuery = $this->entityManager->getRepository(OemRaw::class)
                        ->findAllOemRaw(['prev1' => $oem->getCode()]);
        $nextQuery = $this->entityManager->getRepository(OemRaw::class)
                        ->findAllOemRaw(['next1' => $oem->getCode()]);        

        // Render the view template.
        return new ViewModel([
            'oem' => $oem,
            'rawpriceCountBySupplier' => $rawpriceCountBySupplier,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
            'oemManager' => $this->oemManager,
        ]);
    }
    
    public function parseAction()
    {
        $rawpriceId = (int)$this->params()->fromRoute('id', -1);
        if ($rawpriceId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $rawprice = $this->entityManager->getRepository(\Application\Entity\Rawprice::class)
                ->findOneById($rawpriceId);
        
        if ($rawprice == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->nameManager->addNewTokenFromRawprice($rawprice);
        
        return new JsonModel([
            'ok',
        ]);          
    }
    
    public function updateTokenFromRawAction()
    {
        set_time_limit(0);
        $rawId = $this->params()->fromRoute('id', -1);

        if ($rawId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                ->findOneById($rawId);

        if ($raw == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->nameManager->grabTokenFromRaw($raw);
                
        return new JsonModel([
            'ok',
        ]);          
    }
    
    public function updateTokenGroupFromRawpriceAction()
    {
        $rawpriceId = $this->params()->fromRoute('id', -1);

        if ($rawpriceId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $rawprice = $this->entityManager->getRepository(Rawprice::class)
                ->findOneById($rawpriceId);

        if ($rawprice == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        if ($rawprice->getStatusGood() == Rawprice::GOOD_OK){
            $this->nameManager->addGroupTokenFromGood($rawprice->getGood());
        }    
                
        return new JsonModel([
            'ok',
        ]);          
    }
    
    public function updateTokenGroupFromRawAction()
    {
        set_time_limit(0);
        $rawId = $this->params()->fromRoute('id', -1);

        if ($rawId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                ->findOneById($rawId);

        if ($raw == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->nameManager->grabTokenGroupFromRaw($raw);
                
        return new JsonModel([
            'ok',
        ]);          
    }
    
    public function tokenGroupNameFormAction()
    {
        $tokenGroupId = $this->params()->fromRoute('id', -1);
        $name = $this->params()->fromQuery('prompt');
        
        $tokenGroup = $this->entityManager->getRepository(TokenGroup::class)
                ->findOneById($tokenGroupId);      
        
        if ($tokenGroup == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->nameManager->updateTokenGroupName($tokenGroup, $name);
        
        return new JsonModel([
            'ok',
        ]);          
    }
    
    public function deleteTokenGroupFormAction()
    {
        $tokenGroupId = $this->params()->fromRoute('id', -1);
        
        $tokenGroup = $this->entityManager->getRepository(TokenGroup::class)
                ->findOneById($tokenGroupId);      
        
        if ($tokenGroup == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->nameManager->removeTokenGroup($tokenGroup);
        
        return new JsonModel(
           ['ok']
        );           
    }    

    
    public function deleteEmptyAction()
    {
        $deleted = $this->nameManager->removeEmptyToken();
                
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => $deleted.' удалено!',
        ]);          
    }    

    public function tokenGroupAction()
    {
        $total = $this->entityManager->getRepository(TokenGroup::class)
                ->count([]);
                
        return new ViewModel([
            'total' => $total,
        ]);  
    }
    
    public function tokenGroupContentAction()
    {
//        ini_set('memory_limit', '512M');
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        
        $query = $this->entityManager->getRepository(TokenGroup::class)
                        ->findAllTokenGroup(['q' => $q, 'sort' => $sort, 'order' => $order]);

        $total = count($query->getResult(2));
        
        if ($offset) $query->setFirstResult( $offset );
        if ($limit) $query->setMaxResults( $limit );

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);          
    }    
    
    public function viewTokenGroupAction() 
    {       
        $tokenGroupId = (int)$this->params()->fromRoute('id', -1);
        $page = $this->params()->fromQuery('page', 1);

        if ($tokenGroupId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $tokenGroup = $this->entityManager->getRepository(TokenGroup::class)
                ->findOneById($tokenGroupId);
        
        if ($tokenGroup == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $prevQuery = $this->entityManager->getRepository(TokenGroup::class)
                        ->findAllTokenGroup(['prev1' => $tokenGroup->getIds()]);
        $nextQuery = $this->entityManager->getRepository(TokenGroup::class)
                        ->findAllTokenGroup(['next1' => $tokenGroup->getIds()]); 
        
        
        $goodsQuery = $this->entityManager->getRepository(TokenGroup::class)
                        ->findTokenGroupGoods($tokenGroup);

        $adapter = new DoctrineAdapter(new ORMPaginator($goodsQuery, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);

        $totalGoodsCount = $paginator->getTotalItemCount();
        

        // Render the view template.
        return new ViewModel([
            'tokenGroup' => $tokenGroup,
            'goods' => $paginator,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
            'nameManager' => $this->nameManager,
            'totalGoodsCount' => $totalGoodsCount,
        ]);
    }    
    
    public function goodsTokenGroupAction()
    {
        $goodId = (int)$this->params()->fromRoute('id', -1);
        if ($goodId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $good = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->findOneById($goodId);
        
        if ($good == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->nameManager->addGroupTokenFromGood($good);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }
    
    
    public function goodCountTokenGroupAction()
    {
        $this->nameManager->updateAllTokenGroupGoodCount();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }
    
    
    
    public function deleteEmptyTokenGroupAction()
    {
        $deleted = $this->nameManager->removeEmptyTokenGroup();
                
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => $deleted.' удалено!',
        ]);          
    }    
    
}
