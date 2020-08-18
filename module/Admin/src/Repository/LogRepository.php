<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Repository;

use Doctrine\ORM\EntityRepository;
use Admin\Entity\Log;
//use User\Entity\User;


/**
 * Description of LogRepository
 *
 * @author Daddy
 */
class LogRepository extends EntityRepository{
    
    /**
     * Выбрать по типу документа
     * 
     * @param string $docType
     * @param array $options
     */
    public function findByDocType($docType, $options = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('l')
                ->from(Log::class, 'l')
                ->where("l.logKey like '$docType:%'")
//                ->setParameter(1, $docType)
                ->orderBy('l.id', 'DESC')
                ;
        if (is_array($options)){
            if (isset($options['limit'])){
                $queryBuilder->setMaxResults($options['limit']);
            }
        }
        
        $data = $queryBuilder->getQuery()->getResult();
        $result = [];
        foreach ($data as $row){
            $message = $row->getMessageAsArray();
            $result[$row->getId()] = [
                'id' => $row->getIdFromLogKey(),                
                'priority' => $row->getPriorityAsString(),                
                'status' => $row->getStatusAsString(),                
                'dateCreated' => date('d-m-y H:i:s', strtotime($row->getDateCreated())),                
                'user' => $row->getUser()->getFullName(),
            ];
        }
                
        return $result;
        
    }
    
}