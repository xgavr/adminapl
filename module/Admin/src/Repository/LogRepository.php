<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Repository;

use Doctrine\ORM\EntityRepository;
use Laminas\Json\Json;
use Stock\Entity\Ptu;
use Stock\Entity\PtuGood;
use Admin\Entity\Log;
use User\Entity\User;
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
     * @param integer $status 
     * @param integer $userId 
     */
    public function infoPtu($ptu, $status, $userId = 0)
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
                'user_id' => $userId,
            ];
            
            $this->getEntityManager()->getConnection()->insert('log', $data);
            return;
        }
        
        return;
    }           
}