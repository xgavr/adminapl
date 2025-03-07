<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Application\Entity\Rawprice;
use Application\Entity\Token;
use Application\Entity\Bigram;
use Application\Entity\TokenGroup;
use Application\Entity\GenericGroup;
use Laminas\View\Model\JsonModel;
use Application\Entity\Goods;
use Application\Entity\Rate;
use Application\Entity\FpTree;
use Application\Entity\FpGroup;
use Application\Entity\TitleToken;
use Application\Entity\TitleBigram;
use Application\Entity\TokenGroupToken;
use Application\Entity\TokenGroupBigram;
use Fasade\Entity\GroupSite;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Laminas\Paginator\Paginator;

class NameController extends AbstractActionController
{
   
    /**
    * Менеджер сущностей.
    * @var \Doctrine\ORM\EntityManager
    */
    private $entityManager;
    
    /**
     * Менеджер производителей.
     * @var \Application\Service\ProducerManager 
     */
    private $producerManager;    
    
    /**
     * Менеджер артикулов производителей.
     * @var \Application\Service\ArticleManager 
     */
    private $articleManager;    
    
    /**
     * Менеджер наименований товаров.
     * @var \Application\Service\NameManager 
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
        $statusTokenCount = $this->entityManager->getRepository(Token::class)
                ->statusTokenCount();
                
        return new ViewModel([
            'stages' => $stages,
            'statuses' => Token::getStatusList(),
            'flags' => Token::getFlagList(),
            'total' => $total,
            'statusTokenCount' => $statusTokenCount,
        ]);  
    }
    
    public function contentTokenAction()
    {
        ini_set('memory_limit', '512M');
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        $limit = $this->params()->fromQuery('limit');
        $status = $this->params()->fromQuery('status', Token::IS_DICT);
        $flag = $this->params()->fromQuery('flag', Token::WHITE_LIST);
        $isCorrect = $this->params()->fromQuery('isCorrect');
        
        $query = $this->entityManager->getRepository(Token::class)
                        ->findAllToken([
                            'q' => $q, 
                            'sort' => $sort, 
                            'order' => $order, 
                            'status' => $status,
                            'flag' => $flag,
                            'isCorrect' => $isCorrect,
                                ]);
        
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
        $lemma = $this->params()->fromQuery('lemma');
        $page = $this->params()->fromQuery('page', 1);

        if ($tokenId>0) {
            $token = $this->entityManager->getRepository(Token::class)
                    ->findOneById($tokenId);        
        } elseif ($lemma){
            $token = $this->entityManager->getRepository(Token::class)
                    ->findOneByLemma($lemma);                        
        } else {
            $this->getResponse()->setStatusCode(404);
            return;            
        }
        
        if ($token == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $prevQuery = $this->entityManager->getRepository(Token::class)
                        ->findAllToken(['prev1' => $token->getLemma()]);
        $nextQuery = $this->entityManager->getRepository(Token::class)
                        ->findAllToken(['next1' => $token->getLemma()]); 
        
        
        $articleQuery = $this->entityManager->getRepository(Token::class)
                        ->findTokenArticles($token);

        $adapter = new DoctrineAdapter(new ORMPaginator($articleQuery, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);

        // Render the view template.
        return new ViewModel([
            'token' => $token,
            'articles' => $paginator,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
            'articleManager' => $this->articleManager,
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
        
        switch ($flag){
            case Token::BLACK_LIST: $this->nameManager->addToBlackList($token); break;
            case Token::GRAY_LIST: $this->nameManager->addToGrayList($token); break;
            default:
                if ($token->inBlackList()){
                    $this->nameManager->removeFromBlackList($token);
                }
                if ($token->inGrayList()){
                    $this->nameManager->removeFromGrayList($token);
                }
                break;
        }    
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }
    
    public function resetTokenStatusAction()
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
        
        $this->nameManager->resetTokenStatus($token);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }    

    public function changeTokenStatusAction()
    {
        $tokenId = (int)$this->params()->fromRoute('id', -1);
        $newStatus = (int) $this->params()->fromQuery('status', -1);

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
        
        $this->nameManager->changeTokenStatus($token, $newStatus);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }    

    public function addTokenToMyDictAction()
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
        
        $this->nameManager->addToMyDict($token);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }

    
    public function updateTokenFormAction()
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

        $form = new \Application\Form\TokenForm();

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                $newLemma = mb_strtoupper($data['name']);
//                if ($token->getLemma() != $newLemma){
                    $this->nameManager->updateCorrect($token, $newLemma);
//                }    
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($token){
                $data = [
                    'name' => ($token->getCorrect()) ? $token->getCorrect():$token->getLemma(),  
                ];
                $form->setData($data);
            }  
        }    
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'token' => $token,
        ]);                                
    }        
    
    public function abbrAction()
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
        
        $this->nameManager->abbrStatus($token);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }

    public function deleteTokenFromMyDictAction()
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
        
        $this->nameManager->updateCorrect($token);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }

    public function indexBigramAction()
    {
        $total = $this->entityManager->getRepository(Bigram::class)
                ->count([]);
        $statusBigramCount = $this->entityManager->getRepository(Bigram::class)
                ->statusBigramCount();
                
        return new ViewModel([
            'statuses' => Bigram::getStatusList(),
            'flags' => Bigram::getFlagList(),
            'total' => $total,
            'statusBigramCount' => $statusBigramCount,
        ]);  
    }
    
    public function contentBigramAction()
    {
        ini_set('memory_limit', '512M');
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        $limit = $this->params()->fromQuery('limit');
        $status = $this->params()->fromQuery('status', Bigram::RU_RU);
        $flag = $this->params()->fromQuery('flag', Bigram::WHITE_LIST);
        $isCorrect = $this->params()->fromQuery('isCorrect');
        
        $query = $this->entityManager->getRepository(Bigram::class)
                        ->findAllBigram([
                            'q' => $q, 
                            'sort' => $sort, 
                            'order' => $order, 
                            'status' => $status,
                            'flag' => $flag,
                            'isCorrect' => $isCorrect,
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
    
    public function viewBigramAction() 
    {       
        $bigramId = (int)$this->params()->fromRoute('id', -1);
        $page = $this->params()->fromQuery('page', 1);

        if ($bigramId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $bigram = $this->entityManager->getRepository(Bigram::class)
                ->findOneById($bigramId);
        
        if ($bigram == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $prevQuery = $this->entityManager->getRepository(Bigram::class)
                        ->findAllBigram(['prev1' => $bigram->getBilemma()]);
        $nextQuery = $this->entityManager->getRepository(Bigram::class)
                        ->findAllBigram(['next1' => $bigram->getBilemma()]); 
        
        
        $articleQuery = $this->entityManager->getRepository(Bigram::class)
                        ->findBigramArticles($bigram);

        $adapter = new DoctrineAdapter(new ORMPaginator($articleQuery, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);

        // Render the view template.
        return new ViewModel([
            'bigram' => $bigram,
            'articles' => $paginator,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
            'articleManager' => $this->articleManager,
        ]);
    }
    
    public function bigramFlagAction()
    {
        $bigramId = (int)$this->params()->fromRoute('id', -1);

        if ($bigramId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $bigram = $this->entityManager->getRepository(Bigram::class)
                ->findOneById($bigramId);
        
        if ($bigram == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $flag = $this->params()->fromQuery('flag', 1);
        
        switch ($flag){
            case Bigram::BLACK_LIST:
                $this->entityManager->getRepository(Bigram::class)
                    ->updateBigram($bigram, ['flag' => Bigram::BLACK_LIST]);                
                $this->entityManager->getRepository(\Application\Entity\Article::class)
                        ->updateBigramUpdateFlag($bigram);
                break;
            case Bigram::GRAY_LIST: 
                $this->entityManager->getRepository(Bigram::class)
                    ->updateBigram($bigram, ['flag' => Bigram::GRAY_LIST]);                
                $this->entityManager->getRepository(\Application\Entity\Article::class)
                        ->updateBigramUpdateFlag($bigram);
                break;
            default:
                $this->entityManager->getRepository(Bigram::class)
                    ->updateBigram($bigram, ['flag' => Bigram::WHITE_LIST]);                
                $this->entityManager->getRepository(\Application\Entity\Article::class)
                        ->updateBigramUpdateFlag($bigram);
                break;
        }    
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }
    
    public function updateArticleCountBigramAction()
    {
        $bigramId = (int)$this->params()->fromRoute('id', -1);
        if ($bigramId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $bigram = $this->entityManager->getRepository(Bigram::class)
                ->findOneById($bigramId);
        
        if ($bigram == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->nameManager->updateBigramArticleCount($bigram);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }
    
    public function articleCountBigramAction()
    {
        $this->nameManager->updateAllBigramArticleCount();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }    
    
    
    public function updateBigramFormAction()
    {
        $bigramId = (int)$this->params()->fromRoute('id', -1);
        
        if ($bigramId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $bigram = $this->entityManager->getRepository(Bigram::class)
                ->findOneById($bigramId);
        
        if ($bigram == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $form = new \Application\Form\BigramForm();

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                $newBilemma = mb_strtoupper($data['name']);
                $this->nameManager->updateBigramCorrect($bigram, $newBilemma);
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($bigram){
                $data = [
                    'name' => ($bigram->getCorrect()) ? $bigram->getCorrect():$bigram->getBilemma(),  
                ];
                $form->setData($data);
            }  
        }    
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'bigram' => $bigram,
        ]);                                
    }    
    
    public function parseAction()
    {
        $rawpriceId = (int)$this->params()->fromRoute('id', -1);
        if ($rawpriceId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $rawprice = $this->entityManager->getRepository(Rawprice::class)
                ->find($rawpriceId);
        
        if ($rawprice == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->nameManager->addNewTokenFromRawprice($rawprice, true);
        
        if ($rawprice->getCode()){
            $this->entityManager->getRepository(FpTree::class)
                    ->addFromArticle($rawprice->getCode());
        }
        
        return new JsonModel([
            'ok',
        ]);          
    }
    
    public function fixGoodNameAction()
    {
        $rawpriceId = (int)$this->params()->fromRoute('id', -1);
        if ($rawpriceId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $rawprice = $this->entityManager->getRepository(Rawprice::class)
                ->find($rawpriceId);
        
        if ($rawprice == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $result = $this->nameManager->fixTitle($rawprice);
        
        return new JsonModel([
            'alert' =>  $result,
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
    
    public function updateArticleCountTokenAction()
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

        $this->nameManager->updateTokenArticleCount($token->getLemma());
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }
    
    public function articleCountTokenAction()
    {
        $this->nameManager->updateAllTokenArticleCount();
        
        return new JsonModel([
            'result' => 'ok-reload',
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
            $this->nameManager->addGroupTokenFromGood($rawprice->getGood()->getId());
        }    
                
        return new JsonModel([
            'ok',
        ]);          
    }
    
    public function updateGoodTokenFromRawAction()
    {
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

        $this->nameManager->grabGoodTokenFromRaw($raw);
                
        return new JsonModel([
            'ok',
        ]);          
    }
    
    public function updateTokenGroupFromRawAction()
    {
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
    
    public function updateDescriptionAction()
    {
        $goodId = $this->params()->fromRoute('id', -1);

        if ($goodId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $good = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodId);

        if ($good == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->nameManager->updateBestDescription($good->getId(), $good->getName(), $good->getDescription());
                
        return new JsonModel([
            'result' => 'ok-reload',
        ]);           
    }
    
    public function updateDescriptionFromRawAction()
    {
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

        $this->nameManager->descriptionFromRaw($raw);
                
        return new JsonModel([
            'ok',
        ]);          
    }
    
    public function updateBestNameFromRawAction()
    {
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

        $this->nameManager->bestNameFromRaw($raw);
                
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
        
        $newName = $this->nameManager->updateTokenGroupName($tokenGroup, $name);
        
        return new JsonModel([
            $newName,
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

    public function deleteEmptyBigramAction()
    {
        $deleted = $this->nameManager->removeEmptyBigram();
                
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => $deleted.' удалено!',
        ]);          
    }    

    public function tokenGroupAction()
    {
        $total = $this->entityManager->getRepository(TokenGroup::class)
                ->count([]);
        $totalGoods = $this->entityManager->getRepository(Goods::class)
                ->count([]);
        $nameCoverage = $this->entityManager->getRepository(TokenGroup::class)->nameCoverage();
        $goodCoverage = $this->entityManager->getRepository(TokenGroup::class)->goodCoverage();
        $goodWithBestName = $this->entityManager->getRepository(Goods::class)->counWithBestName();
                
        return new ViewModel([
            'total' => $total,
            'totalGoods' => $totalGoods,
            'goodWithBestName' => $goodWithBestName,
            'nameCoverage' => $nameCoverage,
            'goodCoverage' => $goodCoverage,
        ]);  
    }
    
    public function tokenGroupContentAction()
    {
        ini_set('memory_limit', '1024M');
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        $goodCountLevel = $this->params()->fromQuery('goodCountLevel');
        $withoutName = $this->params()->fromQuery('withoutName');
        $withGenericGroup = $this->params()->fromQuery('withGenericGroup');
        $withGroupSite = $this->params()->fromQuery('withGroupSite');
        
        $query = $this->entityManager->getRepository(TokenGroup::class)
                        ->findAllTokenGroup([
                            'q' => $q, 
                            'sort' => $sort, 
                            'order' => $order,
                            'goodCountLevel' => $goodCountLevel,
                            'withoutName' => $withoutName,
                            'withGenericGroup' => $withGenericGroup,
                            'withGroupSite' => $withGroupSite,
                                ]);

        $total = count($query->getResult(2));
        
        if ($offset) $query->setFirstResult( $offset );
        if ($limit) $query->setMaxResults( $limit );

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);          
    }    
    
    public function updateTokenGroupCategoryAction()
    {    
        $tokenGroupId = $this->params()->fromRoute('id', -1);
        $groupSiteId = $this->params()->fromQuery('groupSite', -1);
        
        $groupSite = $this->entityManager->getRepository(GroupSite::class)
                ->find($groupSiteId);
        
        if ($tokenGroupId > 0){
            $tokenGroup = $this->entityManager->getRepository(TokenGroup::class)
                    ->find($tokenGroupId);
            if ($tokenGroup){
                $this->nameManager->updateTokenGroupCategory($tokenGroup, $groupSite);

                $query = $this->entityManager->getRepository(TokenGroup::class)
                                ->findAllTokenGroup(['id' => $tokenGroup->getId()]);
                
                $result = $query->getOneOrNullResult(2);
                return new JsonModel([
                    'id' => $tokenGroup->getId(),
                    'row' => $result,
                ]);
            }
        }
    }
    
    public function viewTokenGroupAction() 
    {       
        $tokenGroupId = (int)$this->params()->fromRoute('id', -1);
        $page = $this->params()->fromQuery('page', 1);
        $tdGroup = $this->params()->fromQuery('tdGroup');

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
        $aplGroups = $this->entityManager->getRepository(TokenGroup::class)
                ->getGroupApl($tokenGroup);
                       
        $tdGroups = $this->entityManager->getRepository(GenericGroup::class)
                ->genericTokenGroup($tokenGroup->getId());
        
        $meanFrequency = $this->entityManager->getRepository(TokenGroup::class)
                ->meanFrequency($tokenGroup);

        $rate = $this->entityManager->getRepository(Rate::class)
                ->findRate(['tokenGroup' => $tokenGroup->getId()]);
        
//        var_dump($tdGroups); exit;

        // Render the view template.
        return new ViewModel([
            'tokenGroup' => $tokenGroup,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
            'nameManager' => $this->nameManager,
            'tdGroups' => $tdGroups,
            'tdGroupActive' => $tdGroup,
            'aplGroups' => $aplGroups,
            'meanFrequency' => $meanFrequency,
            'rate' => $rate,
            'tokenStatuses' => Token::getStatusList(),
            'bigramStatuses' => bigram::getStatusList(),
        ]);
    }    
    
    public function tokenGroupTokenContentAction()
    {
        $tokenGroupId = (int)$this->params()->fromRoute('id', -1);

        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        $status = $this->params()->fromQuery('status', Token::IS_DICT);
        
        // Validate input parameter
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
        
        $query = $this->entityManager->getRepository(TokenGroupToken::class)
                        ->findTokenGroupToken($tokenGroup, ['status' => $status]);

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
    
    public function tokenGroupBigramContentAction()
    {
        $tokenGroupId = (int)$this->params()->fromRoute('id', -1);

        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        $status = $this->params()->fromQuery('status', Bigram::RU_RU);
        
        // Validate input parameter
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
        
        $query = $this->entityManager->getRepository(TokenGroupBigram::class)
                        ->findTokenGroupBigram($tokenGroup, ['status' => $status]);

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
    
    public function tokenGroupGoodContentAction()
    {
        $tokenGroupId = (int)$this->params()->fromRoute('id', -1);

        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        $group = $this->params()->fromQuery('group');
        
        // Validate input parameter
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
        
        $query = $this->entityManager->getRepository(TokenGroup::class)
                        ->findTokenGroupGoodName($tokenGroup, ['group' => $group]);

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
    
    public function updateTitleTokenDisplayAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $tokenGroupId = $data['pk'];
            $lemma = $data['name'];
            if ($tokenGroupId > 0 && $lemma){
                $tokenGroup = $this->entityManager->getRepository(TokenGroup::class)
                        ->findOneById($tokenGroupId);
                $token = $this->entityManager->getRepository(Token::class)
                        ->findOneByLemma($lemma);
                if ($tokenGroup && $token){
                    $this->entityManager->getRepository(TitleToken::class)
                            ->updateTitleTokens($tokenGroup, $token, $data['value']);
                }    
            }    
        }
        
        exit;        
    }
    
    public function updateTitleBigramDisplayAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $tokenGroupId = $data['pk'];
            $bigramId = $data['name'];
            if ($tokenGroupId > 0 && $bigramId > 0){
                $tokenGroup = $this->entityManager->getRepository(TokenGroup::class)
                        ->findOneById($tokenGroupId);
                $bigram = $this->entityManager->getRepository(Bigram::class)
                        ->findOneById($bigramId);
                if ($tokenGroup && $bigram){
                    $this->entityManager->getRepository(TitleBigram::class)
                            ->updateTitleBigrams($tokenGroup, $bigram, $data['value']);
                }    
            }    
        }
        
        exit;        
    }

    public function displayTitleTokenAction()
    {
        $displayLemma = $id = null;
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
        
            $tokenGroupId = $data['tokenGroupId'];
            $lemma = $data['lemma'];
            
            if (!$tokenGroupId || !$lemma){
                $this->getResponse()->setStatusCode(404);
                return;
            }

            $id = $tokenGroupId.'_'.$lemma;
            
            $token = $this->entityManager->getRepository(Token::class)
                    ->findOneByLemma($lemma);

            if (!$token){
                $this->getResponse()->setStatusCode(404);
                return;
            }

            $titleToken = $this->entityManager->getRepository(TitleToken::class)
                    ->findOneBy(['tokenGroup' => $tokenGroupId, 'token' => $token->getId()]);
            
            if ($titleToken){
                $displayLemma = $titleToken->getDisplayLemma();
            }
        }

        return new JsonModel([
            'id' => $id,
            'displayLemma' => $displayLemma,
        ]);          
    }
    
    public function displayTitleBigramAction()
    {
        $displayBilemma = $id = null;
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
        
            $tokenGroupId = $data['tokenGroupId'];
            $bigramId = $data['bigramId'];
            
            if (!$tokenGroupId || !$bigramId){
                $this->getResponse()->setStatusCode(404);
                return;
            }

            $id = $tokenGroupId.'_'.$bigramId;
            
            $titleBigram = $this->entityManager->getRepository(TitleBigram::class)
                    ->findOneBy(['tokenGroup' => $tokenGroupId, 'bigram' => $bigramId]);
            
            if ($titleBigram){
                $displayBilemma = $titleBigram->getDisplayBilemma();
            }
        }

        return new JsonModel([
            'id' => $id,
            'displayBilemma' => $displayBilemma,
        ]);          
    }
    

    public function goodsTokenGroupAction()
    {
        $goodId = (int)$this->params()->fromRoute('id', -1);
        if ($goodId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $good = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodId);
        
        if ($good == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->nameManager->addGroupTokenFromGood($good->getId());
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }
    
    public function tokenGroupTokenAction()
    {
        ini_set('memory_limit', '4096M');
        
        $tokenGroupId = (int)$this->params()->fromRoute('id', -1);
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

        $this->entityManager->getRepository(TokenGroupToken::class)
                ->updateTokenGroupToken($tokenGroup);
        $this->entityManager->getRepository(TokenGroupBigram::class)
                ->updateTokenGroupBigram($tokenGroup);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }
    
    public function goodTokenAction()
    {
        $goodId = (int)$this->params()->fromRoute('id', -1);
        if ($goodId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $good = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodId);
        
        if ($good == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->nameManager->addGoodTokenFromGood($good);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }
    
    public function goodSignTokenAction()
    {
        $goodId = (int)$this->params()->fromRoute('id', -1);
        if ($goodId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $good = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodId);        
        
        if ($good == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $data = $this->nameManager->goodSignTokens($good);
//        $associator = $this->nameManager->aprioriTokens($data);
        
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => $data,
        ]);          
    }
    
    public function updateGoodCountTokenGroupAction()
    {
        $tokenGroupId = (int)$this->params()->fromRoute('id', -1);
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

        $this->nameManager->updateTokenGroupGoodCount($tokenGroup->getId());
        
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
    
    public function updateMovementTokenGroupAction()
    {
        $tokenGroupId = (int)$this->params()->fromRoute('id', -1);
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

        $this->nameManager->updateTokenGroupMovement($tokenGroup);
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }
        
    public function movementTokenGroupAction()
    {
        $this->nameManager->updateTokenGroupsMovement();
        
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
    
    public function deleteAllTokenGroupAction()
    {
        $deleted = $this->entityManager->getRepository(TokenGroup::class)
                ->deleteAllTokenGroup();
                
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => $deleted.' удалено!',
        ]);          
    }    
    
    public function tokenGroupTotalFeatureAction()
    {
        $feature = $this->params()->fromQuery('feature');
        
        switch ($feature){
            case 'goodsWithoutTokenGroup' : 
                $query = $this->entityManager->getRepository(Goods::class)
                    ->findAllGoods(['withTokenGroup' => false]);
                $result = count($query->getResult());
                break; 
            case 'goodsWithTokenGroup' : 
                $query = $this->entityManager->getRepository(Goods::class)
                    ->findAllGoods(['withTokenGroup' => true]);
                $result = count($query->getResult());
                break; 
            default: $result = 0;
        }
        
        return new JsonModel([
            'total' => $result,
        ]);                  
    }
    
    public function fillFpTreeAction()
    {
        $this->entityManager->getRepository(FpTree::class)
                ->fillFromArticles();
        
        return new JsonModel([
            'result' => 'ok',
        ]);          
        
    }
    
    public function countFpTreeAction()
    {
        $this->entityManager->getRepository(FpTree::class)
                ->updateSupportCount();
        
        return new JsonModel([
            'result' => 'ok',
        ]);          
        
    }
    
    public function resetFpTreeAction()
    {
        $this->entityManager->getRepository(FpTree::class)
                ->resetFpTree();
        
        return new JsonModel([
            'result' => 'ok',
        ]);          
        
    }
    
    public function deleteEmptyFpTreeAction()
    {
        $this->entityManager->getRepository(FpTree::class)
                ->updateSupportCount();
        $this->entityManager->getRepository(FpTree::class)
                ->deleteEmpty();
        
        return new JsonModel([
            'result' => 'ok',
        ]);          
        
    }
    
    public function prefixWaysAction()
    {
        $tokenId = $this->params()->fromRoute('id', -1);
        if ($tokenId > 0){
            $token = $this->entityManager->getRepository(Token::class)
                    ->findOneBy(['id' => $tokenId]);
            
            $ways = $this->entityManager->getRepository(FpTree::class)
                    ->updateFpGroup($token); 
//            var_dump($ways);
        }
        
        return new JsonModel([
            'result' => 'ok',
        ]);          
        
    }
    
    public function fpGroupsAction()
    {
        $this->entityManager->getRepository(FpGroup::class)
                ->updateFpGroups(); 

        return new JsonModel([
            'result' => 'oke',
        ]);          
        
    }

    public function fillTokenGroupTokenAction()
    {
        $this->entityManager->getRepository(TokenGroupToken::class)
                ->fillTokenGroupToken(); 

        return new JsonModel([
            'result' => 'oke',
        ]);          
        
    }

    public function fillTokenGroupBigramAction()
    {
        $this->entityManager->getRepository(TokenGroupBigram::class)
                ->fillTokenGroupBigram(); 

        return new JsonModel([
            'result' => 'oke',
        ]);          
        
    }

    public function supportTitleTokensAction()
    {
        $this->entityManager->getRepository(TokenGroupToken::class)
                ->supporTitleTokens(); 

        return new JsonModel([
            'result' => 'oke',
        ]);          
        
    }

    public function supportTitleBigramsAction()
    {
        $this->entityManager->getRepository(TokenGroupBigram::class)
                ->supporTitleBigrams(); 

        return new JsonModel([
            'result' => 'oke',
        ]);          
        
    }
    
    public function aiNamingAction()
    {
        $goodId = $this->params()->fromRoute('id', -1);
        
        $good = $this->entityManager->getRepository(Goods::class)
                ->find($goodId);
        $result = [];
        if ($good){
            $result = $this->nameManager->aiNaming($good);
//            var_dump($result); exit;
        }
        
        return new JsonModel(
           $result
        );                   
        
    }

}
