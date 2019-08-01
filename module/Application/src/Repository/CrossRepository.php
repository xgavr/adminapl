<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Cross;
use Application\Entity\CrossList;
use Application\Filter\Basename;

/**
 * Description of CrossRepository
 *
 * @author Daddy
 */
class CrossRepository extends EntityRepository{

    const CROSS_DIR = './data/cross'; //папка для хранения кроссов  
    const TMP_CROSS_DIR = './data/cross/tmp'; //временная папка для хранения кроссов 
    
    /**
     * Получить временную папку с коссами
     * 
     */
    public function getTmpCrossFolder()
    {
        $cross_folder = self::CROSS_DIR;
        if (!is_dir($cross_folder)){
            mkdir($cross_folder);
        }
        
        $tmp_cross_folder = self::TMP_CROSS_DIR;
        if (!is_dir($tmp_cross_folder)){
            mkdir($tmp_cross_folder);
        }

        return $tmp_cross_folder;
    }        
    
    /**
     * Получить файлы во временной папке
     * @param string $tmpFolder
     * @return array;
     */
    public function getTmpFiles($tmpFolder = null)
    {
        
        if (!$tmpFolder){
            $tmpFolder = $this->getTmpCrossFolder();
        }
        
        $result = [];
        if (is_dir($tmpFolder)){
            foreach (new \DirectoryIterator($tmpFolder) as $fileInfo) {
                if ($fileInfo->isDot()) {
                    continue;
                }
                if ($fileInfo->isFile()){
                    $result[] = $fileInfo->getFileInfo();                            
                }
                if ($fileInfo->isDir()){
                    $result = array_merge($result, $this->getTmpFiles($fileInfo->getPathname()));                            
                }
            }
        }
        
        return $result;
    }    
    
    /*
     * Переместить файл в архив
     * @var string $filename
     */
    public function renameToArchive($filename)            
    {

        if (file_exists($filename)){
            $filter = new Basename();
            $arx_folder = self::CROSS_DIR;
            if (is_dir($arx_folder)){
                if (copy(realpath($filename), realpath($arx_folder).'/'.$filter->filter($filename))){
                    unlink(realpath($filename));
                }
            }
        }        
        return;
    }

    
    
    /**
     * Быстрая вставка строки кросса
     * @param array $row 
     * @return integer
     */
    public function insertLine($row)
    {
        return $this->getEntityManager()->getConnection()->insert('cross_list', $row);
    }    
    
    /**
     * Выборка кроссов
     * @param integer $status
     * @return type
     */
    public function findAllCross($status = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select("c, s")
            ->from(Cross::class, 'c')
            ->leftJoin('c.supplier', 's')    
                ;
        
        if ($status){
            $queryBuilder->andWhere('c.status = ?2')
            ->setParameter('2', (int) $status)    
                ;                    
        }

        $queryBuilder->addOrderBy('c.id', 'DESC');
        
//var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }        
    
    /**
     * Выборка кросс листа
     * @param Cross $cross
     * @param array $params
     * @return type
     */
    public function crossList($cross, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('cl')
            ->from(CrossList::class, 'cl')
            ->where('cl.cross = ?1')
            ->setParameter('1', $cross->getId())    
                ;
        
        if (is_array($params)){
            if (isset($params['status'])){
                $queryBuilder->andWhere('cl.status = ?2')
                        ->setParameter('2', $params['status'])
                        ;
            }
        }
        
        return $queryBuilder->getQuery();
    }   
    
    /**
     * Обновление строки кросс листа
     * 
     * @param CrossList $line
     * @param array $data
     */
    public function updateCrossList($line, $data)
    {
        $this->getEntityManager()->getConnection()->update('cross_list', $data, ['id' => $line->getId()]);
        return;
    }
    
    /**
     * Удаление кросса
     * @param Cross $cross
     */
    public function deleteCrossList($cross)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('cl.id')
                ->from(CrossList::class, 'cl')
                ->where('cl.cross = ?1')
                ->setParameter('1', $cross->getId())
                ;
        
        $iterator = $queryBuilder->getQuery()->iterate();
        
        foreach ($iterator as $item){
            foreach ($item as $row){
                $this->getEntityManager()->getConnection()->delete('cross_list', ['id' => $row['id']]);                
            }
        }
            
        unset($iterator);
        return;
    }
    
}
