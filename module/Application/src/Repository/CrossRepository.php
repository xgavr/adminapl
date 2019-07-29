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
    
}
