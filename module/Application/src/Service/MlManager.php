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
use Application\Entity\MlTitle;

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
     * Разложить наименование из строки прайса
     * 
     * @param \Application\Entity\Rawprice $rawprice
     * @return array
     */
    public function rawpriceToMlTitle($rawprice)
    {
        return $this->nameManager->rawpriceToMlTitle($rawprice);
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
