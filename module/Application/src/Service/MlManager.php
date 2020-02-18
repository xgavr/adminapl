<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Phpml\Classification\KNearestNeighbors;
use Phpml\ModelManager;
use Phpml\Clustering\DBSCAN;
use Phpml\Dataset\CsvDataset;
use Phpml\CrossValidation\StratifiedRandomSplit;
use Phpml\Metric\Accuracy;
use Phpml\Classification\MLPClassifier;
use Phpml\Preprocessing\Normalizer;
use Phpml\Regression\SVR;
use Phpml\Regression\LeastSquares;
use Phpml\SupportVectorMachine\Kernel;
use Application\Entity\MlTitle;
use Application\Entity\Goods;
use Application\Entity\TokenGroup;
use Application\Entity\Rawprice;
use Application\Entity\Token;
use Application\Entity\Bigram;
use Application\Entity\Rate;
use Application\Entity\ScaleTreshold;
use Application\Entity\FpGroup;
use Application\Entity\ArticleTitle;
use Application\Entity\ArticleToken;


use Application\Filter\TokenizerQualifier;

/**
 * Description of CurrencyService
 *
 * @author Daddy
 */
class MlManager
{
    
    const ML_DATA_PATH     = './data/ann/'; //путь к папке с моделями ml
    const ML_TITLE_DIR    = './data/ann/ml_title/'; //путь к папке с моделями mlTitle
    const ML_TITLE_FILE   = './data/ann/ml_title/dataset.csv'; //данные mlTitle
    const ML_TOKEN_GROUP_FILE   = './data/ann/ml_title/token_group_dataset.csv'; //данные групп наименований
    const ML_RATE_PATH = './data/ann/rate/'; //папка расценок
    const ML_RATE_PRIMARY_SCALE = './data/ann/rate/primary_scale.dat'; //начальная шкала
    
    const RATE_SAMPLES = [ 
        50,  100,  500,  800,  1000,  2000,  3000,  5000,  10000,  20000,  50000,  100000,
    ];

    const RATE_TARGETS = [ 
       150,   70,   50,   45,    40,    35,    32,    29,     26,     24,     15,      10,
    ];

    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    /**
     * Name manager.
     * @var \Application\Service\NameManager
     */
    private $nameManager;
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $nameManager)
    {
        $this->entityManager = $entityManager;
        $this->nameManager = $nameManager;
    }
    
    /**
     * Проверка качеста предсказания наценки
     * 
     * @param array $testPrice
     * @param array $testMargin
     * @param array $samples
     * @param array $targets
     * 
     * @return boolean
     */
    public function rateAccuracy($testPrice, $testMargin, $samples, $targets)
    {        
        foreach ($samples as $key => $sample){
            if ((float) $testPrice < (float) $sample){
                if ($key > 0){
                    if ((float)$testMargin >= (float)$targets[$key] && 
                            (float)$testMargin <= (float)$targets[$key-1]){
                        return true;
                    }                    
                } else {
                    if ((float)$testMargin >= (float)$targets[$key]){
                        return true;
                    }                    
                }
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Инициализация первоначальной шкалы для расценок
     */
    public function trainPrimaryScale()
    {
        $samples = self::RATE_SAMPLES;
        $targets = self::RATE_TARGETS;
        
        $treshots = [
            rand(5, 100),
            rand(100, 1000),
            rand(1000, 5000),
            rand(5000, 10000),
            rand(10000, 20000),
            rand(20000, 50000),
            rand(50000, 100000),
        ];
        
        $samples_log = [];
        foreach ($samples as $sample){
            $samples_log[] = [log($sample)];
        }
        
        $treshots_log = [];
        foreach ($treshots as $treshot){
            $treshots_log[] = [log($treshot)];
        }
        
        $result = [
            'treshots' => $treshots,
            'predicts' => [],
            'samples' => $samples,
            'targets' => $targets,
        ];
        
        $regression = new SVR(Kernel::LINEAR);
//        $regression = new SVR(Kernel::POLYNOMIAL, $degree=1);
        $regression->train($samples_log, $targets);

        $result['predicts'][$degree] = $regression->predict($treshots_log);
        
        if (!is_dir(self::ML_RATE_PATH)){
            mkdir(self::ML_RATE_PATH);
        }

        $modelManager = new ModelManager();
        $modelManager->saveToFile($regression, self::ML_RATE_PRIMARY_SCALE);
        
        return $result;
    }

    /**
     * Предсказание процента по порогу по первоначальной шкале
     * 
     * @param float $treshold
     */
    public function predictPrimaryScale($treshold)
    {
        $modelManager = new ModelManager();
        $regression = $modelManager->restoreFromFile(self::ML_RATE_PRIMARY_SCALE);
        $treshold_log = [log($treshold)];
        return round($regression->predict($treshold_log), 2);                
    }
    
    /**
     * Обучение специальной расценки
     * 
     * @param Rate $rate
     * @param array $samples
     * @param array $targets
     * @return null
     */
    public function trainRateScale($rate, $samples, $targets)
    {
        $samples_log = [];
        foreach ($samples as $sample){
            $samples_log[] = [log($sample)];
        }

        $regression = new SVR(Kernel::LINEAR);
        $regression->train($samples_log, $targets);

        if (!is_dir(self::ML_RATE_PATH)){
            mkdir(self::ML_RATE_PATH);
        }
        
        $modelFilename = self::ML_RATE_PATH.$rate->getRateModelFileName();

        $modelManager = new ModelManager();
        $modelManager->saveToFile($regression, $modelFilename);
        
        return;
    }
    
    /**
     * Предсказание процента по порогу по специальной шкале расценки
     * 
     * @param float $treshold
     * @param string $modelFileName
     * 
     * @return float
     */
    public function predictRateScale($treshold, $modelFileName = null)
    {
        $modelFileNameFull = self::ML_RATE_PRIMARY_SCALE;
        if ($modelFileName){
            if (file_exists(self::ML_RATE_PATH.$modelFileName)){
                $modelFileNameFull = self::ML_RATE_PATH.$modelFileName;
            }
        }    

        $modelManager = new ModelManager();
        $regression = $modelManager->restoreFromFile($modelFileNameFull);
        $treshold_log = [log($treshold)];
        return max(round($regression->predict($treshold_log), 2), ScaleTreshold::MIN_RATE);                
    }
    
    /**
     * Удалить модель специальной шкалы
     * @param Rate $rate
     */
    public function removeModelRateScale($rate)
    {
        
        $modelFilename = self::ML_RATE_PATH.$rate->getRateModelFileName();
        if (file_exists($modelFilename)){
            unlink($modelFilename);
        }
        
        return;
    }
    
    /**
     * Обучение сравения строк прайсов
     */
    public function matchingRawpriceTrain()
    {
        $samples = [
        //  Token Price OEM  
            [1, 1, 1], 
            [1, 1, 0], 
            [1, 0, 0], 
            [0, 0, 0],
            [0, 0, 1],
            [0, 1, 1],
            [1, 0, 1],
            [0, 1, 0],
            [1, 0, 0],
        ];
        
        $labels = [1, 1, 0, 0, 0, 1, 0, 0, 0];

        $classifier = new KNearestNeighbors();
        $classifier->train($samples, $labels);

        $filepath = (self::ML_DATA_PATH . 'matching_rawprice.net');
        $modelManager = new ModelManager();
        $modelManager->saveToFile($classifier, $filepath);
        
    }
    
    /**
     * Решение о сравнении строки прайса с артикулом
     * 
     * @param array $data
     * 
     * return bool
     */
    public function matchingRawprice($data)
    {
        $filepath = (self::ML_DATA_PATH . 'matching_rawprice.net');
        $modelManager = new ModelManager();
        $restoredClassifier = $modelManager->restoreFromFile($filepath);
        return $restoredClassifier->predict($data);        
    }
    
    /**
     * Подготовка матрицы для классификации наименований
     */
    public function featureNameMatrix()
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(1200);

        $filename = (self::ML_DATA_PATH . 'name_samples.csv');
        
        $goods = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->findBy([]);
        $tokens = $this->entityManager->getRepository(\Application\Entity\Token::class)
                ->findBy(['status' => \Application\Entity\Token::IS_DICT]);
        
//        if (file_exists($filename)){
//            unlink($filename);
//        }
        
        $fp = fopen($filename, 'w');
        foreach ($goods as $good){
            $row = [];
            foreach ($tokens as $token){
                if ($good->hasToken($token)){
                    $row[$token->getId()] = 1;
                } else {
                    $row[$token->getId()] = 0;
                }
            }
            fputcsv($fp, $row);
        }
        fclose($fp);
        
        
        return;
    }  
    
    /**
     * Кластеризация товаров
     * 
     * @return array
     */
    public function clusterName()
    {
        ini_set('memory_limit', '8192M');
        set_time_limit(1200);

        $filename = (self::ML_DATA_PATH . 'name_samples.csv');

        $samples = [];
        $row = 1;
        if (($handle = fopen($filename, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 10000)) !== FALSE) {
                $samples[] = $data;
            }
            fclose($handle);
        }

        $dbscan = new DBSCAN($epsilon = 2, $minSamples = 100);
        $result = $dbscan->cluster($samples);        
    }
    
    /**
     * Разбивает строку на токены
     * 
     * @param string $text
     * @return array
     */
    public function tokenize($text)
    {
        $tokenizer = new TokenizerQualifier();
        return $tokenizer->filter($text);
    }
    
    /**
     * Средняя частота строки
     * 
     * @param string $str
     * @param string $article
     * @param string $producer
     * @return array
     */
    public function strMeanFrequency($str, $article = null, $producer = null)
    {
        return $this->nameManager->meanFrequency($str, $article, $producer);
    }
    
    /**
     * Средняя частота группы токенов
     * 
     * @param \Application\Entity\TokenGroup $tokenGroup
     * @return int
     */
    public function tokenGroupMeanFrequency($tokenGroup)
    {
        return $this->entityManager->getRepository(\Application\Entity\TokenGroup::class)
                ->meanFrequency($tokenGroup);
    }
    
    /**
     * Получить строки прайсов товара из строки прайса
     * 
     * @param Rawprice $rawprice
     * @return type
     */
    public function rawpricesFromGoodRawprice($rawprice)
    {
        $good = $rawprice->getGood();
        if ($good){
            return $this->entityManager->getRepository(Goods::class)
                ->rawpriceArticles($good);
        } 
       
       return;
    }
    
    /**
     * Получить наименования товара из строки прайса
     * 
     * @param Rawprice $rawprice
     * @return type
     */
    public function titlesFromGoodRawprice($rawprice)
    {
        $good = $rawprice->getGood();
        if ($good){
            return $this->entityManager->getRepository(Goods::class)
                ->articleTitles($good);
        } 
       
       return;
    }
    
    /**
     * Наименование группы токенов
     * 
     * @param ArticleTitle $articleTitle
     */
    public function tokenGroupArticleTitle($articleTitle)
    {
        return $this->entityManager->getRepository(Token::class)
                ->tokenGroupArticleTitle($articleTitle);
    }

    /**
     * Лучшее наименование группы токенов товара
     * 
     * @param Goods $good
     * @return arrray
     */
    public function choiceTokenGroupTitle($good)
    {
        $titles = $this->entityManager->getRepository(Token::class)
                ->choiceGroupTitle($good);
        if (is_array($titles)){
            return array_shift($titles);
        }
        
        return;
    }

    /**
     * Заголовок товара разбить на значимые токены 
     * 
     * @param ArticleTtile $articleTitle
     * @param integer $gc
     * @param integer $tgc
     * 
     * @return array
     */
    public function titleToToken($articleTitle, $gc = null, $tgc = null)
    {
        if (!$gc){
            $gc = $this->entityManager->getRepository(Goods::class)
                    ->count([]);
        }
        if (!$tgc){
            $tgc = $this->entityManager->getRepository(TokenGroup::class)
                    ->count([]);
        }
        
        $result = [];
        
        $articleTokens = $this->entityManager->getRepository(ArticleToken::class)
                ->findBy(['articleTitle' => $articleTitle->getId()]);


        $preWord = $preToken = $token = null;
        $k = 0;
        foreach ($articleTokens as $articleToken){
            $token = $this->entityManager->getRepository(Token::class)
                    ->findOneByLemma($articleToken->getLemma());
            if ($token){
                $pmi = 0;
                if ($token->getFrequency() > Token::MIN_DF && 
                        in_array($token->getStatus(), [Token::IS_DICT, Token::IS_RU, Token::IS_RU_1])){
                        //&& $token->getFlag() == Token::WHITE_LIST){
                    $pmi = $token->getFrequency();
                    $result[] = ['pmi' => $pmi,  'token' => $token];
                }
            }    
        }    

        usort($result, function($a, $b){
            if ($a['pmi'] == $b['pmi']) {
                return 0;
            }
            return ($a['pmi'] > $b['pmi']) ? -1 : 1;            
        }); 

        $fpGroupNames = [];
        foreach ($result as $row){
            $fpGroupNames[] = $row['token']->getLemma();
        }
        
        return $result; 
    }
           
    /**
     * Разложить наименование из строки прайса
     * 
     * @param ArticleTitle $articleTitle
     * @return array
     */
    public function rawpriceToMlTitle($articleTitle)
    {
        $result = $this->titleToToken($articleTitle, 1070000, 40000);
        $empt = array_fill(200, 10 - count($result), false);
//        var_dump($empt);
        return array_merge($result, $empt);
    }
    
    /**
     * Сохранить выборку в dataset
     */
    public function mlTitlesToCsv()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        
        if (!is_dir(self::ML_TITLE_DIR)){
            mkdir(self::ML_TITLE_DIR);
        }        

        $mlTitlesQuery = $this->entityManager->getRepository(\Application\Entity\Token::class)
                    ->findMlTitles();
        $iterable = $mlTitlesQuery->iterate();
        
        $fp = fopen(self::ML_TITLE_FILE, 'w');
        foreach ($iterable as $row) {
            foreach ($row as $mlTitle){
                $sample = $this->rawpriceToMlTitle($mlTitle->getRawprice());
                $sample[] = $mlTitle->getStatus();
                fputcsv($fp, $sample);
                $this->entityManager->detach($mlTitle);
            }    
        }

        fclose($fp);
        return;
    }
    
    /**
     * Выгрузить токены групп наименований
     */
    public function tokenGroupsToCsv()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        
        if (!is_dir(self::ML_TITLE_DIR)){
            mkdir(self::ML_TITLE_DIR);
        }   
        
        $tokenGroupsQuery = $this->entityManager->getRepository(\Application\Entity\Token::class)
                    ->findAllTokenGroup();
        $iterable = $tokenGroupsQuery->iterate();
        
        $fp = fopen(self::ML_TOKEN_GROUP_FILE, 'w');
        foreach ($iterable as $row) {
            foreach ($row as $tokenGroup){                
                $sample = $this->tokenGroupMeanFrequency($tokenGroup);
                $sample[] = $tokenGroup->getId();
                fputcsv($fp, $sample);
                $this->entityManager->detach($tokenGroup);
            }    
        }

        fclose($fp);
        return;        
    }
    
    /**
     * Класетеризация групп наименований
     */
    public function clusteringTokenGroup()
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $dataset = new CsvDataset(self::ML_TOKEN_GROUP_FILE, 5, false);
        $kmeans = new \Phpml\Clustering\KMeans(3000);
        
        //array_walk($dataset, function(&$x) { $x=array_map('intval', $x);});
        
        $samples = $dataset->getSamples();
//        var_dump(array_slice($samples, 0, 5, true)); exit;

        $normalizer = new Normalizer();
        $normalizer->fit($samples);
        $normalizer->transform($samples);
//        var_dump(array_slice($samples, 0, 5, true)); exit;

        $targets = $dataset->getTargets();
        $result = $kmeans->cluster($samples);
        
        var_dump(array_slice($result, 0, 5, true));
        
        return;
    }
    
    /**
     * Наименования товара
     * 
     * @param Goods $good
     * @return QueryResult
     */
    public function goodTitles($good)
    {
        return $this->entityManager->getRepository(\Application\Entity\Token::class)
                ->goodTitles($good);
    }
    
    /**
     * Обновление статуса наименования
     * 
     * @param MlTitle $mlTitle
     * @param integer $status
     */
    public function updateMlTitleStatus($mlTitle, $status)
    {
        $mlTitle->setStatus($status);
        $this->entityManager->persist($mlTitle);
        $this->entityManager->flush($mlTitle);
        
        return;
    }
    
    /**
     * Обучение классификатора наименований
     */
    public function mlTitlePredict()
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);
        
        $csvDataset = new CsvDataset(self::ML_TITLE_FILE, 27, false);
        $dataset = new StratifiedRandomSplit($csvDataset, 0.33, 1234);

        $trainSamples = $dataset->getTrainSamples();
        $testSamples = $dataset->getTestSamples();
        array_walk($trainSamples, function(&$x) { $x=array_map('intval', $x);});
        array_walk($testSamples, function(&$x) { $x=array_map('intval', $x);});
//        var_dump($dataset->getTrainLabels()); exit;
        
        $normalizer = new Normalizer();
        $normalizer->fit($trainSamples);
        $normalizer->transform($trainSamples);
        $normalizer->fit($testSamples);
        $normalizer->transform($testSamples);
//        var_dump($testSamples); exit;
        
//        $classifierSVC = new \Phpml\Classification\SVC(\Phpml\SupportVectorMachine\Kernel::LINEAR, $cost = 1000);
//        $classifierSVC->train($trainSamples, $dataset->getTrainLabels());
//        $predictLabelsSVC = $classifierSVC->predict($testSamples);
        
        $classifierKNN = new KNearestNeighbors($k=3);
        $classifierKNN->train($trainSamples, $dataset->getTrainLabels());
        $predictLabelsKNN = $classifierKNN->predict($testSamples);
        
        $modelManager = new ModelManager();
        $modelManager->saveToFile($classifierKNN, \Application\Entity\Token::ML_TITLE_MODEL_FILE);
        
//        $classifierNB = new \Phpml\Classification\NaiveBayes();
//        $classifierNB->train($trainSamples, $dataset->getTrainLabels());
//        $predictLabelsNB = $classifierNB->predict($testSamples);
        
//        $mlp = new MLPClassifier(27, [14], ['1', '2', '3']);        
//        $mlp->train($trainSamples, $dataset->getTrainLabels());        
//        $predictLabelsMLP = $mlp->predict($testSamples);
        
        $result = [
//            'accuracySVC' => Accuracy::score($dataset->getTestLabels(), $predictLabelsSVC),
            'accuracyKNN' => Accuracy::score($dataset->getTestLabels(), $predictLabelsKNN),
//            'accuracyNB' => Accuracy::score($dataset->getTestLabels(), $predictLabelsNB),
//            'accuracyMLP' => Accuracy::score($dataset->getTestLabels(), $predictLabelsMLP),
        ];
                
        var_dump($result);  
        return;
    }
}
