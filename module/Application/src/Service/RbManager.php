<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Tax;
use Application\Entity\Country;
use Application\Entity\Producer;
use MvlabsPHPExcel\Service;

/**
 * Description of RbService
 *
 * @author Daddy
 */
class RbManager
{
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    public function addNewTax($data) 
    {
        // Создаем новую сущность Tax.
        $tax = new Tax();
        $tax->setName($data['name']);
        $tax->setAmount($data['amount']);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($tax);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }   
    
    public function updateTax($tax, $data) 
    {
        $tax->setName($data['name']);
        $tax->setAmount($data['amount']);
               
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    
    
    public function removeTax($tax) 
    {   
        $this->entityManager->remove($tax);
        
        $this->entityManager->flush();
    }    

    public function addNewCountry($data) 
    {
        // Создаем новую сущность Tax.
        $country = new Country();
        $country->setName($data['name']);
        $country->setFullname($data['fullname']);
        $country->setCode($data['code']);
        $country->setAlpha2($data['alpha2']);
        $country->setAlpha3($data['alpha3']);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($country);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }   
    
    public function updateCountry($country, $data) 
    {
        $country->setName($data['name']);
        $country->setFullname($data['fullname']);
        $country->setCode($data['code']);
        $country->setAlpha2($data['alpha2']);
        $country->setAlpha3($data['alpha3']);
               
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    
    
    public function removeCountry($country) 
    {   
        $this->entityManager->remove($country);
        
        $this->entityManager->flush();
    }
    
    public function uploadCountry(){
                
        $filename = './data/upload/oksm.xls';

        if (file_exists($filename)){
            $mvexcel = new Service\PhpExcelService();    
            $excel = $mvexcel->createPHPExcelObject($filename);
            
            $sheets = $excel->getAllSheets();
            foreach ($sheets as $sheet) { // PHPExcel_Worksheet

                $excel_sheet_content = $sheet->toArray();

                if (count($sheet)){
                    foreach ($excel_sheet_content as $row){
                        try{
                            if (is_numeric($row[0])){    
                                
                                $country = $this->entityManager->getRepository(Country::class)
                                    ->findOneBy(['code'=>$row[0]]);
                                if ($country == null){
                                    $country = new Country();
                                }
                                
                                $country->setName($row[1]);
                                $country->setFullName(($row[2]) ? $row[2]:$row[1]);
                                $country->setCode($row[0]);
                                $country->setAlpha2($row[3]);
                                $country->setAlpha3($row[4]);

                                // Добавляем сущность в менеджер сущностей.
                                $this->entityManager->persist($country);
                            }    

                        } catch (Exception $e){	
                        }	                                // if ($i++ > 100)	break;
                    }
                    // Применяем изменения к базе данных.
                    $this->entityManager->flush();                    
                }	
            }
        }
    }

    public function addNewProducer($data) 
    {
        // Создаем новую сущность Producer.
        $producer = new Producer();
        $producer->setName($data['name']);

        $country = $this->entityManager->getRepository(Country::class)
                    ->findOneById($data['country']);
        if ($country == null){
            $country = new Country();
        }

        $producer->setCountry($country);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($producer);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }   
    
    public function updateProducer($producer, $data) 
    {
        $producer->setName($data['name']);
        
        $country = $this->entityManager->getRepository(Country::class)
                    ->findOneById($data['country']);
        if ($country == null){
            $country = new Country();
        }
        
        $producer->setCountry($country);
               
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    
    
    public function removeProducer($producer) 
    {   
        $this->entityManager->remove($producer);
        
        $this->entityManager->flush();
    }    
    
}
