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
use Application\Entity\Goods;
use Application\Entity\GoodTitle;
use Application\Entity\Car;
use Application\Entity\Oem;

use Phpml\Tokenization\WhitespaceTokenizer;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Association\Apriori;
use Application\Filter\NameTokenizer;
use Application\Filter\Lemma;
use Application\Filter\Tokenizer;
use Application\Filter\IdsFormat;
use Application\Validator\IsRU;
use Application\Validator\IsEN;
use Application\Validator\IsNUM;
use Application\Filter\ArticleCode;
use Application\Filter\ProducerName;
use Application\Filter\ModelName;
use Admin\Filter\TransferName;

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
     * Восстановить статус токена
     * 
     * @param Token $token
     * @return null
     */
    public function resetTokenStatus($token)
    {
        $isRu = new IsRU();
        $isEn = new IsEN();
        $isNum = new IsNUM();
        
        $status = $token->getStatus();
        if ($isNum->isValid($token->getLemma())){
            $status = Token::IS_NUMERIC;
        } else {
            if ($isRu->isValid($token->getLemma())){
                if (mb_strlen($token->getLemma()) == 1){
                    $status = Token::IS_RU_1;
                } else {
                    $status = Token::IS_RU;                    
                }    
            }
            if ($isEn->isValid($token->getLemma())){
                if (mb_strlen($token->getLemma()) == 1){
                    $status = Token::IS_EN_1;
                } else {
                    $status = Token::IS_EN;                    
                }            
            }
        }                          
        
        if ($status != $token->getStatus()){
            $token->setStatus($status);
            $token->setCorrect(null);
            $this->entityManager->persist($token);
            $this->entityManager->flush($token);
        }
        
        $this->entityManager->getRepository(Article::class)
                ->updateTokenUpdateFlag($token->getLemma());
        
        return;
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
        $oldCorrects = $token->getCorrectAsArray();
        if ($oldCorrects){
            foreach ($oldCorrects as $oldLemma){
                $this->entityManager->getRepository(Article::class)
                        ->updateTokenUpdateFlag($oldLemma);                
            }
        }

        $token->setCorrect($correctStr);
                
        $ruValidator = new IsRU();
        if ($correctStr){
            if ($ruValidator->isValid($correctStr)){
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
     * Поставить слову метку аббревиатуры
     * 
     * @param Token $token
     * @param integer $newStatus
     */
    public function changeTokenStatus($token, $newStatus)
    {
        ini_set('memory_limit', '2048M');
        
        if ($newStatus > 0){
            if ($token->getStatus() != $newStatus){
                $word = $token->getLemma();

                $this->entityManager->getRepository(Token::class)
                        ->updateToken($word, ['status' => $newStatus]);

                $this->entityManager->getRepository(Article::class)
                        ->updateTokenUpdateFlag($word);
            }    
        }    
        
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
            $goods = $this->entityManager->getRepository(Goods::class)
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
        
        $goods = $this->entityManager->getRepository(Goods::class)
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
     * Обновить исправление bigram
     * 
     * @param Bigram $bigram
     * @param string $correctStr
     */
    public function updateBigramCorrect($bigram, $correctStr = null)
    {
        $bigram->setCorrect($correctStr);
        $bilemms = explode(' ', $correctStr); 
        $token2 = null;
        if (isset($bilemms[1])){
            $token2 = $bilemms[1];
        }
        $bigram->setStatus($this->entityManager->getRepository(Bigram::class)->biStatus($bilemms[0], $token2));        
        
        $this->entityManager->persist($bigram);
        $this->entityManager->flush($bigram);

        $this->entityManager->getRepository(Article::class)
                ->updateBigramUpdateFlag($bigram);
        
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
            $goods = $this->entityManager->getRepository(Goods::class)
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
        
        $goods = $this->entityManager->getRepository(Goods::class)
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
        $preWord = $preToken = $token = null;
        $k = 0;
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
                        $token = $this->entityManager->getRepository(Token::class)
                                ->findOneByLemma($word);
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
                        $flag = Bigram::WHITE_LIST;
                        if ($token && $preToken){
                            $flag = max($token->getFlag(), $preToken->getFlag());
                        }
                        $bigram = $this->entityManager->getRepository(Bigram::class)
                                        ->insertBigram($preWord, $word, $flag);
                        
                        $this->entityManager->getRepository(Bigram::class)
                                ->insertArticleBigram($article, $bigram);
                    }
                    $preWord = $word;
                    $preToken = $token;
                }    
            }    
        }    
        if ($k == 0 && $token){
            $bigram = $this->entityManager->getRepository(Bigram::class)
                            ->insertBigram($token->getLemma(), null, $token->getFlag());

            $this->entityManager->getRepository(Bigram::class)
                    ->insertArticleBigram($article, $bigram);
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
                        $this->entityManager->getRepository(Article::class)
                                ->deleteArticleTitle($article->getId());
                        
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
     * Средняя длина наименования товара
     */
    private function avgD()
    {
        $goodsCount = $this->entityManager->getRepository(Goods::class)
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
     * @param Goods $good
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
     * @param Goods $good
     */
    public function goodTitlesIds($good)
    {
        $articleTitles = $this->entityManager->getRepository(Goods::class)
                ->findArticleTitles($good);
        
        if (count($articleTitles)){
            $articleTitleIds = [];
            foreach ($articleTitles as $articleTitle){
                $articleTitleIds[] = $articleTitle['id'];
            } 

            $idsFilter = new IdsFormat();
            return $idsFilter->filter($articleTitleIds);
        } 
        return;
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
//                $good = $rawprice->getGood();
//                if ($good){
//                    $goodTitleStr = $this->goodTitlesIds($good);
//                    $goodTitleStrMd5 = md5($goodTitleStr);
//
//                    if ($goodTitleStr){ 
//                        $goodTitle = $this->entityManager->getRepository(GoodTitle::class)
//                                ->findOneBy(['good' => $good->getId(), 'titleMd5' => $goodTitleStrMd5]);
//
//                        if ($goodTitle == null){
//                            
//                            $this->entityManager->getRepository(Goods::class)
//                                    ->removeGoodTitles($good);
//                            
//                            $this->addGoodTokenFromGood($good, $avgD);
//
//                            $this->entityManager->getRepository(Goods::class)
//                                    ->insertGoodTitle(['good_id' => $good->getId(), 'title' => $goodTitleStr, 'title_md5' => $goodTitleStrMd5]);
//                        }    
//                    }    
//                }    

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
     * Проверить флаг обновления групп токенов
     * 
     * @param Goods $good
     * @param integer $groupTokenUpdateFlag
     * @return type
     */
    public function checkUpdateGroupTokenFlag($good, $groupTokenUpdateFlag)
    {
        if ($groupTokenUpdateFlag != Goods::GROUP_TOKEN_UPDATE_FLAG){
            
            $this->entityManager->getRepository(Goods::class)
                    ->removeGoodTitles($good);

            $this->entityManager->getRepository(Goods::class)
                    ->updateGood($good, ['groupTokenUpdateFlag' => Goods::GROUP_TOKEN_UPDATE_FLAG]);
        }
        
        return;
    }
    
    /**
     * Добавить группу наименований по токенам товара
     * 
     * @param Application\Entity\Goods $good
     * @param integer $gc
     * 
     * @return Application\Entity\TokenGroup Description
     */
    public function addGroupTokenFromGood($good, $gc = null)
    {
        if (!$gc){
            $gc = $this->entityManager->getRepository(Goods::class)
                    ->count([]);
        }
        
        $dictTokens = $this->goodSignTokens($good, $gc);
        
//        var_dump(count($dictTokens)); exit;
        if ($dictTokens){
            if (isset($dictTokens['tokens'])){
                if (count($dictTokens['tokens']) > 0){
        
                    $tokenIds = [];
                    $tokenLemms = [];
                    foreach ($dictTokens['tokens'] as $token){
                        $tokenIds[] = $token->getId();
                        $tokenLemms[] = $token->getLemma();
                    }

                    $idsFilter = new IdsFormat();
                    $tokenIdsStr = md5($idsFilter->filter($tokenIds));

                    $lemmsFilter = new IdsFormat(['separator' => ' ']);
                    $tokenLemmsStr = $lemmsFilter->filter($tokenLemms);

                    $tokenGroup = $this->entityManager->getRepository(TokenGroup::class)
                        ->findOneByIds($tokenIdsStr);

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

                        foreach($dictTokens['tokens'] as $token){
                            $this->entityManager->getRepository(TokenGroup::class)
                                    ->insertTokenGroupToken([
                                        'token_group_id' => $tokenGroup->getId(),
                                        'token_id' => $token->getId(),
                                    ]);
                        }                
                    }    

                    if ($tokenGroup){
                        $this->entityManager->getRepository(Goods::class)
                                ->updateGoodId($good->getId(), ['token_group_id' => $tokenGroup->getId()]);
                    }    

                    return $tokenGroup;
                }
            }
        }    
        $this->entityManager->getRepository(Goods::class)
                ->updateGoodId($good->getId(), ['token_group_id' => null]);
        return;
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
        
        $gc = $this->entityManager->getRepository(Goods::class)
                ->count([]);
        
        $rawpricesQuery = $this->entityManager->getRepository(Token::class)
                ->findTokenGroupsForAccembly($raw);
        $iterable = $rawpricesQuery->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $rawprice){
                try {
                    $good = $rawprice->getGood();           
                    $groupTokenUpdateFlag = $good->getGroupTokenUpdateFlag();
                } catch (\Doctrine\ORM\EntityNotFoundException $e){
                    $good = null;
                }
                
                if ($good){
                    $this->checkUpdateGroupTokenFlag($good, $groupTokenUpdateFlag);

                    $goodTitleStr = $this->goodTitlesIds($good);
                    $goodTitleStrMd5 = md5($goodTitleStr);

                    if ($goodTitleStr){ 
                        $goodTitle = $this->entityManager->getRepository(GoodTitle::class)
                                ->findOneBy(['good' => $good->getId(), 'titleMd5' => $goodTitleStrMd5]);

                        if ($goodTitle == null){

                            $this->entityManager->getRepository(Goods::class)
                                    ->removeGoodTitles($good);

                            $this->addGroupTokenFromGood($good, $gc);

                            $this->entityManager->getRepository(Goods::class)
                                    ->insertGoodTitle(['good_id' => $good->getId(), 'title' => $goodTitleStr, 'title_md5' => $goodTitleStrMd5]);
                        }
                    }
                }    
                
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
            $goodCount = $this->entityManager->getRepository(Goods::class)
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
        $goods = $this->entityManager->getRepository(Goods::class)
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
     * @param Goods $good
     */
    public function goodNames($good)
    {
        $result = [];
        $rawprices = $this->entityManager->getRepository(Goods::class)
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
     * @param Goods $good
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
     * Заголовок товара разбить на значимые токены 
     * 
     * @param Rawprice $rawprice
     * @param integer $gc
     * 
     * @return array
     */
    public function titleToToken($rawprice, $gc = null)
    {
        if (!$gc){
            $gc = $this->entityManager->getRepository(Goods::class)
                    ->count([]);
        }
        
        $result = [];
        $lemms = $this->lemmsFromRawprice($rawprice);
        $preWord = $preToken = $token = null;
        $k = 0;
        foreach ($lemms as $k => $words){
            foreach ($words as $key => $word){
                $token = $this->entityManager->getRepository(Token::class)
                        ->findOneByLemma($word);

                if ($k > 0 && $token && $preToken){
                    $bigram = $this->entityManager->getRepository(Bigram::class)
                            ->findBigram($preWord, $word);
                    if ($bigram && $preToken->getFrequency() > 0 && $token->getFrequency() > 0){
                        if (in_array($bigram->getStatus(), [Bigram::RU_RU, Bigram::RU_EN, Bigram::RU_NUM])){
                            $pmi = log($bigram->getFrequency()*$gc*$bigram->getStatus()/($preToken->getFrequency()*$token->getFrequency()*$k));
                            if ($pmi < 0 
                                    || $bigram->getFlag() != Bigram::WHITE_LIST
                                    || $bigram->getFrequency() < 10){
                                $pmi = 0;
                            }
                            $result[] = ['pmi' => $pmi,  'token1' => $preToken, 'token2' => $token, 'bigram' => $bigram];
                        }    
                    }    
                }
                $preWord = $word;
                $preToken = $token;
            }
        }
        if ($k == 0 && $token){
            $bigram = $this->entityManager->getRepository(Bigram::class)
                            ->findBigram($token->getLemma());

            if ($bigram){
                if (in_array($bigram->getStatus(), [Bigram::RU_RU, Bigram::RU_EN, Bigram::RU_NUM])){
                    $pmi = log(($bigram->getFrequency()*$gc*$bigram->getStatus())/($token->getFrequency()*2));
                    if ($pmi < 0 
                            || $bigram->getFlag() != Bigram::WHITE_LIST
                            || $bigram->getFrequency() < 10){
                        $pmi = 0;
                    }
                    $result[] = ['pmi' => $pmi, 'token1' => $token, 'bigram' => $bigram];
                }    
            }    
        }
        
        usort($result, function($a, $b){
            if ($a['pmi'] == $b['pmi']) {
                return 0;
            }
            return ($a['pmi'] > $b['pmi']) ? -1 : 1;            
        }); 
        
        return array_slice($result, 0, 10, true);
    }
    
    
    /**
     * Получить значимые токены наименования
     * 
     * @param array $rawpriceTokens
     * 
     * @return array
     */
    public function signTokens($rawpriceTokens)
    {
        $result = [];
        foreach ($rawpriceTokens as $signToken){
            if ($signToken['pmi'] > 0){
                if ($signToken['token1']->getStatus() == Token::IS_DICT && $signToken['token1']->getFlag() == Token::WHITE_LIST){
                    $result[$signToken['token1']->getId()] = $signToken['token1'];
                }
                if (isset($signToken['token2'])){
                    if ($signToken['token2']->getStatus() == Token::IS_DICT && $signToken['token2']->getFlag() == Token::WHITE_LIST){
                        $result[$signToken['token2']->getId()] = $signToken['token2'];
                    }
                }    
            }
        }
        ksort($result);
        return $result;
    }
    
    /**
     * Токены наименований товара
     * 
     * @param Goods $good
     * @param integer $gc
     * @return array
     */
    public function goodSignTokens($good, $gc = null)
    {
        $result = [];
        if (!$gc){
            $gc = $this->entityManager->getRepository(Goods::class)
                    ->count([]);
        }
        $rawprices = $this->entityManager->getRepository(Goods::class)
                        ->rawpriceArticles($good);
        
        $idsFilter = new IdsFormat();
        $maxK = 0;
        foreach ($rawprices as $rawprice){
            $rawpriceTokens = $this->titleToToken($rawprice, $gc);
            $tokens = $this->signTokens($rawpriceTokens);
            $tokenStr = [];
            $tokenId = [];
            foreach ($tokens as $token){
                $tokenStr[] = $token->getLemma();
                $tokenId[] = $token->getId();
            }
            if (count($tokenId)){
                $ids = md5($idsFilter->filter($tokenId));
                if (array_key_exists($ids, $result)){
                    $result[$ids]['k'] += 1;
                } else {
                    $result[$ids] = [
                        'k' => 1, 
                        'title' => $rawprice->getTitle(),
                        'tokenCount' => count($tokenStr),
                        'tokenStr' => implode(' ', $tokenStr),
                        'tokens' => $tokens,
                    ];
                }    
                if ($maxK < $result[$ids]['k']){
                    $maxK = $result[$ids]['k'];
                }
            }    
        }
        
        if (count($result) > 1){
            $maxResult = array_filter($result, function($v) use($maxK){
                    return $v['k'] == $maxK;
                });
            if (count($maxResult) > 1){
                $maxK = 0;
                foreach ($maxResult as $key => $value){
                    $tokenGroup = $this->entityManager->getRepository(TokenGroup::class)
                            ->findOneByIds($key);
                    $maxResult[$key]['k'] = 0;
                    if ($tokenGroup){
                        $maxResult[$key]['k'] = $tokenGroup->getGoodCount();
                        if ($maxK < $maxResult[$key]['k']){
                            $maxK = $maxResult[$key]['k'];
                        }
                    }    
                }
                $maxMaxResults = array_filter($maxResult, function($v)  use($maxK){
                    return $v['k'] == $maxK;
                });  
                
                usort($maxMaxResults, function($a, $b){
                    if ($a['tokenCount'] == $b['tokenCount']) {
                        return 0;
                    }
                    return ($a['tokenCount'] < $b['tokenCount']) ? -1 : 1;            
                }); 
                
                return array_shift($maxMaxResults);
            }    
            return array_shift($maxResult);    
        }
        
        return array_shift($result);
    }
    
    public function aprioriTokens($signTokens)
    {
        usort($signTokens, function($a, $b){
            if ($a['tokenCount'] == $b['tokenCount']) {
                return 0;
            }
            return ($a['tokenCount'] > $b['tokenCount']) ? -1 : 1;            
        }); 
        
        $associator = new Apriori($support = 0.5, $confidence = 0.5);
        $labels = [];
        $samples = [];
        foreach ($signTokens as $signToken){
            $samples[] = explode(' ', $signToken['tokenStr']);
            $associator->train($samples, $labels);
        }
        
        return $associator;
    }
    
    /**
     * Характеристики наименованя из строки прайса
     * 
     * @param Rawprice $rawprice
     * @return array
     */
    public function rawpriceToMlTitle($rawprice)
    {
        $result = $this->titleToToken($rawprice, 1040000);
        $empt = array_fill(200, 10 - count($result), false);
//        var_dump($empt);
        return array_merge($result, $empt);
    }    
    
    
    /**
     * Марка из ОЕ
     * 
     * @param Goods $good
     * 
     */
    protected function oeCar($good)
    {
        return $this->entityManager->getRepository(Oem::class)
                ->cars($good);
    }
    
    /**
     * Аттрибуты машины
     * 
     * @param Car $car
     * @param array $params
     * @return array
     */
    protected function extraCarAttr($car, $params)
    {
        $manu = $car->getModel()->getMake()->getDisplayName();
        $model = $car->getModel()->getDisplayName();
        
        $body = $this->entityManager->getRepository(Car::class)
                ->carDetailValue($car, 'constructionType');
        $litres = $this->entityManager->getRepository(Car::class)
                ->carDetailValue($car, 'cylinderCapacityLiter');
        $cfrom = $this->entityManager->getRepository(Car::class)
                ->carDetailValue($car, 'yearOfConstrFrom');
        $cto = $this->entityManager->getRepository(Car::class)
                ->carDetailValue($car, 'yearOfConstrTo');
        $fuel = $this->entityManager->getRepository(Car::class)
                ->carDetailValue($car, 'motorType');
        $type = $this->entityManager->getRepository(Car::class)
                ->carDetailValue($car, 'typeName');
                
        $modelNameFilter = new ModelName(['body' => $body]);
        $transferFilter = new TransferName();
        $result = [
            $transferFilter->filter($manu) => [
                ' '.$modelNameFilter->filter($model) => [
                    ' '.$type => [
                        'litres' => (string) round($litres/100, 1),
                        'from' => substr($cfrom, 0, 4),
                        'cto' => substr($cto, 0, 4),
                        'fuel' => $fuel,                        
                    ],                            
                ],                    
            ],
        ];
        
        return $result;
    }
    /**
     * Часть наименования - машины
     * @param Goods $good
     * @return string
     */
    public function carPart($good)
    {
        $result = [];
        
        $query = $this->entityManager->getRepository(Goods::class)
                        ->findCars($good, ['sort' => 'goodCount', 'order' => 'DESC', 'limit' => 100]);
        $cars = $query->getResult();
        foreach ($cars as $car){
            $data = $this->extraCarAttr($car, []);
//            $result = array_merge_recursive($result, $data);
            foreach ($data as $make => $makeValue){
                if (!array_key_exists($make, $result)){
                    $result[$make]['B']['litresMin'] = 9999;
                    $result[$make]['B']['litresMax'] = 0;
                    $result[$make]['D']['litresMin'] = 9999;
                    $result[$make]['D']['litresMax'] = 0;
                    $result[$make]['from'] = 9999;
                    $result[$make]['cto'] = 0;
                    $result[$make]['models'] = [];
                }
                foreach ($makeValue as $model => $modelValue){
                    $result[$make]['models'][$model] = $model;
                    foreach ($modelValue as $typeValue){
                        if ($typeValue['fuel'] == 'Дизель'){
                            if (isset($result[$make]['D']['litresMin'])){
                                $result[$make]['D']['litresMin'] = min($result[$make]['D']['litresMin'], $typeValue['litres']);
                            } else {
                                $result[$make]['D']['litresMin'] = $typeValue['litres'];                                
                            }
                            if (isset($result[$make]['D']['litresMax'])){
                                $result[$make]['D']['litresMax'] = max($result[$make]['D']['litresMax'], $typeValue['litres']);
                            } else {
                                $result[$make]['D']['litresMax'] = $typeValue['litres'];                                                                
                            }    
                        } else {
                            if (isset($result[$make]['B']['litresMin'])){
                                $result[$make]['B']['litresMin'] = min($result[$make]['B']['litresMin'], $typeValue['litres']);
                            } else {
                                $result[$make]['B']['litresMin'] = $typeValue['litres'];                                
                            }
                            if (isset($result[$make]['B']['litresMax'])){
                                $result[$make]['B']['litresMax'] = max($result[$make]['B']['litresMax'], $typeValue['litres']);
                            } else {
                                $result[$make]['B']['litresMax'] = $typeValue['litres'];                                                                
                            }    
                        }    
                        $result[$make]['from'] = min($result[$make]['from'], $typeValue['from']);
                        $result[$make]['cto'] = max($result[$make]['cto'], $typeValue['cto']);
                    }
                }
                
                if (isset($result[$make]['B']['litresMin'])){
                    if ($result[$make]['B']['litresMin'] == 9999){
                        unset($result[$make]['B']['litresMin']);
                    }
                }    
                if (isset($result[$make]['B']['litresMax'])){
                    if ($result[$make]['B']['litresMax'] == 0){
                        unset($result[$make]['B']['litresMax']);
                    }
                }    
                if (isset($result[$make]['D']['litresMin'])){
                    if ($result[$make]['D']['litresMin'] == 9999){
                        unset($result[$make]['D']['litresMin']);
                    }
                }    
                if (isset($result[$make]['D']['litresMax'])){
                    if ($result[$make]['D']['litresMax'] == 0){
                        unset($result[$make]['D']['litresMax']);
                    }
                }    
                if (isset($result[$make]['cto'])){
                    if ($result[$make]['cto'] == 0){
                        unset($result[$make]['cto']);
                    }
                }    
                if (isset($result[$make]['from'])){
                    if ($result[$make]['from'] == date('Y')){
                        unset($result[$make]['from']);
                    }
                }    
            }
        }
                        
        return $result;
    }
    
    /**
     * Часть наименования машины как строка
     * 
     * @param array $carPart
     * @param array $options
     * @return string
     */
    public function carPartStr($carPart, $options = null)
    {
        $makeSeparator = ',';
        $modelSeparator = '/';
        $litresSeparator = '-';
        $yearSeparator = '-';
        $partSeparator = ' ';
        $partMaxLength = 150;
        
        if (is_array($options)){
            if (isset($options['makeSeparator'])){
                $makeSeparator = $options['makeSeparator'];
            }
            if (isset($options['modelSeparator'])){
                $modelSeparator = $options['modelSeparator'];
            }
            if (isset($options['litresSeparator'])){
                $litresSeparator = $options['litresSeparator'];
            }
            if (isset($options['yearSeparator'])){
                $yearSeparator = $options['yearSeparator'];
            }
            if (isset($options['partSeparator'])){
                $partSeparator = $options['partSeparator'];
            }
            if (isset($options['partMaxLength'])){
                $partMaxLength = $options['partMaxLength'];
            }
        }
                
        $makeNames = [];
        foreach ($carPart as $make => $makeValue){
            $result['make'] = $make; 
            if (isset($makeValue['models'])){
                $result['models'] = implode($modelSeparator, $makeValue['models']);
            }
            if (isset($makeValue['B'])){
                if (isset($makeValue['B']['litresMin'])){
                    $result['B'] = $makeValue['B']['litresMin'];
                    if (isset($makeValue['B']['litresMax'])){
                        if ($makeValue['B']['litresMin'] != $makeValue['B']['litresMax']){
                            $result['B'] .= $litresSeparator.$makeValue['B']['litresMax'];
                        }
                    }    
                }    
            }    
            if (isset($makeValue['D'])){
                if (isset($makeValue['D']['litresMin'])){
                    $result['D'] = $makeValue['D']['litresMin'];
                    if (isset($makeValue['D']['litresMax'])){
                        if ($makeValue['D']['litresMin'] != $makeValue['D']['litresMax']){
                            $result['D'] .= $litresSeparator.$makeValue['D']['litresMax'];
                        }
                    }    
                }    
            }    
            if (isset($makeValue['from'])){
                $result['Y'] = substr($makeValue['from'], 2, 2).$yearSeparator;
                if ($makeValue['from'] != $makeValue['cto']){
                    $result['Y'] .= substr($makeValue['cto'], 2, 2);
                }
            }   
            
            $makeNames[] = implode($partSeparator, $result[$make]);
            unset($result);
        }
        
        return implode($makeSeparator, $makeNames);
    }
    
    /**
     * Часть описания нименования
     * 
     * @param Goods $good
     * @return string
     */
    public function textPart($good)
    {
        $result = '';
        if ($good->getTokenGroup()){
            if ($good->getTokenGroup()->getName()){
                return $good->getTokenGroup()->getName();
            }
        }
        if ($good->getGenericGroup()){
            return $good->getGenericGroup()->getName();
        }
        
        return $result;
    }
    
    /**
     * Поиск лучшего наименования для товара
     * 
     * @param Goods $good
     * @return array
     */
    public function findBestName($good)
    {
        if ($good->getGenericGroup()){
            $result['genericGroup'] = $good->getGenericGroup()->getName();
        }    
        if ($good->getTokenGroup()){
            $result['tokenGroup'] = $good->getTokenGroup()->getName();
        }    
        
        $carPart = $this->carPart($good);
        $result['textPart'] = $this->textPart($good);
        $result['oeCarPart'] = $this->oeCar($good);
        $result['carPartStr'] = $this->carPartStr($carPart);
        $result['carPart'] = $carPart;
        
        return $result;
    }
        
}
