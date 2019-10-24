<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Bigram;
use Application\Entity\Article;
use Application\Entity\ArticleBigram;
use Application\Entity\Rawprice;


/**
 * Description of BigramRepository
 *
 * @author Daddy
 */
class BigramRepository  extends EntityRepository
{
    
    /**
     * Возвращает статус биграмы
     * 
     * @param string $lemma1
     * @param string $lemma2
     * 
     * @return integer
     */
    protected function biStatus($lemma1, $lemma2)
    {
        $isRU = new \Application\Validator\IsRU();
        $isEN = new \Application\Validator\IsEN();
        $isNUM = new \Application\Validator\IsNUM();
        
        if ($isRU->isValid($lemma1)){
            if ($isRU->isValid($lemma2)){
                return Bigram::RU_RU;
            }
            if ($isEN->isValid($lemma2)){
                return Bigram::RU_EN;
            }
            if ($isNUM->isValid($lemma2)){
                return Bigram::RU_NUM;
            }
        }
        if ($isEN->isValid($lemma1)){
            if ($isRU->isValid($lemma2)){
                return Bigram::RU_EN;
            }
            if ($isEN->isValid($lemma2)){
                return Bigram::EN_EN;
            }
            if ($isNUM->isValid($lemma2)){
                return Bigram::EN_NUM;
            }
        }
        if ($isNUM->isValid($lemma1)){
            if ($isRU->isValid($lemma2)){
                return Bigram::RU_NUM;
            }
            if ($isEN->isValid($lemma2)){
                return Bigram::EN_NUM;
            }
            if ($isNUM->isValid($lemma2)){
                return Bigram::NUM_NUM;
            }
        }
    }

    /**
     * Получить билемму
     * 
     * @param string $lemma1
     * @param string $lemma2
     * @return string
     */
    private function bilemma($lemma1, $lemma2)
    {
        $lemms = [$lemma1, $lemma2];
        return implode(' ', $lemms);        
    }
    
    /**
     * Получить билему в md5
     * 
     * @param string $lemma1
     * @param string $lemma2
     * 
     * @return string
     */
    private function bilemmaMd5($lemma1, $lemma2)
    {
        return md5($this->bilemma($lemma1, $lemma2));        
    }
    
    /**
     * Поиск биграмы
     * 
     * @param string $lemma1
     * @param string $lemma2
     * @return type
     */
    public function findBigram($lemma1, $lemma2)
    {
        if ($lemma1 && $lemma2){
            $bigram = $this->getEntityManager()->getRepository(Bigram::class)
                    ->findOneByBilemmaMd5($this->bilemmaMd5($lemma1, $lemma2));            
            return $bigram;
        }
        
        return;
    }
    
    /**
     * Быстрая вставка bigram
     * @param string $lemma1 
     * @param string $lemma2 
     *      * 
     * @return null
     */
    public function insertBigram($lemma1, $lemma2)
    {
        $bigram = $this->findBigram($lemma1, $lemma2);
        
        if (!$bigram){                 
            $bilemmaMd5 = $this->bilemmaMd5($lemma1, $lemma2);
            $row = [
                'bilemma_md5' => $bilemmaMd5,
                'bilemma' => $this->bilemma($lemma1, $lemma2),
                'status' => $this->biStatus($lemma1, $lemma2),
            ];

            $this->getEntityManager()->getConnection()->insert('bigram', $row);

            $bigram = $this->getEntityManager()->getRepository(Bigram::class)
                    ->findOneByBilemmaMd5($bilemmaMd5);
        } 

        return $bigram;
    }    

    /**
     * Быстрое обновление биграм
     * 
     * @param Bigram $bigram
     * @param array $data
     * @return integer
     */
    public function updateBigram($bigram, $data)
    {
        if (!count($data)){
            return;
        }
        
        if (isset($data['status'])){
            $this->updateArticleBigram($bigram, $data);
        }    
        
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->update(Bigram::class, 'b')
                ->where('b.id = ?1')
                ->setParameter('1', $bigram->getId())
                ;
        foreach ($data as $key => $value){
            $queryBuilder->set('b.'.$key, $value);
        }
        
        return $queryBuilder->getQuery()->getResult();        
    }
    
    /**
     * Найти биграмы для удаления
     * 
     * @return object
     */
    public function findBigramForDelete()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('b')
            ->from(Bigram::class, 'b')
            ->andWhere('b.frequency <= ?1')
            ->andWhere('b.flag = ?2')    
            ->setParameter('1', 0)    
            ->setParameter('2', Bigram::WHITE_LIST)    
                ;
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Быстрая вставка артикула биграм
     * @param Article $article
     * @param Bigram $bigram 
     * @return null
     */
    public function insertArticleBigram($article, $bigram)
    {
        if ($article && $bigram){
            $articleBigram = $this->getEntityManager()->getRepository(ArticleBigram::class)
                    ->findOneBy(['article' => $article->getId(), 'bigram' => $bigram->getId()]);

            if (!$articleBigram){
                $row = [
                    'article_id' => $article->getId(),
                    'bigram_id' => $bigram->getId(),
                    'bilemma' => $bigram->getBilemma(),
                    'status' => $bigram->getStatus(),
                ];
                $inserted = $this->getEntityManager()->getConnection()->insert('article_bigram', $row);
            }    
        }
        
        return;
    }    
    
    /**
     * Быстрое обновление биграм артикула
     * 
     * @param Bigram $bigram
     * @param array $data
     * @return integer
     */
    public function updateArticleBigram($bigram, $data)
    {
        unset($data['flag']);
        unset($data['frequency']);
        unset($data['idf']);
        
        if (!count($data)){
            return;
        }
        
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->update(ArticleBigram::class, 'ab')
                ->where('ab.bigram = ?1')
                ->setParameter('1', $bigram)
                ;
        foreach ($data as $key => $value){
            $queryBuilder->set('ab.'.$key, $value);
        }
        
        return $queryBuilder->getQuery()->getResult();        
    }
    
    /**
     * Быстрое удаление артикулов биграм, свзанных с биграммой
     * @param Bigram $bigram 
     * @return integer
     */
    public function deleteArticleBigram($bigram)
    {
        if (is_numeric($bigram)){
            $bigramId = $bigram;
        } else {
            $bigramId = $bigram->getId();
        }

        $deleted = $this->getEntityManager()->getConnection()->delete('article_bigram', ['bigram_id' => $bigramId]);
        return $deleted;
    }        
    
    /**
     * Количество биграм по статусу
     * 
     * @return array
     */
    public function statusBigramCount()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('b.status, count(b.id) as bigramCount')
                ->from(Bigram::class, 'b')
                ->groupBy('b.status')
            ;
        
        return $queryBuilder->getQuery()->getResult();        
    }
    
    /**
     * Запрос по биграмам по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllBigram($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('b')
            ->from(Bigram::class, 'b')
            ->addOrderBy('b.bilemma')                
                ;
        
        if (is_array($params)){
            if (isset($params['q'])){
                $queryBuilder->where('b.bilemma like :search')
                    ->setParameter('search', '%' . $params['q'] . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->where('b.bilemma > ?1')
                    ->setParameter('1', $params['next1'])
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('b.bilemma < ?2')
                    ->setParameter('2', $params['prev1'])
                    ->orderBy('b.bilemma', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('b.'.$params['sort'], $params['order']);                
            }            
            if (isset($params['status'])){
                $queryBuilder->andWhere('b.status = ?3')
                    ->setParameter('3', $params['status'])
                        ;                
            }            
            if (isset($params['flag'])){
                $queryBuilder->andWhere('b.flag = ?4')
                    ->setParameter('4', $params['flag'])
                        ;                
            }            
        }

//            var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }            
    
    /**
     * Найти строки прайсов биграма
     * 
     * @param Bigram $bigram
     * @return object
     */
    public function findBigramRawprice($bigram)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
            ->from(Rawprice::class, 'r')
            ->join('r.code', 'a')
            ->join('a.articleBigrams', 'ab')
            ->where('ab.bigram = ?1')    
            ->andWhere('r.status = ?2')    
            ->setParameter('1', $bigram->getId())
            ->setParameter('2', Rawprice::STATUS_PARSED)
            ;
        
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Количество товаров с этим биграмом
     * @param Bigram $bigram
     */
    public function bigramGoodCount($bigram)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('identity(a.good)')
                ->distinct()
                ->from(ArticleBigram::class, 'ab')
                ->join('ab.article', 'a')
                ->where('ab.bigram = ?1')
                ->setParameter('1', $bigram->getId())
                ;
        
        return count($queryBuilder->getQuery()->getResult());
    }
    
}
