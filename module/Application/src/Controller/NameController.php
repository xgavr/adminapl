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
use Application\Entity\UnknownProducer;
use Application\Entity\Article;
use Application\Entity\Token;
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
        
    public function indexAction()
    {
        $bind = $this->entityManager->getRepository(Rawprice::class)
                ->count(['status' => Rawprice::STATUS_PARSED, 'statusOem' => Rawprice::OEM_PARSED]);
        $noBind = $this->entityManager->getRepository(Rawprice::class)
                ->count(['status' => Rawprice::STATUS_PARSED, 'statusOem' => Rawprice::OEM_NEW]);
        $total = $this->entityManager->getRepository(Token::class)
                ->count([]);
                
        return new ViewModel([
            'bind' => $bind,
            'noBind' => $noBind,
            'total' => $total,
        ]);  
    }
    
    public function wordsAction()
    {
        $text = 'Колодки тормозные перед LEXUS GX470/460/TOYOTA LAND CRUISER PRADO 120/150 02-';
        $article = new \Mystem\Article($text);
        foreach ($article->words as $word) {
            echo $word." ";
        }
        
        exit;
    }
    
    public function tokenArticleAction()
    {
        
    }
    
    public function contentAction()
    {
        ini_set('memory_limit', '512M');
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        
        $query = $this->entityManager->getRepository(OemRaw::class)
                        ->findAllOemRaw(['q' => $q]);

        $total = count($query->getResult(2));
        
        if ($offset) $query->setFirstResult( $offset );
        if ($limit) $query->setMaxResults( $limit );

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);          
    }    
    
    public function indexTokenAction()
    {
        $bind = $this->entityManager->getRepository(Rawprice::class)
                ->count(['status' => Rawprice::STATUS_PARSED, 'statusToken' => Rawprice::TOKEN_PARSED]);
        $noBind = $this->entityManager->getRepository(Rawprice::class)
                ->count(['status' => Rawprice::STATUS_PARSED, 'statusToken' => Rawprice::TOKEN_NEW]);
        $total = $this->entityManager->getRepository(Token::class)
                ->count([]);
                
        return new ViewModel([
            'bind' => $bind,
            'noBind' => $noBind,
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

        // Render the view template.
        return new ViewModel([
            'token' => $token,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
            'nameManager' => $this->nameManager,
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
    
    public function viewOnCodeAction() 
    {       
        $oemCode = $this->params()->fromQuery('code');

        if (!$oemCode) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        $filter = new \Application\Filter\ArticleCode();

        $oem = $this->entityManager->getRepository(OemRaw::class)
                ->findOneByCode($filter->filter($oemCode));
        
        if ($oem == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }   
        
        $this->redirect()->toUrl('/oem/view/'.$oem->getId());
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
    
    public function deleteEmptyAction()
    {
        $deleted = $this->oemManager->removeEmpty();
                
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => $deleted.' удалено!',
        ]);          
    }    
}
