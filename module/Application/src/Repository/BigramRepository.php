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


/**
 * Description of BigramRepository
 *
 * @author Daddy
 */
class BigramRepository  extends EntityRepository
{
    /**
     * 
     * @param integer $articleId
     * @return type
     */
    public function findArticleTitle($articleId)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r.id, r.goodname, r.statusToken')
            ->from(Rawprice::class, 'r')
            ->where('r.code = ?1')
            ->andWhere('r.status = ?2')    
            ->setParameter('1', $articleId)    
            ->setParameter('2', Rawprice::STATUS_PARSED)    
            ;    

        return $queryBuilder->getQuery()->getResult();
        
    }
    
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
     * Быстрая вставка bigram
     * @param string $lemma1 
     * @param string $lemma2 
     *      * 
     * @return null
     */
    public function insertBigram($lemma1, $lemma2)
    {
        if ($lemma1 && $lemma2){
            $lemms = [$lemma1, $lemma2];
            $bilemma = implode(' ', $lemms);
            $bilemmaMd5 = md5($bilemma);

            $bigram = $this->getEntityManager()->getRepository(Bigram::class)
                    ->findOneByBilemmaMd5($bilemmaMd5);

            if (!$bigram){                
                $row = [
                    'bilemma_md5' => $bilemmaMd5,
                    'bilemma' => $bilemma,
                    'status' => $this->biStatus($lemma1, $lemma2),
                ];

                $this->getEntityManager()->getConnection()->insert('bigram', $row);
                
                $bigram = $this->getEntityManager()->getRepository(Bigram::class)
                        ->findOneByBilemmaMd5($bilemmaMd5);
            } 
            
            return $bigram;
        }    
        return;
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
    
}
