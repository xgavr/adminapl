<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Application\Entity\OemRaw;
use Application\Entity\Oem;
use Application\Entity\Raw;
use Application\Entity\Rawprice;
use Application\Filter\ArticleCode;

/**
 * Description of RbService
 *
 * @author Daddy
 */
class OemManager
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
     * Добавить оригинальный номер
     * 
     * @param Application\Entity\Goods $good
     * @param array $data
     * 
     * @return \Application\Entity\Oem;
     */
    public function addOem($good, $data)
    {
        $oem = $this->entityManager->getRepository(Oem::class)
                ->addOemToGood($good, $data, Oem::SOURCE_MAN);
        
        if (!$oem){
            $filter = new ArticleCode();        
            $oem = $this->entityManager->getRepository(Oem::class)
                        ->findOneBy(['oe' => $filter->filter($data['oeNumber']), 'good' => $good->getId()]);
        }    
        
        return $oem;
    }
    
    /**
     * Изменение оригинального номера
     * 
     * @param \Application\Entity\Oem $oem
     */
    public function updateOem($oem, $data)
    {
        $filter = new ArticleCode();
        
        $oe = $filter->filter($data['oeNumber']);
        $newOem = $this->entityManager->getRepository(Oem::class)
                ->findOneBy(['good' => $oem->getGood()->getId(), 'oe' => $oe]);
        
        if ($newOem->getId() == $oem->getId()){
            $oem->setStatus($data['status']);
            $oem->setBrandName($data['brandName']);

            $this->entityManager->persist($oem);
            $this->entityManager->flush();                    
        }
        
        return;
    }
    
    /**
     * Добавить новый код
     * 
     * @param string $code
     * @param Application\Entity\Article $article
     * @param bool $flushnow
     */
    public function addOemRaw($code, $article, $flushnow = true)
    {
        $filter = new ArticleCode();
        $filteredCode = mb_strcut(trim($filter->filter($code)), 0, 24, 'UTF-8');
        
        $oem = $this->entityManager->getRepository(OemRaw::class)
                    ->findOneBy(['code' => $filteredCode, 'article' => $article->getId()]);

        if ($oem == null){

            $oem = new OemRaw();
            $oem->setCode($filteredCode);            
            $oem->setFullCode($code);
            $oem->setArticle($article);

            // Добавляем сущность в менеджер сущностей.
            $this->entityManager->persist($oem);

            // Применяем изменения к базе данных.
            $this->entityManager->flush($oem);
        } else {
            if (mb_strlen($oem->getFullCode()) < mb_strlen(trim($code))){
                $oem->setFullCode(trim($code));                
                $this->entityManager->persist($oem);
                if ($flushnow){
                    $this->entityManager->flush($oem);
                }    
            }
        }  
        
        return $oem;        
    }        
    
    /**
     * Добавление нового кода из прайса
     * 
     * @param Application\Entity\Rawprice $rawprice
     * @param bool $flush
     */
    public function addNewOemRawFromRawprice($rawprice, $flush = true) 
    {
        $rawprice->getOemRaw()->clear();

        if ($rawprice->getArticle()){
            $oems = $rawprice->getOemAsArray();
            if (is_array($oems)){
                foreach ($oems as $oemCode){
                    $oem = $this->addOemRaw($oemCode, $rawprice->getCode(), $flush);
                    if ($oem){
                        $rawprice->addOemRaw($oem);
                    }   
                }    
            }    
        }    
        $rawprice->setStatusOem(Rawprice::OEM_PARSED);
        $this->entityManager->persist($rawprice);
        if ($flush){
            $this->entityManager->flush();
        }    
        return;
    }  
    
    /**
     * Выборка оригинальных номеров из прайса и добавление их в таблицу оригинальных номеров
     */
    public function grabOemFromRaw($raw)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(900);
        $startTime = time();
        
        $filter = new ArticleCode();
        
        $rawpriceQuery = $this->entityManager->getRepository(OemRaw::class)
                ->findOemForInsert($raw);
        $iterable = $rawpriceQuery->iterate();
        
        foreach ($iterable as $row){
            foreach($row as $rawprice){    
                if ($rawprice->getCode()){
                    $oems = $rawprice->getOemAsArray();            
                    if (is_array($oems)){
                        foreach ($oems as $oemCode){                    
                            $filteredCode = mb_strcut(trim($filter->filter($oemCode)), 0, 24, 'UTF-8');                    
                            try{
                                $inserted = $this->entityManager->getRepository(OemRaw::class)
                                        ->insertOemRaw([
                                            'code' => $filteredCode,
                                            'fullcode' => mb_substr($oemCode, 0, 36),
                                            'article_id' => $rawprice->getCode()->getId(),                                
                                        ]);
                            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e){ 
                                //дубликат;
                            }                        
                            $this->entityManager->getRepository(Rawprice::class)
                                    ->updateRawpriceField($rawprice->getId(), ['status_oem' => Rawprice::OEM_PARSED]);
                        }
                    }    
                }    
                $this->entityManager->detach($rawprice);
            }                
            if (time() > $startTime + 840){
                return;
            }            
        }
                
        $raw->setParseStage(Raw::STAGE_OEM_PARSED);
        $this->entityManager->persist($raw);        
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Удаление кода
     * 
     * @param Application\Entity\OemRaw $oemRaw
     */
    public function removeOemRaw($oemRaw) 
    {   
        $this->entityManager->remove($oemRaw);
        
        $this->entityManager->flush($oemRaw);
    }    
    
    /**
     * Поиск и удаление номеров не привязаных к строкам прайсов
     */
    public function removeEmpty()
    {
        ini_set('memory_limit', '2048M');
        
        $oemForDelete = $this->entityManager->getRepository(OemRaw::class)
                ->findOemRawForDelete();

        foreach ($oemForDelete as $row){
            $this->removeOemRaw($row[0], false);
        }
        
        $this->entityManager->flush();
        
        return count($oemForDelete);
    }    
    

    /**
     * Выборка из прайсов по id артикля и id поставщика 
     * @param array $params
     * @return object      
     */
    public function randRawpriceBy($params)
    {
        return $this->entityManager->getRepository(OemRaw::class)
                ->randRawpriceBy($params);
    }   
}
