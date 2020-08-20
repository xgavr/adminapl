<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Repository;

use Doctrine\ORM\EntityRepository;
use Admin\Entity\Log;
use Application\Entity\Rate;
use Application\Entity\Producer;
use Application\Entity\GenericGroup;
use Application\Entity\TokenGroup;
use Company\Entity\Office;
//use User\Entity\User;


/**
 * Description of LogRepository
 *
 * @author Daddy
 */
class LogRepository extends EntityRepository{
    
    
    /**
     * Текст лога
     * 
     * @param string $ident
     * @param array $message
     * 
     * @return string
     */
    private function messageText($ident, $message)
    {
        $entityManager = $this->getEntityManager();
        switch ($ident){
            case 'rate':
                $name = $message['name'];
                $statusName = Rate::getStatusName($message['status']);
                $modeName = Rate::getModeName($message['mode']);
                $param = '';
                if (!empty($message['producer'])){
                    $producer = $entityManager->getRepository(Producer::class)
                            ->findOneById($message['producer']);
                    $param = $producer->getName();
                }
                if (!empty($message['genericGroup'])){
                    $genericGroup = $entityManager->getRepository(GenericGroup::class)
                            ->findOneById($message['genericGroup']);
                    $param = $genericGroup->getName();
                }
                if (!empty($message['tokenGroup'])){
                    $tokenGroup = $entityManager->getRepository(TokenGroup::class)
                            ->findOneById($message['tokenGroup']);
                    $param = $tokenGroup->getName();
                }
                if (!empty($message['office'])){
                    $office = $entityManager->getRepository(Office::class)
                            ->findOneById($message['office']);
                    $officeName = $office->getName();
                }
                $result = trim("$name $officeName $param");
                break;
            default: break;
        }
        
        return;
    }
    
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
                'dateCreated' => date('Y-m-d H:i:s', strtotime($row->getDateCreated())),                
                'user' => $row->getUser()->getFullName(),
            ];
        }
                
        return $result;
        
    }
    
}