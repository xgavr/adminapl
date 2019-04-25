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
     * @param \Application\Entity\Goods $good
     * @param status integer
     * @return string
     */
    public function getImageFolder($good, $status)
    {
        return self::GOOD_IMAGE_DIR.'/'.$good->getId().'/'.$status;
    }

    /**
     * Создать папку с картинками
     * 
     * @param \Application\Entity\Goods $good
     * @param integer $status
     */
    public function addImageFolder($good, $status)
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

        $status_image_folder = $this->getImageFolder($good, $status);
        if (!is_dir($status_image_folder)){
            mkdir($status_image_folder);
        }
        return;
    }        
    
    
    /*
     * Очистить содержимое папки c картинками товара
     * 
     * @param \Application\Entity\Goods $folderName
     * @param integer $status
     * 
     */
    public function clearImageGoodFolder($good, $status)
    {
        $folderName = $this->getImageFolder($good, $status);
                
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
     * Добавить картинку товаров
     * 
     * @param array $data
     */
    public function addImage($data)
    {
        if (file_exists($data['path'])){
            $image = $this->getEntityManager()->getRepository(Images::class)
                    ->findOneByPath($data['path']);

            if ($image == null){
                $this->getEntityManager()->getConnection()->insert('images', $data);
            }
        }    
       
       return;
    }
    
    /**
     * Удаление картинки
     * 
     * @param \Application\Entity\Images $image
     */
    public function removeImage($image)
    {
        unlink($image->getPath());        
        $this->getEntityManager()->getConnection()->delete('images', ['id' => $image->getId()]);        
    }
    
    /**
     * Удаление картинок товара
     * @param \Application\Entity\Goods $good
     * @param integer $status
     * 
     */
    public function removeGoodImages($good, $status = null)
    {
        $images = $this->getEntityManager()->getRepository(Images::class)
                ->findBy(['good' => $good->getId(), 'status' => $status]);
        
        foreach ($images as $image){
            $this->removeImage($image);
        }
    }

    /**
     * Сохранить картинку товара по ссылке
     * 
     * @param \Application\Entity\Goods $good
     * @param string $uri
     * @param string $docFileName
     * @param integer $status
     * @param integer $similar
     */
    public function saveImageGood($good, $uri, $docFileName, $status, $similar)
    {
        $headers = get_headers($uri);
        
        if(preg_match("|200|", $headers[0])) {
            
            $image = file_get_contents($uri);
            $path = $this->getImageFolder($good, $status)."/".$docFileName;
            file_put_contents($path, $image);
            
            $this->addImage([
                'name' => $docFileName,
                'path' => $path,
                'status' => $status,
                'similar' => $similar,
                'good_id' => $good->getId(),
            ]);
        } 
        
        return;
            
    }
    
    /**
     * Сохранить картинку товара загруженная вручную
     * 
     * @param \Application\Entity\Goods $good
     * @param string $path
     * @param integer $status
     * @param integer $similar
     */
    public function uploadImageGood($good, $path, $status, $similar)
    {
        if(file_exists($path)) {
            
            $this->addImage([
                'name' => basename($path),
                'path' => $path,
                'status' => $status,
                'similar' => $similar,
                'good_id' => $good->getId(),
            ]);
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
    
}
