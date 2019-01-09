<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Application\Entity\Token;
use Application\Entity\Raw;
use Application\Entity\Rawprice;
use Application\Entity\TokenGroup;

use Phpml\FeatureExtraction\TokenCountVectorizer;
use Application\Filter\NameTokenizer;
use Application\Filter\Lemma;
use Application\Filter\Tokenizer;
use Application\Filter\IdsFormat;

/**
 * Description of RbService
 *
 * @author Daddy
 */
class NameManager
{
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Разбивает наименование товара на токены
     * 
     * @param Application\Entity\Article $article
     * @return array
     */
    public function tokenArticle($article)
    {
        $titles = [];
        $rawprices = $article->getRawprice();
        foreach ($rawprices as $rawprice){
            if ($rawprice->getStatus() == $rawprice::STATUS_PARSED){
                $titles[] = $rawprice->getTitle();
            }    
        }
        
        if (count($titles)){
            $vectorizer = new TokenCountVectorizer(new NameTokenizer());
            $vectorizer->fit($titles);
            $vacabulary = $vectorizer->getVocabulary();

            $vectorizer->transform($titles);
            //\Zend\Debug\Debug::dump($titles);
            return ['NameTokenizer' => $vacabulary];
        }
        
        return;
    }
    
    /**
     * Добавить новый токен
     * 
     * @param string $word
     * @param bool $flushnow
     */
    public function addToken($data, $flushnow = true)
    {
        
        $word = mb_strcut(trim($data['word']), 0, 64, 'UTF-8');
        
        $token = $this->entityManager->getRepository(Token::class)
                    ->findOneBy(['lemma' => $word]);

        if ($token == null){

            $token = new Token();
            $token->setLemma($word);            
            $token->setStatus($data['status']);            

            // Добавляем сущность в менеджер сущностей.
            $this->entityManager->persist($token);

            // Применяем изменения к базе данных.
            $this->entityManager->flush($token);
        } else {
            if ($token->getStatus() != $data['status']){
                $token->setStatus($data['status']);                            
                $this->entityManager->persist($token);
                $this->entityManager->flush($token);
            }
        }
        
        return $token;        
    }  
    
    /**
     * Обновить флаг токена
     * 
     * @param Application\Entity\Token $token
     * @param integer $flag
     */
    public function updateTokenFlag($token, $flag)
    {
        $token->setFlag($flag);
        $this->entityManager->persist($token);
        $this->entityManager->flush($token);
    }
    
    /**
     * Добавление нового слова со статусом
     * 
     */
    public function addLemms($rawprice, $lemms, $status, $flush)
    {
        
        if (is_array($lemms)){
            foreach ($lemms as $lemma){
                $token = $this->addToken(['word' => $lemma, 'status' => $status], $flush);
                if ($token){
                    $rawprice->addToken($token);
                }   
            }
        }    
    }
    
    /**
     * Добавление нового слова из прайса
     * 
     * @param Application\Entity\Rawprice $rawprice
     * @param bool $flush
     */
    public function addNewTokenFromRawprice($rawprice, $flush = true) 
    {
        $rawprice->getTokens()->clear();

        $title = $rawprice->getTitle();
        
        if ($title){
            $lemmaFilter = new Lemma();
            $tokenFilter = new Tokenizer();
            
            $lemms = $lemmaFilter->filter($tokenFilter->filter($title));
            
            foreach ($lemms as $key => $words){
                if ($key == Token::IS_RU){
                    foreach ($words as $word){
                        $predictWords = $this->entityManager->getRepository(Token::class)
                               ->findNearToken($word);
                        if (count($predictWords)){
                            foreach($predictWords as $predictWord){
//                                var_dump($predictWord['lemma']); exit;
                                $this->addLemms($rawprice, [$predictWord['lemma']], Token::IS_DICT, $flush);
                            }    
                        } else {
                            $this->addLemms($rawprice, [$word], $key, $flush);
                        }
                    }    
                } else {
                    $this->addLemms($rawprice, $words, $key, $flush);
                }    
            }    
        }  
        
        $rawprice->setStatusToken(Rawprice::TOKEN_PARSED);
        $this->entityManager->persist($rawprice);
        if ($flush){
            $this->entityManager->flush();
        }    
        return;
    }  
    
    /**
     * Выборка токенов из прайса и добавление их в таблицу токенов
     * @param Appllication\Entity\Raw $raw
     */
    public function grabTokenFromRaw($raw)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1200);
        $startTime = time();
        
        $rawprices = $this->entityManager->getRepository(Rawprice::class)
                ->findBy(['raw' => $raw->getId(), 'statusToken' => Rawprice::TOKEN_NEW]);
        
        foreach ($rawprices as $rawprice){
            if ($rawprice->getStatusToken() == $rawprice::TOKEN_NEW){
                $this->addNewTokenFromRawprice($rawprice, false);
                if (time() > $startTime + 400){
                    $this->entityManager->flush();
                    return;
                }
            }    
        }
        
        $raw->setParseStage(Raw::STAGE_TOKEN_PARSED);
        $this->entityManager->persist($raw);
        
        $this->entityManager->flush();
    }
    
    /**
     * Удаление токена
     * 
     * @param Application\Entity\Token $token
     */
    public function removeToken($token) 
    {   
        $this->entityManager->remove($token);
        
        $this->entityManager->flush($token);
    }    
    
    /**
     * Поиск и удаление токенов не привязаных к строкам прайсов
     */
    public function removeEmptyToken()
    {
        ini_set('memory_limit', '2048M');
        
        $tokenForDelete = $this->entityManager->getRepository(Token::class)
                ->findTokenForDelete();

        foreach ($tokenForDelete as $row){
            $this->removeToken($row[0], false);
        }
        
        $this->entityManager->flush();
        
        return count($tokenForDelete);
    }

    /**
     * Поиск лучшего наименования для товара
     * 
     * @param Application\Entity\Goods $good
     * @return string
     */
    public function findBestName($good)
    {
        $result = '';
        $dict = 0;
        foreach ($good->getRawprice() as $rawprice){
            $dictRu = $rawprice->getDictRuTokens()->count();
            $dictEn = $rawprice->getDictEnTokens()->count();
            if ($dict < (2*$dictRu + $dictEn)){
                $dict = 2*$dictRu + $dictEn;
                $result = $rawprice->getTitle();
            }
        }
        
        return $result;
    }
    
    /**
     * Добавить группу наименований по токенам товара
     * 
     * @param Application\Entity\Goods $good
     * @param bool $flush Description
     * @return Application\Entity\TokenGroup Description
     */
    public function addGroupTokenFromGood($good, $flush = true)
    {
        
        if ($good->getTokenGroup()){
            return;
        }
        
//        if (count($good->getDictRuTokens()) == 0){
//            return;
//        }
        $dictTokens = $this->entityManager->getRepository(Token::class)
                ->findTokenGoodsByStatus($good, Token::IS_DICT);
        
//        var_dump(count($dictTokens)); exit;
        if (count($dictTokens) == 0){
            return;
        }
        
        $tokenIds = [];
        $tokenLemms = [];
        foreach ($dictTokens as $token){
            $tokenIds[] = $token['id'];
            $tokenLemms[] = $token['lemma'];
        }
        
        $idsFilter = new IdsFormat();
        
        $tokenGroup = $this->entityManager->getRepository(TokenGroup::class)
                ->findOneByIds($idsFilter->filter($tokenIds));
        
        if ($tokenGroup === NULL){
            
            $tokenGroup = new TokenGroup();
        
            foreach($dictTokens as $token){
                $tokenRef = $this->entityManager->getReference(Token::class, $token['id']);
                $tokenGroup->addToken($tokenRef);
            }
        
            $tokenGroup->setName('');
            $tokenGroup->setLemms($tokenLemms);
            $tokenGroup->setIds($tokenIds);
        }
        
        $good->setTokenGroup($tokenGroup);
        
        $this->entityManager->persist($tokenGroup);
        if ($flush){
            $this->entityManager->flush();
        }    
        
        return $tokenGroup;
    }
    
    
    /**
     * Выборка токенов из прайса и добавление их в таблицу токенов
     * @param Appllication\Entity\Raw $raw
     */
    public function grabTokenGroupFromRaw($raw)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1200);
        $startTime = time();
        
        $rawprices = $this->entityManager->getRepository(Rawprice::class)
                ->findBy(['raw' => $raw->getId(), 'statusToken' => Rawprice::TOKEN_PARSED]);
        
        foreach ($rawprices as $rawprice){
            if ($rawprice->getStatusToken() == Rawprice::TOKEN_PARSED && $rawprice->getStatusGood() == Rawprice::GOOD_OK){
                $this->addGroupTokenFromGood($rawprice->getGood());
                
                $rawprice->setStatusToken(Rawprice::TOKEN_GROUP_PARSED);
                $this->entityManager->persist($rawprice);
                
                if (time() > $startTime + 400){
                    $this->entityManager->flush();
                    return;
                }
            }    
        }
        
        $raw->setParseStage(Raw::STAGE_TOKEN_GROUP_PARSED);
        $this->entityManager->persist($raw);
        
        $this->entityManager->flush();
    }
    
    
    /**
     * Обновить наименование группы наименований
     * 
     * @param Application\Entity\TokenGroup $tokenGroup
     * @param string $name
     */
    public function updateTokenGroupName($tokenGroup, $name)
    {
        $tokenGroup->setName($name);
        $this->entityManager->persist($tokenGroup);
        
        $this->entityManager->flush($tokenGroup);
        
    }
    
    /**
     * Обновление количества товара у группы наименований
     * 
     * @param Application\Entity\TokenGroup $tokenGroup
     * @param bool $flush
     */
    public function updateTokenGroupGoodCount($tokenGroup, $flush = true)
    {
        $goodCount = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->count(['tokenGroup' => $tokenGroup->getId()]);
        
        $tokenGroup->setGoodCount($goodCount);
        $this->entityManager->persist($tokenGroup);
        
        if ($flush){
            $this->entityManager->flush($tokenGroup);
        }
    }
    
    /**
     * Обновление количества товара у всех групп наименований
     */
    public function updateAllTokenGroupGoodCount()
    {
        $tokenGroups = $this->entityManager->getRepository(TokenGroup::class)
                ->findBy([]);

        foreach ($tokenGroups as $tokenGroup){
            $this->updateTokenGroupGoodCount($tokenGroup, false);
        }   
        $this->entityManager->flush();        
    }
    
    
    
    /**
     * Удаление TokenGroup
     * 
     * @param Application\Entity\TokenGroup $tokenGroup
     */
    public function removeTokenGroup($tokenGroup, $flush = true)
    {
        foreach ($tokenGroup->getGoods() as $good){
            $good->setTokenGroup(null);
        }
        
        $tokenGroup->getTokens()->clear();
        
        $this->entityManager->remove($tokenGroup);
        if ($flush){
            $this->entityManager->flush();
        }    
    }
    
    /**
     * Поиск и удаление пустых групп наименований
     */
    public function removeEmptyTokenGroup()
    {
        ini_set('memory_limit', '2048M');
        
        $tokenGroups = $this->entityManager->getRepository(TokenGroup::class)
                ->findBy(['goodCount' => 0]);

        foreach ($tokenGroups as $tokenGroup){
            $this->removeTokenGroup($tokenGroup, false);
        }
        
        $this->entityManager->flush();
        
        return count($tokenGroups);
    }    
}
