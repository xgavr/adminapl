<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Application\Entity\Token;
use Application\Entity\Article;
use Application\Entity\ArticleToken;
use Application\Entity\Raw;
use Application\Entity\Rawprice;
use Application\Entity\TokenGroup;
use Application\Entity\GoodToken;
use Application\Entity\Bigram;
use Application\Entity\ArticleBigram;

use Phpml\Tokenization\WhitespaceTokenizer;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Application\Filter\NameTokenizer;
use Application\Filter\Lemma;
use Application\Filter\Tokenizer;
use Application\Filter\IdsFormat;
use Application\Validator\IsRU;
use Application\Filter\ArticleCode;
use Application\Filter\ProducerName;

use Zend\Config\Config;
use Zend\Config\Writer\PhpArray;


/**
 * Description of RbService
 *
 * @author Daddy
 */
class NameManager
{ 
        
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Добавить слово в локальный словарь
     * 
     * @param \Application\Entity\Token $token
     * @param string $lemma
     */
    public function addToMyDict($token, $lemma = null)
    {
        ini_set('memory_limit', '2048M');
        
        if (!is_dir(Token::MY_DICT_PATH)){
            mkdir(Token::MY_DICT_PATH);
        }        
        
        if (file_exists(Token::MY_DICT_FILE)){
            $dict = new Config(include Token::MY_DICT_FILE, true);
        }  else {
            $dict = new Config([], true);
        }
        $word = $token->getLemma();
        
        $dict->$word = ($lemma) ? $lemma:$word;

        $writer = new PhpArray();
        
        $writer->toFile(Token::MY_DICT_FILE, $dict);
        
        $ruValidator = new IsRU();
        if ($ruValidator->isValid($word)){
            $status = Token::IS_DICT;
        } else {
            $status = Token::IS_EN_DICT;
        }
        
        $this->entityManager->getRepository(Token::class)
                ->updateToken($word, ['status' => $status]);
        
        $this->entityManager->getRepository(Article::class)
                ->updateTokenUpdateFlag($word);
    }
    
    
    /**
     * Удалить слово из локального словаря
     * 
     * @param Application\Entity\Token $token
     */
    public function removeFromMyDict($token)
    {
        ini_set('memory_limit', '2048M');
        
        if (file_exists(Token::MY_DICT_FILE)){
            $dict = new Config(include Token::MY_DICT_FILE, true);
            $word = $token->getLemma();
            unset($dict->$word);
            
            $writer = new PhpArray();

            $writer->toFile(Token::MY_DICT_FILE, $dict);

            $ruValidator = new IsRU();
            if ($ruValidator->isValid($word)){
                if (mb_strlen($word) == 1){
                    $status = Token::IS_RU_1;
                } else {
                    $status = Token::IS_RU;
                }    
            } else {
                if (mb_strlen($word) == 1){
                    $status = Token::IS_EN_1;
                } else {
                    $status = Token::IS_EN;
                }    
            }

            $this->entityManager->getRepository(Token::class)
                    ->updateToken($word, ['status' => $status]);
            
            $this->entityManager->getRepository(Article::class)
                    ->updateTokenUpdateFlag($word);
        }
        
        return;
    }
    
    /**
     * Добавить слово в черный список
     * 
     * @param Application\Entity\Token $token
     */
    public function addToBlackList($token)
    {
        ini_set('memory_limit', '2048M');
        
        if (!is_dir(Token::MY_DICT_PATH)){
            mkdir(Token::MY_DICT_PATH);
        }        
        
        if (file_exists(Token::MY_BLACK_LIST)){
            $dict = new Config(include Token::MY_BLACK_LIST, true);
        }  else {
            $dict = new Config([], true);
        }
        $word = $token->getLemma();
        $dict->$word = $word;

        $writer = new PhpArray();
        
        $writer->toFile(Token::MY_BLACK_LIST, $dict);
        
        $this->updateTokenFlag($token, Token::BLACK_LIST);
        $this->entityManager->getRepository(Article::class)
                ->updateTokenUpdateFlag($word);
        
        return;
    }
    
    /**
     * Добавить слово в серый список
     * 
     * @param Application\Entity\Token $token
     */
    public function addToGrayList($token)
    {
        ini_set('memory_limit', '2048M');
        
        if (!is_dir(Token::MY_DICT_PATH)){
            mkdir(Token::MY_DICT_PATH);
        }        
        
        if (file_exists(Token::MY_GRAY_LIST)){
            $dict = new Config(include Token::MY_GRAY_LIST, true);
        }  else {
            $dict = new Config([], true);
        }
        $word = $token->getLemma();
        $dict->$word = $word;

        $writer = new PhpArray();
        
        $writer->toFile(Token::MY_GRAY_LIST, $dict);
        
        $this->entityManager->getRepository(Article::class)
                ->updateTokenUpdateFlag($word);
        $this->updateTokenFlag($token, Token::GRAY_LIST);
        
        return;
    }
    
    /**
     * Удалить слово из черного списка
     * 
     * @param Application\Entity\Token $token
     */
    public function removeFromBlackList($token)
    {
        ini_set('memory_limit', '2048M');
        
        if (file_exists(Token::MY_BLACK_LIST)){
            $dict = new Config(include Token::MY_BLACK_LIST, true);
            $word = $token->getLemma();
            unset($dict->$word);
            
            $writer = new PhpArray();

            $writer->toFile(Token::MY_BLACK_LIST, $dict);

            $this->updateTokenFlag($token, Token::WHITE_LIST);

            $this->entityManager->getRepository(Article::class)
                    ->updateTokenUpdateFlag($word);
        }
        
        return;
    }

    /**
     * Удалить слово из серого списка
     * 
     * @param Application\Entity\Token $token
     */
    public function removeFromGrayList($token)
    {
        ini_set('memory_limit', '2048M');
        
        if (file_exists(Token::MY_GRAY_LIST)){
            $dict = new Config(include Token::MY_GRAY_LIST, true);
            $word = $token->getLemma();
            unset($dict->$word);
            
            $writer = new PhpArray();

            $writer->toFile(Token::MY_GRAY_LIST, $dict);

            $this->updateTokenFlag($token, Token::WHITE_LIST);

            $this->entityManager->getRepository(Article::class)
                    ->updateTokenUpdateFlag($word);
        }
        
        return;
    }
    
    /**
     * Обновить исправление токена
     * 
     * @param Token $token
     * @param string $correctStr
     */
    public function updateCorrect($token, $correctStr = null)
    {
        $token->setCorrect($correctStr);
                
        $ruValidator = new IsRU();
        if ($correctStr){
            if ($ruValidator->isValid($token->getLemma())){
                $status = Token::IS_DICT;
            } else {
                $status = Token::IS_EN_DICT;
            }
        } else {    
            if ($ruValidator->isValid($token->getLemma())){
                if (mb_strlen($token->getLemma()) == 1){
                    $status = Token::IS_RU_1;
                } else {
                    $status = Token::IS_RU;
                }    
            } else {
                if (mb_strlen($token->getLemma()) == 1){
                    $status = Token::IS_EN_1;
                } else {
                    $status = Token::IS_EN;
                }    
            }
        }    
        $token->setStatus($status);        
        
        $this->entityManager->persist($token);
        $this->entityManager->flush($token);

        $this->entityManager->getRepository(Article::class)
                ->updateTokenUpdateFlag($token->getLemma());
        
        return;
    }
    
    /**
     * Поставить слову метку аббревиатуры
     * 
     * @param \Application\Entity\Token $token
     */
    public function abbrStatus($token)
    {
        ini_set('memory_limit', '2048M');
        
        $word = $token->getLemma();
        
        $isRuValidator = new IsRU();
        if ($isRuValidator->isValid($word)){
            if ($token->getStatus() == Token::IS_RU){
                $status = Token::IS_RU_ABBR;
            } else {
                $status = Token::IS_RU;                
            }    
        } else {
            if ($token->getStatus() == Token::IS_EN){
                $status = Token::IS_EN_ABBR;
            } else {
                $status = Token::IS_EN;                
            }    
        }
        
        $this->entityManager->getRepository(Token::class)
                ->updateToken($word, ['status' => $status]);

        $this->entityManager->getRepository(Article::class)
                ->updateTokenUpdateFlag($word);
        
        return;
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
     * @param array $data
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

            
        } else {
            if ($token->getStatus() != $data['status']){
                $token->setStatus($data['status']);                            
                $this->entityManager->persist($token);
            }
        }
        
        if ($flushnow){
            $this->entityManager->flush($token);
        }    
        
        return $token;        
    }  
    
    /**
     * Добавить новый article токен
     * 
     * @param Application\Entity\Article $article
     * @param array $data
     * @param bool $flushnow
     */
    public function addArticleToken($article, $data, $flushnow = true)
    {
        
        $word = mb_strcut(trim($data['word']), 0, 64, 'UTF-8');
        
        $articleToken = $this->entityManager->getRepository(ArticleToken::class)
                    ->findOneBy(['lemma' => $word, 'article' => $article->getId()]);

        if ($articleToken == null){
            $articleToken = new ArticleToken();
            $articleToken->setArticle($article);            
            $articleToken->setLemma($word);            
            $articleToken->setStatus($data['status']);            

            // Добавляем сущность в менеджер сущностей.
            $this->entityManager->persist($articleToken);


        } else {
            if ($articleToken->getStatus() != $data['status']){
                $articleToken->setStatus($data['status']);                            
                $this->entityManager->persist($articleToken);
            }
        }

        if ($flushnow){
            $this->entityManager->flush($articleToken);
        }    

        return $articleToken;        
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
    public function addLemms($article, $lemms, $status, $flush)
    {        
        if (is_array($lemms)){
            foreach ($lemms as $lemma){
                $this->addToken(['word' => $lemma, 'status' => $status], true);
                $this->addArticleToken($article, ['word' => $lemma, 'status' => $status], $flush);
            }
        }    
    }
    
    
    /**
     * Обновление количества товаров у токена
     * 
     * @param Token $token
     * @param integer $goodCount
     * @param integer $goods
     */
    public function updateTokenArticleCount($token, $goodCount = null, $goods = null, $avgD = null)
    {
        if ($goodCount == null){
            $goodCount = $this->entityManager->getRepository(ArticleToken::class)
                    ->tokenGoodCount($token->getLemma());
//            var_dump($articleCount);
        }    
        if ($goods == null){
            $goods = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                    ->count([]);
        }
        
        $idf = log10(($goods - $goodCount + 0.5)/($goodCount + 0.5));
        
        $this->entityManager->getRepository(Token::class)
                ->updateToken($token->getLemma(), ['frequency' => $goodCount, 'idf' => $idf]);

    }
    
    /**
     * Обновление количества артикулов у всех токенов
     */
    public function updateAllTokenArticleCount()
    {
        set_time_limit(1800);        
        ini_set('memory_limit', '2048M');
        
        $goods = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->count([]);
        
        $tokensQuery = $this->entityManager->getRepository(Token::class)
                ->findAllToken();
        $iterable = $tokensQuery->iterate();
        foreach ($iterable as $row){
            foreach ($row as $token){
                $this->updateTokenArticleCount($token, null, $goods);
                $this->entityManager->detach($token);
            }   
        }    
        return;
    }
    
    /**
     * Обновление количества товаров у биграм
     * 
     * @param Bigram $bigram
     * @param integer $goodCount
     * @param integer $goods
     */
    public function updateBigramArticleCount($bigram, $goodCount = null, $goods = null)
    {
        if ($goodCount == null){
            $goodCount = $this->entityManager->getRepository(ArticleBigram::class)
                    ->bigramGoodCount($bigram);
        }    
        if ($goods == null){
            $goods = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                    ->count([]);
        }
        
        $idf = log10(($goods - $goodCount + 0.5)/($goodCount + 0.5));
        
        $this->entityManager->getRepository(Bigram::class)
                ->updateBigram($bigram, ['frequency' => $goodCount, 'idf' => $idf]);

    }
    
    /**
     * Обновление количества артикулов у всех биграм
     */
    public function updateAllBigramArticleCount()
    {
        set_time_limit(3600);        
        ini_set('memory_limit', '2048M');
        
        $goods = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->count([]);
        
        $bigramsQuery = $this->entityManager->getRepository(Bigram::class)
                ->findAllBigram();
        $iterable = $bigramsQuery->iterate();
        foreach ($iterable as $row){
            foreach ($row as $bigram){
                $this->updateBigramArticleCount($bigram, null, $goods);
                $this->entityManager->detach($bigram);
            }   
        }    
        return;
    }
    
    /**
     * Дополнительная проверка лемм
     * 
     * @param string $str
     * @return array Description
     */
    public function lemmsFromStr($str)
    {
        $lemmaFilter = new Lemma($this->entityManager);
        $tokenFilter = new Tokenizer();

        $lemms = $lemmaFilter->filter($tokenFilter->filter($str));
            
        return $lemms;    
    }
    
    /**
     * Получить биграммы из сторки
     * 
     * @param string $str
     * @return array Description
     */
    public function bigramFromStr($str)
    {
        $lemmaFilter = new Lemma($this->entityManager);
        $tokenFilter = new Tokenizer();

        $lemms = $lemmaFilter->filter($tokenFilter->filter($str));
        $result = [];
//        exit;
        foreach ($lemms as $key => $words){            
            foreach ($words as $word){
                $wordMd5 = md5($word);
                $result[$key][$wordMd5] = $word;
                if ($key == Token::IS_DICT){
                    $token = $this->entityManager->getRepository(Token::class)
                            ->findOneByLemma($word);
                    if ($token){
                        if ($token->getCorrect()){
                            unset($result[Token::IS_DICT][$wordMd5]);
                            $lemms = $token->getCorrectAsArray();
                            foreach ($lemms as $lemma){
                                $result[Token::IS_DICT][md5($lemma)] = $lemma;
                            }
                        }                        
                    }    
                }   
            }            
        } 
            
        return $result;    
    }

    /**
     * Разбить наименование товара из строки прайса
     * 
     * @param Rawprice $rawprice
     * @return array;
     */
    public function lemmsFromRawprice($rawprice)
    {
        $articleFilter = new ArticleCode();
        $producerFilter = new ProducerName();
        $search = [
            mb_strtoupper($rawprice->getArticle()),
            $articleFilter->filter($rawprice->getArticle()),
            mb_strtoupper($rawprice->getProducer()),
            $producerFilter->filter($rawprice->getProducer()),
        ];
        $titleStr =  str_replace($search, '', mb_strtoupper($rawprice->getTitle()));
        
        return $this->lemmsFromStr($titleStr);
    }
            
    
    /**
     * Добавление нового слова из прайса
     * 
     * @param Rawprice $rawprice
     * @param bool $updateArticleToken
     */
    public function addNewTokenFromRawprice($rawprice, $updateArticleToken = false) 
    {
        $article = $rawprice->getCode();
        if ($article && $updateArticleToken){
            $this->entityManager->getRepository(Article::class)
                    ->deleteArticleToken($article->getId());
        }    

        $lemms = $this->lemmsFromRawprice($rawprice);
//        var_dump($lemms);
        $preWord = null;
        foreach ($lemms as $k => $words){
            foreach ($words as $key => $word){
                if (mb_strlen($word) < 64){
                    $token = $this->entityManager->getRepository(Token::class)
                            ->findOneByLemma($word);
                    if (!$token){
                        $this->entityManager->getRepository(Token::class)
                                ->insertToken([
                                    'lemma' => $word,
                                    'status' => $key,
                                    ]);
                    }    

                    $articleToken = $this->entityManager->getRepository(ArticleToken::class)
                            ->findOneBy(['article' => $article->getId(), 'lemma' => $word]);

                    if (!$articleToken){
                        $this->entityManager->getRepository(Token::class)
                                ->insertArticleToken([
                                    'article_id' => $article->getId(),
                                    'lemma' => $word,
                                    'status' => $key,
                                ]);
                    }   
                    
                    if ($k > 0){
                        $bigram = $this->entityManager->getRepository(Bigram::class)
                                        ->insertBigram($preWord, $word);
                        
                        $this->entityManager->getRepository(Bigram::class)
                                ->insertArticleBigram($article, $bigram);
                    }
                    $preWord = $word;
                }    
            }    
        }    
        $this->entityManager->getRepository(Rawprice::class)
                ->updateRawpriceField($rawprice->getId(), ['status_token' => Rawprice::TOKEN_PARSED]);        
        return;
    }  
    
    /**
     * Проверить флаг обновления токенов
     * 
     * @param integer $articleId
     * @param integer $tokenUpdateFlag
     * @return type
     */
    public function checkUpdateTokenFlag($articleId, $tokenUpdateFlag)
    {
        if ($tokenUpdateFlag != Article::TOKEN_UPDATE_FLAG){
            $this->entityManager->getRepository(Article::class)
                    ->deleteArticleToken($articleId);

            $this->entityManager->getRepository(Article::class)
                    ->deleteArticleBigram($articleId);

            $this->entityManager->getRepository(Article::class)
                    ->deleteArticleTitle($articleId);

            $this->entityManager->getRepository(Article::class)
                    ->updateArticle($articleId, ['token_update_flag' => Article::TOKEN_UPDATE_FLAG]);
        }
        
        return;
    }
    
    /**
     * Выборка токенов из прайса и добавление их в таблицу токенов
     * @param Appllication\Entity\Raw $raw
     */
    public function grabTokenFromRaw($raw)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(900);
        $startTime = time();
        
        $rawpricesQuery = $this->entityManager->getRepository(Token::class)
                ->findRawpriceTitle($raw);
        $iterable = $rawpricesQuery->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $rawprice){
                $article = $rawprice->getCode();
                if ($article){
                    $this->checkUpdateTokenFlag($article->getId(), $article->getTokenUpdateFlag());

                    $title = mb_strtoupper(trim($rawprice->getTitle()), 'UTF-8');
                    $titleMd5 = md5($title);

                    $articleTitle = $this->entityManager->getRepository(\Application\Entity\ArticleTitle::class)
                            ->findOneBy(['article' => $article->getId(), 'titleMd5' => $titleMd5]);

                    if ($articleTitle == null){
                        $this->addNewTokenFromRawprice($rawprice);
                        $this->entityManager->getRepository(Article::class)
                                ->insertArticleTitle(['article_id' => $article->getId(), 'title' => $title, 'title_md5' => $titleMd5]);
                    } else {
                        $this->entityManager->getRepository(Rawprice::class)
                                ->updateRawpriceField($rawprice->getId(), ['status_token' => Rawprice::TOKEN_PARSED]);                        
                    }   
                }    
                
                $this->entityManager->detach($rawprice);
            }    
            
            if (time() > $startTime + 840){
                return;
            }            
        }

        $raw->setParseStage(Raw::STAGE_TOKEN_PARSED);
        $this->entityManager->persist($raw);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Удаление токена
     * 
     * @param \Application\Entity\Token $token
     */
    public function removeToken($token) 
    {   
        
        $this->entityManager->getRepository(Token::class)
                ->deleteArticleToken($token);
        $this->entityManager->getRepository(GoodToken::class)
                ->deleteGoodToken($token);
        $this->entityManager->getConnection()->delete('token_group_token', ['token_id' => $token->getId()]);
        $this->entityManager->getConnection()->delete('generic_group_token', ['token_id' => $token->getId()]);
        $this->entityManager->getConnection()->delete('token', ['id' => $token->getId()]);
    }    
    
    /**
     * Поиск и удаление токенов не привязаных к товарам
     */
    public function removeEmptyToken()
    {
        set_time_limit(900);        
        ini_set('memory_limit', '2048M');
        $startTime = time();
        
        $tokenForDeleteQuery = $this->entityManager->getRepository(Token::class)
                ->findTokenForDelete();
        $iterable = $tokenForDeleteQuery->iterate();
        foreach ($iterable as $row){
            foreach($row as $token){
                $this->removeToken($token);
            }    
            if (time() > $startTime + 840){
                return;
            }
        }
        
        return;
    }

    /**
     * Удаление биграм
     * 
     * @param Bigram $bigram
     */
    public function removeBigram($bigram) 
    {   
        
        $this->entityManager->getRepository(Bigram::class)
                ->deleteArticleBigram($bigram);

        $this->entityManager->getConnection()->delete('bigram', ['id' => $bigram->getId()]);
    }    

    /**
     * Поиск и удаление биграм не привязаных к товарам
     */
    public function removeEmptyBigram()
    {
        set_time_limit(900);        
        ini_set('memory_limit', '2048M');
        $startTime = time();
        
        $bigramForDeleteQuery = $this->entityManager->getRepository(Bigram::class)
                ->findBigramForDelete();
        $iterable = $bigramForDeleteQuery->iterate();
        foreach ($iterable as $row){
            foreach($row as $bigram){
                $this->removeBigram($bigram);
            }    
            if (time() > $startTime + 840){
                return;
            }
        }
        
        return;
    }

    /**
     * Поиск лучшего наименования для товара
     * 
     * @param \Application\Entity\Goods $good
     * @param \Phpml\Classification\KNearestNeighbors $$classifier
     * @return string
     */
    public function findBestName($good, $classifier = null)
    {
        if (!file_exists(Token::ML_TITLE_MODEL_FILE)){
            return;
        }
        
        if ($classifier == null){
            $modelManager = new \Phpml\ModelManager();
            $classifier = $modelManager->restoreFromFile(Token::ML_TITLE_MODEL_FILE);
        }    
        
        $normalizer = new \Phpml\Preprocessing\Normalizer();
        
        $rawprices = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->rawpriceArticles($good);
        $predicted = [];
        $titles = [];
        $mlTitleSamples = [];
        foreach ($rawprices as $rawprice){
            $mlTitleSample = $this->rawpriceToMlTitle($rawprice);
            $titles[] = $rawprice->getTitle();
            if (count($mlTitleSample)){
                $mlTitleSamples[] = $mlTitleSample;
            }    
        }
        if (count($mlTitleSamples)){
            $normalizer->fit($mlTitleSamples);
            $normalizer->transform($mlTitleSamples);
            $predicted = $classifier->predict($mlTitleSamples);
            var_dump($predicted);
            var_dump($titles);
        }    
        return;
    }
    
    /**
     * Средняя длина наименования товара
     */
    private function avgD()
    {
        $goodsCount = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->count([]);
        $goodsTokenCount = $this->entityManager->getRepository(GoodToken::class)
                ->count([]);
        if ($goodsCount){
            return $goodsTokenCount/$goodsCount;
        }
        return 0;
    }
    
    /**
     * Добавить токены товара
     * 
     * @param \Application\Entity\Goods $good
     * @param float avgD
     */
    public function addGoodTokenFromGood($good, $avgD = null, $update = true)
    {        
        if ($update){
            $this->entityManager->getRepository(GoodToken::class)
                    ->deleteTokenGood($good);                    
        }
        
        $tokens = $this->entityManager->getRepository(Token::class)
                ->findGoodsToken($good);
        
        if ($avgD == null){
            $avgD = $this->avgD();
        }        
        $k1 = 2;
        $b = 0.75;
        
        $tokensTf = $this->goodNamesVectorizer($good);

        foreach ($tokens as $token){
            $tf = $tf_idf = null;
            if (isset($tokensTf[$token->getLemma()])){
                $tf = round($tokensTf[$token->getLemma()], 5);
                $tf_idf = round($token->getIdf() * ($tf * ($k1 + 1))/($tf + $k1 * (1 - $b + $b * (count($tokensTf)/$avgD))), 5);
            }    
            $goodToken = $this->entityManager->getRepository(GoodToken::class)
                ->findOneBy(['good' => $good->getId(), 'lemma' => $token->getLemma()]);

            if (!$goodToken){
                $this->entityManager->getRepository(GoodToken::class)
                        ->insertGoodToken([
                            'good_id' => $good->getId(),
                            'lemma' => $token->getLemma(),
                            'status' => $token->getStatus(),
                            'tf' => $tf,
                            'tf_idf' => $tf_idf,
                        ]);

            } else {
                if ($tf_idf != $goodToken->getTfIdf()){
                    $this->entityManager->getRepository(GoodToken::class)
                            ->updateGoodToken(
                                    $goodToken,
                                    [
                                        'tf' => $tf,
                                        'tf_idf' => $tf_idf,
                                    ]);                    
                }
            }
        }    
        return;
    }
    
    /**
     * Получить строку наименований товара
     * 
     * @param \Application\Entity\Goods $good
     */
    public function goodTitlesIds($good)
    {
        $articleTitles = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->findArticleTitles($good);
        
        if (count($articleTitles)){
            $articleTitleIds = [];
            foreach ($articleTitles as $articleTitle){
                $articleTitleIds[] = $articleTitle['id'];
            } 

            $idsFilter = new IdsFormat();
            return $idsFilter->filter($articleTitleIds);
        }    
    }
    
    /**
     * Выборка токенов товара из прайса и добавление их в таблицу токенов
     * @param Raw $raw
     */
    public function grabGoodTokenFromRaw($raw)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(900);
        $startTime = time();

        $avgD = $this->avgD();
        
        $rawpricesQuery = $this->entityManager->getRepository(Token::class)
                ->findGoodTokenForParse($raw);
        
        $iterable = $rawpricesQuery->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $rawprice){
                $good = $rawprice->getGood();
                if ($good){
                    $goodTitleStr = $this->goodTitlesIds($good);
                    $goodTitleStrMd5 = md5($goodTitleStr);

                    if ($goodTitleStr){ 
                        $goodTitle = $this->entityManager->getRepository(\Application\Entity\GoodTitle::class)
                                ->findOneBy(['good' => $good->getId(), 'titleMd5' => $goodTitleStrMd5]);

                        if ($goodTitle == null){
                            
                            $this->entityManager->getRepository(\Application\Entity\Goods::class)
                                    ->removeGoodTitles($good);
                            
                            $this->addGoodTokenFromGood($good, $avgD);

                            $this->entityManager->getRepository(\Application\Entity\Goods::class)
                                    ->insertGoodTitle(['good_id' => $good->getId(), 'title' => $goodTitleStr, 'title_md5' => $goodTitleStrMd5]);
                        }    
                    }    
                }    

                $this->entityManager->getRepository(Rawprice::class)
                        ->updateRawpriceField($rawprice->getId(), ['status_token' => Rawprice::TOKEN_GOOD_PARSED]);
                
                $this->entityManager->detach($rawprice);
            }
            if (time() > $startTime + 840){
                return;
            }            
        }
        
        $raw->setParseStage(Raw::STAGE_GOOD_TOKEN);
        $this->entityManager->persist($raw);
        $this->entityManager->flush();
        
        return;
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
        
        $dictTokens = $this->entityManager->getRepository(Token::class)
                ->findTokenGoodsByStatus($good);
        
//        var_dump(count($dictTokens)); exit;
        if (count($dictTokens) == 0){
            $this->entityManager->getRepository(\Application\Entity\Goods::class)
                    ->updateGoodId($good->getId(), ['token_group_id' => null]);
            return;
        }
        
        $tokenIds = [];
        $tokenLemms = [];
        foreach ($dictTokens as $token){
            $tokenIds[] = $token['id'];
            $tokenLemms[] = $token['lemma'];
        }
        
        $idsFilter = new IdsFormat();
        $tokenIdsStr = md5($idsFilter->filter($tokenIds));
        
        $lemmsFilter = new IdsFormat(['separator' => ' ']);
        $tokenLemmsStr = $lemmsFilter->filter($tokenLemms);
        
        $tokenGroup = $this->entityManager->getRepository(TokenGroup::class)
            ->findTokenGroupByTokens($dictTokens);
        
        if (!$tokenGroup){
            $this->entityManager->getRepository(TokenGroup::class)
                    ->insertTokenGroup([
                        'name' => '',
                        'lemms' => $tokenLemmsStr,
                        'ids' => $tokenIdsStr,
                        'good_count' => 0,
                    ]);

            $tokenGroup = $this->entityManager->getRepository(TokenGroup::class)
                ->findOneByIds($tokenIdsStr);

            foreach($dictTokens as $token){
                $this->entityManager->getRepository(TokenGroup::class)
                        ->insertTokenGroupToken([
                            'token_group_id' => $tokenGroup->getId(),
                            'token_id' => $token['id'],
                        ]);
            }                
        }    
        
        if ($tokenGroup){
            $tokenGroup->addGood($good);
        }
        
        $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->updateGoodId($good->getId(), ['token_group_id' => $tokenGroup->getId()]);
                
        return $tokenGroup;
    }
    
    
    /**
     * Выборка токенов из прайса и добавление их в таблицу токенов
     * @param Raw $raw
     */
    public function grabTokenGroupFromRaw($raw)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(900);
        $startTime = time();
        
        $rawpricesQuery = $this->entityManager->getRepository(Token::class)
                ->findTokenGroupsForAccembly($raw);
        $iterable = $rawpricesQuery->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $rawprice){
                $this->addGroupTokenFromGood($rawprice->getGood());
                $this->entityManager->getRepository(Rawprice::class)
                        ->updateRawpriceField($rawprice->getId(), ['status_token' => Rawprice::TOKEN_GROUP_PARSED]); 
                $this->entityManager->detach($rawprice);
            }
            if (time() > $startTime + 840){
                return;
            }            
        }
        
        $raw->setParseStage(Raw::STAGE_TOKEN_GROUP_PARSED);
        $this->entityManager->persist($raw);
        $this->entityManager->flush();
        
        return;
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
     * @param integer $tokenGroupId
     * @param integer $goodCount
     */
    public function updateTokenGroupGoodCount($tokenGroupId, $goodCount = null)
    {
        if ($goodCount === null){
            $goodCount = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                    ->count(['tokenGroup' => $tokenGroupId]);
        }    
        
        $this->entityManager->getRepository(TokenGroup::class)
                ->updateTokenGroup($tokenGroupId, ['goodCount' => $goodCount]);
        
    }
    
    /**
     * Обновление количества товара у всех групп наименований
     */
    public function updateAllTokenGroupGoodCount()
    {
        $tokenGroups = $this->entityManager->getRepository(TokenGroup::class)
                ->goodCountAllTokenGroup();

        foreach ($tokenGroups as $tokenGroup){
            $goodCount = ($tokenGroup['goodCount'] == null) ? 0:$tokenGroup['goodCount']; 
            $this->updateTokenGroupGoodCount($tokenGroup['id'], $goodCount);
        }   
    }
    
    
    
    /**
     * Удаление TokenGroup
     * 
     * @param \Application\Entity\TokenGroup $tokenGroup
     */
    public function removeTokenGroup($tokenGroup)
    {
        $goods = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->findByTokenGroup($tokenGroup->getId());
        foreach ($goods as $good){
            $this->entityManager->getConnection()->update('goods', ['token_group_id' => null], ['id' => $good->getId()]);
        }
        
        $this->entityManager->getConnection()->delete('token_group_token', ['token_group_id' => $tokenGroup->getId()]);
        
        $this->entityManager->getConnection()->delete('token_group', ['id' => $tokenGroup->getId()]);
    }
    
    /**
     * Поиск и удаление пустых групп наименований
     */
    public function removeEmptyTokenGroup()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(900);
        $startTime = time();
        
        $tokenGroups = $this->entityManager->getRepository(TokenGroup::class)
                ->findBy(['goodCount' => 0]);

        foreach ($tokenGroups as $tokenGroup){
            $this->removeTokenGroup($tokenGroup);
            if (time() > $startTime + 840){
                return;
            }
        }
        
        return;
    }    
    
    
    /**
     * Подготовка наименований товара
     * 
     * @param \Application\Entity\Goods $good
     */
    public function goodNames($good)
    {
        $result = [];
        $rawprices = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->rawpriceArticles($good);

        foreach($rawprices as $rawprice){
            $lemms = $this->lemmsFromRawprice($rawprice);
            foreach ($lemms as $lemma){
                foreach ($lemma as $key => $value){
                    $result[] = $value;
                }    
            }
        }
        return [implode(' ', $result)];
    }
    
    /**
     * Векторизация наименований товара
     * 
     * @param \Application\Entity\Goods $good
     * @return array
     */
    public function goodNamesVectorizer($good)
    {
        $words = $this->goodNames($good);
        $vectorizer = new TokenCountVectorizer(new WhitespaceTokenizer());
        $vectorizer->fit($words);
        $vectorizer->transform($words);
        $vocabular = $vectorizer->getVocabulary();
        $wordCount = array_sum($words[0]);
        $result = [];
        foreach($vocabular as $key => $value){
            $result[$value] = $words[0][$key]/$wordCount;
        }            
        return $result;
    }
    
    /**
     * Средняя частота строки
     * 
     * @param string $str
     * @param string $article
     * @param string $producer Description
     * @return array
     */
    public function meanFrequency($str, $article = null, $producer = null)
    {
        $result = [
            Token::IS_DICT => ['mean' => 0, 'sd' => 0, 'sum' => 0], 
            Token::IS_RU => ['mean' => 0, 'sd' => 0, 'sum' => 0],
            Token::IS_RU_1 => ['mean' => 0, 'sd' => 0, 'sum' => 0],
            Token::IS_RU_ABBR => ['mean' => 0, 'sd' => 0, 'sum' => 0],
            Token::IS_EN_DICT => ['mean' => 0, 'sd' => 0, 'sum' => 0],
            Token::IS_EN => ['mean' => 0, 'sd' => 0, 'sum' => 0],
            Token::IS_EN_1 => ['mean' => 0, 'sd' => 0, 'sum' => 0],
            Token::IS_EN_ABBR => ['mean' => 0, 'sd' => 0, 'sum' => 0],
            Token::IS_NUMERIC => ['mean' => 0, 'sd' => 0, 'sum' => 0], 
            Token::IS_ARTICLE => 0,
            Token::IS_PRODUCER => 0,
            Token::IS_UNKNOWN => 0,
        ];
        
        $lemms = $this->lemmsFromStr($str);
        foreach ($lemms as $key => $words){
            $words = array_filter($words);
            $frequencies = [];
            foreach ($words as $word){
                $token = $this->entityManager->getRepository(Token::class)
                        ->findOneByLemma($word);
                if ($token){
                    $frequencies[] = $token->getFrequency();
                }    
            }
            $result[$key]['sum'] = array_sum($frequencies);
            if (count($frequencies)){
                $result[$key]['mean'] = round(\Phpml\Math\Statistic\Mean::arithmetic($frequencies));
                if (count($frequencies) > 1){
                    $result[$key]['sd'] = round(\Phpml\Math\Statistic\StandardDeviation::population($frequencies));                    
                }
            }    
        }    
        
        $stru = iconv('UTF-8', 'UTF-8//IGNORE', $str);
        $punktuation = mb_ereg_replace('[A-ZА-ЯЁ0-9 .,/->-+()]', '', mb_strtoupper($stru));
        if ($punktuation){
//            var_dump($punktuation);
            $result[Token::IS_UNKNOWN] = count(array_unique(str_split($punktuation)));
        }    
        
        $toIntFilter = new \Zend\Filter\ToInt();
        if ($article){
            $result[Token::IS_ARTICLE] = $toIntFilter->filter(mb_stripos($str, $article) !== FALSE);
        }
        if ($producer){
            $producerFilter = new \Application\Filter\ProducerName();
            $result[Token::IS_PRODUCER] = $toIntFilter->filter(mb_stripos($producerFilter->filter($str), $producerFilter->filter($producer)) !== FALSE);
        }
        
        return $result;
    }
    
    /**
     * Характеристики наименованя из строки прайса
     * 
     * @param Rawprice $rawprice
     * @return array
     */
    public function rawpriceToMlTitle($rawprice)
    {
        $gc = 1035342;
        $result = [];               
        $lemms = $this->lemmsFromRawprice($rawprice);
        $preWord = $preToken = null;
        foreach ($lemms as $k => $words){
            foreach ($words as $key => $word){
                $token = $this->entityManager->getRepository(Token::class)
                        ->findOneByLemma($word);
                if ($token){
                    if ($token->getStatus() === Token::IS_DICT){
//                        $result[$token->getIdf().'_'.$token->getId()] = $token;
                    }    
                } 
                if ($k > 0){
                    $bigram = $this->entityManager->getRepository(Bigram::class)
                            ->findBigram($preWord, $word);
                    if ($bigram){
//                        if ($bigram->getFrequency()>5){
                            $tf1 = $tf2 = 1;
                            if ($preToken->getStatus() == Token::IS_DICT){
                                $tf1 = $preToken->getFrequency();
                            }    
                            if ($token->getStatus() == Token::IS_DICT){
                                $tf2 = $token->getFrequency();
                            }    
                            
                            $atf = ($tf1 + $tf2)/2/$gc;
                            $bf = $bigram->getFrequency()/$gc;

                            $pwt = log($bf/$atf);

                            if (in_array($bigram->getStatus(), [Bigram::RU_RU, Bigram::RU_EN, Bigram::RU_NUM])){
                                $result[] = ['bf' => $bf, 'pwt' => $pwt, 'token1' => $preToken, 'token2' => $token, 'bigram' => $bigram];
                            }    
//                        }    
                    }    
                }
                $preWord = $word;
                $preToken = $token;
            }
        }
        
        if ($k == 0){
            $tf1 = $tf2 = 1;
            if ($token->getStatus() == Token::IS_DICT){
                $tf1 = $token->getFrequency();
                $tf2 = $token->getFrequency();
            }    
            $atf = ($tf1 + $tf2)/2/$gc;
            $bf = $token->getFrequency()/$gc;

            $pwt = log($bf/$atf);
            $result[] = ['bf' => $bf, 'pwt' => $pwt, 'token1' => $token, 'token2' => $token];            
        }
//        ksort($result);
        usort($result, function($a, $b){
            if ($a['bf'] == $b['bf']) {
                return 0;
            }
            return ($a['bf'] > $b['bf']) ? -1 : 1;            
        }); 
        
        $result = array_slice($result, 0, 6, true);
        $empt = array_fill(200, 6 - count($result), false);
//        var_dump($empt);
        return array_merge($result, $empt);
    }    
}
