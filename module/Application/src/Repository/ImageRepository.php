<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Images;

/**
 * Description of ImageRepository
 *
 * @author Daddy
 */
class ImageRepository extends EntityRepository
{

    const IMAGE_DIR = './public/img'; //папка для хранения картинок
    const GOOD_IMAGE_DIR = './public/img/goods'; //папка для хранения картинок товаров    
    
    /**
     * Получить путь к папке с картинками
     * 
     * @param Application\Entity\Goods $good
     * @return string
     */
    public function getImageFolder($good)
    {
        return self::GOOD_IMAGE_DIR.'/'.$good->getId().'/td';
    }

    /**
     * Создать папку с картинками
     * 
     * @param Application\Entity\Goods $good
     */
    public function addImageFolder($good)
    {
        $images_folder = self::IMAGE_DIR;
        if (!is_dir($images_folder)){
            mkdir($images_folder);
        }
        
        $image_folder = self::GOOD_IMAGE_DIR;
        if (!is_dir($image_folder)){
            mkdir($image_folder);
        }
        
        $good_image_folder = self::GOOD_IMAGE_DIR.'/'.$good->getId();
        if (!is_dir($good_image_folder)){
            mkdir($good_image_folder);
        }

        $td_image_folder = $this->getImageFolder($good);
        if (!is_dir($td_image_folder)){
            mkdir($td_image_folder);
        }
        return;
    }        
    
    
    /*
     * Очистить содержимое папки c картинками товара
     * 
     * @var Application\Entity\Goods $folderName
     * 
     */
    public function clearImageGoodFolder($good)
    {
        $folderName = $this->getImageFolder($good);
                
        if (is_dir($folderName)){
            foreach (new \DirectoryIterator($folderName) as $fileInfo) {
                if ($fileInfo->isDot()) {
                    continue;
                }
                if ($fileInfo->isFile()){
                    unlink($fileInfo->getFilename());                            
                }
            }
        }
    }
    
    /**
     * Сохранить картинку товара по ссылке
     * 
     * @param string $uri
     */
    public function saveImageGood($good, $uri, $docFileName)
    {
        $headers = get_headers($uri);
        if(preg_match("|200|", $headers[0])) {
            
            $image = file_get_contents($uri);
            file_put_contents($this->getImageFolder($good)."/".$docFileName, $image);
        } 
        
        return;
            
    }
    
    /**
     * Запрос по картинкам по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllImage($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('i')
            ->from(Images::class, 'i')
                ;
        
        if (is_array($params)){
            if (isset($params['i'])){
                $queryBuilder->where('i.name like :search')
                    ->setParameter('search', '%' . $params['i'] . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->where('i.name > ?1')
                    ->setParameter('1', $params['next1'])
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('i.name < ?2')
                    ->setParameter('2', $params['prev1'])
                    ->orderBy('i.name', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['status'])){
                $queryBuilder->andWhere('i.status = ?3')
                    ->setParameter('3', $params['status'])
                        ;
            }            
            if (isset($params['sort'])){
                $queryBuilder->orderBy('i.'.$params['sort'], $params['order']);                
            }            
        }

        return $queryBuilder->getQuery();
    }   
    
    /**
     * Добавить картинку товаров
     * 
     * @param array $data
     */
    public function addImage($data)
    {
       $image = $this->getEntityManager()->getRepository(Images::class)
               ->findOneByTdId($data['td_id']);
       
       if ($image == null){
           $this->getEntityManager()->getConnection()->insert('images', $data);
       }
       
       return;
    }
}
