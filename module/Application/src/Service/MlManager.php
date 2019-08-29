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
     * @return array
     */
    public function strMeanFrequency($str, $article = null)
    {
        return $this->nameManager->meanFrequency($str, $article);
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
        $frequencies = $this->strMeanFrequency($rawprice->getTitle(), $rawprice->getArticle());
        $result = [
            $this->tokenGroupMeanFrequency($rawprice->getGood()->getTokenGroup()),
            $frequencies[\Application\Entity\Token::IS_DICT],
            $frequencies[\Application\Entity\Token::IS_RU],
            $frequencies[\Application\Entity\Token::IS_RU_1],
            $frequencies[\Application\Entity\Token::IS_RU_ABBR],
            $frequencies[\Application\Entity\Token::IS_EN_DICT],
            $frequencies[\Application\Entity\Token::IS_EN],
            $frequencies[\Application\Entity\Token::IS_EN_1],
            $frequencies[\Application\Entity\Token::IS_EN_ABBR],
            $frequencies[\Application\Entity\Token::IS_NUMERIC],
            $frequencies[\Application\Entity\Token::IS_ARTICLE],
        ];        
        return $result;
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
}
