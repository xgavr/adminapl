<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Repository;

use Doctrine\ORM\EntityRepository;
use Stock\Entity\Ptu;
use Stock\Entity\PtuGood;
use Admin\Entity\Log;
use Laminas\Serializer\Serializer;
use Laminas\Serializer\Adapter;
use Laminas\Serializer\Exception;

/**
 * Description of LogRepository
 *
 * @author Daddy
 */
class LogRepository extends EntityRepository{
    
    /**
     * Добавить запись в лог ptu
     * @param Ptu $ptu
     * @params integer $status 
     */
    public function infoPtu($ptu, $status)
    {
        $serializer = new Adapter\PhpSerialize();

        try {
            $serialized = $serializer->serialize($ptu);
        } catch (Exception\ExceptionInterface $e) {
            echo $e;
        }
        
        if (isset($serialized)){
            $data = [
                'logKey' => $ptu->getLogKey(),
                'message' => $serialized,
                'date_created' => date('Y-m-d H:i:s'),
                'status' => $status,
                'priority' => Log::PRIORITY_INFO,
                'user_id' => 
            ];
            
            $this->getEntityManager()->getConnection()->insert('log', $data);
            return;
        }
        
        return;
    }
    
}